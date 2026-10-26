@extends('layouts.layout')

@section('title', 'Wallet Management')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php $isTravclicks = ($roleGroup ?? '') === 'travclicks_owner'; @endphp

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h4 class="mb-1">Wallet Management</h4>
                <p class="text-muted mb-0">
                    @if($isTravclicks)
                        Travclicks → DMC funding · spend record only (no Travclicks balance)
                    @else
                        {{ $ownerDisplayName ?? 'Wallet' }} · {{ $currency ?? 'SGD' }} wallet
                    @endif
                </p>
            </div>
            @unless($isTravclicks)
            <a href="{{ route('finance.wallet.transactions', !empty($isAdmin) ? ['scope' => 'master_dmc_flow'] : array_filter(['owner_type' => $ownerType ?? null, 'owner_id' => $ownerId ?? null, 'currency' => $currency ?? null])) }}"
               class="btn btn-primary btn-sm">
                <i class="ri-pulse-line me-1"></i> Master DMC Fund Flow
            </a>
            @endunless
        </div>

        @if($isTravclicks)
            {{-- Travclicks: only spend-to-DMC totals by currency --}}
            <div class="row mb-4">
                @forelse(($travclicksSpendByCurrency ?? []) as $spend)
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                                    <i class="ri-send-plane-line ri-xl"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Spent to DMC ({{ $spend['currency'] }})</div>
                                    <div class="fs-4 fw-bold">{{ $spend['currency'] }} {{ number_format((float) $spend['amount'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-muted">No spend to DMC yet. Send funds below — amount uses the DMC’s currency.</div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            {{-- Balance cards (Master DMC / DMC / others) --}}
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                                <i class="ri-wallet-3-line ri-xl"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Available Balance</div>
                                <div class="fs-4 fw-bold">{{ $currency ?? 'SGD' }} {{ number_format((float)($wallet->available_balance ?? 0), 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                                <i class="ri-lock-line ri-xl"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Reserved</div>
                                <div class="fs-4 fw-bold">{{ $currency ?? 'SGD' }} {{ number_format((float)($wallet->reserved_balance ?? 0), 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                                <i class="ri-arrow-down-circle-line ri-xl"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Credited</div>
                                <div class="fs-5 fw-semibold">{{ $currency ?? 'SGD' }} {{ number_format((float)($ledgerSummary['credit'] ?? $wallet->total_credited ?? 0), 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                                <i class="ri-arrow-up-circle-line ri-xl"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Total Debited</div>
                                <div class="fs-5 fw-semibold">{{ $currency ?? 'SGD' }} {{ number_format((float)($ledgerSummary['debit'] ?? $wallet->total_debited ?? 0), 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            @if(in_array($roleGroup, ['travclicks_owner', 'master_dmc', 'dmc'], true))
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">
                                <i class="ri-exchange-dollar-line me-1"></i>
                                @if($roleGroup === 'travclicks_owner') Send to DMC @endif
                                @if($roleGroup === 'master_dmc') Allocate Wallet (Master) @endif
                                @if($roleGroup === 'dmc') Allocate Wallet (DMC) @endif
                            </h5>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('finance.wallet.topup') }}">
                                @csrf

                                <input type="hidden" name="wallet_owner_type" id="wallet_owner_type_input" value="">
                                <input type="hidden" name="wallet_owner_id" id="wallet_owner_id_input" value="">

                                @if($roleGroup === 'travclicks_owner')
                                    <p class="small text-muted mb-3">
                                        Travclicks sends money to a DMC wallet only. No Travclicks balance is required.
                                        Amount is in the <strong>DMC’s currency</strong>.
                                    </p>
                                    <div class="mb-3">
                                        <label class="form-label">DMC</label>
                                        <select id="travclicks_dmc_select" class="form-select" required></select>
                                        <div id="travclicks_dmcs_loading" class="text-muted small mt-1" style="display:none;">
                                            Loading DMCs...
                                        </div>
                                    </div>
                                @elseif($roleGroup === 'master_dmc')
                                    <div class="mb-3">
                                        <label class="form-label">DMC</label>
                                        <select id="master_dmc_select" class="form-select" style="width:100%;" required></select>
                                        <div id="master_dmcs_loading" class="text-muted small mt-1" style="display:none;">
                                            Loading DMCs...
                                        </div>
                                    </div>
                                @elseif($roleGroup === 'dmc')
                                    <div class="mb-3">
                                        <label class="form-label">Agency</label>
                                        <select id="dmc_agent_select" class="form-select" style="width:100%;" required></select>
                                        <div id="dmc_agents_loading" class="text-muted small mt-1" style="display:none;">
                                            Loading Agencies...
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Currency</label>
                                    <select name="wallet_currency_code" id="wallet_currency_code" class="form-select" required>
                                        <option value="">Select DMC first...</option>
                                    </select>
                                    <div id="wallet_currency_loading" class="text-muted small mt-1" style="display:none;">
                                        Loading currencies...
                                    </div>
                                    <div class="form-text small">
                                        @if($roleGroup === 'dmc')
                                            DMC country currency
                                        @elseif($roleGroup === 'travclicks_owner')
                                            Only currencies configured for this DMC
                                        @else
                                            Recipient configured currency
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Amount
                                        <span class="text-muted fw-normal" id="allocator_currency_label"></span>
                                    </label>
                                    <input type="number" name="topup_amount" id="topup_amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00" value="{{ old('topup_amount') }}">
                                    <div class="form-text small" id="amount_help_text">
                                        @if($roleGroup === 'travclicks_owner')
                                            Credited to the DMC in the currency selected above
                                        @else
                                            Debited from your wallet in <strong id="allocator_currency_text">{{ $allocatorCurrency ?? 'SGD' }}</strong>
                                        @endif
                                    </div>
                                </div>

                                @unless($roleGroup === 'travclicks_owner')
                                <div id="fx-conversion-preview" class="alert alert-light border mb-3 py-2 px-3" style="display:none;">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="ri-exchange-dollar-line ri-lg text-primary mt-1"></i>
                                        <div class="small">
                                            <div id="fx-conversion-text" class="fw-semibold">—</div>
                                            <div id="fx-conversion-rate" class="text-muted">—</div>
                                        </div>
                                    </div>
                                </div>
                                <div id="fx-conversion-error" class="alert alert-warning py-2 px-3 small mb-3" style="display:none;"></div>
                                @endunless

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="ledger_description" class="form-control" placeholder="e.g. Wallet funding" maxlength="255">
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-wallet-3-line me-1"></i>
                                    {{ $roleGroup === 'travclicks_owner' ? 'Send to DMC' : 'Allocate Wallet' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="{{ in_array($roleGroup, ['travclicks_owner','master_dmc','dmc'], true) ? 'col-lg-8' : 'col-12' }} mb-4">
                @include('finance.partials.wallet-ledger', [
                    'ledger' => $ledger,
                    'ledgerFilters' => $ledgerFilters ?? null,
                    'ledgerBadge' => $ownerDisplayName ?? (ucfirst($ownerType ?? 'wallet').' #'.($ownerId ?? '-')),
                    'ledgerAjaxUrl' => route('finance.wallet.ledger', [], false),
                    'ledgerSummary' => $ledgerSummary ?? null,
                    'ledgerFilteredSummary' => $ledgerFilteredSummary ?? null,
                    'ledgerPersistParams' => [
                        'owner_type' => $ownerType ?? null,
                        'owner_id' => $ownerId ?? null,
                        'currency' => $currency ?? null,
                    ],
                    'showReferenceColumn' => true,
                ])
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    const roleGroup = {!! json_encode($roleGroup) !!};
    const topUpTargets = {!! json_encode($topUpTargets ?? []) !!};
    const currenciesUrl = {!! json_encode(route('finance.wallet.dropdowns.currencies')) !!};
    const convertPreviewUrl = {!! json_encode(route('finance.wallet.convert-preview')) !!};
    const allocatorCurrency = {!! json_encode($allocatorCurrency ?? 'SGD') !!};

    let fxPreviewTimer = null;

    function ensureSelect2(selector) {
        const el = $(selector);
        if (!el.length) return null;

        if (typeof el.select2 !== 'function') {
            return el;
        }

        if (el.data('select2')) {
            el.select2('destroy');
        }
        el.select2({
            width: '100%',
            allowClear: true,
            placeholder: 'Select...'
        });
        return el;
    }

    function setAllocRecipientHidden(recipientType, recipientId) {
        const typeInput = document.getElementById('wallet_owner_type_input');
        const idInput = document.getElementById('wallet_owner_id_input');
        if (typeInput) typeInput.value = recipientType;
        if (idInput) idInput.value = recipientId || '';
    }

    function setEmptySelect(selectEl, message) {
        selectEl.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = message;
        opt.disabled = true;
        opt.selected = true;
        selectEl.appendChild(opt);
    }

    function lockDmcCurrency() {
        const currencySelect = document.getElementById('wallet_currency_code');
        if (!currencySelect || roleGroup !== 'dmc') {
            return;
        }
        const code = (allocatorCurrency || '').toUpperCase();
        currencySelect.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = code;
        opt.textContent = code;
        opt.selected = true;
        currencySelect.appendChild(opt);
        currencySelect.disabled = true;
        currencySelect.removeAttribute('name');
        let hidden = document.getElementById('wallet_currency_code_locked');
        if (!hidden && currencySelect.form) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'wallet_currency_code';
            hidden.id = 'wallet_currency_code_locked';
            currencySelect.form.appendChild(hidden);
        }
        if (hidden) {
            hidden.value = code;
        }
        updateFxPreview();
    }

    async function refreshCurrencyOptions(ownerType, ownerId) {
        const currencySelect = document.getElementById('wallet_currency_code');
        const loadingEl = document.getElementById('wallet_currency_loading');
        if (!currencySelect) return;

        if (roleGroup === 'dmc') {
            lockDmcCurrency();
            return;
        }

        if (!ownerType || !ownerId) {
            setEmptySelect(currencySelect, 'Select recipient first...');
            return;
        }

        if (loadingEl) loadingEl.style.display = 'block';
        currencySelect.disabled = true;

        try {
            const url = currenciesUrl
                + '?owner_type=' + encodeURIComponent(ownerType)
                + '&owner_id=' + encodeURIComponent(ownerId)
                + '&_=' + Date.now();
            const res = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });
            const json = await res.json();
            const currencies = json.currencies || [];
            const defaultCurrency = json.default || (currencies.length === 1 ? currencies[0] : null);

            currencySelect.innerHTML = '';

            if (!currencies.length) {
                setEmptySelect(currencySelect, 'No currency configured for this DMC');
                return;
            }

            currencies.forEach((code) => {
                const opt = document.createElement('option');
                opt.value = code;
                opt.textContent = code;
                if (defaultCurrency && code === defaultCurrency) {
                    opt.selected = true;
                }
                currencySelect.appendChild(opt);
            });

            if (defaultCurrency) {
                currencySelect.value = defaultCurrency;
            } else if (currencies.length === 1) {
                currencySelect.value = currencies[0];
            }

            updateFxPreview();
        } catch (error) {
            console.error(error);
            setEmptySelect(currencySelect, 'Unable to load currencies');
        } finally {
            currencySelect.disabled = false;
            if (loadingEl) loadingEl.style.display = 'none';
        }
    }

    function formatMoney(value) {
        const num = Number(value);
        if (!Number.isFinite(num)) return '—';
        return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
    }

    async function updateFxPreview() {
        const amountInput = document.getElementById('topup_amount');
        const toSelect = document.getElementById('wallet_currency_code');
        const previewBox = document.getElementById('fx-conversion-preview');
        const previewText = document.getElementById('fx-conversion-text');
        const previewRate = document.getElementById('fx-conversion-rate');
        const errorBox = document.getElementById('fx-conversion-error');

        if (!amountInput || !toSelect || !previewBox) return;

        const amount = parseFloat(amountInput.value);
        const from = (allocatorCurrency || '').toUpperCase();
        const to = (toSelect.value || '').toUpperCase();

        if (errorBox) {
            errorBox.style.display = 'none';
            errorBox.textContent = '';
        }

        if (!amount || amount <= 0 || !from || !to) {
            previewBox.style.display = 'none';
            return;
        }

        if (from === to) {
            previewBox.style.display = 'block';
            previewText.textContent = formatMoney(amount) + ' ' + from + ' (same currency — no conversion)';
            previewRate.textContent = 'Recipient is credited the same amount from your wallet.';
            return;
        }

        previewBox.style.display = 'block';
        previewText.textContent = 'Converting...';
        previewRate.textContent = from + ' → ' + to;

        try {
            const url = convertPreviewUrl
                + '?amount=' + encodeURIComponent(amount)
                + '&from=' + encodeURIComponent(from)
                + '&to=' + encodeURIComponent(to)
                + '&_=' + Date.now();
            const res = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });
            const json = await res.json();

            if (!json.success) {
                previewBox.style.display = 'none';
                if (errorBox) {
                    errorBox.style.display = 'block';
                    errorBox.textContent = json.message || 'Unable to fetch exchange rate.';
                }
                return;
            }

            previewText.textContent = formatMoney(json.amount) + ' ' + json.from
                + ' = ' + formatMoney(json.converted_amount) + ' ' + json.to;
            previewRate.textContent = 'Rate: 1 ' + json.from + ' = ' + formatMoney(json.exchange_rate) + ' ' + json.to
                + ' · Debit your ' + json.from + ' wallet, credit recipient ' + json.to;
        } catch (error) {
            console.error(error);
            previewBox.style.display = 'none';
            if (errorBox) {
                errorBox.style.display = 'block';
                errorBox.textContent = 'Unable to fetch exchange rate. Please try again.';
            }
        }
    }

    function scheduleFxPreview() {
        clearTimeout(fxPreviewTimer);
        fxPreviewTimer = setTimeout(updateFxPreview, 350);
    }

    function filterTopupTargets() {
        const type = document.getElementById('topup_owner_type')?.value;
        const select = document.getElementById('topup_owner_id');
        if (!type || !select) return;

        select.innerHTML = '';
        topUpTargets.filter(t => t.type === type).forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.label;
            select.appendChild(opt);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const amountInput = document.getElementById('topup_amount');
        const targetCurrency = document.getElementById('wallet_currency_code');

        if (amountInput) amountInput.addEventListener('input', scheduleFxPreview);
        if (targetCurrency) targetCurrency.addEventListener('change', scheduleFxPreview);

        if (roleGroup === 'travclicks_owner') {
            const dmcSelect = $('#travclicks_dmc_select');
            const loadingDmcs = document.getElementById('travclicks_dmcs_loading');
            const dmcsUrl = {!! json_encode(route('finance.wallet.dropdowns.travclicks-dmcs')) !!};
            const currencyLabel = document.getElementById('allocator_currency_label');

            ensureSelect2('#travclicks_dmc_select');

            function syncAmountCurrencyLabel() {
                const toSelect = document.getElementById('wallet_currency_code');
                const code = (toSelect && toSelect.value) ? toSelect.value.toUpperCase() : '';
                if (currencyLabel) {
                    currencyLabel.textContent = code ? '(' + code + ')' : '';
                }
            }

            const currencySelectEl = document.getElementById('wallet_currency_code');
            if (currencySelectEl) {
                currencySelectEl.addEventListener('change', syncAmountCurrencyLabel);
            }

            async function loadDmcs() {
                loadingDmcs.style.display = 'block';
                try {
                    dmcSelect.empty();
                    setAllocRecipientHidden('', '');
                    const res = await fetch(dmcsUrl, { credentials: 'same-origin' });
                    const json = await res.json();
                    const items = json.items || [];

                    if (!items.length) {
                        setEmptySelect(dmcSelect[0], 'No DMCs available');
                        return;
                    }

                    items.forEach(i => dmcSelect.append('<option value="' + i.id + '">' + i.label + '</option>'));
                    dmcSelect.val(items[0].id);
                    dmcSelect.trigger('change');
                } finally {
                    loadingDmcs.style.display = 'none';
                }
            }

            dmcSelect.on('change', async function () {
                const dmcId = dmcSelect.val();
                setAllocRecipientHidden('dmc', dmcId);
                await refreshCurrencyOptions('dmc', dmcId);
                syncAmountCurrencyLabel();
            });

            loadDmcs();
            return;
        }

        if (roleGroup === 'master_dmc') {
            const masterDmcSelect = $('#master_dmc_select');
            const loadingDmcs = document.getElementById('master_dmcs_loading');
            const dmcsUrl = {!! json_encode(route('finance.wallet.dropdowns.master-dmcs')) !!};

            ensureSelect2('#master_dmc_select');

            async function loadMasterDmcs() {
                loadingDmcs.style.display = 'block';
                masterDmcSelect.empty();
                setAllocRecipientHidden('', '');
                try {
                    const res = await fetch(dmcsUrl, { credentials: 'same-origin' });
                    const json = await res.json();
                    const items = json.items || [];

                    if (!items.length) {
                        setEmptySelect(masterDmcSelect[0], 'No DMCs available');
                        return;
                    }

                    items.forEach(i => masterDmcSelect.append('<option value="' + i.id + '">' + i.label + '</option>'));
                    masterDmcSelect.val(items[0].id);
                    masterDmcSelect.trigger('change');
                } finally {
                    loadingDmcs.style.display = 'none';
                }
            }

            masterDmcSelect.on('change', function () {
                const dmcId = masterDmcSelect.val();
                setAllocRecipientHidden('dmc', dmcId);
                refreshCurrencyOptions('dmc', dmcId);
            });

            loadMasterDmcs();
            return;
        }

        if (roleGroup === 'dmc') {
            lockDmcCurrency();
            const dmcAgentsSelect = $('#dmc_agent_select');
            const loadingAgents = document.getElementById('dmc_agents_loading');
            const agentsUrl = {!! json_encode(route('finance.wallet.dropdowns.dmc-agents')) !!};
            const dmcIdForRole = {!! json_encode($dmcIdForRole ?? null) !!};

            ensureSelect2('#dmc_agent_select');

            async function loadDmcAgents() {
                loadingAgents.style.display = 'block';
                dmcAgentsSelect.empty();
                try {
                    const res = await fetch(agentsUrl + '?_=' + Date.now(), {
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });
                    const json = await res.json();
                    const items = json.items || [];

                    if (!items.length) {
                        setEmptySelect(dmcAgentsSelect[0], 'No Agencies available');
                        if (dmcIdForRole) {
                            await refreshCurrencyOptions('dmc', dmcIdForRole);
                        }
                    } else {
                        const groups = new Map();
                        items.forEach((item) => {
                            const country = item.country || 'Other';
                            if (!groups.has(country)) {
                                groups.set(country, []);
                            }
                            groups.get(country).push(item);
                        });
                        [...groups.keys()].sort((a, b) => a.localeCompare(b)).forEach((country) => {
                            const group = document.createElement('optgroup');
                            group.label = country;
                            groups.get(country).forEach((item) => {
                                const opt = document.createElement('option');
                                opt.value = item.selectable ? item.id : '';
                                opt.textContent = item.selectable ? item.label : (item.label + ' (no agent)');
                                opt.disabled = !item.selectable;
                                group.appendChild(opt);
                            });
                            dmcAgentsSelect[0].appendChild(group);
                        });
                    }
                    if (dmcAgentsSelect.data('select2')) {
                        dmcAgentsSelect.select2('destroy');
                    }
                    ensureSelect2('#dmc_agent_select');
                    const firstSelectable = dmcAgentsSelect.find('option:not(:disabled)').first();
                    if (firstSelectable.length) {
                        dmcAgentsSelect.val(firstSelectable.val());
                    }
                    dmcAgentsSelect.trigger('change');
                } finally {
                    loadingAgents.style.display = 'none';
                }
            }

            dmcAgentsSelect.on('change', function () {
                const agentId = dmcAgentsSelect.val();
                setAllocRecipientHidden('agent', agentId);
                if (agentId) {
                    refreshCurrencyOptions('agent', agentId);
                } else if (dmcIdForRole) {
                    refreshCurrencyOptions('dmc', dmcIdForRole);
                }
            });

            loadDmcAgents();
        }
    });
</script>
@endsection
