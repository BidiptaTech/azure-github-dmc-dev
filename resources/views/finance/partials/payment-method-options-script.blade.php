{{--
  Extends existing Add Payment modals with server-driven Wallet / Stripe options.
  Laravel-only payment service (no Python / no Razorpay).
  Use url() so APP_URL subpath (e.g. /backadm-dmc) is included — root /payment-service returns HTML.
--}}
@php $paymentServiceBase = rtrim(url('/payment-service'), '/'); @endphp
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    if (window.TravclicksPaymentService) {
        return;
    }

    if (!document.getElementById('payment-eligibility-tooltip-style')) {
        const style = document.createElement('style');
        style.id = 'payment-eligibility-tooltip-style';
        style.textContent = [
            '.payment-eligibility-tooltip .tooltip-inner{max-width:280px;text-align:left;font-size:12px;line-height:1.35;}',
            'select.form-select option.pay-method-unavailable,#acceptancePayMethod option.pay-method-unavailable{color:#9ca3af !important;background-color:#f3f4f6;font-style:italic;}'
        ].join('');
        document.head.appendChild(style);
    }

    function formatPaymentMethodLabel(method, eligibility) {
        if (!method) {
            return '';
        }
        // Wallet always shows live balance (same as before). Enable/disable is separate.
        if (method.code === 'wallet') {
            const bal = Number(method.wallet?.available_balance ?? 0);
            const cur = method.wallet?.currency || eligibility?.currency || '';
            const balTxt = bal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            return `Wallet (${cur} ${balTxt})`;
        }
        if (method.available) {
            return method.label || method.code;
        }
        return `${method.label || method.code} — Not available`;
    }

    const PAYMENT_SERVICE_BASE = @json($paymentServiceBase);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value;

    async function parseJsonResponse(res) {
        const text = await res.text();
        try {
            return text ? JSON.parse(text) : {};
        } catch (e) {
            throw new Error(
                res.ok
                    ? 'Payment service returned HTML instead of JSON. Check APP_URL / base path.'
                    : `Payment service error (${res.status}).`
            );
        }
    }

    async function fetchEligibility(tourId, amount) {
        const params = new URLSearchParams();
        if (amount != null && amount !== '' && !Number.isNaN(Number(amount)) && Number(amount) > 0) {
            params.set('amount', String(amount));
        }
        const qs = params.toString();
        const res = await fetch(`${PAYMENT_SERVICE_BASE}/tours/${tourId}/eligibility${qs ? `?${qs}` : ''}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await parseJsonResponse(res);
        if (!res.ok || json.success === false) {
            throw new Error(json.message || 'Unable to load payment options');
        }
        return json;
    }

    function moneyFmt(n, maxFrac = 2) {
        const num = Number(n);
        if (!Number.isFinite(num)) {
            return '—';
        }
        return num.toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: maxFrac,
        });
    }

    function round4(n) {
        return Math.round(Number(n) * 10000) / 10000;
    }

    function roundMoney(n, currency) {
        const num = Number(n);
        if (!Number.isFinite(num)) {
            return 0;
        }
        if (String(currency || '').toUpperCase() === 'IDR') {
            return Math.round(num);
        }
        return Math.round(num * 100) / 100;
    }

    function fxCurrencies(eligibility) {
        const fx = eligibility?.fx || {};
        const walletCur = String(
            fx.wallet_currency
            || eligibility?.methods?.find((m) => m.code === 'wallet')?.wallet?.currency
            || ''
        ).toUpperCase();
        const orderCur = String(fx.order_currency || eligibility?.currency || '').toUpperCase();
        return { fx, walletCur, orderCur, converted: !!(walletCur && orderCur && walletCur !== orderCur) };
    }

    /** Order currency → DMC (includes conversion charge). */
    function orderToDmc(orderAmount, fx) {
        const rate = Number(fx?.exchange_rate || 0);
        const pct = Number(fx?.charge_percent || 0);
        if (!(rate > 0)) {
            return Number(orderAmount) || 0;
        }
        const converted = Number(orderAmount) * rate;
        const fee = pct > 0 ? converted * (pct / 100) : 0;
        return round4(converted + fee);
    }

    /** DMC pay amount → order currency (reverse of orderToDmc). */
    function dmcToOrder(dmcAmount, fx) {
        const rate = Number(fx?.exchange_rate || 0);
        const pct = Number(fx?.charge_percent || 0);
        const factor = rate * (1 + (pct > 0 ? pct / 100 : 0));
        if (!(factor > 0)) {
            return Number(dmcAmount) || 0;
        }
        return round4(Number(dmcAmount) / factor);
    }

    function walletRequiredAmount(eligibility, enteredOrderAmount) {
        const { fx, walletCur, orderCur, converted } = fxCurrencies(eligibility);
        const payable = Number(eligibility.payable_amount || 0);
        const walletPayable = Number(fx.wallet_payable_amount || 0);
        const orderAmount = Number.isFinite(enteredOrderAmount) && enteredOrderAmount > 0
            ? enteredOrderAmount
            : payable;

        if (!converted) {
            return { required: orderAmount, currency: orderCur || walletCur, converted: false, fx };
        }

        // Eligibility refreshed with typed order amount → wallet_payable already includes FX + fee.
        if (walletPayable > 0) {
            return { required: walletPayable, currency: walletCur, converted: true, fx };
        }

        if (Number(fx.exchange_rate || 0) > 0) {
            return { required: orderToDmc(orderAmount, fx), currency: walletCur, converted: true, fx };
        }

        return { required: orderAmount, currency: walletCur, converted: true, fx };
    }

    function maxOrderAmount(tourId) {
        const hidden = document.getElementById(`payment_amount${tourId}`);
        const fromData = parseFloat(hidden?.dataset?.maxOrder || '');
        if (Number.isFinite(fromData) && fromData > 0) {
            return Math.ceil(fromData);
        }
        const fromAmount = Math.ceil(parseFloat(document.getElementById(`amount${tourId}`)?.value) || 0);
        return fromAmount > 0 ? fromAmount : 0;
    }

    /** Sync hidden order amount + readonly order display from editable DMC pay field. */
    function syncOrderFromDmcPay(tourId, eligibility) {
        const dmcEl = document.getElementById(`dmc_pay_amount${tourId}`);
        const orderHidden = document.getElementById(`payment_amount${tourId}`);
        const orderDisplay = document.getElementById(`order_currency_amount${tourId}`);
        if (!dmcEl || !orderHidden) {
            return NaN;
        }

        const elig = eligibility || window.__paymentEligibility?.[tourId] || null;
        const { fx, walletCur, orderCur, converted } = fxCurrencies(elig || {
            currency: dmcEl.dataset.tourCurrency || '',
            fx: { wallet_currency: dmcEl.dataset.dmcCurrency || '' },
        });
        const tourCur = String(orderCur || dmcEl.dataset.tourCurrency || '').toUpperCase();
        const dmcCur = String(walletCur || dmcEl.dataset.dmcCurrency || '').toUpperCase();
        const needsFx = !!(tourCur && dmcCur && tourCur !== dmcCur);
        const rate = Number(fx.exchange_rate || 0);
        const dmcAmount = parseFloat(dmcEl.value);
        if (!Number.isFinite(dmcAmount) || dmcAmount <= 0) {
            orderHidden.value = '';
            if (orderDisplay) orderDisplay.value = '';
            return NaN;
        }

        // Wait for FX quote before writing order currency — avoid storing IDR into SGD field.
        if (needsFx && !(rate > 0)) {
            return parseFloat(orderHidden.value) || NaN;
        }

        let orderAmount = (needsFx || converted) ? dmcToOrder(dmcAmount, fx) : dmcAmount;
        orderAmount = roundMoney(orderAmount, tourCur);
        if (orderAmount < 0.01 && orderAmount > 0) {
            orderAmount = 0.01;
        }
        orderHidden.value = String(orderAmount);
        if (orderDisplay) {
            orderDisplay.value = moneyFmt(orderAmount, tourCur === 'IDR' ? 0 : 2);
        }
        return orderAmount;
    }

    /** Seed editable DMC pay from remaining order amount (modal open). */
    function seedDmcPayFromOrder(tourId, eligibility, force) {
        const dmcEl = document.getElementById(`dmc_pay_amount${tourId}`);
        const orderHidden = document.getElementById(`payment_amount${tourId}`);
        if (!dmcEl || !orderHidden) {
            return;
        }
        if (!force && dmcEl.dataset.seeded === '1' && dmcEl.value !== '') {
            syncOrderFromDmcPay(tourId, eligibility);
            return;
        }

        const { fx, walletCur, orderCur, converted } = fxCurrencies(eligibility);
        const maxOrder = maxOrderAmount(tourId);
        const orderSeed = maxOrder > 0
            ? maxOrder
            : (parseFloat(orderHidden.value) || Number(eligibility?.payable_amount || 0) || 0);
        const dmcCur = converted ? walletCur : (orderCur || walletCur || dmcEl.dataset.dmcCurrency || '');
        const dmcSeed = converted ? orderToDmc(orderSeed, fx) : orderSeed;
        const rounded = roundMoney(dmcSeed, dmcCur);

        dmcEl.value = rounded > 0 ? String(rounded) : '';
        dmcEl.dataset.seeded = '1';
        if (dmcCur) {
            dmcEl.dataset.dmcCurrency = dmcCur;
            // min must align with step (min=0.01 + step=1 rejects whole IDR amounts).
            dmcEl.min = dmcCur === 'IDR' ? '1' : '0.01';
            dmcEl.step = dmcCur === 'IDR' ? '1' : '0.01';
        }
        syncOrderFromDmcPay(tourId, eligibility);
    }

    function updateWalletFxDisplay(tourId, eligibility) {
        const box = document.getElementById(`walletFxBox${tourId}`);
        const amountEl = document.getElementById(`walletFxAmount${tourId}`);
        const dmcPayEl = document.getElementById(`dmc_pay_amount${tourId}`);
        const dmcCodeEl = document.getElementById(`dmcCurrencyCode${tourId}`);
        const orderBox = document.getElementById(`orderAmountBox${tourId}`);
        const orderCodeEl = document.getElementById(`orderCurrencyCode${tourId}`);
        const orderDisplay = document.getElementById(`order_currency_amount${tourId}`);
        const detailEl = document.getElementById(`walletFxDetail${tourId}`);
        const maxHelpEl = document.getElementById(`dmcPayMaxHelp${tourId}`);
        const blockEl = document.getElementById(`walletFxBlock${tourId}`);
        const rateEl = document.getElementById(`exchange_rate${tourId}`);
        const orderHidden = document.getElementById(`payment_amount${tourId}`);
        if (!eligibility) {
            return;
        }

        const { fx, walletCur, orderCur, converted } = fxCurrencies(eligibility);
        const enteredOrder = orderHidden ? parseFloat(orderHidden.value) : NaN;
        const quote = walletRequiredAmount(eligibility, enteredOrder);
        const wallet = eligibility.methods?.find((m) => m.code === 'wallet');
        const balance = Number(wallet?.wallet?.available_balance ?? wallet?.available_balance ?? 0);
        const dmcCur = quote.currency || walletCur || orderCur;
        const frac = dmcCur === 'IDR' ? 0 : 2;
        const maxOrder = maxOrderAmount(tourId);
        const maxDmc = converted && maxOrder > 0 ? roundMoney(orderToDmc(maxOrder, fx), dmcCur) : maxOrder;

        if (box) {
            box.classList.remove('d-none');
            box.style.display = '';
        }
        if (dmcCodeEl && dmcCur) {
            dmcCodeEl.textContent = dmcCur;
        }
        const dmcCurrencyHidden = document.getElementById(`dmc_currency${tourId}`);
        if (dmcCurrencyHidden && dmcCur) {
            dmcCurrencyHidden.value = dmcCur;
        }
        const fxRateHidden = document.getElementById(`fx_exchange_rate${tourId}`);
        if (fxRateHidden) {
            const rate = Number(fx.exchange_rate || 0);
            fxRateHidden.value = converted && rate > 0 ? String(rate) : '';
        }
        if (orderCodeEl && orderCur) {
            orderCodeEl.textContent = orderCur;
        }
        if (orderBox) {
            if (converted) {
                orderBox.classList.remove('d-none');
            } else {
                orderBox.classList.add('d-none');
            }
        }
        if (orderDisplay && Number.isFinite(enteredOrder)) {
            orderDisplay.value = moneyFmt(enteredOrder, orderCur === 'IDR' ? 0 : 2);
        }
        if (dmcPayEl && dmcCur) {
            dmcPayEl.dataset.dmcCurrency = dmcCur;
            dmcPayEl.dataset.tourCurrency = orderCur || dmcPayEl.dataset.tourCurrency || '';
            dmcPayEl.min = dmcCur === 'IDR' ? '1' : '0.01';
            dmcPayEl.step = dmcCur === 'IDR' ? '1' : '0.01';
            // Clamp in JS only — HTML max + FX fractions causes "nearest valid value" errors.
            dmcPayEl.removeAttribute('max');
        }
        if (maxHelpEl) {
            maxHelpEl.textContent = maxDmc > 0
                ? `Maximum: ${moneyFmt(maxDmc, frac)} ${dmcCur}`
                : `Pay in ${dmcCur || 'DMC currency'}.`;
        }
        if (amountEl) {
            const dmcTyped = dmcPayEl ? parseFloat(dmcPayEl.value) : NaN;
            const show = Number.isFinite(dmcTyped) ? dmcTyped : quote.required;
            amountEl.textContent = `${moneyFmt(show, frac)} ${dmcCur || ''}`.trim();
        }
        if (detailEl) {
            // Rate = base + FX fee (no % / fee lines). Paid amount already uses effective rate.
            const sgdEffective = Number(fx.sgd_rate_wallet_effective || 0) > 0
                ? Number(fx.sgd_rate_wallet_effective)
                : (() => {
                    const base = Number(fx.sgd_rate_wallet || 0);
                    const pct = Number(fx.charge_percent || 0);
                    return base > 0 && pct > 0 ? base * (1 + pct / 100) : base;
                })();
            if (converted && sgdEffective > 0 && dmcCur && dmcCur !== 'SGD') {
                detailEl.textContent = `1 SGD = ${moneyFmt(sgdEffective, 2)} ${dmcCur}`;
            } else if (converted) {
                detailEl.textContent = `Display only — booking remaining stays in ${orderCur}.`;
            } else {
                detailEl.textContent = dmcCur ? `DMC currency: ${dmcCur}` : '';
            }
        }
        // Offline methods store order-currency amount; keep exchange_rate = 1.
        if (rateEl) {
            rateEl.value = '1';
        }

        const requiredDmc = Number.isFinite(parseFloat(dmcPayEl?.value))
            ? parseFloat(dmcPayEl.value)
            : quote.required;
        const enough = requiredDmc > 0 && balance + 0.0001 >= requiredDmc;
        if (blockEl) {
            if (converted && requiredDmc > 0 && !enough) {
                blockEl.classList.add('is-visible');
                blockEl.classList.remove('d-none');
                blockEl.textContent = `Insufficient wallet balance: required ${moneyFmt(requiredDmc, frac)} ${dmcCur}, available ${moneyFmt(balance, frac)} ${dmcCur}.`;
            } else {
                blockEl.classList.remove('is-visible');
                blockEl.classList.add('d-none');
                blockEl.textContent = '';
            }
        }
    }

    function applyWalletAvailabilityFromAmount(tourId) {
        const eligibility = window.__paymentEligibility?.[tourId];
        if (!eligibility) {
            return;
        }
        const dmcEl = document.getElementById(`dmc_pay_amount${tourId}`);
        const amountEl = document.getElementById(`payment_amount${tourId}`);
        const enteredOrder = amountEl ? parseFloat(amountEl.value) : NaN;
        const wallet = eligibility.methods?.find((m) => m.code === 'wallet');
        if (!wallet) {
            updateWalletFxDisplay(tourId, eligibility);
            return;
        }
        const quote = walletRequiredAmount(eligibility, enteredOrder);
        const typedDmc = dmcEl ? parseFloat(dmcEl.value) : NaN;
        const checkAmount = Number.isFinite(typedDmc) && typedDmc > 0 ? typedDmc : quote.required;
        const balance = Number(wallet.wallet?.available_balance ?? wallet.available_balance ?? 0);
        const currency = quote.currency || wallet.wallet?.currency || eligibility.currency || '';
        const settingsOk = eligibility.settings?.wallet_enabled !== false;
        const available = settingsOk && checkAmount > 0 && balance + 0.0001 >= checkAmount;
        wallet.available = available;
        wallet.reason = available
            ? null
            : (checkAmount <= 0
                ? 'No payable amount on this booking.'
                : (balance <= 0
                    ? `No ${currency} balance in the DMC wallet.`
                    : `Insufficient DMC wallet balance (${currency} ${moneyFmt(balance, currency === 'IDR' ? 0 : 2)} available, ${moneyFmt(checkAmount, currency === 'IDR' ? 0 : 2)} required).`));
        if (wallet.wallet) {
            wallet.wallet.required_amount = checkAmount;
        }
        updateWalletFxDisplay(tourId, eligibility);
        applyMethodsToSelect(document.getElementById(`payment_type${tourId}`), eligibility);
    }

    function bindAmountWatcher(tourId) {
        const dmcEl = document.getElementById(`dmc_pay_amount${tourId}`);
        if (!dmcEl || dmcEl.dataset.walletWatchBound === '1') {
            return;
        }
        dmcEl.dataset.walletWatchBound = '1';
        let timer = null;
        const refresh = () => {
            syncOrderFromDmcPay(tourId);
            clearTimeout(timer);
            timer = setTimeout(async () => {
                const orderHidden = document.getElementById(`payment_amount${tourId}`);
                try {
                    const payload = await fetchEligibility(tourId, orderHidden?.value);
                    if (payload?.data) {
                        window.__paymentEligibility = window.__paymentEligibility || {};
                        window.__paymentEligibility[tourId] = payload.data;
                        // Re-sync order with refreshed FX factors; keep typed DMC value.
                        syncOrderFromDmcPay(tourId, payload.data);
                        applyMethodsToSelect(document.getElementById(`payment_type${tourId}`), payload.data);
                    }
                } catch (e) {
                    console.warn('payment eligibility refresh', e);
                }
                applyWalletAvailabilityFromAmount(tourId);
            }, 250);
        };
        dmcEl.addEventListener('input', refresh);
        dmcEl.addEventListener('change', refresh);
    }

    function ensureEligibilityTooltip(tourId) {
        let btn = document.getElementById(`paymentEligibilityInfo${tourId}`);
        const selectEl = document.getElementById(`payment_type${tourId}`);
        if (!btn && selectEl?.parentElement) {
            const label = selectEl.parentElement.querySelector('label');
            btn = document.createElement('button');
            btn.type = 'button';
            btn.id = `paymentEligibilityInfo${tourId}`;
            btn.className = 'btn btn-link p-0 border-0 text-muted lh-1';
            btn.setAttribute('data-bs-toggle', 'tooltip');
            btn.setAttribute('data-bs-placement', 'left');
            btn.setAttribute('data-bs-title', 'Payment options load when this modal opens.');
            btn.setAttribute('aria-label', 'Payment method info');
            btn.innerHTML = '<i class="fas fa-info-circle"></i>';
            if (label) {
                label.classList.add('d-flex', 'align-items-center', 'gap-1');
                label.appendChild(btn);
            } else {
                selectEl.parentElement.insertBefore(btn, selectEl);
            }
        }
        // Hide any old block help text if it still exists from earlier builds.
        const legacy = document.getElementById(`paymentEligibilityHelp${tourId}`);
        if (legacy) {
            legacy.remove();
        }
        return btn;
    }

    function setEligibilityTooltip(tourId, message) {
        const btn = ensureEligibilityTooltip(tourId);
        if (!btn) {
            return;
        }
        const text = (message || '').trim() || 'Payment options load when this modal opens.';
        // Use data-bs-title only — keep native `title` empty so the browser white tooltip does not appear.
        btn.removeAttribute('title');
        btn.setAttribute('data-bs-title', text);
        btn.setAttribute('data-bs-original-title', text);
        btn.setAttribute('aria-label', 'Payment method info');
        if (window.bootstrap?.Tooltip) {
            const existing = bootstrap.Tooltip.getInstance(btn);
            if (existing) {
                existing.setContent({ '.tooltip-inner': text });
            } else {
                new bootstrap.Tooltip(btn, {
                    title: text,
                    trigger: 'hover focus',
                    container: 'body',
                    customClass: 'payment-eligibility-tooltip',
                });
            }
        }
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
            opt.textContent = formatPaymentMethodLabel(method, eligibility);
            opt.disabled = !method.available;
            // Only gray-out non-wallet methods when unavailable (wallet keeps normal balance label).
            opt.classList.toggle('pay-method-unavailable', method.code !== 'wallet' && !method.available);
            opt.dataset.reason = method.reason || '';
            if (!method.available && current === opt.value) {
                selectEl.value = '';
            }
        });

        let msg = `Stage: ${eligibility.payment_stage}. Recipient: ${eligibility.payment_recipient}.`;
        const walletMethod = eligibility.methods.find((m) => m.code === 'wallet');
        if (walletMethod && !walletMethod.available && walletMethod.reason) {
            msg += ` Wallet: ${walletMethod.reason}`;
        }
        const fx = eligibility.fx || {};
        const sgdEff = Number(fx.sgd_rate_wallet_effective || 0) > 0
            ? Number(fx.sgd_rate_wallet_effective)
            : (() => {
                const base = Number(fx.sgd_rate_wallet || 0);
                const pct = Number(fx.charge_percent || 0);
                return base > 0 && pct > 0 ? base * (1 + pct / 100) : base;
            })();
        if (sgdEff > 0 && fx.wallet_currency && String(fx.wallet_currency).toUpperCase() !== 'SGD'
            && fx.order_currency && String(fx.order_currency).toUpperCase() !== String(fx.wallet_currency).toUpperCase()) {
            msg += ` Rate: 1 SGD = ${sgdEff.toFixed(2)} ${fx.wallet_currency}.`;
        }
        if (eligibility.acceptance?.hint) {
            msg += ` ${eligibility.acceptance.hint}`;
        }
        if (eligibility.amount_readonly) {
            msg += ` Payable (read-only): ${eligibility.payable_amount} ${eligibility.currency}.`;
            const orderHidden = document.getElementById(`payment_amount${eligibility.tour_id}`);
            const dmcPay = document.getElementById(`dmc_pay_amount${eligibility.tour_id}`);
            if (orderHidden) {
                orderHidden.value = eligibility.payable_amount;
            }
            if (dmcPay) {
                seedDmcPayFromOrder(eligibility.tour_id, eligibility, true);
                dmcPay.readOnly = true;
                dmcPay.classList.add('bg-light');
            }
        } else {
            const dmcPay = document.getElementById(`dmc_pay_amount${eligibility.tour_id}`);
            if (dmcPay) {
                dmcPay.readOnly = false;
                dmcPay.classList.remove('bg-light');
            }
        }
        setEligibilityTooltip(eligibility.tour_id, msg);
        updateAddPaymentOnlinePaidBanner(eligibility);
        window.__paymentEligibility = window.__paymentEligibility || {};
        window.__paymentEligibility[eligibility.tour_id] = eligibility;
    }

    function updateAddPaymentOnlinePaidBanner(eligibility) {
        if (!eligibility || !eligibility.tour_id) {
            return;
        }
        const tourId = eligibility.tour_id;
        const banner = document.getElementById(`addPaymentOnlinePaidBanner${tourId}`);
        const textEl = document.getElementById(`addPaymentOnlinePaidText${tourId}`);
        if (!banner || !textEl) {
            return;
        }

        const acceptance = eligibility.acceptance || {};
        const onlinePaid = !!acceptance.online_paid;
        const currency = String(acceptance.currency || eligibility.currency || '').trim().toUpperCase();
        const paidAmount = Number(acceptance.online_paid_amount || acceptance.online_amount || 0);
        const hint = String(acceptance.hint || '').trim();
        const amountLabel = (currency ? currency + ' ' : '') + (Number.isFinite(paidAmount) ? paidAmount.toFixed(2) : '0.00');

        if (!onlinePaid || !(paidAmount > 0)) {
            banner.classList.add('d-none');
            textEl.textContent = '';
            return;
        }

        if (hint) {
            const safeHint = hint
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            const safeAmount = amountLabel
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            textEl.innerHTML = safeHint.includes(amountLabel)
                ? safeHint.replace(safeAmount, '<strong>' + safeAmount + '</strong>')
                : safeHint;
        } else {
            textEl.innerHTML = 'Online payment complete: <strong>' + amountLabel
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                + '</strong>. Collect the offline remaining amount below.';
        }
        banner.classList.remove('d-none');
    }

    async function enhancePaymentModal(tourId) {
        try {
            const amountEl = document.getElementById(`payment_amount${tourId}`);
            const dmcEl = document.getElementById(`dmc_pay_amount${tourId}`);
            if (dmcEl) {
                dmcEl.dataset.seeded = '';
            }
            const payload = await fetchEligibility(tourId, amountEl?.value);
            if (!payload?.data) {
                return null;
            }
            window.__paymentEligibility = window.__paymentEligibility || {};
            window.__paymentEligibility[tourId] = payload.data;
            seedDmcPayFromOrder(tourId, payload.data, true);
            applyMethodsToSelect(document.getElementById(`payment_type${tourId}`), payload.data);
            bindAmountWatcher(tourId);
            updateWalletFxDisplay(tourId, payload.data);
            applyWalletAvailabilityFromAmount(tourId);
            if (typeof window.validatePaymentAmountInput === 'function') {
                window.validatePaymentAmountInput(tourId);
            }
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
        const res = await fetch(`${PAYMENT_SERVICE_BASE}/tours/${tourId}/pay`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });
        const json = await parseJsonResponse(res);
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
        const res = await fetch(`${PAYMENT_SERVICE_BASE}/tours/${tourId}/${path}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ attempt_uuid: attemptUuid, supplier_results: [] }),
        });
        const json = await parseJsonResponse(res);
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
            // Older page sessions may lack Transaction ID — inject before Remarks.
            if (!document.getElementById('acceptancePayTransactionId')) {
                const remarks = document.getElementById('acceptancePayRemarks');
                const remarksLabel = remarks?.previousElementSibling;
                const txnHtml = `
                        <label class="form-label mt-3" for="acceptancePayTransactionId">
                            Transaction ID <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <input type="text"
                            id="acceptancePayTransactionId"
                            class="form-control"
                            maxlength="128"
                            placeholder="Bank / payment reference">`;
                if (remarksLabel && remarksLabel.tagName === 'LABEL') {
                    remarksLabel.insertAdjacentHTML('beforebegin', txnHtml);
                } else if (remarks) {
                    remarks.insertAdjacentHTML('beforebegin', txnHtml);
                }
            }
            return modal;
        }
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'acceptancePayModal';
        modal.tabIndex = -1;
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <div class="small text-white-50 mb-1" style="opacity:.85;letter-spacing:.04em;text-transform:uppercase;font-weight:600;font-size:.68rem;">Online services</div>
                            <h5 class="modal-title mb-0" id="acceptancePayTitle">Payment</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="acceptancePayHint" class="small mb-3"></p>
                        <label class="form-label" for="acceptancePayAmount">Amount due</label>
                        <input id="acceptancePayAmount" class="form-control mb-3" readonly>
                        <label class="form-label" for="acceptancePayMethod">Payment method</label>
                        <select id="acceptancePayMethod" class="form-select"></select>
                        <label class="form-label mt-3" for="acceptancePayTransactionId">
                            Transaction ID <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <input type="text"
                            id="acceptancePayTransactionId"
                            class="form-control"
                            maxlength="128"
                            placeholder="Bank / payment reference">
                        <label class="form-label mt-3" for="acceptancePayRemarks">
                            Remarks <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea id="acceptancePayRemarks" class="form-control" rows="2" maxlength="2000" placeholder="Optional note for this payment"></textarea>
                        <small id="acceptancePayHelp" class="text-muted d-block mt-2"></small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="acceptancePaySubmit">
                            <i class="ri-secure-payment-line me-1"></i>Pay now
                        </button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
        return modal;
    }

    async function openAcceptancePay(tourId) {
        try {
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
                opt.textContent = formatPaymentMethodLabel(method, eligibility);
                opt.disabled = !method.available;
                opt.classList.toggle('pay-method-unavailable', method.code !== 'wallet' && !method.available);
                opt.dataset.reason = method.reason || '';
                select.appendChild(opt);
            });
            const txnEl = document.getElementById('acceptancePayTransactionId');
            if (txnEl) txnEl.value = '';
            const remarksEl = document.getElementById('acceptancePayRemarks');
            if (remarksEl) remarksEl.value = '';
            const help = document.getElementById('acceptancePayHelp');
            const orderCur = String(fx.order_currency || eligibility.currency || '').toUpperCase();
            const walletLine = `Wallet from this DMC: ${walletCurrency} ${Number(walletBalance || 0).toFixed(2)}.`;
            const sgdEffective = Number(fx.sgd_rate_wallet_effective || 0) > 0
                ? Number(fx.sgd_rate_wallet_effective)
                : (() => {
                    const base = Number(fx.sgd_rate_wallet || 0);
                    const pct = Number(fx.charge_percent || 0);
                    return base > 0 && pct > 0 ? base * (1 + pct / 100) : base;
                })();
            const rateLine = (sgdEffective > 0 && walletCurrency && walletCurrency !== 'SGD'
                && orderCur && orderCur !== walletCurrency)
                ? ` Rate: 1 SGD = ${sgdEffective.toFixed(2)} ${walletCurrency}.`
                : '';
            help.textContent = `${walletLine}${rateLine}`;
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
                    const userRemarks = (document.getElementById('acceptancePayRemarks')?.value || '').trim();
                    const transactionId = (document.getElementById('acceptancePayTransactionId')?.value || '').trim();
                    const result = await initiatePayment(tourId, method, eligibility.payable_amount, {
                        user_remarks: userRemarks,
                        transaction_id: transactionId || undefined,
                    });
                    if (method === 'stripe' && result.stripe?.client_secret) {
                        await confirmStripeCard(result.stripe);
                        if (result.attempt?.attempt_uuid && result.requires_supplier_booking) {
                            await finalizeStripe(tourId, result.attempt.attempt_uuid);
                        }
                    } else if (result.requires_supplier_booking && result.attempt?.attempt_uuid) {
                        await finalizeWallet(tourId, result.attempt.attempt_uuid);
                    }

                    // Close pay modal, then open the same online-hotel approve flow used on confirmed.
                    if (window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    } else {
                        modalEl.classList.remove('show');
                        modalEl.style.display = 'none';
                    }

                    if (typeof window.openPendingOnlineHotelApprovalsAfterPay === 'function'
                        && window.openPendingOnlineHotelApprovalsAfterPay(tourId)) {
                        return;
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
        } catch (e) {
            alert(e.message || 'Unable to open payment options');
        }
    }

    // DMC-first amount field: validate typed DMC, clamp to max, sync hidden order amount.
    const previousValidatePaymentAmountInput = window.validatePaymentAmountInput;
    window.validatePaymentAmountInput = function (tourId) {
        const dmcEl = document.getElementById(`dmc_pay_amount${tourId}`);
        if (!dmcEl) {
            if (typeof previousValidatePaymentAmountInput === 'function') {
                return previousValidatePaymentAmountInput(tourId);
            }
            return;
        }

        const orderHidden = document.getElementById(`payment_amount${tourId}`);
        const validationError = document.getElementById(`paymentValidationError${tourId}`);
        const validationMessage = document.getElementById(`validationMessage${tourId}`);
        const saveBtn = document.getElementById(`savePaymentBtn${tourId}`);
        const eligibility = window.__paymentEligibility?.[tourId] || null;
        const { fx, walletCur, orderCur, converted } = fxCurrencies(eligibility || {
            currency: dmcEl.dataset.tourCurrency || '',
            fx: { wallet_currency: dmcEl.dataset.dmcCurrency || '' },
        });
        const dmcCur = converted
            ? (walletCur || dmcEl.dataset.dmcCurrency || '')
            : (orderCur || walletCur || dmcEl.dataset.dmcCurrency || '');
        const maxOrder = maxOrderAmount(tourId);
        const maxDmc = converted && maxOrder > 0
            ? roundMoney(orderToDmc(maxOrder, fx), dmcCur)
            : maxOrder;

        let dmcAmount = parseFloat(dmcEl.value);
        if (!Number.isFinite(dmcAmount)) {
            dmcAmount = 0;
        }
        if (maxDmc > 0 && dmcAmount > maxDmc) {
            dmcEl.value = String(maxDmc);
            dmcAmount = maxDmc;
        }

        let orderAmount = syncOrderFromDmcPay(tourId, eligibility);
        if (Number.isFinite(orderAmount) && maxOrder > 0 && orderAmount > maxOrder && orderHidden) {
            orderHidden.value = String(maxOrder);
            const clampedDmc = converted ? roundMoney(orderToDmc(maxOrder, fx), dmcCur) : maxOrder;
            dmcEl.value = String(clampedDmc);
            dmcAmount = clampedDmc;
            orderAmount = syncOrderFromDmcPay(tourId, eligibility);
        }

        if (typeof window.updatePaymentAmountMax === 'function') {
            window.updatePaymentAmountMax(tourId);
        }

        if (!dmcAmount || dmcAmount <= 0) {
            if (validationError) validationError.style.display = 'block';
            if (validationMessage) validationMessage.textContent = 'Please enter a valid payment amount';
            if (saveBtn) saveBtn.disabled = true;
            return;
        }

        if (validationError) validationError.style.display = 'none';
        if (saveBtn) saveBtn.disabled = false;

        if (eligibility) {
            updateWalletFxDisplay(tourId, eligibility);
        }
    };

    window.TravclicksPaymentService = {
        fetchEligibility,
        enhancePaymentModal,
        initiatePayment,
        confirmStripeCard,
        openAcceptancePay,
        syncOrderFromDmcPay,
        seedDmcPayFromOrder,
    };
})();
</script>
<style>
    #acceptancePayModal .modal-content {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    }
    #acceptancePayModal .modal-header {
        background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
        color: #fff;
        border: 0;
        padding: 1rem 1.25rem;
    }
    #acceptancePayModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%);
        opacity: 0.85;
    }
    #acceptancePayModal .modal-title {
        font-weight: 700;
        font-size: 1.05rem;
    }
    #acceptancePayModal .modal-body {
        padding: 1.15rem 1.25rem 0.5rem;
    }
    #acceptancePayModal #acceptancePayHint {
        margin-bottom: 1rem;
        padding: 0.65rem 0.75rem;
        border-radius: 0.65rem;
        background: #f0fdfa;
        border: 1px solid #99f6e4;
        color: #134e4a;
        font-size: 0.82rem;
        line-height: 1.4;
    }
    #acceptancePayModal #acceptancePayHint:empty {
        display: none;
    }
    #acceptancePayModal .form-label {
        font-size: 0.78rem;
        font-weight: 650;
        color: #334155;
        margin-bottom: 0.3rem;
    }
    #acceptancePayModal #acceptancePayAmount {
        font-weight: 700;
        font-size: 1.05rem;
        color: #0f766e;
        background: #f8fafc;
    }
    #acceptancePayModal .modal-footer {
        border: 0;
        padding: 0.75rem 1.25rem 1.15rem;
        gap: 0.5rem;
    }
    #acceptancePayModal #acceptancePaySubmit {
        background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
        border: 0;
        font-weight: 650;
        min-width: 7rem;
    }
</style>
