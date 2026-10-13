<script>
(function () {
    function getProductCurrencyText(note) {
        const currency = note.dataset.productCurrency || '';
        return currency
            ? '* Product currency is ' + currency
            : '* Product currency is not set';
    }

    function getDmcCurrencyText(currency) {
        return currency
            ? '* For The Selected DMC Currency is ' + currency
            : '* For The Selected DMC Currency is not set';
    }

    function getCountryCurrencyMap(note) {
        try {
            const mapId = note.dataset.countryMapId || '';
            const mapEl = mapId ? document.getElementById(mapId) : null;
            if (mapEl && mapEl.textContent) {
                return JSON.parse(mapEl.textContent) || {};
            }
            // Legacy fallback
            return JSON.parse(note.dataset.countryCurrencyMap || '{}') || {};
        } catch (e) {
            return {};
        }
    }

    function resolveCurrencyFromCountry(note, countryName) {
        if (!countryName) {
            return '';
        }
        const map = getCountryCurrencyMap(note);
        const direct = map[countryName] || '';
        if (direct) {
            return String(direct).toUpperCase();
        }
        const target = String(countryName).trim().toLowerCase();
        for (const key in map) {
            if (Object.prototype.hasOwnProperty.call(map, key) && String(key).trim().toLowerCase() === target) {
                return String(map[key]).toUpperCase();
            }
        }
        return '';
    }

    function setNoteText(note, text) {
        const textEl = note.querySelector('.currency-price-note-text');
        if (textEl) {
            textEl.textContent = text;
        }
    }

    function applyProductCurrency(note, countryName, currency) {
        if (countryName) {
            note.dataset.productCountry = countryName;
        }
        if (currency) {
            note.dataset.productCurrency = currency;
        }
        setNoteText(note, currency ? ('* Product currency is ' + currency) : '* Product currency is not set');
    }

    window.updateCurrencyPriceNoteFromCountry = function (selectEl, noteEl) {
        const note = noteEl || document.querySelector('.currency-price-note');
        if (!note) {
            return;
        }

        if (note.dataset.adminDmcMode === '1') {
            const dmcSelect = document.getElementById('dmc_selection')
                || document.getElementById('dmc_select')
                || document.getElementById('dmcSelect');
            if (dmcSelect && dmcSelect.value) {
                window.updateCurrencyPriceNoteFromDmc(dmcSelect);
                return;
            }
        }

        const selected = selectEl && selectEl.options ? selectEl.options[selectEl.selectedIndex] : null;
        const countryFromData = selected ? (selected.getAttribute('data-country') || selected.dataset.country || '') : '';
        const currencyFromData = selected ? (selected.getAttribute('data-currency') || selected.dataset.currency || '') : '';
        let countryName = String(countryFromData || (selectEl && selectEl.value ? selectEl.value : '')).trim();

        const existingCountry = String(note.dataset.productCountry || '').trim();
        const existingCurrency = String(note.dataset.productCurrency || '').trim();

        if (!countryName) {
            if (existingCurrency) {
                setNoteText(note, '* Product currency is ' + existingCurrency);
                return;
            }
            if (existingCountry) {
                const resolved = resolveCurrencyFromCountry(note, existingCountry);
                if (resolved) {
                    applyProductCurrency(note, existingCountry, resolved);
                    return;
                }
            }
            setNoteText(note, '* Product currency is not set');
            return;
        }

        let currency = currencyFromData
            ? String(currencyFromData).toUpperCase()
            : resolveCurrencyFromCountry(note, countryName);

        // Never wipe a valid server-rendered currency if lookup fails for same/related country.
        if (!currency && existingCurrency) {
            currency = existingCurrency;
        }

        applyProductCurrency(note, countryName, currency);
    };

    window.updateCurrencyPriceNoteFromDmc = function (selectEl) {
        const note = document.querySelector('.currency-price-note');
        if (!note || note.dataset.adminDmcMode !== '1') {
            return;
        }

        if (!selectEl || !selectEl.value) {
            setNoteText(note, getProductCurrencyText(note));
            return;
        }

        const selected = selectEl.options[selectEl.selectedIndex];
        let currency = selected
            ? (selected.getAttribute('data-currency') || selected.dataset.currency || '')
            : '';

        if (!currency && selected) {
            const countryName = selected.getAttribute('data-country') || selected.dataset.country || '';
            currency = resolveCurrencyFromCountry(note, countryName);
        }

        setNoteText(note, getDmcCurrencyText(currency));
    };

    window.bindCurrencyPriceNoteToDmcSelect = function (selectId) {
        const dmcSelect = document.getElementById(selectId || 'dmc_selection');
        if (!dmcSelect) {
            return;
        }
        dmcSelect.addEventListener('change', function () {
            window.updateCurrencyPriceNoteFromDmc(this);
        });
    };

    window.bindCurrencyPriceNoteToCountrySelect = function (selectId) {
        const note = document.querySelector('.currency-price-note');
        if (!note || note.dataset.watchCountry !== '1') {
            return;
        }

        const countrySelect = document.getElementById(selectId || note.dataset.countrySelectId || 'country');
        if (!countrySelect) {
            return;
        }

        const sync = function () {
            window.updateCurrencyPriceNoteFromCountry(countrySelect, note);
        };

        countrySelect.addEventListener('change', sync);
        if (window.jQuery) {
            window.jQuery(countrySelect).on('change.select2', sync);
        }
        sync();
    };

    function initCurrencyPriceNoteBindings() {
        const note = document.querySelector('.currency-price-note');
        if (!note) {
            return;
        }

        if (note.dataset.watchCountry === '1') {
            window.bindCurrencyPriceNoteToCountrySelect(note.dataset.countrySelectId || 'country');
        }

        if (note.dataset.adminDmcMode === '1') {
            if (document.getElementById('dmc_selection')) {
                window.bindCurrencyPriceNoteToDmcSelect('dmc_selection');
            } else if (document.getElementById('dmc_select')) {
                window.bindCurrencyPriceNoteToDmcSelect('dmc_select');
            } else if (document.getElementById('dmcSelect')) {
                window.bindCurrencyPriceNoteToDmcSelect('dmcSelect');
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCurrencyPriceNoteBindings);
    } else {
        initCurrencyPriceNoteBindings();
    }
})();
</script>
