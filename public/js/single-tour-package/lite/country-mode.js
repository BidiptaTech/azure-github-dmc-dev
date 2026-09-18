/* === STP LITE: country-mode.js ===
 * Depends: STP_LITE_CONFIG (isThirdPartyDmc, mdmcCountries), #lite_countries / city selects
 * Owns: auto Single/Multi Country from selected distinct countries; toggle is read-only
 * Internal values stay city_mode / city_type = single|multi (DB parity)
 * === */
(function (window, document) {
    'use strict';

    function cfg() {
        return window.STP_LITE_CONFIG || {};
    }

    function allowedCountries() {
        var list = cfg().mdmcCountries || [];
        return list.map(function (c) {
            return String(c.name || c).trim();
        }).filter(Boolean);
    }

    function isCountryAllowed(name) {
        var n = String(name || '').trim().toLowerCase();
        if (!n) return false;
        return allowedCountries().some(function (a) {
            return a.toLowerCase() === n;
        });
    }

    function getSelectedCountries() {
        var set = {};

        // Tour cities (primary UI)
        var tourCities = document.getElementById('tour_cities');
        if (tourCities) {
            Array.prototype.forEach.call(tourCities.selectedOptions || [], function (opt) {
                var country = String(opt.getAttribute('data-country') || '').trim();
                if (!country) {
                    var m = String(opt.textContent || '').match(/\(([^)]+)\)\s*$/);
                    if (m && m[1]) country = String(m[1]).trim();
                }
                if (country) set[country] = true;
            });
        }

        // Multi-country master select (hidden, synced from cities)
        var multi = document.getElementById('lite_countries');
        if (multi) {
            Array.prototype.forEach.call(multi.selectedOptions || [], function (opt) {
                var name = String(opt.value || '').trim();
                if (name) set[name] = true;
            });
        }

        // Master cities mirror
        var multiCities = document.getElementById('multi_cities');
        if (multiCities) {
            Array.prototype.forEach.call(multiCities.selectedOptions || [], function (opt) {
                var country = String(opt.getAttribute('data-country') || '').trim();
                if (!country) {
                    var m = String(opt.textContent || '').match(/\(([^)]+)\)\s*$/);
                    if (m && m[1]) country = String(m[1]).trim();
                }
                if (country) set[country] = true;
            });
        }

        // City plans
        document.querySelectorAll('#segmentsWrapper .city-select').forEach(function (sel) {
            var opt = sel.options && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex] : null;
            if (!opt) return;
            var country = String(opt.getAttribute('data-country') || '').trim();
            if (!country) {
                var m2 = String(opt.textContent || '').match(/\(([^)]+)\)\s*$/);
                if (m2 && m2[1]) country = String(m2[1]).trim();
            }
            if (!country && window.resolveCountryForCity) {
                var cityName = String(opt.getAttribute('data-city-name') || opt.textContent || '')
                    .replace(/\s*\([^)]*\)\s*$/, '').trim();
                country = String(window.resolveCountryForCity(cityName) || '').trim();
            }
            if (country) set[country] = true;
        });

        // Single-city option may carry data-country
        var singleCity = document.getElementById('single_city');
        if (singleCity && singleCity.selectedOptions && singleCity.selectedOptions[0]) {
            var opt = singleCity.selectedOptions[0];
            var country = String(opt.getAttribute('data-country') || '').trim();
            if (!country) {
                var text = String(opt.textContent || '');
                var m = text.match(/\(([^)]+)\)\s*$/);
                if (m && m[1]) country = String(m[1]).trim();
            }
            if (country) set[country] = true;
        }

        // Hidden user_country (may already be CSV "India, Singapore")
        var uc = document.getElementById('user_country');
        if (uc && uc.value) {
            String(uc.value).split(',').forEach(function (part) {
                var c = String(part || '').trim();
                if (c) set[c] = true;
            });
        }

        return Object.keys(set).filter(Boolean);
    }

    function setCityMode(mode) {
        var next = String(mode || 'single').toLowerCase() === 'multi' ? 'multi' : 'single';

        if (cfg().isThirdPartyDmc) {
            next = 'single';
        }

        var prev = '';
        var cityTypeHidden = document.getElementById('city_type_value');
        if (cityTypeHidden) prev = String(cityTypeHidden.value || 'single');

        var single = document.getElementById('city_mode_single');
        var multi = document.getElementById('city_mode_multi');
        if (single && multi) {
            single.checked = next === 'single';
            multi.checked = next === 'multi';
            single.disabled = true;
            multi.disabled = true;
        }

        if (cityTypeHidden) cityTypeHidden.value = next;
        var cityModeHidden = document.getElementById('city_mode_value');
        if (cityModeHidden) cityModeHidden.value = next;

        var multiPlanner = document.getElementById('multiCountryPlanner');
        if (multiPlanner) {
            multiPlanner.classList.toggle('d-none', next !== 'multi');
        }

        // City picker stays visible in both modes (tour_cities)
        var singleCityWrap = document.getElementById('singleCountryCityWrap');
        if (singleCityWrap) {
            singleCityWrap.classList.remove('d-none');
        }

        var hint = document.getElementById('countryModeAutoHint');
        if (hint) {
            if (cfg().isThirdPartyDmc) {
                hint.textContent = '3rd party DMC: Multi Country is locked to Single Country.';
            } else {
                var cityCount = 0;
                var tc = document.getElementById('tour_cities');
                if (tc) cityCount = (tc.selectedOptions || []).length;
                if (!cityCount && window.StpLiteTourDetails) {
                    cityCount = (window.StpLiteTourDetails.getTourCityItems() || []).length;
                }
                var countries = getSelectedCountries();
                if (cityCount > 1 || countries.length > 1) {
                    hint.textContent = 'Auto Multi Country — ' + cityCount + ' cities selected.';
                } else {
                    hint.textContent = 'Auto Single Country — select 2+ cities to switch.';
                }
            }
        }

        renderCountryTags(getSelectedCountries());
        // Only notify when mode actually changes — avoids remount storms
        if (prev !== next) {
            document.dispatchEvent(new CustomEvent('stp:country-mode-changed', {
                detail: { cityType: next, countries: getSelectedCountries() }
            }));
        }
        return next;
    }

    function renderCountryTags(countries) {
        var box = document.getElementById('liteSelectedCountryTags');
        if (!box) return;
        if (!countries.length) {
            box.innerHTML = '<span class="text-muted" style="font-size:0.78rem;">No country selected yet.</span>';
            return;
        }
        box.innerHTML = countries.map(function (name, idx) {
            return '<span class="stp-lite-country-tag" data-idx="' + (idx % 6) + '">' +
                escapeHtml(name) + '</span>';
        }).join('');
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function syncUserCountryFromSelection() {
        var countries = getSelectedCountries();
        var uc = document.getElementById('user_country');
        var countryId = document.getElementById('country_id');
        if (!uc) return;

        // Backup: store posts user_country as CSV of all countries ("India, Singapore")
        // → tours.destination drives booking-list multi-country tabs/badge
        var joined = countries.length ? countries.join(', ') : '';
        var primary = countries.length ? countries[0] : '';
        if (joined) {
            var hasOpt = false;
            Array.prototype.forEach.call(uc.options || [], function (opt) {
                if (String(opt.value) === joined) hasOpt = true;
            });
            if (!hasOpt && uc.tagName === 'SELECT') {
                var o = document.createElement('option');
                o.value = joined;
                o.textContent = joined;
                if (primary) {
                    var primaryOpt = null;
                    Array.prototype.forEach.call(uc.options || [], function (opt) {
                        if (String(opt.value) === primary) primaryOpt = opt;
                    });
                    if (primaryOpt) {
                        o.setAttribute('data-country-id', primaryOpt.getAttribute('data-country-id') || '');
                    }
                }
                uc.appendChild(o);
            }
            uc.value = joined;
            if (window.jQuery && jQuery(uc).data('select2')) {
                jQuery(uc).val(joined).trigger('change.select2');
            }
        }

        if (countryId && primary) {
            var map = cfg().countryIdByName || {};
            var fromOpt = '';
            Array.prototype.forEach.call(uc.options || [], function (opt) {
                if (String(opt.value) === primary) {
                    fromOpt = opt.getAttribute('data-country-id') || '';
                }
            });
            countryId.value = fromOpt || map[primary] || '';
        }

        // Validate against MDMC allow-list
        var warn = document.getElementById('mdmcCountryWarn');
        if (warn) {
            var bad = countries.filter(function (c) { return !isCountryAllowed(c); });
            if (bad.length) {
                warn.classList.remove('d-none');
                warn.textContent = 'These countries are not in Master DMC list: ' + bad.join(', ');
            } else {
                warn.classList.add('d-none');
                warn.textContent = '';
            }
        }
    }

    function syncFromSelections() {
        syncUserCountryFromSelection();
        var countries = getSelectedCountries();
        var cityCount = 0;
        var tc = document.getElementById('tour_cities');
        if (tc) cityCount = (tc.selectedOptions || []).length;
        if (!cityCount && window.jQuery) {
            var vals = window.jQuery('#tour_cities').val();
            cityCount = Array.isArray(vals) ? vals.length : 0;
        }
        // User rule: multiple cities → Multi Country automatically
        var mode = (cityCount > 1 || countries.length > 1) ? 'multi' : 'single';
        return setCityMode(mode);
    }

    function init() {
        document.querySelectorAll('.stp-lite-toggle.country-mode label, .stp-lite-toggle.country-mode input').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        var multi = document.getElementById('lite_countries');
        if (multi) {
            multi.addEventListener('change', syncFromSelections);
        }

        document.addEventListener('stp:city-country-changed', syncFromSelections);
        syncFromSelections();
    }

    window.StpLiteCountryMode = {
        init: init,
        sync: syncFromSelections,
        getSelectedCountries: getSelectedCountries,
        syncUserCountryFromSelection: syncUserCountryFromSelection,
        isCountryAllowed: isCountryAllowed,
        setCityMode: setCityMode
    };
})(window, document);
/* === END country-mode.js === */
