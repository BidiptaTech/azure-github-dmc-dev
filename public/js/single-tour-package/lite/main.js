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
