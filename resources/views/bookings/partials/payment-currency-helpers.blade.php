{{-- Include inside an existing script block (no script tags here). --}}
function getTourPaymentCurrency(tourId) {
    const modal = document.getElementById(`addPaymentModal${tourId}`);
    const fromModal = modal?.dataset?.tourCurrency;
    if (fromModal) {
        return fromModal;
    }
    return window.bookingCurrency || 'SGD';
}

function updatePaymentAmountMax(tourId) {
    const paymentAmountInput = document.getElementById(`payment_amount${tourId}`);
    if (!paymentAmountInput) {
        return;
    }

    const maxBaseAmount = Math.ceil(parseFloat(document.getElementById(`amount${tourId}`)?.value) || 0);
    paymentAmountInput.setAttribute('max', String(maxBaseAmount));
}

function fetchExchangeRate(currency, tourId) {
    const baseCurrency = getTourPaymentCurrency(tourId);
    const exchangeRateInput = document.getElementById(`exchange_rate${tourId}`);
    const rateSourceText = document.getElementById(`rateSourceText${tourId}`);

    if (!exchangeRateInput) {
        return;
    }

    if (!currency || currency === baseCurrency) {
        exchangeRateInput.value = '1.0000';
        if (rateSourceText) {
            rateSourceText.textContent = 'API Rate';
        }
        return;
    }

    fetch(`/get-exchange-rate?from=${encodeURIComponent(baseCurrency)}&to=${encodeURIComponent(currency)}`)
        .then((response) => response.json())
        .then((data) => {
            if (data && data.success && data.rate) {
                exchangeRateInput.value = Number(data.rate).toFixed(4);
                if (rateSourceText) {
                    rateSourceText.textContent = 'API Rate';
                }
                window.paymentRateSources = window.paymentRateSources || {};
                window.paymentRateSources[tourId] = window.paymentRateSources[tourId] || {};
                window.paymentRateSources[tourId].liveRate = String(data.rate);
                if (typeof recalculateFromExchangeRate === 'function') {
                    recalculateFromExchangeRate(tourId);
                }
                if (typeof validatePaymentAmountInput === 'function') {
                    validatePaymentAmountInput(tourId);
                }
            } else {
                exchangeRateInput.value = '1.0000';
                if (rateSourceText) {
                    rateSourceText.textContent = 'API Rate';
                }
            }
        })
        .catch((error) => {
            console.error('Error fetching exchange rate:', error);
            exchangeRateInput.value = '1.0000';
            if (rateSourceText) {
                rateSourceText.textContent = 'API Rate';
            }
        });
}

function fetchDmcRateForCurrency(tourId, currency) {
    if (!tourId || !currency) return;

    const url = @json(route('bookings.dmc-exchange-rate')) + `?tour_id=${encodeURIComponent(tourId)}&currency=${encodeURIComponent(currency)}`;
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then((response) => response.json())
    .then((data) => {
        window.paymentRateSources = window.paymentRateSources || {};
        window.paymentRateSources[tourId] = window.paymentRateSources[tourId] || {};

        const dmcRate = (data && data.success && data.dmc_rate !== null && data.dmc_rate !== '')
            ? String(data.dmc_rate)
            : '1';
        window.paymentRateSources[tourId].dmcRate = dmcRate;
        window.paymentRateSources[tourId].dmcRateSource = 'ajax';

        const dmcRadio = document.getElementById(`rateSourceDmc${tourId}`);
        const hint = document.getElementById(`rateSourceHint${tourId}`);
        if (dmcRadio) dmcRadio.disabled = false;

        if (typeof getSelectedRateSource === 'function' && getSelectedRateSource(tourId) === 'dmc' && dmcRate) {
            applyRateSourceSelection(tourId, 'dmc');
        }

        if (hint) {
            const prevMissing = !(window.paymentRateSources[tourId].previousRate && window.paymentRateSources[tourId].previousCurrency);
            const hints = [];
            if (!dmcRate || dmcRate === '1') hints.push('DMC Rate unavailable for selected currency.');
            if (prevMissing) hints.push('No previous payment rate found.');
            if (hints.length) {
                hint.textContent = hints.join(' ');
                hint.style.display = 'block';
            } else {
                hint.textContent = '';
                hint.style.display = 'none';
            }
        }
    })
    .catch(() => {
        window.paymentRateSources = window.paymentRateSources || {};
        window.paymentRateSources[tourId] = window.paymentRateSources[tourId] || {};
        window.paymentRateSources[tourId].dmcRate = '1';
        window.paymentRateSources[tourId].dmcRateSource = 'ajax';
        const dmcRadio = document.getElementById(`rateSourceDmc${tourId}`);
        if (dmcRadio) dmcRadio.disabled = false;
    });
}
