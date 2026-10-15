@php
    $ledgerFilters = $ledgerFilters ?? ['date_from' => null, 'date_to' => null, 'per_page' => 25];
    $ledgerPersistParams = array_filter(array_merge([
        'owner_type' => request('owner_type'),
        'owner_id' => request('owner_id'),
        'currency' => request('currency'),
    ], $ledgerPersistParams ?? []), fn ($value) => $value !== null && $value !== '');
    $ledgerQueryParams = array_merge($ledgerPersistParams, array_filter([
        'ledger_date_from' => $ledgerFilters['date_from'] ?? null,
        'ledger_date_to' => $ledgerFilters['date_to'] ?? null,
        'ledger_per_page' => $ledgerFilters['per_page'] ?? null,
    ], fn ($value) => $value !== null && $value !== ''));
    $showReferenceColumn = $showReferenceColumn ?? true;
    $ledgerAjaxUrl = $ledgerAjaxUrl ?? '';
@endphp

<div class="card border-0 shadow-sm wallet-ledger-card"
     id="wallet-ledger-card"
     data-ledger-url="{{ $ledgerAjaxUrl }}"
     data-show-reference="{{ $showReferenceColumn ? '1' : '0' }}">
    <div class="card-header bg-transparent">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h5 class="mb-0">
                <i class="ri-file-list-3-line me-1"></i> Wallet Ledger
            </h5>
            @if(!empty($ledgerBadge))
                <span class="badge bg-label-secondary">{{ $ledgerBadge }}</span>
            @endif
        </div>

        <form id="wallet-ledger-filter-form" class="row g-2 align-items-end" autocomplete="off">
            @foreach($ledgerPersistParams as $paramKey => $paramValue)
                <input type="hidden" name="{{ $paramKey }}" value="{{ $paramValue }}">
            @endforeach
            <input type="hidden" name="ledger_page" id="wallet-ledger-page" value="{{ $ledger->currentPage() ?? 1 }}">

            <div class="col-sm-6 col-md-3">
                <label class="form-label small mb-1" for="ledger_date_from">From date</label>
                <input type="date"
                       id="ledger_date_from"
                       name="ledger_date_from"
                       class="form-control form-control-sm"
                       value="{{ $ledgerFilters['date_from'] ?? '' }}">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small mb-1" for="ledger_date_to">To date</label>
                <input type="date"
                       id="ledger_date_to"
                       name="ledger_date_to"
                       class="form-control form-control-sm"
                       value="{{ $ledgerFilters['date_to'] ?? '' }}">
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="form-label small mb-1" for="ledger_per_page">Per page</label>
                <select id="ledger_per_page" name="ledger_per_page" class="form-select form-select-sm">
                    @foreach([25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int)($ledgerFilters['per_page'] ?? 25) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-8 col-md-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm" id="wallet-ledger-apply-btn">
                    <i class="ri-filter-3-line me-1"></i> Apply
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="wallet-ledger-clear-btn">
                    Clear
                </button>
            </div>
        </form>
    </div>

    <div class="wallet-ledger-results-wrap position-relative">
        <div id="wallet-ledger-loader" class="wallet-ledger-loader d-none" aria-hidden="true">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted small mt-2">Loading ledger...</div>
            </div>
        </div>

        <div id="wallet-ledger-results">
            @include('finance.partials.wallet-ledger-results', [
                'ledger' => $ledger,
                'ledgerFilters' => $ledgerFilters,
                'ledgerQueryParams' => $ledgerQueryParams,
                'showReferenceColumn' => $showReferenceColumn,
                'ledgerSummary' => $ledgerSummary ?? null,
                'ledgerFilteredSummary' => $ledgerFilteredSummary ?? null,
            ])
        </div>
    </div>
</div>

@once
    @push('css')
        <style>
            .wallet-ledger-results-wrap { min-height: 140px; }
            .wallet-ledger-loader {
                position: absolute;
                inset: 0;
                z-index: 5;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.78);
                backdrop-filter: blur(1px);
            }
            .wallet-ledger-card.is-loading .wallet-ledger-loader { display: flex; }
            .wallet-ledger-card.is-loading #wallet-ledger-results {
                opacity: 0.45;
                pointer-events: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                function initWalletLedgerCard(card) {
                    if (!card || card.dataset.ledgerBound === '1') {
                        return;
                    }

                    const ajaxUrl = card.dataset.ledgerUrl;
                    if (!ajaxUrl) {
                        return;
                    }

                    card.dataset.ledgerBound = '1';

                    const form = card.querySelector('#wallet-ledger-filter-form');
                    const resultsEl = card.querySelector('#wallet-ledger-results');
                    const loaderEl = card.querySelector('#wallet-ledger-loader');
                    const pageInput = card.querySelector('#wallet-ledger-page');
                    const applyBtn = card.querySelector('#wallet-ledger-apply-btn');
                    const clearBtn = card.querySelector('#wallet-ledger-clear-btn');
                    const showReference = card.dataset.showReference === '1';

                    function setLoading(isLoading) {
                        card.classList.toggle('is-loading', isLoading);
                        if (loaderEl) {
                            loaderEl.classList.toggle('d-none', !isLoading);
                            loaderEl.setAttribute('aria-hidden', isLoading ? 'false' : 'true');
                        }
                        if (applyBtn) {
                            applyBtn.disabled = isLoading;
                        }
                        if (clearBtn) {
                            clearBtn.disabled = isLoading;
                        }
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
                        params.set('show_reference', showReference ? '1' : '0');
                        return params.toString();
                    }

                    async function loadLedger(extraParams) {
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
                                throw new Error('Failed to load ledger data.');
                            }

                            const payload = await response.json();
                            resultsEl.innerHTML = payload.html || '';
                            bindPagination();
                        } catch (error) {
                            console.error(error);
                            resultsEl.innerHTML = '<div class="alert alert-danger m-3 mb-0">Unable to load ledger entries. Please try again.</div>';
                        } finally {
                            setLoading(false);
                        }
                    }

                    function bindPagination() {
                        resultsEl.querySelectorAll('.wallet-ledger-pagination a.page-link').forEach((link) => {
                            link.addEventListener('click', function (event) {
                                event.preventDefault();

                                let page = 1;
                                try {
                                    const url = new URL(link.href, window.location.origin);
                                    page = url.searchParams.get('ledger_page') || url.searchParams.get('page') || 1;
                                } catch (e) {
                                    const match = (link.getAttribute('href') || '').match(/ledger_page=(\d+)/);
                                    page = match ? match[1] : 1;
                                }

                                if (pageInput) {
                                    pageInput.value = page;
                                }

                                loadLedger({ ledger_page: page });
                            });
                        });
                    }

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        if (pageInput) {
                            pageInput.value = '1';
                        }
                        loadLedger({ ledger_page: 1 });
                    });

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function () {
                            const fromInput = form.querySelector('#ledger_date_from');
                            const toInput = form.querySelector('#ledger_date_to');
                            const perPageInput = form.querySelector('#ledger_per_page');

                            if (fromInput) fromInput.value = '';
                            if (toInput) toInput.value = '';
                            if (perPageInput) perPageInput.value = '25';
                            if (pageInput) pageInput.value = '1';

                            loadLedger({
                                ledger_date_from: '',
                                ledger_date_to: '',
                                ledger_per_page: '25',
                                ledger_page: 1,
                            });
                        });
                    }

                    bindPagination();
                }

                function bootWalletLedgerCards() {
                    document.querySelectorAll('.wallet-ledger-card').forEach(initWalletLedgerCard);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bootWalletLedgerCards);
                } else {
                    bootWalletLedgerCards();
                }
            })();
        </script>
    @endpush
@endonce
