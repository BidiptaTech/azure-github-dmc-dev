@php $scoped = $scoped ?? false; @endphp

@if($view === 'ledger' || ($view === 'dmcs' && ! $scoped))
    <nav class="px-3 pt-3 small">
        @unless($scoped)
            <a href="#" data-nav="masters">Master DMCs</a>
            <span class="text-muted mx-1">/</span>
        @endunless
        @if($view === 'ledger')
            <a href="#" data-nav="dmcs" data-master-id="{{ $masterId }}">{{ $masterName }}</a>
            <span class="text-muted mx-1">/</span>
            <span class="fw-semibold">{{ $dmcName }}</span>
        @else
            <span class="fw-semibold">{{ $masterName }}</span>
        @endif
    </nav>
@endif

@if($view !== 'ledger')
    <div class="px-3 pt-3 pb-2">
        <input type="search" id="dmc-drill-search" class="form-control form-control-sm"
               placeholder="{{ $view === 'masters' ? 'Search Master DMC...' : 'Search DMC...' }}" autocomplete="off">
    </div>
    @include('finance.partials.dmc-drill-list')
@else
    @php $f = $dmcLedgerFilters; @endphp
    <form id="dmc-ledger-filter-form" class="row g-2 align-items-end px-3 pt-3" autocomplete="off">
        <input type="hidden" name="view" value="ledger">
        <input type="hidden" name="master_id" value="{{ $masterId }}">
        <input type="hidden" name="dmc_id" value="{{ $dmcId }}">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Currency</label>
            <select name="dmc_currency" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($currencyOptions as $code)
                    <option value="{{ $code }}" @selected(($f['currency'] ?? null) === $code)>{{ $code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">Type</label>
            <select name="dmc_entry_type" class="form-select form-select-sm">
                <option value="">All types</option>
                @foreach(\App\Services\Finance\WalletQueryService::DMC_LEDGER_ENTRY_TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(($f['entry_type'] ?? null) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">From date</label>
            <input type="date" name="dmc_date_from" class="form-control form-control-sm" value="{{ $f['date_from'] ?? '' }}">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">To date</label>
            <input type="date" name="dmc_date_to" class="form-control form-control-sm" value="{{ $f['date_to'] ?? '' }}">
        </div>
        <div class="col-6 col-md-1">
            <label class="form-label small mb-1">Per page</label>
            <select name="dmc_per_page" class="form-select form-select-sm">
                @foreach([25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected((int) ($f['per_page'] ?? 25) === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
    </form>
    <div id="dmc-ledger-results">
        @include('finance.partials.dmc-ledger-results')
    </div>
@endif
