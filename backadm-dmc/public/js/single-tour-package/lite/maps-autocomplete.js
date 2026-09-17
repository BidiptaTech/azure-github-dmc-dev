/* === STP LITE: maps-autocomplete.js ===
 * Google Places autocomplete for zone_on = 0 (and transport PTP/hourly maps fields)
 * Biased to selected country/city when available
 * === */
(function (window, document) {
    'use strict';

    var COUNTRY_CODES = {
        India: 'IN', Singapore: 'SG', Malaysia: 'MY', Indonesia: 'ID', Thailand: 'TH',
        Vietnam: 'VN', Philippines: 'PH', 'Sri Lanka': 'LK', 'United Arab Emirates': 'AE',
        UAE: 'AE', Dubai: 'AE', Australia: 'AU', 'United Kingdom': 'GB', 'United States': 'US',
        Canada: 'CA', Germany: 'DE', France: 'FR', Italy: 'IT', Spain: 'ES', Japan: 'JP',
        China: 'CN', 'Hong Kong': 'HK', 'South Korea': 'KR', Nepal: 'NP', Bangladesh: 'BD',
        Maldives: 'MV', Cambodia: 'KH', Laos: 'LA', Myanmar: 'MM', Brunei: 'BN'
    };

    var pendingRetry = null;

    function mapsReady() {
        return typeof google !== 'undefined'
            && google.maps
            && google.maps.places
            && typeof google.maps.places.Autocomplete === 'function';
    }

    function getCountryCode(countryName) {
        if (!countryName) return null;
        var name = String(countryName).trim();
        if (/^[A-Za-z]{2}$/.test(name)) return name.toUpperCase();
        if (COUNTRY_CODES[name]) return COUNTRY_CODES[name];
        var lower = name.toLowerCase();
        for (var key in COUNTRY_CODES) {
            if (Object.prototype.hasOwnProperty.call(COUNTRY_CODES, key) && key.toLowerCase() === lower) {
                return COUNTRY_CODES[key];
            }
        }
        return null;
    }

    function resolveCountryCity(hintEl) {
        var country = '';
        var city = '';
        var host = hintEl && hintEl.closest
            ? (hintEl.closest('[data-city-name]')
                || hintEl.closest('[data-country]')
                || hintEl.closest('.stp-lite-service-panel')
                || hintEl.closest('.stp-lite-country-section')
                || hintEl.closest('.stp-lite-svc'))
            : null;
        if (host) {
            country = String(host.getAttribute('data-country') || '').trim();
            city = String(host.getAttribute('data-city-name') || '').trim();
        }
        if ((!country || !city) && hintEl && hintEl.closest) {
            var stayPanel = hintEl.closest('[data-stay-start], [data-plan-index]');
            if (stayPanel) {
                if (!country) country = String(stayPanel.getAttribute('data-country') || '').trim();
                if (!city) city = String(stayPanel.getAttribute('data-city-name') || '').trim();
            }
        }
        if (!country && window.StpLiteCountryMode && typeof window.StpLiteCountryMode.getSelectedCountries === 'function') {
            var list = window.StpLiteCountryMode.getSelectedCountries() || [];
            if (list.length) country = list[0];
        }
        if (!city && window.StpLiteTourDetails && typeof window.StpLiteTourDetails.getTourCityItems === 'function') {
            var items = window.StpLiteTourDetails.getTourCityItems() || [];
            if (items.length) {
                city = items[0].name || '';
                if (!country) country = items[0].country || '';
            }
        }
        return { country: country, city: city };
    }

    function applyBounds(autocomplete, cityName, countryName) {
        if (!autocomplete || !mapsReady() || !google.maps.Geocoder) return;
        var address = [cityName, countryName].filter(Boolean).join(', ');
        if (!address) return;
        try {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: address }, function (results, status) {
                if (status !== 'OK' || !results || !results[0] || !results[0].geometry) return;
                try {
                    if (results[0].geometry.viewport) {
                        autocomplete.setBounds(results[0].geometry.viewport);
                    } else if (results[0].geometry.location) {
                        var circle = new google.maps.Circle({
                            center: results[0].geometry.location,
                            radius: 40000
                        });
                        autocomplete.setBounds(circle.getBounds());
                    }
                    autocomplete.setOptions({ strictBounds: false });
                } catch (e) { /* ignore */ }
            });
        } catch (e2) { /* ignore */ }
    }

    function hidePacContainers() {
        try {
            var pac = document.querySelectorAll('.pac-container');
            Array.prototype.forEach.call(pac, function (el) {
                el.classList.add('stp-lite-pac-hidden');
                el.style.display = 'none';
            });
        } catch (e) { /* ignore */ }
    }

    function showPacContainers() {
        try {
            var pac = document.querySelectorAll('.pac-container');
            Array.prototype.forEach.call(pac, function (el) {
                el.classList.remove('stp-lite-pac-hidden');
                if (el.style.display === 'none') el.style.display = '';
                el.style.zIndex = '20000';
            });
        } catch (e) { /* ignore */ }
    }

    function initOne(input, countryName, cityName) {
        if (!input || input.disabled) return false;
        if (input.getAttribute('data-autocomplete-initialized') === '1' && input._placesAutocomplete) {
            return true;
        }
        if (!mapsReady()) return false;

        var geo = resolveCountryCity(input);
        var country = countryName || geo.country;
        var city = cityName || geo.city;
        // Do not mix type collections — empty types returns all predictions (most reliable)
        var opts = {
            fields: ['formatted_address', 'geometry', 'name', 'place_id'],
            strictBounds: false
        };
        var code = getCountryCode(country);
        if (code) opts.componentRestrictions = { country: code };

        try {
            var autocomplete = new google.maps.places.Autocomplete(input, opts);
            input._placesAutocomplete = autocomplete;
            applyBounds(autocomplete, city, country);
            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                if (place && place.formatted_address) {
                    input.value = place.formatted_address;
                } else if (place && place.name) {
                    input.value = place.name;
                }
                // Close dropdown so "powered by Google" does not linger and break layout
                hidePacContainers();
                try { input.blur(); } catch (b) { /* ignore */ }
                try {
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                } catch (e) { /* ignore */ }
            });
            input.addEventListener('focus', function () {
                showPacContainers();
            });
            input.addEventListener('keydown', function () {
                showPacContainers();
            });
            input.addEventListener('blur', function () {
                setTimeout(hidePacContainers, 180);
            });
            input.setAttribute('data-autocomplete-initialized', '1');
            try {
                setTimeout(function () {
                    var pac = document.querySelectorAll('.pac-container');
                    Array.prototype.forEach.call(pac, function (el) {
                        el.style.zIndex = '20000';
                    });
                }, 0);
            } catch (z) { /* ignore */ }
            return true;
        } catch (err) {
            console.warn('STP Lite Maps autocomplete failed', err);
            input.removeAttribute('data-autocomplete-initialized');
            input._placesAutocomplete = null;
            return false;
        }
    }

    function visibleMapsInputs(root) {
        var nodes = root && root.querySelectorAll
            ? root.querySelectorAll('.google-maps-autocomplete')
            : [];
        var out = [];
        Array.prototype.forEach.call(nodes, function (input) {
            if (!input || input.disabled) return;
            // Skip inputs in hidden parents (d-none / display:none)
            var el = input;
            var hidden = false;
            while (el && el !== document.body) {
                if (el.classList && el.classList.contains('d-none')) { hidden = true; break; }
                var style = window.getComputedStyle ? window.getComputedStyle(el) : null;
                if (style && (style.display === 'none' || style.visibility === 'hidden')) {
                    hidden = true;
                    break;
                }
                el = el.parentElement;
            }
            if (!hidden) out.push(input);
        });
        return out;
    }

    function initIn(scope, countryName, cityName) {
        var root = scope || document;
        if (!mapsReady()) {
            scheduleRetry(root, countryName, cityName);
            return 0;
        }
        var nodes = visibleMapsInputs(root);
        var ok = 0;
        nodes.forEach(function (input) {
            if (initOne(input, countryName, cityName)) ok += 1;
        });
        if (ok < nodes.length) scheduleRetry(root, countryName, cityName);
        return ok;
    }

    function scheduleRetry(scope, countryName, cityName) {
        if (pendingRetry) return;
        var attempts = 0;
        pendingRetry = setInterval(function () {
            attempts += 1;
            if (mapsReady()) {
                clearInterval(pendingRetry);
                pendingRetry = null;
                initIn(scope || document, countryName, cityName);
            } else if (attempts >= 40) {
                clearInterval(pendingRetry);
                pendingRetry = null;
            }
        }, 250);
    }

    function reinitIn(scope, countryName, cityName) {
        var root = scope || document;
        var nodes = root.querySelectorAll
            ? root.querySelectorAll('.google-maps-autocomplete')
            : [];
        Array.prototype.forEach.call(nodes, function (input) {
            input.removeAttribute('data-autocomplete-initialized');
            input._placesAutocomplete = null;
        });
        return initIn(root, countryName, cityName);
    }

    window.StpLiteMaps = {
        getCountryCode: getCountryCode,
        mapsReady: mapsReady,
        initIn: initIn,
        initOne: initOne,
        reinitIn: reinitIn
    };
    window.getCountryCode = window.getCountryCode || getCountryCode;
    window.initializeGoogleMapsAutocomplete = function () {
        initIn(document);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initIn(document); });
    } else {
        initIn(document);
    }
})(window, document);
/* === END maps-autocomplete.js === */
