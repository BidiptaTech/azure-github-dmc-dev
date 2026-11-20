/* === STP LITE: main.js ===
 * Depends: input-sanitize, geo, guest-caps, tour-type, country-mode, tour-details,
 *          accordion-manager, hotel, transport-shared, arrival, departure, transport,
 *          attraction, guide, restaurant, country-segments
 * Owns: boot order for lite create/edit
 * === */
(function (window, document) {
    'use strict';

    function boot() {
        if (!window.STP_LITE_CONFIG) {
            console.error('STP_LITE_CONFIG missing');
            return;
        }

        if (window.StpLiteInputSanitize) {
            window.StpLiteInputSanitize.bind(document.getElementById('stpLiteRoot') || document);
        }

        if (window.StpLiteAccordion) window.StpLiteAccordion.init();
        if (window.StpLiteGuestCaps) window.StpLiteGuestCaps.init();
        if (window.StpLiteTourType) window.StpLiteTourType.init();
        if (window.StpLiteCountryMode) window.StpLiteCountryMode.init();
        if (window.StpLiteTourDetails) window.StpLiteTourDetails.init();
        if (window.StpLiteCountrySegments) window.StpLiteCountrySegments.init();
        if (window.StpLiteGuests && typeof window.StpLiteGuests.init === 'function') {
            window.StpLiteGuests.init();
        }

        // Enquiry multi-city: re-apply stay seeds after segments listeners are bound
        var enqCities = (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.enquiryPrefillCities) || [];
        if (enqCities.length && window.StpLiteCountrySegments
            && typeof window.StpLiteCountrySegments.syncAutoCityPlanRows === 'function') {
            var forceReturn = {};
            Object.keys(window.__stpLiteStaySeedByCity || {}).forEach(function (cityKey) {
                var seed = window.__stpLiteStaySeedByCity[cityKey];
                if (!seed || !seed.returnChecked) return;
                document.querySelectorAll('#segmentsWrapper .segment:not(.is-return-stay)').forEach(function (row) {
                    var name = String(row.getAttribute('data-city-name') || '').trim().toLowerCase();
                    var id = String(row.getAttribute('data-city-id') || '');
                    if (id && name === cityKey) forceReturn[id] = true;
                });
            });
            window.StpLiteCountrySegments.syncAutoCityPlanRows(
                Object.keys(forceReturn).length ? { forceReturnByCityId: forceReturn } : {}
            );
            if (typeof window.StpLiteCountrySegments.updateServicesGate === 'function') {
                window.StpLiteCountrySegments.updateServicesGate();
            }
            if (typeof window.StpLiteCountrySegments.render === 'function') {
                window.StpLiteCountrySegments.render();
            }
        }

        document.addEventListener('stp:guests-changed', function () {
            if (window.StpLiteTourType) window.StpLiteTourType.sync();
        });

        if (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.mode === 'edit'
            && window.StpLiteHydrateEdit && typeof window.StpLiteHydrateEdit.run === 'function') {
            setTimeout(function () { window.StpLiteHydrateEdit.run(); }, 50);
        }

        console.info('[STP Lite] Services ready (hotel + arrival/attraction/guide/restaurant/transport/departure/miscellaneous)');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window, document);
/* === END main.js === */
