<div class="card border-0 shadow-sm h-100 dmc-pane" id="dmc-ledger-card" data-url="{{ route('finance.wallet.dmc-ledger', [], false) }}">
    <div class="card-header bg-transparent">
        <h5 class="mb-0"><i class="ri-wallet-3-line me-1"></i> DMC Wallets</h5>
    </div>
    <div class="position-relative" style="min-height:140px;">
        <div class="dmc-loader d-none position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center" style="z-index:5;background:rgba(255,255,255,.78);">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
        <div id="dmc-drill-body">
            @include('finance.partials.dmc-drill', $dmcDrill)
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const card = document.getElementById('dmc-ledger-card');
        const detail = document.getElementById('dmc-ledger-detail');
        const body = card.querySelector('#dmc-drill-body');
        const detailBody = detail.querySelector('#dmc-ledger-body');

        async function load(params, target) {
            const clean = new URLSearchParams();
            Object.entries(params).forEach(([k, v]) => { if (v !== '' && v != null) clean.set(k, v); });

            const loader = target.closest('.dmc-pane').querySelector('.dmc-loader');
            loader.classList.remove('d-none');
            loader.classList.add('d-flex');
            try {
                const res = await fetch(card.dataset.url + '?' + clean, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error('Request failed');
                target.innerHTML = (await res.json()).html || '';
            } catch (e) {
                console.error(e);
                target.innerHTML = '<div class="alert alert-danger m-3">Unable to load data. Please try again.</div>';
            } finally {
                loader.classList.add('d-none');
                loader.classList.remove('d-flex');
            }
        }

        function loadResults(page) {
            const form = detail.querySelector('#dmc-ledger-filter-form');
            const params = Object.fromEntries(new FormData(form));
            params.dmc_ledger_page = page;
            params.results_only = 1;
            load(params, detail.querySelector('#dmc-ledger-results'));
        }

        function loadList(page) {
            const list = card.querySelector('#dmc-drill-list');
            load({
                view: list.dataset.view,
                master_id: list.dataset.masterId,
                q: card.querySelector('#dmc-drill-search').value.trim(),
                dmc_ledger_page: page,
                list_only: 1,
            }, list);
        }

        let searchTimer = null;
        card.addEventListener('input', function (e) {
            if (e.target.id !== 'dmc-drill-search') return;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadList(1), 300);
        });

        function onClick(e) {
            const nav = e.target.closest('[data-nav]');
            if (nav) {
                e.preventDefault();
                const params = { view: nav.dataset.nav, master_id: nav.dataset.masterId, dmc_id: nav.dataset.dmcId };
                if (nav.dataset.nav === 'ledger') {
                    detail.classList.remove('d-none');
                    load(params, detailBody);
                    detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    detail.classList.add('d-none');
                    detailBody.innerHTML = '';
                    load(params, body);
                }
                return;
            }
            const link = e.target.closest('.dmc-ledger-pagination a.page-link');
            if (link) {
                e.preventDefault();
                const page = new URL(link.href, window.location.origin).searchParams.get('dmc_ledger_page') || 1;
                link.closest('#dmc-drill-list') ? loadList(page) : loadResults(page);
            }
        }
        card.addEventListener('click', onClick);
        detail.addEventListener('click', onClick);

        // Filters apply automatically.
        detail.addEventListener('change', function (e) {
            if (e.target.closest('#dmc-ledger-filter-form')) loadResults(1);
        });
    })();
</script>
@endpush
