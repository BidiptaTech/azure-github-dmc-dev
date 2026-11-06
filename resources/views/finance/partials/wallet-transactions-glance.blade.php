@php
    $glanceFilters = $glanceFilters ?? ['date_from' => null, 'date_to' => null, 'per_page' => 10];
    $glancePersistParams = array_filter(array_merge([
        'owner_type' => request('owner_type'),
        'owner_id' => request('owner_id'),
        'currency' => request('currency'),
    ], $glancePersistParams ?? []), fn ($value) => $value !== null && $value !== '');
    $glanceQueryParams = array_merge($glancePersistParams, array_filter([
        'glance_date_from' => $glanceFilters['date_from'] ?? null,
        'glance_date_to' => $glanceFilters['date_to'] ?? null,
        'glance_per_page' => $glanceFilters['per_page'] ?? null,
    ], fn ($value) => $value !== null && $value !== ''));
    $glanceAjaxUrl = $glanceAjaxUrl ?? '';
    $glanceCurrency = $glanceCurrency ?? ($currency ?? 'USD');
@endphp

<div class="card border-0 shadow-sm wallet-glance-card mb-4"
     id="wallet-glance-card"
     data-glance-url="{{ $glanceAjaxUrl }}">
    <div class="card-header bg-transparent">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div>
                <h5 class="mb-0">
                    <i class="ri-pulse-line me-1"></i> At a Glance
                </h5>
                <small class="text-muted">Recent wallet activity in plain language</small>
            </div>
            @if(!empty($glanceBadge))
                <span class="badge bg-label-primary">{{ $glanceBadge }}</span>
            @endif
        </div>

        <form id="wallet-glance-filter-form" class="row g-2 align-items-end" autocomplete="off">
            @foreach($glancePersistParams as $paramKey => $paramValue)
                <input type="hidden" name="{{ $paramKey }}" value="{{ $paramValue }}">
            @endforeach
            <input type="hidden" name="glance_page" id="wallet-glance-page" value="{{ $transactions->currentPage() ?? 1 }}">

            @if(($glanceScope ?? '') === 'master_dmc_flow' && !empty($masterDmcOptions) && count($masterDmcOptions))
                <div class="col-sm-12 col-md-4">
                    <label class="form-label small mb-1" for="glance_master_dmc_filter">Master DMC</label>
                    <select id="glance_master_dmc_filter" class="form-select form-select-sm">
                        <option value="">All Master DMCs</option>
                        @foreach($masterDmcOptions as $masterDmc)
                            <option value="{{ $masterDmc->wallet_owner_id }}" @selected((int)($selectedMasterDmcId ?? 0) === (int) $masterDmc->wallet_owner_id)>
                                {{ $masterDmc->owner_name ?? 'Master DMC' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @elseif(($glanceScope ?? '') === 'all' && !empty($walletOptions) && count($walletOptions))
                <div class="col-sm-12 col-md-4">
                    <label class="form-label small mb-1" for="glance_wallet_filter">Wallet</label>
                    <select id="glance_wallet_filter" class="form-select form-select-sm">
                        <option value="">All wallets</option>
                        @foreach($walletOptions as $wallet)
                            @php
                                $walletKey = $wallet->wallet_owner_type.':'.$wallet->wallet_owner_id.':'.strtoupper((string) $wallet->wallet_currency_code);
                            @endphp
                            <option value="{{ $walletKey }}" @selected(($selectedWalletKey ?? '') === $walletKey)>
                                {{ $wallet->owner_name ?? 'Wallet' }} ({{ strtoupper((string) $wallet->wallet_currency_code) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-sm-6 col-md-3">
                <label class="form-label small mb-1" for="glance_date_from">From date</label>
                <input type="date"
                       id="glance_date_from"
                       name="glance_date_from"
                       class="form-control form-control-sm"
                       value="{{ $glanceFilters['date_from'] ?? '' }}">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small mb-1" for="glance_date_to">To date</label>
                <input type="date"
                       id="glance_date_to"
                       name="glance_date_to"
                       class="form-control form-control-sm"
                       value="{{ $glanceFilters['date_to'] ?? '' }}">
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="form-label small mb-1" for="glance_per_page">Show</label>
                <select id="glance_per_page" name="glance_per_page" class="form-select form-select-sm">
                    @foreach([10, 20, 50] as $size)
                        <option value="{{ $size }}" @selected((int)($glanceFilters['per_page'] ?? 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-8 col-md-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm" id="wallet-glance-apply-btn">
                    <i class="ri-filter-3-line me-1"></i> Apply
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="wallet-glance-clear-btn">
                    Clear
                </button>
                @if(!empty($ledgerAnchor))
                    <a href="{{ $ledgerAnchor }}" class="btn btn-outline-primary btn-sm ms-auto">
                        <i class="ri-file-list-3-line me-1"></i> Full ledger
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="wallet-glance-results-wrap position-relative">
        <div id="wallet-glance-loader" class="wallet-glance-loader d-none" aria-hidden="true">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted small mt-2">Loading transactions...</div>
            </div>
        </div>

        <div id="wallet-glance-results">
            @include('finance.partials.wallet-transactions-glance-results', [
                'transactions' => $transactions,
                'glanceQueryParams' => $glanceQueryParams,
                'currency' => $glanceCurrency,
                'flowSummary' => $flowSummary ?? null,
            ])
        </div>
    </div>
</div>

@once
    @push('css')
        <style>
            .wallet-glance-results-wrap { min-height: 120px; }
            .wallet-glance-loader {
                position: absolute;
                inset: 0;
                z-index: 5;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.78);
                backdrop-filter: blur(1px);
            }
            .wallet-glance-card.is-loading .wallet-glance-loader { display: flex; }
            .wallet-glance-card.is-loading #wallet-glance-results {
                opacity: 0.45;
                pointer-events: none;
            }
            .wallet-glance-feed {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                padding: 0 1rem 1rem;
            }
            .wallet-glance-item {
                display: flex;
                gap: 0.875rem;
                padding: 0.875rem 1rem;
                border: 1px solid rgba(67, 89, 113, 0.12);
                border-radius: 0.625rem;
                background: #fff;
                transition: box-shadow 0.15s ease, border-color 0.15s ease;
            }
            .wallet-glance-item:hover {
                border-color: rgba(105, 108, 255, 0.35);
                box-shadow: 0 2px 10px rgba(67, 89, 113, 0.08);
            }
            .wallet-glance-icon {
                width: 42px;
                height: 42px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 1.25rem;
            }
            .wallet-glance-body {
                flex: 1;
                min-width: 0;
            }
            .wallet-glance-pagination .pagination { margin-bottom: 0; }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                function initWalletGlanceCard(card) {
                    if (!card || card.dataset.glanceBound === '1') {
                        return;
                    }

                    const ajaxUrl = card.dataset.glanceUrl;
                    if (!ajaxUrl) {
                        return;
                    }

                    card.dataset.glanceBound = '1';

                    const form = card.querySelector('#wallet-glance-filter-form');
                    const resultsEl = card.querySelector('#wallet-glance-results');
                    const loaderEl = card.querySelector('#wallet-glance-loader');
                    const pageInput = card.querySelector('#wallet-glance-page');
                    const applyBtn = card.querySelector('#wallet-glance-apply-btn');
                    const clearBtn = card.querySelector('#wallet-glance-clear-btn');
                    const walletFilter = card.querySelector('#glance_wallet_filter');
                    const masterDmcFilter = card.querySelector('#glance_master_dmc_filter');

                    function syncWalletFilterToForm() {
                        if (!form) {
                            return;
                        }

                        const ownerTypeInput = form.querySelector('input[name="owner_type"]');
                        const ownerIdInput = form.querySelector('input[name="owner_id"]');
                        const currencyInput = form.querySelector('input[name="currency"]');

                        if (masterDmcFilter) {
                            if (ownerTypeInput) ownerTypeInput.value = masterDmcFilter.value ? 'master_dmc' : '';
                            if (ownerIdInput) ownerIdInput.value = masterDmcFilter.value || '';
                            if (currencyInput) currencyInput.value = '';
                            return;
                        }

                        if (!walletFilter) {
                            return;
                        }

                        const parts = (walletFilter.value || '').split(':');

                        if (ownerTypeInput) ownerTypeInput.value = parts[0] || '';
                        if (ownerIdInput) ownerIdInput.value = parts[1] || '';
                        if (currencyInput) currencyInput.value = parts[2] || '';
                    }

                    function setLoading(isLoading) {
                        card.classList.toggle('is-loading', isLoading);
                        if (loaderEl) {
                            loaderEl.classList.toggle('d-none', !isLoading);
                            loaderEl.setAttribute('aria-hidden', isLoading ? 'false' : 'true');
                        }
                        if (applyBtn) applyBtn.disabled = isLoading;
                        if (clearBtn) clearBtn.disabled = isLoading;
                    }

                    function buildQueryString(extraParams) {
                        const params = new URLSearchParams(new FormData(form));
                        if (extraParams) {
                            Object.entries(extraParams).forEach(([key, value]) => {
                                if (value === null || value === undefined || value === '') {
                                    params.delete(key);
                                } else {
                                    params.set(key, value);
                                }
                            });
                        }
                        return params.toString();
                    }

                    async function loadGlance(extraParams) {
                        setLoading(true);

                        try {
                            const response = await fetch(ajaxUrl + '?' + buildQueryString(extraParams), {
                                method: 'GET',
                                credentials: 'same-origin',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            });

                            if (!response.ok) {
                                throw new Error('Failed to load transactions.');
                            }

                            const payload = await response.json();
                            resultsEl.innerHTML = payload.html || '';
                            bindPagination();
                        } catch (error) {
                            console.error(error);
                            resultsEl.innerHTML = '<div class="alert alert-danger m-3 mb-0">Unable to load transactions. Please try again.</div>';
                        } finally {
                            setLoading(false);
                        }
                    }

                    function bindPagination() {
                        resultsEl.querySelectorAll('.wallet-glance-pagination a.page-link').forEach((link) => {
                            link.addEventListener('click', function (event) {
                                event.preventDefault();

                                let page = 1;
                                try {
                                    const url = new URL(link.href, window.location.origin);
                                    page = url.searchParams.get('glance_page') || url.searchParams.get('page') || 1;
                                } catch (e) {
                                    const match = (link.getAttribute('href') || '').match(/glance_page=(\d+)/);
                                    page = match ? match[1] : 1;
                                }

                                if (pageInput) {
                                    pageInput.value = page;
                                }

                                loadGlance({ glance_page: page });
                            });
                        });
                    }

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        syncWalletFilterToForm();
                        if (pageInput) {
                            pageInput.value = '1';
                        }
                        loadGlance({ glance_page: 1 });
                    });

                    if (walletFilter) {
                        walletFilter.addEventListener('change', function () {
                            syncWalletFilterToForm();
                            if (pageInput) {
                                pageInput.value = '1';
                            }
                            loadGlance({ glance_page: 1 });
                        });
                    }

                    if (masterDmcFilter) {
                        masterDmcFilter.addEventListener('change', function () {
                            syncWalletFilterToForm();
                            if (pageInput) {
                                pageInput.value = '1';
                            }
                            loadGlance({ glance_page: 1 });
                        });
                    }

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function () {
                            const fromInput = form.querySelector('#glance_date_from');
                            const toInput = form.querySelector('#glance_date_to');
                            const perPageInput = form.querySelector('#glance_per_page');

                            if (fromInput) fromInput.value = '';
                            if (toInput) toInput.value = '';
                            if (perPageInput) perPageInput.value = '10';
                            if (pageInput) pageInput.value = '1';
                            if (walletFilter) walletFilter.value = '';
                            if (masterDmcFilter) masterDmcFilter.value = '';
                            syncWalletFilterToForm();

                            loadGlance({
                                glance_date_from: '',
                                glance_date_to: '',
                                glance_per_page: '10',
                                owner_type: '',
                                owner_id: '',
                                currency: '',
                                glance_page: 1,
                            });
                        });
                    }

                    bindPagination();
                }

                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.wallet-glance-card').forEach(initWalletGlanceCard);
                });
            })();
        </script>
    @endpush
@endonce
