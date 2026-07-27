<script>
(function () {
    function getUserCurrencyText(note) {
        const currency = note.dataset.userCurrency || '';
        return currency
            ? '* You have set your currency in ' + currency
            : '* Currency has not been set for the current user';
    }

    function getDmcCurrencyText(currency) {
        return currency
            ? '* For The Selected DMC Currency is ' + currency
            : '* For The Selected DMC Currency is not set';
    }

    window.updateCurrencyPriceNoteFromDmc = function (selectEl) {
        console.log('[currency-price-note] updateCurrencyPriceNoteFromDmc called', selectEl);

        const note = document.querySelector('.currency-price-note');
        console.log('[currency-price-note] note element:', note);
        console.log('[currency-price-note] adminDmcMode:', note ? note.dataset.adminDmcMode : 'no note');

        if (!note) {
            console.warn('[currency-price-note] Aborted: .currency-price-note not found');
            return;
        }

        if (note.dataset.adminDmcMode !== '1') {
            console.warn('[currency-price-note] Aborted: admin DMC mode is not enabled');
            return;
        }

        const textEl = note.querySelector('.currency-price-note-text');
        if (!textEl) {
            console.warn('[currency-price-note] Aborted: .currency-price-note-text not found');
            return;
        }

        if (!selectEl || !selectEl.value) {
            console.log('[currency-price-note] No DMC selected, resetting to user currency');
            textEl.textContent = getUserCurrencyText(note);
            return;
        }

        const selected = selectEl.options[selectEl.selectedIndex];
        const userId = selectEl.value;
        const currency = selected ? (selected.getAttribute('data-currency') || selected.dataset.currency || '') : '';

        console.log('[currency-price-note] Selected DMC userId:', userId, 'currency:', currency);
        console.log('[currency-price-note] Selected option:', selected ? selected.outerHTML : null);

        textEl.textContent = getDmcCurrencyText(currency);
        console.log('[currency-price-note] Updated text:', textEl.textContent);
    };

    window.bindCurrencyPriceNoteToDmcSelect = function (selectId) {
        const dmcSelect = document.getElementById(selectId || 'dmc_selection');
        console.log('[currency-price-note] bindCurrencyPriceNoteToDmcSelect', selectId || 'dmc_selection', dmcSelect);

        if (!dmcSelect) {
            console.warn('[currency-price-note] DMC select not found:', selectId || 'dmc_selection');
            return;
        }

        Array.from(dmcSelect.options).forEach(function (opt) {
            if (opt.value) {
                console.log('[currency-price-note] DMC option loaded — userId:', opt.value, 'currency:', opt.getAttribute('data-currency'));
            }
        });

        dmcSelect.addEventListener('change', function () {
            console.log('[currency-price-note] DMC select change event, value:', this.value);
            window.updateCurrencyPriceNoteFromDmc(this);
        });
    };
})();
</script>
