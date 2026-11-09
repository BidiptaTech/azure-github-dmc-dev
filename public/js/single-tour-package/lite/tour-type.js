/* === STP LITE: tour-type.js ===
 * Depends: STP_LITE_CONFIG.dmcGroupPax, guest-caps (reads adults+children)
 * Owns: auto FIT/GROUP from DMC group_pax; radios are read-only
 * Pax rule: adults + children (infants excluded from group threshold)
 * === */
(function (window, document) {
    'use strict';

    function cfg() {
        return window.STP_LITE_CONFIG || {};
    }

    function readPax() {
        if (window.StpLiteGuestCaps && typeof window.StpLiteGuestCaps.getCaps === 'function') {
            var caps = window.StpLiteGuestCaps.getCaps();
            return {
                adults: caps.adults || 0,
                children: caps.children || 0,
                infants: caps.infants || 0
            };
        }
        var adults = parseInt((document.getElementById('adults') || {}).value || '0', 10) || 0;
        var children = parseInt((document.getElementById('children') || {}).value || '0', 10) || 0;
        var infants = parseInt((document.getElementById('infants') || {}).value || '0', 10) || 0;
        return { adults: adults, children: children, infants: infants };
    }

    function setTourType(type) {
        var next = String(type || 'FIT').toUpperCase() === 'GROUP' ? 'GROUP' : 'FIT';
        var fit = document.getElementById('fit');
        var group = document.getElementById('group');
        if (!fit || !group) return next;

        fit.checked = next === 'FIT';
        group.checked = next === 'GROUP';
        fit.disabled = true;
        group.disabled = true;

        // Hidden submit field (disabled radios are not posted)
        var hidden = document.getElementById('tour_type_value');
        if (hidden) hidden.value = next;

        window.selectedTourType = next;

        var groupBox = document.getElementById('groupDetailsWrapper');
        if (groupBox) {
            groupBox.classList.toggle('d-none', next !== 'GROUP');
        }

        var hint = document.getElementById('tourTypeAutoHint');
        if (hint) {
            var threshold = parseInt(cfg().dmcGroupPax || 0, 10) || 0;
            var pax = readPax();
            var total = pax.adults + pax.children;
            if (threshold > 0) {
                hint.textContent = 'Auto from DMC Group Pax (' + threshold + '). Current paying pax: ' + total + ' (adults + children).';
            } else {
                hint.textContent = 'DMC Group Pax is not set — defaulting to FIT.';
            }
        }

        document.dispatchEvent(new CustomEvent('stp:tour-type-changed', { detail: { tourType: next } }));
        return next;
    }

    function syncTourTypeFromPax() {
        var threshold = parseInt(cfg().dmcGroupPax || 0, 10) || 0;
        var pax = readPax();
        var total = pax.adults + pax.children;
        var next = (threshold > 0 && total >= threshold) ? 'GROUP' : 'FIT';
        return setTourType(next);
    }

    function init() {
        // Prevent any manual click from flipping radios
        document.querySelectorAll('.stp-lite-toggle.tour-type label, .stp-lite-toggle.tour-type input').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });
        syncTourTypeFromPax();
    }

    window.StpLiteTourType = {
        init: init,
        sync: syncTourTypeFromPax,
        set: setTourType
    };
})(window, document);
/* === END tour-type.js === */
