/* === STP LITE: accordion-manager.js ===
 * Depends: Bootstrap 5 Collapse
 * Owns: only ONE service accordion open at a time across the whole form
 * === */
(function (window, document) {
    'use strict';

    function init() {
        document.addEventListener('show.bs.collapse', function (e) {
            var target = e.target;
            if (!target || !target.classList.contains('stp-lite-service-body')) return;

            document.querySelectorAll('.stp-lite-service-body.show, .stp-lite-service-body.collapsing').forEach(function (el) {
                if (el === target) return;
                if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                    var inst = bootstrap.Collapse.getInstance(el);
                    if (inst) inst.hide();
                    else el.classList.remove('show');
                } else {
                    el.classList.remove('show');
                }
            });
        });
    }

    window.StpLiteAccordion = { init: init };
})(window, document);
/* === END accordion-manager.js === */
