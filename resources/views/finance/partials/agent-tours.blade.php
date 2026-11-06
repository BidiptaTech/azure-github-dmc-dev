@php
    $toursFilters = $toursFilters ?? ['date_from' => null, 'date_to' => null, 'per_page' => 25];
    $toursPersistParams = array_filter($toursPersistParams ?? [], fn ($value) => $value !== null && $value !== '');
    $toursQueryParams = array_merge($toursPersistParams, array_filter([
        'tours_date_from' => $toursFilters['date_from'] ?? null,
        'tours_date_to' => $toursFilters['date_to'] ?? null,
        'tours_per_page' => $toursFilters['per_page'] ?? null,
    ], fn ($value) => $value !== null && $value !== ''));
    $toursAjaxUrl = $toursAjaxUrl ?? '';
@endphp

<div class="card border-0 shadow-sm agent-tours-card"
     id="agent-tours-card"
     data-tours-url="{{ $toursAjaxUrl }}">
    <div class="card-header bg-transparent">
        <div class="mb-2">
            <h5 class="mb-1"><i class="ri-map-pin-line me-1"></i> Recent Tours for This Agent</h5>
            <small class="text-muted">Confirmed tours can be settled to debit the agent wallet via Python <code>/wallet/booking</code>.</small>
        </div>

        <form id="agent-tours-filter-form" class="row g-2 align-items-end" autocomplete="off">
            @foreach($toursPersistParams as $paramKey => $paramValue)
                <input type="hidden" name="{{ $paramKey }}" value="{{ $paramValue }}">
            @endforeach
            <input type="hidden" name="tours_page" id="agent-tours-page" value="{{ $tours->currentPage() ?? 1 }}">

            <div class="col-sm-6 col-md-3">
                <label class="form-label small mb-1" for="tours_date_from">From date</label>
                <input type="date"
                       id="tours_date_from"
                       name="tours_date_from"
                       class="form-control form-control-sm"
                       value="{{ $toursFilters['date_from'] ?? '' }}">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small mb-1" for="tours_date_to">To date</label>
                <input type="date"
                       id="tours_date_to"
                       name="tours_date_to"
                       class="form-control form-control-sm"
                       value="{{ $toursFilters['date_to'] ?? '' }}">
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="form-label small mb-1" for="tours_per_page">Per page</label>
                <select id="tours_per_page" name="tours_per_page" class="form-select form-select-sm">
                    @foreach([25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int)($toursFilters['per_page'] ?? 25) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-8 col-md-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm" id="agent-tours-apply-btn">
                    <i class="ri-filter-3-line me-1"></i> Apply
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="agent-tours-clear-btn">
                    Clear
                </button>
            </div>
        </form>
    </div>

    <div class="agent-tours-results-wrap position-relative">
        <div id="agent-tours-loader" class="agent-tours-loader d-none" aria-hidden="true">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted small mt-2">Loading tours...</div>
            </div>
        </div>

        <div id="agent-tours-results">
            @include('finance.partials.agent-tours-results', [
                'tours' => $tours,
                'toursQueryParams' => $toursQueryParams,
                'agentId' => $agentId,
            ])
        </div>
    </div>
</div>

@once
    @push('css')
        <style>
            .agent-tours-results-wrap { min-height: 140px; }
            .agent-tours-loader {
                position: absolute;
                inset: 0;
                z-index: 5;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.78);
                backdrop-filter: blur(1px);
            }
            .agent-tours-card.is-loading .agent-tours-loader { display: flex; }
            .agent-tours-card.is-loading #agent-tours-results {
                opacity: 0.45;
                pointer-events: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                function initAgentToursCard(card) {
                    if (!card || card.dataset.toursBound === '1') {
                        return;
                    }

                    const ajaxUrl = card.dataset.toursUrl;
                    if (!ajaxUrl) {
                        return;
                    }

                    card.dataset.toursBound = '1';

                    const form = card.querySelector('#agent-tours-filter-form');
                    const resultsEl = card.querySelector('#agent-tours-results');
                    const loaderEl = card.querySelector('#agent-tours-loader');
                    const pageInput = card.querySelector('#agent-tours-page');
                    const applyBtn = card.querySelector('#agent-tours-apply-btn');
                    const clearBtn = card.querySelector('#agent-tours-clear-btn');

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

                    async function loadTours(extraParams) {
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
                                throw new Error('Failed to load tours.');
                            }

                            const payload = await response.json();
                            resultsEl.innerHTML = payload.html || '';
                            bindPagination();
                        } catch (error) {
                            console.error(error);
                            resultsEl.innerHTML = '<div class="alert alert-danger m-3 mb-0">Unable to load tours. Please try again.</div>';
                        } finally {
                            setLoading(false);
                        }
                    }

                    function bindPagination() {
                        resultsEl.querySelectorAll('.agent-tours-pagination a.page-link').forEach((link) => {
                            link.addEventListener('click', function (event) {
                                event.preventDefault();

                                let page = 1;
                                try {
                                    const url = new URL(link.href, window.location.origin);
                                    page = url.searchParams.get('tours_page') || url.searchParams.get('page') || 1;
                                } catch (e) {
                                    const match = (link.getAttribute('href') || '').match(/tours_page=(\d+)/);
                                    page = match ? match[1] : 1;
                                }

                                if (pageInput) {
                                    pageInput.value = page;
                                }

                                loadTours({ tours_page: page });
                            });
                        });
                    }

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        if (pageInput) {
                            pageInput.value = '1';
                        }
                        loadTours({ tours_page: 1 });
                    });

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function () {
                            const fromInput = form.querySelector('#tours_date_from');
                            const toInput = form.querySelector('#tours_date_to');
                            const perPageInput = form.querySelector('#tours_per_page');

                            if (fromInput) fromInput.value = '';
                            if (toInput) toInput.value = '';
                            if (perPageInput) perPageInput.value = '25';
                            if (pageInput) pageInput.value = '1';

                            loadTours({
                                tours_date_from: '',
                                tours_date_to: '',
                                tours_per_page: '25',
                                tours_page: 1,
                            });
                        });
                    }

                    bindPagination();
                }

                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.agent-tours-card').forEach(initAgentToursCard);
                });
            })();
        </script>
    @endpush
@endonce
