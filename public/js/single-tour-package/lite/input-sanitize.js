/* === STP LITE: input-sanitize.js ===
 * Depends: none
 * Owns: block 'e/E/+' on number fields; strip special chars on text inputs;
 *       name fields: letters / spaces / hyphen / apostrophe only
 * === */
(function (window, document) {
    'use strict';

    var SPECIAL_CHAR_RE = /[<>"'`\\;$%^*=\[\]{}|~]/g;
    var NAME_STRIP_RE = /[^a-zA-Z\s'.-]/g;
    var NAME_VALID_RE = /^[a-zA-Z]+(?:[ '\-.][a-zA-Z]+)*$/;
    var EMAIL_VALID_RE = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    var PHONE_STRIP_RE = /[^\d]/g;
    var PHONE_VALID_RE = /^\d{6,15}$/;
    var NUMBER_BLOCK_KEYS = ['e', 'E', '+', '-'];

    function isIntegerNumberInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        if (el.type === 'number') return true;
        return el.classList.contains('stp-lite-int') || el.getAttribute('inputmode') === 'numeric';
    }

    function isNameInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        if (el.dataset.sanitize === 'name') return true;
        if (el.classList.contains('stp-lite-guest-name')) return true;
        var id = String(el.id || '');
        var name = String(el.getAttribute('name') || '');
        if (id === 'customerFullName' || name === 'customer_full_name') return true;
        if (/additional_guests\[\d+]\[name]$/.test(name)) return true;
        return false;
    }

    function isEmailInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        if (el.type === 'email') return true;
        if (el.dataset.sanitize === 'email') return true;
        var id = String(el.id || '');
        var name = String(el.getAttribute('name') || '');
        return id === 'customerEmail' || name === 'customer_email';
    }

    function isPhoneInput(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        if (el.dataset.sanitize === 'phone') return true;
        if (el.classList.contains('stp-lite-guest-phone')) return true;
        if (el.getAttribute('inputmode') === 'tel') return true;
        var id = String(el.id || '');
        var name = String(el.getAttribute('name') || '');
        if (id === 'customerPhone' || name === 'customer_phone') return true;
        if (/additional_guests\[\d+]\[contact_no]$/.test(name)) return true;
        return false;
    }

    function sanitizeTextValue(value) {
        return String(value == null ? '' : value).replace(SPECIAL_CHAR_RE, '');
    }

    function sanitizeNameValue(value) {
        return String(value == null ? '' : value)
            .replace(NAME_STRIP_RE, '')
            .replace(/\s{2,}/g, ' ');
    }

    function sanitizePhoneValue(value) {
        return String(value == null ? '' : value).replace(PHONE_STRIP_RE, '');
    }

    function isValidName(value) {
        var v = String(value == null ? '' : value).trim();
        if (!v) return true;
        return NAME_VALID_RE.test(v);
    }

    function isValidEmail(value) {
        var v = String(value == null ? '' : value).trim();
        if (!v) return true;
        return EMAIL_VALID_RE.test(v);
    }

    function isValidPhone(value) {
        var v = String(value == null ? '' : value).trim();
        if (!v) return true;
        return PHONE_VALID_RE.test(v);
    }

    function setFieldValidity(el, ok, message) {
        if (!el) return;
        el.classList.toggle('is-invalid', !ok);
        el.setCustomValidity(ok ? '' : (message || 'Invalid'));
        var tip = el.parentElement && el.parentElement.querySelector('.stp-lite-field-error');
        if (!ok) {
            if (!tip) {
                tip = document.createElement('div');
                tip.className = 'invalid-feedback stp-lite-field-error d-block';
                tip.style.fontSize = '0.72rem';
                el.parentElement.appendChild(tip);
            }
            tip.textContent = message || 'Invalid value';
        } else if (tip) {
            tip.remove();
        }
    }

    function validateNameField(el) {
        if (!el) return true;
        var v = String(el.value || '').trim();
        var ok = isValidName(v);
        setFieldValidity(el, ok, ok ? '' : 'Name: letters only (spaces, hyphen, apostrophe allowed)');
        return ok;
    }

    function validateEmailField(el) {
        if (!el) return true;
        var v = String(el.value || '').trim();
        var ok = isValidEmail(v);
        setFieldValidity(el, ok, ok ? '' : 'Enter a valid email (e.g. name@example.com)');
        return ok;
    }

    function validatePhoneField(el) {
        if (!el) return true;
        var v = sanitizePhoneValue(el.value);
        if (el.value !== v) el.value = v;
        var ok = isValidPhone(v);
        setFieldValidity(el, ok, ok ? '' : 'Phone number: digits only (6–15 digits)');
        return ok;
    }

    function bindRoot(root) {
        var scope = root || document;

        scope.addEventListener('keydown', function (e) {
            var el = e.target;
            if (isPhoneInput(el)) {
                // Allow control keys / navigation; block letters
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                if (e.key.length === 1 && !/\d/.test(e.key)) {
                    e.preventDefault();
                }
                return;
            }
            if (!isIntegerNumberInput(el)) return;
            if (NUMBER_BLOCK_KEYS.indexOf(e.key) !== -1) {
                e.preventDefault();
            }
        }, true);

        scope.addEventListener('input', function (e) {
            var el = e.target;
            if (!el || (el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA')) return;

            if (isPhoneInput(el)) {
                var phoneNext = sanitizePhoneValue(el.value);
                if (el.value !== phoneNext) {
                    var pStart = el.selectionStart;
                    el.value = phoneNext;
                    if (typeof pStart === 'number') {
                        try { el.setSelectionRange(pStart - 1, pStart - 1); } catch (errP) { /* ignore */ }
                    }
                }
                validatePhoneField(el);
                return;
            }

            if (isIntegerNumberInput(el)) {
                var cleaned = String(el.value || '').replace(/[^\d]/g, '');
                if (el.value !== cleaned) el.value = cleaned;
                return;
            }

            if (isNameInput(el)) {
                var nameNext = sanitizeNameValue(el.value);
                if (el.value !== nameNext) {
                    var start = el.selectionStart;
                    el.value = nameNext;
                    if (typeof start === 'number') {
                        try { el.setSelectionRange(start - 1, start - 1); } catch (err) { /* ignore */ }
                    }
                }
                validateNameField(el);
                return;
            }

            if (isEmailInput(el)) {
                validateEmailField(el);
                return;
            }

            if (el.type === 'text' || el.tagName === 'TEXTAREA') {
                if (el.dataset.allowSpecial === '1') return;
                var next = sanitizeTextValue(el.value);
                if (el.value !== next) {
                    var pos = el.selectionStart;
                    el.value = next;
                    if (typeof pos === 'number') {
                        try { el.setSelectionRange(pos - 1, pos - 1); } catch (err2) { /* ignore */ }
                    }
                }
            }
        }, true);

        scope.addEventListener('blur', function (e) {
            var el = e.target;
            if (!el || el.tagName !== 'INPUT') return;
            if (isNameInput(el)) validateNameField(el);
            if (isEmailInput(el)) validateEmailField(el);
            if (isPhoneInput(el)) validatePhoneField(el);
        }, true);
    }

    window.StpLiteInputSanitize = {
        bind: bindRoot,
        sanitizeText: sanitizeTextValue,
        sanitizeName: sanitizeNameValue,
        sanitizePhone: sanitizePhoneValue,
        isValidName: isValidName,
        isValidEmail: isValidEmail,
        isValidPhone: isValidPhone,
        validateNameField: validateNameField,
        validateEmailField: validateEmailField,
        validatePhoneField: validatePhoneField
    };
})(window, document);
/* === END input-sanitize.js === */
