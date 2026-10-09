/* === STP LITE: maps-autocomplete.js ===
 * Google Places autocomplete (create + edit)
 * Pickup focus → pickup list only; dropoff focus → dropoff list only
 * Handles Google reusing one .pac-container across PTP / hourly fields
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
    var activeMapsInput = null;
    var pacGuardTimer = null;
    var pacIdSeq = 0;
    var initQueue = Promise.resolve();

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

    function hidePac(el) {
        if (!el) return;
        el.classList.remove('stp-lite-pac-active');
        el.classList.add('stp-lite-pac-hidden');
        el.style.setProperty('display', 'none', 'important');
        el.style.setProperty('visibility', 'hidden', 'important');
        el.style.setProperty('pointer-events', 'none', 'important');
        el.style.setProperty('opacity', '0', 'important');
    }

    /** Let Google control display for the focused field's pac. */
    function releasePac(el) {
        if (!el) return;
        el.classList.remove('stp-lite-pac-hidden');
        el.classList.add('stp-lite-pac-active');
        el.style.removeProperty('display');
        el.style.removeProperty('visibility');
        el.style.removeProperty('pointer-events');
        el.style.removeProperty('opacity');
        el.style.setProperty('z-index', '20000', 'important');
    }

    function hideAllPacContainers() {
        Array.prototype.forEach.call(document.querySelectorAll('.pac-container'), hidePac);
    }

    function claimPacForInput(input, pac) {
        if (!input || !pac) return null;
        pac._stpLiteOwner = input;
        var uid = input.getAttribute('data-stp-pac-id');
        if (uid) pac.setAttribute('data-stp-for', uid);
        input._pacContainer = pac;
        return pac;
    }

    function claimNewPac(input, beforeList) {
        var found = null;
        Array.prototype.forEach.call(document.querySelectorAll('.pac-container'), function (pac) {
            if (beforeList.indexOf(pac) === -1 && !pac._stpLiteOwner) {
                found = pac;
            }
        });
        if (!found) {
            Array.prototype.forEach.call(document.querySelectorAll('.pac-container'), function (pac) {
                if (!pac._stpLiteOwner) found = pac;
            });
        }
        if (!found) return null;
        claimPacForInput(input, found);
        hidePac(found);
        return found;
    }

    function waitForPac(input, beforeList, timeoutMs) {
        return new Promise(function (resolve) {
            var claimed = claimNewPac(input, beforeList);
            if (claimed) {
                resolve(claimed);
                return;
            }
            var done = false;
            var obs = null;
            var timer = setTimeout(function () {
                if (done) return;
                done = true;
                if (obs) obs.disconnect();
                resolve(claimNewPac(input, beforeList));
            }, timeoutMs || 600);

            if (typeof MutationObserver !== 'undefined') {
                obs = new MutationObserver(function () {
                    if (done) return;
                    var pac = claimNewPac(input, beforeList);
                    if (pac) {
                        done = true;
                        clearTimeout(timer);
                        obs.disconnect();
                        resolve(pac);
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
            }
        });
    }

    function resolveOwnedPac(input) {
        if (!input) return null;
        if (input._pacContainer && input._pacContainer.isConnected) return input._pacContainer;
        var uid = input.getAttribute('data-stp-pac-id');
        if (uid) {
            var byAttr = document.querySelector('.pac-container[data-stp-for="' + uid + '"]');
            if (byAttr) {
                claimPacForInput(input, byAttr);
                return byAttr;
            }
        }
        return null;
    }

    /**
     * Google often reuses one .pac-container for multiple Autocomplete inputs.
     * Prefer this input's own pac / an unowned pac. Only transfer from prevInput
     * (the field we just left) so PTP Pickup↔Dropoff never shows two lists.
     */
    function adoptLivePac(input, prevInput) {
        if (!input) return null;
        var owned = resolveOwnedPac(input);
        if (owned) return owned;

        var orphan = null;
        Array.prototype.forEach.call(document.querySelectorAll('.pac-container'), function (pac) {
            if (!pac || !pac.isConnected) return;
            if (pac._stpLiteOwner && pac._stpLiteOwner !== input) return;
            if (!pac._stpLiteOwner && !orphan) orphan = pac;
        });
        if (orphan) {
            claimPacForInput(input, orphan);
            return orphan;
        }

        // Shared-container case: take over the pac from the field we just left
        if (prevInput && prevInput !== input) {
            var prevPac = prevInput._pacContainer || resolveOwnedPac(prevInput);
            if (prevPac) {
                if (prevInput._pacContainer === prevPac) prevInput._pacContainer = null;
                claimPacForInput(input, prevPac);
                return prevPac;
            }
        }

        return null;
    }

    function hideOtherPacContainers(keepInput) {
        var keepPac = keepInput ? (keepInput._pacContainer || resolveOwnedPac(keepInput)) : null;
        Array.prototype.forEach.call(document.querySelectorAll('.pac-container'), function (pac) {
            if (keepInput && keepPac && pac === keepPac) {
                releasePac(pac);
            } else if (keepInput && pac._stpLiteOwner === keepInput) {
                releasePac(pac);
            } else {
                hidePac(pac);
            }
        });
    }

    function syncPacVisibility(input, prevInput) {
        if (!input) {
            hideAllPacContainers();
            return;
        }
        adoptLivePac(input, prevInput);
        hideOtherPacContainers(input);
    }

    function startPacGuard(input) {
        stopPacGuard();
        var ticks = 0;
        pacGuardTimer = setInterval(function () {
            ticks += 1;
            if (!activeMapsInput || activeMapsInput !== input) {
                stopPacGuard();
                return;
            }
            adoptLivePac(input, null);
            hideOtherPacContainers(input);
            if (ticks > 100) stopPacGuard();
        }, 50);
    }

    function stopPacGuard() {
        if (pacGuardTimer) {
            clearInterval(pacGuardTimer);
            pacGuardTimer = null;
        }
    }

    function activateInput(input) {
        if (!input) return;
        var prev = activeMapsInput;
        // Always close the previous field's dropdown before opening this one
        if (prev && prev !== input) {
            var prevPac = prev._pacContainer || resolveOwnedPac(prev);
            if (prevPac) hidePac(prevPac);
            Array.prototype.forEach.call(document.querySelectorAll('.pac-container'), function (pac) {
                if (pac._stpLiteOwner === prev) hidePac(pac);
            });
        }
        activeMapsInput = input;
        syncPacVisibility(input, prev);
        startPacGuard(input);
    }

    function bindInputEvents(input) {
        if (!input || input._stpLiteMapsBound) return;
        input._stpLiteMapsBound = true;
        input.addEventListener('focus', function () { activateInput(input); });
        input.addEventListener('mousedown', function () { activateInput(input); });
        input.addEventListener('keydown', function () {
            activeMapsInput = input;
            syncPacVisibility(input);
            startPacGuard(input);
        });
        input.addEventListener('input', function () {
            activeMapsInput = input;
            // Pac may appear only after first keystroke — adopt then
            adoptLivePac(input);
            syncPacVisibility(input);
            startPacGuard(input);
        });
        input.addEventListener('blur', function () {
            setTimeout(function () {
                if (document.activeElement === input) return;
                if (activeMapsInput === input) {
                    activeMapsInput = null;
                    stopPacGuard();
                    hideAllPacContainers();
                } else if (activeMapsInput) {
                    syncPacVisibility(activeMapsInput);
                } else {
                    hideAllPacContainers();
                }
            }, 200);
        });
    }

    function initOneSync(input, countryName, cityName) {
        if (!input || input.disabled) return Promise.resolve(false);

        // Already wired — still adopt pac if missing (shared-container recovery)
        if (input.getAttribute('data-autocomplete-initialized') === '1' && input._placesAutocomplete) {
            bindInputEvents(input);
            if (!resolveOwnedPac(input)) {
                claimNewPac(input, []);
            }
            return Promise.resolve(true);
        }
        if (!mapsReady()) return Promise.resolve(false);

        var geo = resolveCountryCity(input);
        var country = countryName || geo.country;
        var city = cityName || geo.city;
        var opts = {
            fields: ['formatted_address', 'geometry', 'name', 'place_id'],
            strictBounds: false
        };
        var code = getCountryCode(country);
        if (code) opts.componentRestrictions = { country: code };

        try {
            if (!input.getAttribute('data-stp-pac-id')) {
                pacIdSeq += 1;
                input.setAttribute('data-stp-pac-id', 'stp-pac-' + pacIdSeq);
            }

            var beforePacs = Array.prototype.slice.call(document.querySelectorAll('.pac-container'));
            var autocomplete = new google.maps.places.Autocomplete(input, opts);
            input._placesAutocomplete = autocomplete;
            applyBounds(autocomplete, city, country);

            return waitForPac(input, beforePacs, 600).then(function () {
                autocomplete.addListener('place_changed', function () {
                    var place = autocomplete.getPlace();
                    if (place && place.formatted_address) {
                        input.value = place.formatted_address;
                    } else if (place && place.name) {
                        input.value = place.name;
                    }
                    hideAllPacContainers();
                    stopPacGuard();
                    try { input.blur(); } catch (b) { /* ignore */ }
                    try {
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    } catch (e) { /* ignore */ }
                });

                bindInputEvents(input);
                input.setAttribute('data-autocomplete-initialized', '1');
                return true;
            });
        } catch (err) {
            console.warn('STP Lite Maps autocomplete failed', err);
            input.removeAttribute('data-autocomplete-initialized');
            input._placesAutocomplete = null;
            input._pacContainer = null;
            return Promise.resolve(false);
        }
    }

    /** Queue inits so pickup and dropoff each get a chance at their own .pac-container. */
    function initOne(input, countryName, cityName) {
        if (!input) return false;
        if (input.getAttribute('data-autocomplete-initialized') === '1' && input._placesAutocomplete) {
            bindInputEvents(input);
            if (!resolveOwnedPac(input)) claimNewPac(input, []);
            return true;
        }
        initQueue = initQueue.then(function () {
            return initOneSync(input, countryName, cityName);
        }).catch(function () { return false; });
        return false;
    }

    function visibleMapsInputs(root) {
        var nodes = root && root.querySelectorAll
            ? root.querySelectorAll('.google-maps-autocomplete')
            : [];
        var out = [];
        Array.prototype.forEach.call(nodes, function (input) {
            if (!input || input.disabled) return;
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
        nodes.forEach(function (input) {
            initOne(input, countryName, cityName);
        });
        scheduleRetry(root, countryName, cityName);
        return nodes.length;
    }

    function scheduleRetry(scope, countryName, cityName) {
        if (pendingRetry) return;
        var attempts = 0;
        pendingRetry = setInterval(function () {
            attempts += 1;
            var root = scope || document;
            if (!mapsReady()) {
                if (attempts >= 40) {
                    clearInterval(pendingRetry);
                    pendingRetry = null;
                }
                return;
            }
            var pending = visibleMapsInputs(root).filter(function (input) {
                return input.getAttribute('data-autocomplete-initialized') !== '1'
                    || !input._placesAutocomplete;
            });
            if (!pending.length || attempts >= 40) {
                clearInterval(pendingRetry);
                pendingRetry = null;
                return;
            }
            pending.forEach(function (input) {
                initOne(input, countryName, cityName);
            });
        }, 300);
    }

    function reinitIn(scope, countryName, cityName) {
        var root = scope || document;
        var nodes = root.querySelectorAll
            ? root.querySelectorAll('.google-maps-autocomplete')
            : [];
        Array.prototype.forEach.call(nodes, function (input) {
            input.removeAttribute('data-autocomplete-initialized');
            input._placesAutocomplete = null;
            input._pacContainer = null;
            input._stpLiteMapsBound = false;
        });
        hideAllPacContainers();
        initQueue = Promise.resolve();
        return initIn(root, countryName, cityName);
    }

    document.addEventListener('focusin', function (ev) {
        var t = ev.target;
        if (!t || !t.classList || !t.classList.contains('google-maps-autocomplete')) return;
        if (t._placesAutocomplete) {
            activateInput(t);
        } else if (window.StpLiteMaps && typeof window.StpLiteMaps.initOne === 'function') {
            // Late bind when Places loaded after mount (common on PTP / hourly)
            var geo = resolveCountryCity(t);
            initOne(t, geo.country, geo.city);
            activateInput(t);
        }
    }, true);

    window.StpLiteMaps = {
        getCountryCode: getCountryCode,
        mapsReady: mapsReady,
        initIn: initIn,
        initOne: initOne,
        reinitIn: reinitIn,
        hidePacContainers: hideAllPacContainers
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
