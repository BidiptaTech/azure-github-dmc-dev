{{--
  Extends existing Add Payment modals with server-driven Wallet / Stripe options.
  Laravel-only payment service (no Python / no Razorpay).
--}}
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    if (window.TravclicksPaymentService) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value;

    async function fetchEligibility(tourId, amount) {
        const params = new URLSearchParams();
        if (amount != null && amount !== '' && !Number.isNaN(Number(amount)) && Number(amount) > 0) {
            params.set('amount', String(amount));
        }
        const qs = params.toString();
        const res = await fetch(`/payment-service/tours/${tourId}/eligibility${qs ? `?${qs}` : ''}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) {
            throw new Error('Unable to load payment options');
        }
        return res.json();
    }

    function applyWalletAvailabilityFromAmount(tourId) {
        const eligibility = window.__paymentEligibility?.[tourId];
        if (!eligibility) {
            return;
        }
        const amountEl = document.getElementById(`payment_amount${tourId}`);
        const entered = amountEl ? parseFloat(amountEl.value) : NaN;
        const checkAmount = Number.isFinite(entered) && entered > 0
            ? entered
            : Number(eligibility.payable_amount || 0);
        const wallet = eligibility.methods?.find((m) => m.code === 'wallet');
        if (!wallet) {
            return;
        }
        const balance = Number(wallet.wallet?.available_balance ?? wallet.available_balance ?? 0);
        const currency = wallet.wallet?.currency || eligibility.currency || '';
        const settingsOk = eligibility.settings?.wallet_enabled !== false;
        const available = settingsOk && checkAmount > 0 && balance >= checkAmount;
        wallet.available = available;
        wallet.reason = available
            ? null
            : (checkAmount <= 0
                ? 'No payable amount on this booking.'
                : (balance <= 0
                    ? `No ${currency} wallet balance for this agency under the tour DMC.`
                    : `Insufficient wallet balance (${currency} ${balance} available, ${checkAmount} required).`));
        if (wallet.wallet) {
            wallet.wallet.required_amount = checkAmount;
        }
        applyMethodsToSelect(document.getElementById(`payment_type${tourId}`), eligibility);
    }

    function bindAmountWatcher(tourId) {
        const amountEl = document.getElementById(`payment_amount${tourId}`);
        if (!amountEl || amountEl.dataset.walletWatchBound === '1') {
            return;
        }
        amountEl.dataset.walletWatchBound = '1';
        let timer = null;
        const refresh = () => {
            clearTimeout(timer);
            timer = setTimeout(() => applyWalletAvailabilityFromAmount(tourId), 200);
        };
        amountEl.addEventListener('input', refresh);
        amountEl.addEventListener('change', refresh);
    }

    function ensureHelper(selectEl, tourId) {
        let el = document.getElementById(`paymentEligibilityHelp${tourId}`);
        if (!el) {
            el = document.createElement('small');
            el.id = `paymentEligibilityHelp${tourId}`;
            el.className = 'text-muted d-block mt-1';
            selectEl.parentElement.appendChild(el);
        }
        return el;
    }

    function applyMethodsToSelect(selectEl, eligibility) {
        if (!selectEl || !eligibility?.methods) {
            return;
        }

        const current = selectEl.value;
        ['wallet', 'stripe'].forEach((code) => {
            const method = eligibility.methods.find((m) => m.code === code);
            const optId = `payOpt_${code}_${eligibility.tour_id}`;
            let opt = document.getElementById(optId);
            if (!method) {
                return;
            }
            if (!opt) {
                opt = document.createElement('option');
                opt.id = optId;
                opt.value = code;
                selectEl.appendChild(opt);
            }
            if (method.code === 'wallet') {
                const bal = Number(method.wallet?.available_balance ?? 0);
                const cur = method.wallet?.currency || eligibility.currency || '';
                opt.textContent = `Wallet (${cur} ${bal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
            } else if (method.available) {
                opt.textContent = method.label;
            } else {
                opt.textContent = method.label + ' (unavailable)';
            }
            opt.disabled = !method.available;
            opt.dataset.reason = method.reason || '';
            if (!method.available && current === opt.value) {
                selectEl.value = '';
            }
        });

        const help = ensureHelper(selectEl, eligibility.tour_id);
        let msg = `Stage: ${eligibility.payment_stage}. Recipient: ${eligibility.payment_recipient}.`;
        const walletMethod = eligibility.methods.find((m) => m.code === 'wallet');
        if (walletMethod && !walletMethod.available && walletMethod.reason) {
            msg += ` Wallet: ${walletMethod.reason}`;
        }
        const fx = eligibility.fx || {};
        if (Number(fx.conversion_charge) > 0) {
            msg += ` Currency conversion charge ${fx.charge_percent}% (${fx.conversion_charge} ${fx.wallet_currency}).`;
        }
        if (eligibility.acceptance?.hint) {
            msg += ` ${eligibility.acceptance.hint}`;
        }
        if (eligibility.amount_readonly) {
            msg += ` Payable (read-only): ${eligibility.payable_amount} ${eligibility.currency}.`;
            const amountInput = document.getElementById(`payment_amount${eligibility.tour_id}`);
            if (amountInput) {
                amountInput.value = eligibility.payable_amount;
                amountInput.readOnly = true;
                amountInput.classList.add('bg-light');
            }
        } else {
            const amountInput = document.getElementById(`payment_amount${eligibility.tour_id}`);
            if (amountInput) {
                amountInput.readOnly = false;
                amountInput.classList.remove('bg-light');
            }
        }
        help.textContent = msg;
        window.__paymentEligibility = window.__paymentEligibility || {};
        window.__paymentEligibility[eligibility.tour_id] = eligibility;
    }

    async function enhancePaymentModal(tourId) {
        try {
            const amountEl = document.getElementById(`payment_amount${tourId}`);
            const payload = await fetchEligibility(tourId, amountEl?.value);
            applyMethodsToSelect(document.getElementById(`payment_type${tourId}`), payload.data);
            bindAmountWatcher(tourId);
            applyWalletAvailabilityFromAmount(tourId);
            return payload.data;
        } catch (e) {
            console.warn('payment eligibility', e);
            return null;
        }
    }

    async function initiatePayment(tourId, paymentMethod, amount, extra = {}) {
        const body = {
            payment_method: paymentMethod,
            amount: amount,
            idempotency_key: extra.idempotency_key || (crypto.randomUUID ? crypto.randomUUID() : String(Date.now())),
            ...extra,
        };
        const res = await fetch(`/payment-service/tours/${tourId}/pay`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });
        const json = await res.json();
        if (!res.ok || !json.success) {
            throw new Error(json.message || 'Payment initiation failed');
        }
        return json.data;
    }

    async function confirmStripeCard(stripePayload) {
        if (!window.Stripe || !stripePayload?.publishable_key || !stripePayload?.client_secret) {
            throw new Error('Stripe.js or client_secret missing');
        }
        const stripe = Stripe(stripePayload.publishable_key);
        const { error, paymentIntent } = await stripe.confirmCardPayment(stripePayload.client_secret);
        if (error) {
            throw new Error(error.message || 'Card payment failed');
        }
        return paymentIntent;
    }

    document.addEventListener('shown.bs.modal', function (ev) {
        const modal = ev.target;
        if (!modal?.id?.startsWith('addPaymentModal')) {
            return;
        }
        enhancePaymentModal(modal.id.replace('addPaymentModal', ''));
    });

    const originalSubmit = window.submitPaymentForm;
    window.submitPaymentForm = async function (tourId) {
        const typeEl = document.getElementById(`payment_type${tourId}`);
        const method = (typeEl?.value || '').toLowerCase();
        if (method !== 'wallet' && method !== 'stripe') {
            if (typeof originalSubmit === 'function') {
                return originalSubmit(tourId);
            }
            return;
        }

        const amountEl = document.getElementById(`payment_amount${tourId}`);
        const btn = document.getElementById(`savePaymentBtn${tourId}`);
        if (typeEl?.selectedOptions?.[0]?.disabled) {
            alert(typeEl.selectedOptions[0].dataset.reason || 'Selected payment method is unavailable.');
            return;
        }

        try {
            if (btn) btn.disabled = true;
            const result = await initiatePayment(tourId, method, amountEl ? amountEl.value : null);

            if (method === 'stripe' && result.stripe?.client_secret) {
                await confirmStripeCard(result.stripe);
                const uuid = result.attempt?.attempt_uuid;
                if (uuid && result.requires_supplier_booking) {
                    await finalizeStripe(tourId, uuid);
                }
                alert('Card payment successful.');
                window.location.reload();
            } else if (result.requires_supplier_booking && result.attempt?.attempt_uuid) {
                await finalizeWallet(tourId, result.attempt.attempt_uuid);
                alert('Payment completed.');
                window.location.reload();
            } else {
                alert(method === 'wallet'
                    ? 'Wallet deducted and payment auto-verified.'
                    : 'Payment processed successfully.');
                window.location.reload();
            }
        } catch (e) {
            alert(e.message || 'Payment failed');
        } finally {
            if (btn) btn.disabled = false;
        }
    };

    async function postComplete(tourId, path, attemptUuid) {
        const res = await fetch(`/payment-service/tours/${tourId}/${path}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ attempt_uuid: attemptUuid, supplier_results: [] }),
        });
        const json = await res.json();
        if (!res.ok || !json.success) {
            throw new Error(json.message || 'Could not finalize payment');
        }
        return json.data;
    }

    function finalizeWallet(tourId, attemptUuid) {
        return postComplete(tourId, 'wallet/complete', attemptUuid);
    }

    function finalizeStripe(tourId, attemptUuid) {
        return postComplete(tourId, 'stripe/complete', attemptUuid);
    }

    function ensureAcceptanceModal() {
        let modal = document.getElementById('acceptancePayModal');
        if (modal) {
            return modal;
        }
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'acceptancePayModal';
        modal.tabIndex = -1;
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="acceptancePayTitle">Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="acceptancePayHint" class="text-muted small"></p>
                        <label class="form-label">Amount</label>
                        <input id="acceptancePayAmount" class="form-control mb-2" readonly>
                        <label class="form-label">Method</label>
                        <select id="acceptancePayMethod" class="form-select"></select>
                        <small id="acceptancePayHelp" class="text-muted d-block mt-2"></small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="acceptancePaySubmit">Pay</button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
        return modal;
    }

    async function openAcceptancePay(tourId) {
        const payload = await fetchEligibility(tourId);
        const eligibility = payload.data || {};
        const modalEl = ensureAcceptanceModal();
        document.getElementById('acceptancePayTitle').textContent = eligibility.acceptance?.button_label || 'Payment';
        document.getElementById('acceptancePayHint').textContent = eligibility.acceptance?.hint || '';
        const amountEl = document.getElementById('acceptancePayAmount');
        const fx = eligibility.fx || {};
        const payable = Number(eligibility.payable_amount || 0);
        const showWalletAmount = fx.wallet_currency && fx.wallet_currency !== eligibility.currency
            && Number(fx.wallet_payable_amount) > 0;
        amountEl.readOnly = true;
        amountEl.value = payable <= 0
            ? `0.00 ${eligibility.currency || ''}`.trim()
            : (showWalletAmount
                ? `${Number(fx.wallet_payable_amount).toFixed(2)} ${fx.wallet_currency}`
                : `${payable.toFixed(2)} ${eligibility.currency || ''}`.trim());
        const select = document.getElementById('acceptancePayMethod');
        select.innerHTML = '';
        const walletMethod = (eligibility.methods || []).find((method) => method.code === 'wallet');
        const walletBalance = Number(walletMethod?.wallet?.available_balance ?? 0);
        const walletCurrency = walletMethod?.wallet?.currency || eligibility.currency || '';
        (eligibility.methods || []).forEach((method) => {
            if (!['wallet', 'stripe'].includes(method.code)) {
                return;
            }
            const opt = document.createElement('option');
            opt.value = method.code;
            if (method.code === 'wallet') {
                opt.textContent = `Wallet (${walletCurrency} ${walletBalance.toFixed(2)})`;
            } else {
                opt.textContent = method.available ? method.label : `${method.label} (unavailable)`;
            }
            opt.disabled = !method.available;
            opt.dataset.reason = method.reason || '';
            select.appendChild(opt);
        });
        const help = document.getElementById('acceptancePayHelp');
        const walletLine = `Wallet from this DMC: ${walletCurrency} ${walletBalance.toFixed(2)}.`;
        help.textContent = Number(fx.conversion_charge) > 0
            ? `${walletLine} Conversion charge ${fx.charge_percent}% is included when the wallet currency differs from ${eligibility.currency}.`
            : walletLine;
        const submit = document.getElementById('acceptancePaySubmit');
        submit.disabled = payable <= 0;
        submit.onclick = async () => {
            if (payable <= 0) {
                alert('This booking has no payable amount yet.');
                return;
            }
            const method = select.value;
            if (!method || select.selectedOptions[0]?.disabled) {
                alert(select.selectedOptions[0]?.dataset.reason || 'Select an available payment method.');
                return;
            }
            submit.disabled = true;
            try {
                const result = await initiatePayment(tourId, method, eligibility.payable_amount);
                if (method === 'stripe' && result.stripe?.client_secret) {
                    await confirmStripeCard(result.stripe);
                    if (result.attempt?.attempt_uuid && result.requires_supplier_booking) {
                        await finalizeStripe(tourId, result.attempt.attempt_uuid);
                    }
                } else if (result.requires_supplier_booking && result.attempt?.attempt_uuid) {
                    await finalizeWallet(tourId, result.attempt.attempt_uuid);
                }
                window.location.reload();
            } catch (e) {
                alert(e.message || 'Payment failed');
            } finally {
                submit.disabled = false;
            }
        };
        if (window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
        }
    }

    window.TravclicksPaymentService = {
        fetchEligibility,
        enhancePaymentModal,
        initiatePayment,
        confirmStripeCard,
        openAcceptancePay,
    };
})();
</script>
