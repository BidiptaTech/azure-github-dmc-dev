{{-- Included inside a parent <script> block (create/edit).
     Markup UI removed — hotel/other values come from DMC profile markup_json (Lite parity).
     FOC discount still auto-applies when "Treat FOC pax as discount" is on. --}}
(function () {
    if (typeof window.enquiryProCurrencyMarkups !== 'object' || window.enquiryProCurrencyMarkups === null) {
        window.enquiryProCurrencyMarkups = {};
    }
    /** @type {Object<string, object>} city-name keyed store */
    if (typeof window.enquiryProCityMarkups !== 'object' || window.enquiryProCityMarkups === null) {
        window.enquiryProCityMarkups = {};
    }

    function enquiryProDmcProfileMarkup() {
        return window.enquiryProDmcProfileMarkup || {
            hotel: { markup_type: 'percentage', markup_value: 0 },
            other: { markup_type: 'percentage', markup_value: 0 },
            dmc_id: null
        };
    }

    function enquiryProNormalizeMarkupType(type) {
        type = String(type || 'percentage').toLowerCase().trim();
        if (type === 'fixed') type = 'flat';
        return (type === 'flat' || type === 'percentage') ? type : 'percentage';
    }

    /** DMC hotel + other markup rules used when seeding city rows / footer apply. */
    function enquiryProDmcMarkupDefaults() {
        const profile = enquiryProDmcProfileMarkup();
        const hotel = profile.hotel || {};
        const other = profile.other || {};
        const hotelType = enquiryProNormalizeMarkupType(hotel.markup_type);
        const otherType = enquiryProNormalizeMarkupType(other.markup_type);
        // currency_markups row has one markup_type; prefer hotel type when both set.
        const sharedType = hotelType || otherType || 'percentage';
        return {
            markup_type: sharedType,
            hotel_markup: Math.max(0, Number(hotel.markup_value) || 0),
            other_markup: Math.max(0, Number(other.markup_value) || 0),
            markup_value: Math.max(0, Number(hotel.markup_value) || 0) + Math.max(0, Number(other.markup_value) || 0),
            hotel_markup_type: hotelType,
            other_markup_type: otherType,
            dmc_id: profile.dmc_id || null
        };
    }
    window.enquiryProDmcMarkupDefaults = enquiryProDmcMarkupDefaults;

    function syncHiddenMarkupInputsFromDmc() {
        const d = enquiryProDmcMarkupDefaults();
        const mt = document.getElementById('markupType');
        const mv = document.getElementById('markupValue');
        if (mt) mt.value = d.markup_type || 'percentage';
        if (mv) {
            mv.disabled = false;
            mv.value = d.hotel_markup || 0;
        }
    }

    function enquiryProNormalizeCityLabel(name) {
        return String(name || '').trim().toLowerCase().replace(/\s+/g, ' ');
    }

    function enquiryProCitiesMatch(a, b) {
        const left = enquiryProNormalizeCityLabel(a);
        const right = enquiryProNormalizeCityLabel(b);
        if (!left || !right) return false;
        return left === right || left.indexOf(right) !== -1 || right.indexOf(left) !== -1;
    }

    function enquiryProServiceMatchesCity(item, cityFilter) {
        const want = String(cityFilter || '').trim();
        if (!want) return true;
        if (!item || typeof item !== 'object') return false;
        const candidates = [];
        if (typeof getHotelServiceCity === 'function') {
            const hotelCity = getHotelServiceCity(item);
            if (hotelCity) candidates.push(hotelCity);
        }
        if (typeof resolveServiceCity === 'function') {
            const resolved = resolveServiceCity(item);
            if (resolved) candidates.push(resolved);
        }
        [
            item.destination, item.city, item.hotelCity, item.hotel_city,
            item.hotelCityKey, item.hotelDestination, item.city_name, item.location
        ].forEach(function (v) {
            const s = String(v || '').trim();
            if (s) candidates.push(s);
        });
        for (let i = 0; i < candidates.length; i++) {
            if (enquiryProCitiesMatch(candidates[i], want)) return true;
        }
        return false;
    }
    window.enquiryProServiceMatchesCity = enquiryProServiceMatchesCity;

    function isTreatFocDiscountActive() {
        const f = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
        return !!(f && f.isGroup && f.focSize > 0 && f.discountOn);
    }
    window.isTreatFocDiscountActive = isTreatFocDiscountActive;

    function snapshotServerFocDiscountByCity() {
        window._enquiryProServerFocByCity = window._enquiryProServerFocByCity || {};
        const entries = window.enquiryProCityMarkups || {};
        Object.keys(entries).forEach(function (city) {
            const row = entries[city] || {};
            if (String(row.discount_type || '').toLowerCase() !== 'foc') return;
            const value = parseFloat(row.discount_value || 0) || 0;
            if (value > 0) {
                window._enquiryProServerFocByCity[String(city).trim()] = value;
            }
        });
        window._enquiryProFocDiscountFromServer = Object.keys(window._enquiryProServerFocByCity).length > 0;
    }

    function lookupCityAmount(map, city) {
        if (!map || typeof map !== 'object') return 0;
        const name = String(city || '').trim();
        if (!name) return 0;
        if (map[name] != null && map[name] !== '') return parseFloat(map[name]) || 0;
        const lower = name.toLowerCase();
        const key = Object.keys(map).find(function (k) {
            return String(k).toLowerCase() === lower;
        });
        return key ? (parseFloat(map[key]) || 0) : 0;
    }

    function storedFocDiscountForCity(city) {
        return lookupCityAmount(window._enquiryProServerFocByCity, city);
    }

    /**
     * Create: live FOC total.
     * Edit: saved city FOC + (live now − live at load) so add/remove/FOC toggles match create.
     */
    function focDiscountAmountForCity(city) {
        if (!isTreatFocDiscountActive()) return 0;
        const live = (typeof computeAutoFocDiscount === 'function')
            ? (computeAutoFocDiscount(city || '') || 0)
            : 0;
        const stored = storedFocDiscountForCity(city);
        if (stored > 0) {
            if (!window._enquiryProFocBaselineCaptured) return stored;
            const base = lookupCityAmount(window._enquiryProFocLiveBaseline, city);
            return Math.max(0, Math.round(stored + live - base));
        }
        return live;
    }

    function captureFocLiveBaseline(opts) {
        opts = opts || {};
        if (window._enquiryProFocBaselineCaptured && !opts.force) return;
        if (typeof computeAutoFocDiscount !== 'function') return;
        const targets = (typeof getEnquiryProCityMarkupTargets === 'function')
            ? getEnquiryProCityMarkupTargets()
            : [];
        if (!targets.length) return;
        const map = {};
        targets.forEach(function (t) {
            map[t.city] = computeAutoFocDiscount(t.city) || 0;
        });
        window._enquiryProFocLiveBaseline = map;
        window._enquiryProFocBaselineCaptured = true;
    }
    window.captureFocLiveBaseline = captureFocLiveBaseline;

    function ensureFocOption(selectEl, visible) {
        if (!selectEl) return null;
        let opt = selectEl.querySelector('option[value="foc"]');
        if (!opt) {
            opt = document.createElement('option');
            opt.value = 'foc';
            opt.textContent = 'FOC';
            selectEl.appendChild(opt);
        }
        opt.hidden = !visible;
        return opt;
    }

    /**
     * FOC is not a manual Disc type. It is applied only when
     * "Treat FOC pax as discount (free)" is on, per city.
     */
    function applyTreatFocToDiscountSelect(typeSel, valInp, city, active) {
        if (!typeSel || !valInp) return;
        if (active) {
            ensureFocOption(typeSel, true);
            typeSel.value = 'foc';
            typeSel.disabled = true;
            valInp.disabled = true;
            valInp.classList.add('is-foc-locked');
            valInp.style.backgroundColor = '';
            valInp.value = focDiscountAmountForCity(city);
            valInp.title = city
                ? ('Auto FOC discount for ' + city + ' (Treat FOC pax as discount). Locked.')
                : 'Auto FOC discount from Treat FOC pax as discount (free). Locked.';
            return;
        }
        typeSel.disabled = false;
        if (typeSel.value === 'foc') {
            ensureFocOption(typeSel, true);
            typeSel.value = '';
            valInp.value = 0;
            valInp.disabled = true;
            valInp.classList.remove('is-foc-locked');
            valInp.style.backgroundColor = '';
            valInp.title = 'Discount value. FOC is applied only when Treat FOC pax as discount is on.';
        }
        ensureFocOption(typeSel, false);
    }

    function applyTreatFocDiscountToPricingUi() {
        if (window._enquiryProApplyingTreatFoc) return;
        window._enquiryProApplyingTreatFoc = true;
        try {
        const active = isTreatFocDiscountActive();
        const dt = document.getElementById('discountType');
        const dv = document.getElementById('discountValue');
        const targets = (typeof getEnquiryProCityMarkupTargets === 'function')
            ? getEnquiryProCityMarkupTargets()
            : [];
        const singleCity = targets.length === 1 ? targets[0].city : '';

        applyTreatFocToDiscountSelect(dt, dv, singleCity, active);

        document.querySelectorAll('#enquiryProCityMarkupBody tr[data-city]').forEach(function (tr) {
            const city = String(tr.getAttribute('data-city') || '').trim();
            applyTreatFocToDiscountSelect(
                tr.querySelector('.city-discount-type'),
                tr.querySelector('.city-discount-value'),
                city,
                active
            );
        });

        if (typeof syncActiveCurrencyMarkupToStore === 'function') {
            syncActiveCurrencyMarkupToStore();
        }
        } finally {
            window._enquiryProApplyingTreatFoc = false;
        }
    }
    window.applyTreatFocDiscountToPricingUi = applyTreatFocDiscountToPricingUi;

    function emptyMarkupDiscountEntry(opts) {
        opts = opts || {};
        const hotelMarkup = opts.hotel_markup != null
            ? (parseFloat(opts.hotel_markup) || 0)
            : (opts.markup_value != null ? (parseFloat(opts.markup_value) || 0) : 0);
        const otherMarkup = opts.other_markup != null
            ? (parseFloat(opts.other_markup) || 0)
            : 0;
        // Always prefer hotel + other when either split field is present
        const markupValue = (opts.hotel_markup != null || opts.other_markup != null)
            ? (hotelMarkup + otherMarkup)
            : (opts.markup_value != null ? (parseFloat(opts.markup_value) || 0) : (hotelMarkup + otherMarkup));
        return {
            city: String(opts.city || '').trim(),
            country: String(opts.country || '').trim(),
            currency: String(opts.currency || '').trim().toUpperCase(),
            markup_type: opts.markup_type || '',
            markup_value: markupValue,
            hotel_markup: hotelMarkup,
            other_markup: otherMarkup,
            hotel_markup_type: opts.hotel_markup_type || opts.markup_type || '',
            other_markup_type: opts.other_markup_type || opts.markup_type || '',
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
            const country = (typeof window.resolveCountryForCity === 'function')
                ? String(window.resolveCountryForCity(city) || '').trim()
                : ((typeof resolveCountryForCity === 'function') ? String(resolveCountryForCity(city) || '').trim() : '');
            let currency = '';
            if (typeof window.resolveCurrencyForCityName === 'function') {
                currency = String(window.resolveCurrencyForCityName(city, country) || '').trim().toUpperCase();
            } else if (typeof resolveCurrencyForCountry === 'function') {
                currency = String(resolveCurrencyForCountry(country) || '').trim().toUpperCase();
            }
            return { city: city, country: country || '', currency: currency };
        }).filter(function (x) {
            return !!x.city && String(x.city).indexOf('__lite_markup_pad__') !== 0;
        });
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
        const dmc = enquiryProDmcMarkupDefaults();
        syncHiddenMarkupInputsFromDmc();
        const focHdr = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
        const focDiscountUiActive = focHdr && focHdr.isGroup && focHdr.focSize > 0 && focHdr.discountOn;
        let discountType = document.getElementById('discountType')?.value || '';
        let discountValue = parseFloat(document.getElementById('discountValue')?.value || 0) || 0;
        if (focDiscountUiActive) {
            discountType = 'foc';
            const singleCity = (getEnquiryProCityMarkupTargets()[0] || {}).city || '';
            discountValue = focDiscountAmountForCity(singleCity);
        } else if (discountType === 'foc') {
            discountType = '';
            discountValue = 0;
        }
        return {
            markup_type: dmc.markup_type || 'percentage',
            markup_value: dmc.markup_value || 0,
            hotel_markup: dmc.hotel_markup || 0,
            other_markup: dmc.other_markup || 0,
            hotel_markup_type: dmc.hotel_markup_type,
            other_markup_type: dmc.other_markup_type,
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
                if (typeof focDiscountAmountForCity === 'function') {
                    const focHdr = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
                    const active = focHdr && focHdr.isGroup && focHdr.focSize > 0 && focHdr.discountOn;
                    const singleCity = (getEnquiryProCityMarkupTargets()[0] || {}).city || '';
                    discountValue.value = active ? focDiscountAmountForCity(singleCity) : 0;
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
            const hotelEl = tr.querySelector('.city-hotel-markup');
            const otherEl = tr.querySelector('.city-other-markup');
            const markupEl = tr.querySelector('.city-markup-value');
            const hotelMk = hotelEl
                ? (parseFloat(hotelEl.value || 0) || 0)
                : (parseFloat(markupEl?.value || 0) || 0);
            const otherMk = otherEl
                ? (parseFloat(otherEl.value || 0) || 0)
                : 0;
            const mv = hotelMk + otherMk;
            let dt = tr.querySelector('.city-discount-type')?.value || '';
            let dv = parseFloat(tr.querySelector('.city-discount-value')?.value || 0) || 0;
            if (dt === 'foc') {
                const focHdr = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
                const active = focHdr && focHdr.isGroup && focHdr.focSize > 0 && focHdr.discountOn;
                dv = active ? focDiscountAmountForCity(city) : 0;
                const inp = tr.querySelector('.city-discount-value');
                if (inp) inp.value = dv;
            }
            const entry = emptyMarkupDiscountEntry({
                city: city,
                country: country,
                currency: currency,
                markup_type: mt,
                markup_value: mv,
                hotel_markup: hotelMk,
                other_markup: otherMk,
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
        const body = document.getElementById('enquiryProCityMarkupBody');
        if (body && body.querySelector('tr[data-city]')) {
            syncMultiCityRowsToStore();
            return;
        }
        // Pricing-by-city UI removed — always seed city/currency store from DMC profile
        const dmc = enquiryProDmcMarkupDefaults();
        const treatFoc = isTreatFocDiscountActive();
        const targets = getEnquiryProCityMarkupTargets();
        const list = targets.length
            ? targets
            : [{ city: '', country: '', currency: getEnquiryProPrimaryCurrency() }];
        list.forEach(function (t) {
            const entry = emptyMarkupDiscountEntry({
                city: t.city || '',
                country: t.country || '',
                currency: t.currency || '',
                markup_type: dmc.markup_type || 'percentage',
                markup_value: dmc.markup_value || 0,
                hotel_markup: dmc.hotel_markup || 0,
                other_markup: dmc.other_markup || 0,
                hotel_markup_type: dmc.hotel_markup_type,
                other_markup_type: dmc.other_markup_type,
                discount_type: treatFoc ? 'foc' : '',
                discount_value: treatFoc ? focDiscountAmountForCity(t.city || '') : 0
            });
            if (entry.city) {
                window.enquiryProCityMarkups[entry.city] = entry;
            }
            if (entry.currency) {
                window.enquiryProCurrencyMarkups[entry.currency] = entry;
            }
        });
        syncHiddenMarkupInputsFromDmc();
    }
    window.syncActiveCurrencyMarkupToStore = syncActiveCurrencyMarkupToStore;

    function getCurrencyMarkupDiscountSettings(currency, city) {
        const cityName = String(city || '').trim();
        if (cityName && window.enquiryProCityMarkups && window.enquiryProCityMarkups[cityName]) {
            const e = window.enquiryProCityMarkups[cityName];
            return {
                markup_type: e.markup_type || '',
                markup_value: parseFloat(e.markup_value || 0) || 0,
                hotel_markup: e.hotel_markup != null ? (parseFloat(e.hotel_markup) || 0) : (parseFloat(e.markup_value || 0) || 0),
                other_markup: e.other_markup != null ? (parseFloat(e.other_markup) || 0) : 0,
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
                    hotel_markup: e.hotel_markup != null ? (parseFloat(e.hotel_markup) || 0) : (parseFloat(e.markup_value || 0) || 0),
                    other_markup: e.other_markup != null ? (parseFloat(e.other_markup) || 0) : 0,
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
                hotel_markup: e.hotel_markup != null ? (parseFloat(e.hotel_markup) || 0) : (parseFloat(e.markup_value || 0) || 0),
                other_markup: e.other_markup != null ? (parseFloat(e.other_markup) || 0) : 0,
                discount_type: e.discount_type || '',
                discount_value: parseFloat(e.discount_value || 0) || 0
            };
        }
        // Always fall back to live DMC hotel/other markups (Pricing-by-city UI removed)
        const dmc = enquiryProDmcMarkupDefaults();
        const treatFoc = isTreatFocDiscountActive();
        return {
            markup_type: dmc.markup_type || 'percentage',
            markup_value: dmc.markup_value || 0,
            hotel_markup: dmc.hotel_markup || 0,
            other_markup: dmc.other_markup || 0,
            hotel_markup_type: dmc.hotel_markup_type,
            other_markup_type: dmc.other_markup_type,
            discount_type: treatFoc ? 'foc' : '',
            discount_value: treatFoc ? focDiscountAmountForCity(cityName) : 0
        };
    }
    window.getCurrencyMarkupDiscountSettings = getCurrencyMarkupDiscountSettings;

    function getCurrencyMarkupsPayload() {
        syncActiveCurrencyMarkupToStore();
        const dmc = enquiryProDmcMarkupDefaults();
        syncHiddenMarkupInputsFromDmc();
        const targets = getEnquiryProCityMarkupTargets();
        const buildRow = function (t, existing) {
            existing = existing || {};
            const treatFoc = isTreatFocDiscountActive();
            return emptyMarkupDiscountEntry({
                city: t.city,
                country: t.country || existing.country || '',
                currency: t.currency || existing.currency || '',
                // Always use DMC profile hotel/other markups (Lite parity)
                markup_type: dmc.markup_type || 'percentage',
                markup_value: dmc.markup_value || 0,
                hotel_markup: dmc.hotel_markup || 0,
                other_markup: dmc.other_markup || 0,
                hotel_markup_type: dmc.hotel_markup_type,
                other_markup_type: dmc.other_markup_type,
                discount_type: treatFoc ? 'foc' : '',
                discount_value: treatFoc ? focDiscountAmountForCity(t.city) : 0
            });
        };
        if (!targets.length) {
            const cities = Object.keys(window.enquiryProCityMarkups || {});
            if (!cities.length) {
                // Still persist DMC markups once (city filled later / single destination)
                return [buildRow({ city: '', country: '', currency: getEnquiryProPrimaryCurrency() }, {})];
            }
            return cities.map(function (city) {
                return buildRow({ city: city, country: '', currency: '' }, window.enquiryProCityMarkups[city]);
            });
        }
        return targets.map(function (t) {
            return buildRow(t, window.enquiryProCityMarkups[t.city] || {});
        });
    }
    window.getCurrencyMarkupsPayload = getCurrencyMarkupsPayload;

    function buildCityMarkupRowHtml(target, entry) {
        const city = target.city;
        const country = target.country || '';
        const currency = target.currency || '';
        const treatFoc = isTreatFocDiscountActive();
        const mt = entry.markup_type || '';
        const hotelMk = entry.hotel_markup != null
            ? (parseFloat(entry.hotel_markup) || 0)
            : (parseFloat(entry.markup_value || 0) || 0);
        const otherMk = entry.other_markup != null
            ? (parseFloat(entry.other_markup) || 0)
            : 0;
        let dt = treatFoc ? 'foc' : ((entry.discount_type === 'foc') ? '' : (entry.discount_type || ''));
        let dv = treatFoc ? focDiscountAmountForCity(city) : ((dt === 'foc') ? 0 : (entry.discount_value || 0));
        const markupDisabled = mt ? '' : 'disabled';
        const discountTypeDisabled = treatFoc ? 'disabled' : '';
        const discountDisabled = (!dt || dt === 'foc' || treatFoc) ? 'disabled' : '';
        const focClass = dt === 'foc' ? ' is-foc-locked' : '';
        const suffix = (mt === 'flat') ? (currency || 'AMT') : '%';
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
            + '<td class="enquiry-md-cell-markup">'
            + '<select class="city-markup-type enquiry-md-control" onchange="handleCityMarkupRowChange(this)">'
            + '<option value=""' + (!mt ? ' selected' : '') + '>Type</option>'
            + '<option value="percentage"' + (mt === 'percentage' ? ' selected' : '') + '>%</option>'
            + '<option value="flat"' + (mt === 'flat' ? ' selected' : '') + '>Fixed</option>'
            + '</select>'
            + '</td>'
            + '<td class="enquiry-md-cell-markup">'
            + '<div class="enquiry-md-markup-input">'
            + '<input type="number" class="city-hotel-markup enquiry-md-control" value="' + hotelMk + '" step="1" min="0" ' + markupDisabled
            + ' placeholder="0" oninput="handleCityMarkupRowChange(this)">'
            + '<span class="city-markup-suffix">' + escapeHtml(suffix) + '</span>'
            + '</div>'
            + '</td>'
            + '<td class="enquiry-md-cell-markup">'
            + '<div class="enquiry-md-markup-input">'
            + '<input type="number" class="city-other-markup enquiry-md-control" value="' + otherMk + '" step="1" min="0" ' + markupDisabled
            + ' placeholder="0" oninput="handleCityMarkupRowChange(this)">'
            + '<span class="city-markup-suffix">' + escapeHtml(suffix) + '</span>'
            + '</div>'
            + '</td>'
            + '<td class="enquiry-md-cell-discount">'
            + '<select class="city-discount-type enquiry-md-control" onchange="handleCityMarkupRowChange(this)" ' + discountTypeDisabled + '>'
            + '<option value=""' + (!dt ? ' selected' : '') + '>Type</option>'
            + '<option value="percentage"' + (dt === 'percentage' ? ' selected' : '') + '>%</option>'
            + '<option value="flat"' + (dt === 'flat' ? ' selected' : '') + '>Fixed</option>'
            + '<option value="foc"' + (dt === 'foc' ? ' selected' : '') + (treatFoc ? '' : ' hidden') + '>FOC</option>'
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
            const hotelMk = tr.querySelector('.city-hotel-markup');
            const otherMk = tr.querySelector('.city-other-markup');
            const dt = tr.querySelector('.city-discount-type');
            const dv = tr.querySelector('.city-discount-value');
            const enabled = !!(mt && mt.value);
            if (mv && mt) {
                mv.disabled = !enabled;
                if (!enabled) mv.value = 0;
            }
            [hotelMk, otherMk].forEach(function (inp) {
                if (!inp) return;
                inp.disabled = !enabled;
                if (!enabled) inp.value = 0;
            });
            const suffix = (mt && mt.value === 'flat')
                ? (String(tr.getAttribute('data-currency') || '').trim().toUpperCase() || 'AMT')
                : '%';
            tr.querySelectorAll('.city-markup-suffix').forEach(function (s) {
                s.textContent = suffix;
            });
            if (dv && dt) {
                if (dt.value === 'foc' && !isTreatFocDiscountActive()) {
                    dt.value = '';
                }
                if (!dt.value) {
                    dv.disabled = true;
                    dv.value = 0;
                    dv.classList.remove('is-foc-locked');
                    dv.style.backgroundColor = '';
                } else if (dt.value === 'foc') {
                    dv.disabled = true;
                    dv.classList.add('is-foc-locked');
                    dv.style.backgroundColor = '';
                    if (typeof focDiscountAmountForCity === 'function') {
                        const focHdr = (typeof getEnquiryProGroupFocFactors === 'function') ? getEnquiryProGroupFocFactors() : null;
                        const active = focHdr && focHdr.isGroup && focHdr.focSize > 0 && focHdr.discountOn;
                        const rowCity = String(tr.getAttribute('data-city') || '').trim();
                        dv.value = active ? focDiscountAmountForCity(rowCity) : 0;
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
        const dmc = enquiryProDmcMarkupDefaults();

        // UI permanently hidden — markups come from DMC settings
        if (singleWrap) singleWrap.style.display = 'none';
        if (multiWrap) multiWrap.style.display = 'none';

        const countEl = document.getElementById('enquiryProMarkupCityCount');
        if (countEl) countEl.textContent = String(targets.length || 0);

        syncHiddenMarkupInputsFromDmc();

        if (!targets.length) {
            if (body) body.innerHTML = '';
            if (typeof applyMarkupDiscount === 'function') applyMarkupDiscount();
            return;
        }

        targets.forEach(function (t) {
            const treatFoc = isTreatFocDiscountActive();
            const currency = (typeof window.resolveCurrencyForCityName === 'function')
                ? (window.resolveCurrencyForCityName(t.city, t.country) || t.currency || '')
                : (t.currency || '');
            const entry = emptyMarkupDiscountEntry({
                city: t.city,
                country: t.country || '',
                currency: currency,
                markup_type: dmc.markup_type || 'percentage',
                markup_value: dmc.markup_value || 0,
                hotel_markup: dmc.hotel_markup || 0,
                other_markup: dmc.other_markup || 0,
                discount_type: treatFoc ? 'foc' : '',
                discount_value: treatFoc ? focDiscountAmountForCity(t.city) : 0
            });
            window.enquiryProCityMarkups[t.city] = entry;
            if (entry.currency) {
                window.enquiryProCurrencyMarkups[entry.currency] = Object.assign({}, entry);
            }
        });

        if (body) body.innerHTML = '';
        if (typeof applyTreatFocDiscountToPricingUi === 'function') {
            applyTreatFocDiscountToPricingUi();
        }
        if (typeof applyMarkupDiscount === 'function') applyMarkupDiscount();
    }
    window.buildCityMarkupRowHtml = buildCityMarkupRowHtml;
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
            snapshotServerFocDiscountByCity();
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
