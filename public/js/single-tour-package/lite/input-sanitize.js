/* === STP LITE: input-sanitize.js ===
 * Depends: none
 * Owns: block 'e/E/+' on number fields; strip special chars on text inputs
 * === */
(function (window, document) {
    'use strict';

    var SPECIAL_CHAR_RE = /[<>"'`\\;$%^*=\[\]{}|~]/g;
    var NUMBER_BLOCK_KEYS = ['e', 'E', '+', '-'];

    function isIntegerNumberInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        if (el.type === 'number') return true;
        return el.classList.contains('stp-lite-int') || el.getAttribute('inputmode') === 'numeric';
    }

    function sanitizeTextValue(value) {
        return String(value == null ? '' : value).replace(SPECIAL_CHAR_RE, '');
    }

    function bindRoot(root) {
        var scope = root || document;

        scope.addEventListener('keydown', function (e) {
            var el = e.target;
            if (!isIntegerNumberInput(el)) return;
            if (NUMBER_BLOCK_KEYS.indexOf(e.key) !== -1) {
                e.preventDefault();
            }
        }, true);

        scope.addEventListener('input', function (e) {
            var el = e.target;
            if (!el || el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA') return;

            if (isIntegerNumberInput(el)) {
                var cleaned = String(el.value || '').replace(/[^\d]/g, '');
                if (el.value !== cleaned) el.value = cleaned;
                return;
            }

            if (el.type === 'text' || el.tagName === 'TEXTAREA') {
                if (el.dataset.allowSpecial === '1') return;
                var next = sanitizeTextValue(el.value);
                if (el.value !== next) {
                    var start = el.selectionStart;
                    el.value = next;
                    if (typeof start === 'number') {
                        try { el.setSelectionRange(start - 1, start - 1); } catch (err) { /* ignore */ }
                    }
                }
            }
        }, true);
    }

    window.StpLiteInputSanitize = {
        bind: bindRoot,
        sanitizeText: sanitizeTextValue
    };
})(window, document);
/* === END input-sanitize.js === */
