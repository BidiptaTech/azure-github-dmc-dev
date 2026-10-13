{{-- Included inside a parent <script> block (create/edit).
     Single city: classic #markupType / #discountType controls.
     Multi-city: one row per selected city in #enquiryProCityMarkupBody. --}}
(function () {
    if (typeof window.enquiryProCurrencyMarkups !== 'object' || window.enquiryProCurrencyMarkups === null) {
        window.enquiryProCurrencyMarkups = {};
    }
    /** @type {Object<string, object>} city-name keyed store */
    if (typeof window.enquiryProCityMarkups !== 'object' || window.enquiryProCityMarkups === null) {
        window.enquiryProCityMarkups = {};
    }

    function emptyMarkupDiscountEntry(opts) {
        opts = opts || {};
        return {
            city: String(opts.city || '').trim(),
            country: String(opts.country || '').trim(),
            currency: String(opts.currency || '').trim().toUpperCase(),
            markup_type: opts.markup_type || '',
            markup_value: parseFloat(opts.markup_value || 0) || 0,
            discount_type: opts.discount_type || '',
            discount_value: parseFloat(opts.discount_value || 0) || 0
        };
    }

    function storeKeyForEntry(entry) {
        const city = String(entry && entry.city || '').trim();
        if (city) return 'city:' + city.toLowerCase();
        const currency = String(entry && entry.currency || '').trim().toUpperCase();
        return currency ? ('cur:' + currency) : '';
    }

    /** One entry per selected destination city (multi-city markup rows). */
    function getEnquiryProCityMarkupTargets() {
        const cities = (typeof selectedDestinations !== 'undefined' && Array.isArray(selectedDestinations))
            ? selectedDestinations
            : [];
        return cities.map(function (cityName) {
            const city = String(cityName || '').trim();
            const country = (typeof resolveCountryForCity === 'function') ? resolveCountryForCity(city) : '';
            const currency = (typeof resolveCurrencyForCountry === 'function')
                ? String(resolveCurrencyForCountry(country) || '').trim().toUpperCase()
                : '';
            return { city: city, country: country || '', currency: currency };
        }).filter(function (x) { return !!x.city; });
    }
    window.getEnquiryProCityMarkupTargets = getEnquiryProCityMarkupTargets;

    function getEnquiryProAvailableCurrencies() {
        const seen = {};
        const out = [];
        getEnquiryProCityMarkupTargets().forEach(function (item) {
            const code = item.currency;
            if (!code || seen[code]) return;
            seen[code] = true;
            out.push({ currency: code, country: item.country, city: item.city });
        });
        return out;
    }
    window.getEnquiryProAvailableCurrencies = getEnquiryProAvailableCurrencies;

    function getEnquiryProPrimaryCurrency() {
        const list = getEnquiryProCityMarkupTargets();
        return list.length ? (list[0].currency || '') : '';
    }
    window.getEnquiryProPrimaryCurrency = getEnquiryProPrimaryCurrency;

    function isEnquiryProMultiCity() {
        return getEnquiryProCityMarkupTargets().length > 1;
    }
    window.isEnquiryProMultiCity = isEnquiryProMultiCity;

    function resolveServiceCurrency(service) {
        if (!service || typeof service !== 'object') {
            return getEnquiryProPrimaryCurrency();
        }
        let currency = String(service.currency || '').trim().toUpperCase();
        if (currency) return currency;
        const country = String(service.country || '').trim()
            || ((typeof resolveCountryForCity === 'function')
                ? resolveCountryForCity(service.destination || service.city || service.hotelCity || '')
                : '');
        if (typeof resolveCurrencyForCountry === 'function' && country) {
            currency = String(resolveCurrencyForCountry(country) || '').trim().toUpperCase();
        }
        return currency || getEnquiryProPrimaryCurrency();
    }
    window.resolveServiceCurrency = resolveServiceCurrency;

    function resolveServiceCity(service) {
        if (!service || typeof service !== 'object') return '';
        return String(
            service.destination || service.city || service.hotelCity || service.hotel_city || ''
        ).trim();
    }
    window.resolveServiceCity = resolveServiceCity;

    function readSingleMarkupDiscountInputs() {
        const focHdr = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
        const focDiscountUiActive = focHdr && focHdr.isGroup && focHdr.focSize > 0 && focHdr.discountOn;
        const discountType = document.getElementById('discountType')?.value || '';
        let discountValue = parseFloat(document.getElementById('discountValue')?.value || 0) || 0;
        if (discountType === 'foc' && focDiscountUiActive && typeof computeAutoFocDiscount === 'function') {
            discountValue = computeAutoFocDiscount();
        } else if (discountType === 'foc') {
            discountValue = 0;
        }
        return {
            markup_type: document.getElementById('markupType')?.value || '',
            markup_value: parseFloat(document.getElementById('markupValue')?.value || 0) || 0,
            discount_type: discountType,
            discount_value: discountValue
        };
    }

    function writeSingleMarkupDiscountInputs(entry) {
        const markupType = document.getElementById('markupType');
        const markupValue = document.getElementById('markupValue');
        const discountType = document.getElementById('discountType');
        const discountValue = document.getElementById('discountValue');
        if (!markupType || !markupValue || !discountType || !discountValue) return;

        const mt = entry && entry.markup_type ? String(entry.markup_type) : '';
        const mv = entry && entry.markup_value != null ? parseFloat(entry.markup_value) || 0 : 0;
        const dt = entry && entry.discount_type ? String(entry.discount_type) : '';
        const dv = entry && entry.discount_value != null ? parseFloat(entry.discount_value) || 0 : 0;

        window._enquiryProCurrencyMdLoading = true;
        try {
            markupType.value = mt;
            markupValue.value = mv;
            markupValue.disabled = !mt;
            discountType.value = dt;
            if (dt === '') {
                discountValue.disabled = true;
                discountValue.value = 0;
                discountValue.classList.remove('is-foc-locked');
                discountValue.style.backgroundColor = '';
            } else if (dt === 'foc') {
                discountValue.disabled = true;
                discountValue.classList.add('is-foc-locked');
                discountValue.style.backgroundColor = '';
                if (typeof computeAutoFocDiscount === 'function') {
                    const focHdr = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
                    const active = focHdr && focHdr.isGroup && focHdr.focSize > 0 && focHdr.discountOn;
                    discountValue.value = active ? computeAutoFocDiscount() : 0;
                } else {
                    discountValue.value = dv;
                }
            } else {
                discountValue.disabled = false;
                discountValue.classList.remove('is-foc-locked');
                discountValue.style.backgroundColor = '';
                discountValue.value = dv;
            }
        } finally {
            window._enquiryProCurrencyMdLoading = false;
        }
    }

    function syncMultiCityRowsToStore() {
        const body = document.getElementById('enquiryProCityMarkupBody');
        if (!body) return;
        body.querySelectorAll('tr[data-city]').forEach(function (tr) {
            const city = String(tr.getAttribute('data-city') || '').trim();
            if (!city) return;
            const country = String(tr.getAttribute('data-country') || '').trim();
            const currency = String(tr.getAttribute('data-currency') || '').trim().toUpperCase();
            const mt = tr.querySelector('.city-markup-type')?.value || '';
            const mv = parseFloat(tr.querySelector('.city-markup-value')?.value || 0) || 0;
            let dt = tr.querySelector('.city-discount-type')?.value || '';
            let dv = parseFloat(tr.querySelector('.city-discount-value')?.value || 0) || 0;
            if (dt === 'foc' && typeof computeAutoFocDiscount === 'function') {
                const focHdr = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
                const active = focHdr && focHdr.isGroup && focHdr.focSize > 0 && focHdr.discountOn;
                dv = active ? computeAutoFocDiscount() : 0;
                const inp = tr.querySelector('.city-discount-value');
                if (inp) inp.value = dv;
            }
            const entry = emptyMarkupDiscountEntry({
                city: city,
                country: country,
                currency: currency,
                markup_type: mt,
                markup_value: mv,
                discount_type: dt,
                discount_value: dv
            });
            window.enquiryProCityMarkups[city] = entry;
            if (currency) {
                window.enquiryProCurrencyMarkups[currency] = Object.assign({}, entry);
            }
        });
    }

    function syncActiveCurrencyMarkupToStore() {
        if (window._enquiryProCurrencyMdLoading) return;
        if (isEnquiryProMultiCity()) {
            syncMultiCityRowsToStore();
            return;
        }
        const targets = getEnquiryProCityMarkupTargets();
        const target = targets[0] || { city: '', country: '', currency: getEnquiryProPrimaryCurrency() };
        const inputs = readSingleMarkupDiscountInputs();
        const entry = emptyMarkupDiscountEntry(Object.assign({}, target, inputs));
        if (entry.city) {
            window.enquiryProCityMarkups[entry.city] = entry;
        }
        if (entry.currency) {
            window.enquiryProCurrencyMarkups[entry.currency] = entry;
        }
    }
    window.syncActiveCurrencyMarkupToStore = syncActiveCurrencyMarkupToStore;

    function getCurrencyMarkupDiscountSettings(currency, city) {
        const cityName = String(city || '').trim();
        if (cityName && window.enquiryProCityMarkups && window.enquiryProCityMarkups[cityName]) {
            const e = window.enquiryProCityMarkups[cityName];
            return {
                markup_type: e.markup_type || '',
                markup_value: parseFloat(e.markup_value || 0) || 0,
                discount_type: e.discount_type || '',
                discount_value: parseFloat(e.discount_value || 0) || 0
            };
        }
        // Case-insensitive city match
        if (cityName && window.enquiryProCityMarkups) {
            const lower = cityName.toLowerCase();
            const matchedKey = Object.keys(window.enquiryProCityMarkups).find(function (k) {
                return String(k).toLowerCase() === lower;
            });
            if (matchedKey) {
                const e = window.enquiryProCityMarkups[matchedKey];
                return {
                    markup_type: e.markup_type || '',
                    markup_value: parseFloat(e.markup_value || 0) || 0,
                    discount_type: e.discount_type || '',
                    discount_value: parseFloat(e.discount_value || 0) || 0
                };
            }
        }
        const key = String(currency || '').trim().toUpperCase();
        if (key && window.enquiryProCurrencyMarkups && window.enquiryProCurrencyMarkups[key]) {
            const e = window.enquiryProCurrencyMarkups[key];
            return {
                markup_type: e.markup_type || '',
                markup_value: parseFloat(e.markup_value || 0) || 0,
                discount_type: e.discount_type || '',
                discount_value: parseFloat(e.discount_value || 0) || 0
            };
        }
        if (!isEnquiryProMultiCity()) {
            return readSingleMarkupDiscountInputs();
        }
        return { markup_type: '', markup_value: 0, discount_type: '', discount_value: 0 };
    }
    window.getCurrencyMarkupDiscountSettings = getCurrencyMarkupDiscountSettings;

    function getCurrencyMarkupsPayload() {
        syncActiveCurrencyMarkupToStore();
        const targets = getEnquiryProCityMarkupTargets();
        if (!targets.length) {
            return Object.keys(window.enquiryProCityMarkups || {}).map(function (city) {
                return window.enquiryProCityMarkups[city];
            }).filter(Boolean);
        }
        return targets.map(function (t) {
            const existing = window.enquiryProCityMarkups[t.city] || {};
            return emptyMarkupDiscountEntry({
                city: t.city,
                country: t.country || existing.country || '',
                currency: t.currency || existing.currency || '',
                markup_type: existing.markup_type || '',
                markup_value: existing.markup_value || 0,
                discount_type: existing.discount_type || '',
                discount_value: existing.discount_value || 0
            });
        });
    }
    window.getCurrencyMarkupsPayload = getCurrencyMarkupsPayload;

    function buildCityMarkupRowHtml(target, entry) {
        const city = target.city;
        const country = target.country || '';
        const currency = target.currency || '';
        const mt = entry.markup_type || '';
        const mv = entry.markup_value || 0;
        const dt = entry.discount_type || '';
        const dv = entry.discount_value || 0;
        const markupDisabled = mt ? '' : 'disabled';
        const discountDisabled = (!dt || dt === 'foc') ? 'disabled' : '';
        const focClass = dt === 'foc' ? ' is-foc-locked' : '';
        const label = currency
            ? (city + ' · ' + currency + (country ? ' (' + country + ')' : ''))
            : city;

        return ''
            + '<tr data-city="' + escapeHtmlAttr(city) + '" data-country="' + escapeHtmlAttr(country) + '" data-currency="' + escapeHtmlAttr(currency) + '">'
            + '<td>'
            + '<div class="enquiry-md-city" title="' + escapeHtmlAttr(label) + '">'
            + '<span class="enquiry-md-city__name">' + escapeHtml(city) + '</span>'
            + (country ? '<span class="enquiry-md-city__meta">' + escapeHtml(country) + '</span>' : '')
            + '</div>'
            + '</td>'
            + '<td><span class="enquiry-md-badge">' + escapeHtml(currency || '—') + '</span></td>'
            + '<td class="enquiry-md-cell-markup">'
            + '<select class="city-markup-type enquiry-md-control" onchange="handleCityMarkupRowChange(this)">'
            + '<option value=""' + (!mt ? ' selected' : '') + '>Type</option>'
            + '<option value="percentage"' + (mt === 'percentage' ? ' selected' : '') + '>%</option>'
            + '<option value="flat"' + (mt === 'flat' ? ' selected' : '') + '>Fixed</option>'
            + '</select>'
            + '</td>'
            + '<td class="enquiry-md-cell-markup">'
            + '<input type="number" class="city-markup-value enquiry-md-control" value="' + mv + '" step="1" min="0" ' + markupDisabled
            + ' placeholder="0" oninput="handleCityMarkupRowChange(this)">'
            + '</td>'
            + '<td class="enquiry-md-cell-discount">'
            + '<select class="city-discount-type enquiry-md-control" onchange="handleCityMarkupRowChange(this)">'
            + '<option value=""' + (!dt ? ' selected' : '') + '>Type</option>'
            + '<option value="percentage"' + (dt === 'percentage' ? ' selected' : '') + '>%</option>'
            + '<option value="flat"' + (dt === 'flat' ? ' selected' : '') + '>Fixed</option>'
            + '<option value="foc"' + (dt === 'foc' ? ' selected' : '') + '>FOC</option>'
            + '</select>'
            + '</td>'
            + '<td class="enquiry-md-cell-discount">'
            + '<input type="number" class="city-discount-value enquiry-md-control' + focClass + '" value="' + dv + '" step="1" min="0" ' + discountDisabled
            + ' placeholder="0" oninput="handleCityMarkupRowChange(this)"'
            + ' title="Discount value. FOC is auto-computed when Treat FOC is on.">'
            + '</td>'
            + '</tr>';
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
    function escapeHtmlAttr(str) {
        return escapeHtml(str).replace(/'/g, '&#39;');
    }

    function handleCityMarkupRowChange(el) {
        const tr = el && el.closest ? el.closest('tr[data-city]') : null;
        if (tr) {
            const mt = tr.querySelector('.city-markup-type');
            const mv = tr.querySelector('.city-markup-value');
            const dt = tr.querySelector('.city-discount-type');
            const dv = tr.querySelector('.city-discount-value');
            if (mv && mt) {
                mv.disabled = !mt.value;
                if (!mt.value) mv.value = 0;
            }
            if (dv && dt) {
                if (!dt.value) {
                    dv.disabled = true;
                    dv.value = 0;
                    dv.classList.remove('is-foc-locked');
                    dv.style.backgroundColor = '';
                } else if (dt.value === 'foc') {
                    dv.disabled = true;
                    dv.classList.add('is-foc-locked');
                    dv.style.backgroundColor = '';
                    if (typeof computeAutoFocDiscount === 'function') {
                        const focHdr = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
                        const active = focHdr && focHdr.isGroup && focHdr.focSize > 0 && focHdr.discountOn;
                        dv.value = active ? computeAutoFocDiscount() : 0;
                    }
                } else {
                    dv.disabled = false;
                    dv.classList.remove('is-foc-locked');
                    dv.style.backgroundColor = '';
                }
            }
        }
        syncMultiCityRowsToStore();
        if (typeof applyMarkupDiscount === 'function') applyMarkupDiscount();
    }
    window.handleCityMarkupRowChange = handleCityMarkupRowChange;

    function toggleEnquiryMdAccordion(headEl) {
        const panel = headEl && headEl.closest ? headEl.closest('.enquiry-md-panel') : null;
        if (!panel) return;
        const collapsed = panel.classList.toggle('is-collapsed');
        headEl.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }
    window.toggleEnquiryMdAccordion = toggleEnquiryMdAccordion;

    function refreshEnquiryProCurrencyMarkupOptions() {
        const singleWrap = document.getElementById('enquiryProMarkupSingleWrap');
        const multiWrap = document.getElementById('enquiryProMarkupMultiWrap');
        const body = document.getElementById('enquiryProCityMarkupBody');
        const targets = getEnquiryProCityMarkupTargets();
        const multi = targets.length > 1;

        if (singleWrap) singleWrap.style.display = multi ? 'none' : 'block';
        if (multiWrap) multiWrap.style.display = multi ? 'block' : 'none';

        const countEl = document.getElementById('enquiryProMarkupCityCount');
        if (countEl) countEl.textContent = String(targets.length || 0);

        if (!multi) {
            // Keep single-city controls in sync with store for the one city
            if (targets[0]) {
                const t = targets[0];
                if (!window.enquiryProCityMarkups[t.city]) {
                    // Seed from current single inputs if store empty
                    const inputs = readSingleMarkupDiscountInputs();
                    window.enquiryProCityMarkups[t.city] = emptyMarkupDiscountEntry(Object.assign({}, t, inputs));
                    if (t.currency) {
                        window.enquiryProCurrencyMarkups[t.currency] = window.enquiryProCityMarkups[t.city];
                    }
                } else {
                    writeSingleMarkupDiscountInputs(window.enquiryProCityMarkups[t.city]);
                }
            }
            return;
        }

        if (!body) return;

        // Preserve current row edits before rebuild
        syncMultiCityRowsToStore();

        body.innerHTML = targets.map(function (t) {
            const entry = window.enquiryProCityMarkups[t.city] || emptyMarkupDiscountEntry(t);
            // Ensure currency/country stay current if city map changed
            entry.city = t.city;
            entry.country = t.country || entry.country || '';
            entry.currency = t.currency || entry.currency || '';
            window.enquiryProCityMarkups[t.city] = entry;
            if (entry.currency) {
                window.enquiryProCurrencyMarkups[entry.currency] = Object.assign({}, entry);
            }
            return buildCityMarkupRowHtml(t, entry);
        }).join('');
    }
    window.refreshEnquiryProCurrencyMarkupOptions = refreshEnquiryProCurrencyMarkupOptions;

    function initEnquiryProCurrencyMarkupUi(seed) {
        window.enquiryProCityMarkups = {};
        window.enquiryProCurrencyMarkups = {};
        if (seed && typeof seed === 'object') {
            const list = Array.isArray(seed) ? seed : Object.keys(seed).map(function (k) {
                const row = seed[k] || {};
                if (!row.currency && !row.city) {
                    return Object.assign({}, row, { currency: k });
                }
                return row;
            });
            list.forEach(function (row) {
                const entry = emptyMarkupDiscountEntry(row);
                if (entry.city) {
                    window.enquiryProCityMarkups[entry.city] = entry;
                }
                if (entry.currency) {
                    window.enquiryProCurrencyMarkups[entry.currency] = entry;
                }
            });
        }
        refreshEnquiryProCurrencyMarkupOptions();
    }
    window.initEnquiryProCurrencyMarkupUi = initEnquiryProCurrencyMarkupUi;

    // Back-compat stubs (old currency dropdown API)
    window.handleMarkupCurrencyChange = function () {};
    window.bindMarkupCurrencySelect = function () {};

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            if (window._enquiryProCurrencyMarkupsSeed) {
                initEnquiryProCurrencyMarkupUi(window._enquiryProCurrencyMarkupsSeed);
                window._enquiryProCurrencyMarkupsSeed = null;
            } else {
                refreshEnquiryProCurrencyMarkupOptions();
            }
        });
    } else {
        setTimeout(function () {
            if (window._enquiryProCurrencyMarkupsSeed) {
                initEnquiryProCurrencyMarkupUi(window._enquiryProCurrencyMarkupsSeed);
                window._enquiryProCurrencyMarkupsSeed = null;
            } else {
                refreshEnquiryProCurrencyMarkupOptions();
            }
        }, 0);
    }
})();
