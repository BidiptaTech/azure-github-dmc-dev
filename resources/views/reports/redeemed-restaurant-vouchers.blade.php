@extends('layouts.layout')

@section('content')
@php
    $dmcCurrency = \App\Helpers\CommonHelper::getDmcCurrencyByCountry();
    $symbol = $dmcCurrency['symbol'] ?? '';
    $hasActiveFilters = ($search ?? '') !== ''
        || ($startDate ?? '')
        || ($endDate ?? '')
        || ($tourStatus ?? '') !== ''
        || ($restaurantFilter ?? '') !== '';
@endphp
<style>
    .rv-page {
        --rv-ink: #12263a;
        --rv-muted: #6b7c8f;
        --rv-line: #e3e9ef;
        --rv-soft: #f4f7fa;
        --rv-accent: #1f6f78;
        --rv-good: #157347;
        --rv-danger: #b42318;
        --rv-shadow: 0 1px 2px rgba(18, 38, 58, .04), 0 8px 24px rgba(18, 38, 58, .06);
    }
    .rv-page .rv-shell {
        background: linear-gradient(180deg, #eef3f7 0%, #f8fafc 120px, #f8fafc 100%);
        min-height: calc(100vh - 120px);
        margin: -1.5rem -1.5rem 0;
        padding: 1.25rem 1.5rem 2rem;
    }
    .rv-page .rv-eyebrow {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--rv-accent);
        margin-bottom: .3rem;
    }
    .rv-page .rv-title {
        margin: 0;
        color: var(--rv-ink);
        font-size: 1.4rem;
        font-weight: 750;
        letter-spacing: -.02em;
    }
    .rv-page .rv-subtitle {
        margin: .25rem 0 0;
        color: var(--rv-muted);
        font-size: .86rem;
    }
    .rv-page .rv-kpi-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin: 1rem 0;
    }
    .rv-page .rv-kpi {
        background: #fff;
        border: 1px solid var(--rv-line);
        border-radius: 10px;
        padding: .75rem .9rem;
        box-shadow: var(--rv-shadow);
        border-left: 3px solid var(--rv-accent);
    }
    .rv-page .rv-kpi .k {
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--rv-muted);
    }
    .rv-page .rv-kpi .v {
        margin-top: .15rem;
        font-size: 1.2rem;
        font-weight: 750;
        color: var(--rv-ink);
    }
    .rv-page .rv-filters {
        background: #fff;
        border: 1px solid var(--rv-line);
        border-radius: 12px;
        box-shadow: var(--rv-shadow);
        padding: .85rem 1rem;
        margin-bottom: 1rem;
    }
    .rv-page .rv-filters-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .7rem;
        padding-bottom: .55rem;
        border-bottom: 1px solid var(--rv-line);
    }
    .rv-page .rv-filters-head h6 {
        margin: 0;
        font-size: .88rem;
        font-weight: 700;
        color: var(--rv-ink);
    }
    .rv-page .rv-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .7rem;
        font-weight: 650;
        color: var(--rv-accent);
        background: #eaf5f6;
        border: 1px solid #c9e4e7;
        border-radius: 999px;
        padding: .18rem .5rem;
    }
    .rv-page .rv-filters .form-label {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--rv-muted);
        margin-bottom: .28rem;
    }
    .rv-page .rv-filters .form-control,
    .rv-page .rv-filters .form-select {
        border-color: #d5dee8;
        border-radius: 8px;
        min-height: 38px;
        font-size: .86rem;
        color: var(--rv-ink);
        background: #fcfdfe;
    }
    .rv-page .rv-btn-primary {
        background: var(--rv-ink);
        border-color: var(--rv-ink);
        color: #fff;
        font-weight: 650;
        border-radius: 8px;
        min-height: 38px;
    }
    .rv-page .rv-btn-ghost {
        border: 1px solid #d5dee8;
        background: #fff;
        color: var(--rv-ink);
        border-radius: 8px;
        min-height: 38px;
        font-weight: 600;
    }
    .rv-page .rv-results-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .7rem;
        color: var(--rv-muted);
        font-size: .82rem;
    }
    .rv-page .rv-tour-block {
        background: #fff;
        border: 1px solid var(--rv-line);
        border-radius: 12px;
        box-shadow: var(--rv-shadow);
        margin-bottom: .85rem;
        overflow: hidden;
    }
    .rv-page .rv-tour-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .55rem 1rem;
        padding: .65rem .9rem;
        background: linear-gradient(180deg, #f8fafc, #fff);
        border-bottom: 1px solid var(--rv-line);
    }
    .rv-page .rv-tour-id {
        margin: 0;
        font-size: .95rem;
        font-weight: 750;
        color: var(--rv-ink);
        letter-spacing: -.01em;
    }
    .rv-page .rv-tour-meta {
        color: var(--rv-muted);
        font-size: .78rem;
        display: flex;
        flex-wrap: wrap;
        gap: .35rem .65rem;
        align-items: center;
    }
    .rv-page .rv-badge {
        display: inline-flex;
        align-items: center;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
        padding: .22rem .5rem;
        border-radius: 6px;
        background: #eef2f6;
        color: #405466;
        border: 1px solid #d7e0ea;
        white-space: nowrap;
    }
    .rv-page .rv-badge.status-confirmed,
    .rv-page .rv-badge.status-complete,
    .rv-page .rv-badge.status-completed,
    .rv-page .rv-badge.status-closed {
        background: #e8f6ee;
        color: var(--rv-good);
        border-color: #bfe3cd;
    }
    .rv-page .rv-badge.status-actual {
        background: #e8f3f8;
        color: #0b6e99;
        border-color: #b9dceb;
    }
    .rv-page .rv-badge.status-auto-cancel---confirmed,
    .rv-page .rv-badge.status-cancel,
    .rv-page .rv-badge.status-cancelled {
        background: #fdecec;
        color: var(--rv-danger);
        border-color: #f2c7c3;
    }
    .rv-page .rv-count-pill {
        font-size: .72rem;
        font-weight: 700;
        color: var(--rv-accent);
        background: #eaf5f6;
        border: 1px solid #c9e4e7;
        border-radius: 999px;
        padding: .18rem .55rem;
    }
    .rv-page .rv-table-wrap {
        overflow-x: auto;
    }
    .rv-page .rv-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        font-size: .8125rem;
    }
    .rv-page .rv-table thead th {
        background: var(--rv-soft);
        color: var(--rv-muted);
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: .5rem .7rem;
        border-bottom: 1px solid var(--rv-line);
        white-space: nowrap;
        vertical-align: middle;
    }
    .rv-page .rv-table tbody td {
        padding: .5rem .7rem;
        border-bottom: 1px solid #edf1f5;
        color: var(--rv-ink);
        vertical-align: middle;
    }
    .rv-page .rv-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .rv-page .rv-table tbody tr:hover td {
        background: #fafbfc;
    }
    .rv-page .rv-resto {
        font-weight: 700;
        color: var(--rv-ink);
    }
    .rv-page .rv-sub {
        display: block;
        margin-top: .1rem;
        font-size: .72rem;
        color: var(--rv-muted);
        font-weight: 500;
    }
    .rv-page .rv-amt {
        font-weight: 750;
        white-space: nowrap;
        text-align: right;
    }
    .rv-page .rv-empty {
        border: 1px dashed #c9d4df;
        border-radius: 12px;
        background: #fff;
        padding: 2.75rem 1.25rem;
        text-align: center;
        color: var(--rv-muted);
        box-shadow: var(--rv-shadow);
    }
    @media (max-width: 767.98px) {
        .rv-page .rv-shell { margin: -1rem -1rem 0; padding: 1rem 1rem 1.5rem; }
        .rv-page .rv-kpi-row { grid-template-columns: 1fr; }
        .rv-page .rv-title { font-size: 1.2rem; }
    }
</style>

<div class="content-wrapper rv-page">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="rv-shell">
            <div class="mb-2">
                <div class="rv-eyebrow">View Reports · Restaurant</div>
                <h1 class="rv-title">Redeemed Voucher</h1>
                <p class="rv-subtitle">Compact tour-wise listing of redeemed restaurant vouchers. Multiple restaurants under the same tour are grouped together.</p>
            </div>

            <div class="rv-kpi-row">
                <div class="rv-kpi">
                    <div class="k">Tours</div>
                    <div class="v">{{ number_format($tourCount) }}</div>
                </div>
                <div class="rv-kpi">
                    <div class="k">Redeemed vouchers</div>
                    <div class="v">{{ number_format($totalCount) }}</div>
                </div>
                <div class="rv-kpi">
                    <div class="k">Total value</div>
                    <div class="v">{{ $symbol }}{{ number_format($totalValue, 2) }}</div>
                </div>
            </div>

            <div class="rv-filters">
                <div class="rv-filters-head">
                    <h6><i class="ri-equalizer-line me-1"></i> Filtration</h6>
                    @if($hasActiveFilters)
                        <span class="rv-chip"><i class="ri-filter-fill"></i> Filters active</span>
                    @endif
                </div>
                <form method="GET" action="{{ route('reports.redeemed-restaurant-vouchers') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-xl-2 col-lg-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                               placeholder="Tour ID, guest, booking…">
                    </div>
                    <div class="col-6 col-xl-2 col-lg-4">
                        <label class="form-label">Restaurant</label>
                        <select name="restaurant" class="form-select">
                            <option value="">All restaurants</option>
                            @foreach(($restaurantOptions ?? []) as $restoOption)
                                <option value="{{ $restoOption }}" @selected(($restaurantFilter ?? '') === $restoOption)>
                                    {{ $restoOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-xl-2 col-lg-4">
                        <label class="form-label">Tour status</label>
                        <select name="tour_status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(($tourStatuses ?? []) as $statusOption)
                                <option value="{{ $statusOption }}" @selected(($tourStatus ?? '') === $statusOption)>
                                    {{ $statusOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-xl-2 col-md-4">
                        <label class="form-label">Redeemed from</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                    </div>
                    <div class="col-6 col-xl-2 col-md-4">
                        <label class="form-label">To</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                    </div>
                    <div class="col-12 col-xl-2 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn rv-btn-primary flex-grow-1">
                            <i class="ri-search-line me-1"></i> Apply
                        </button>
                        <a href="{{ route('reports.redeemed-restaurant-vouchers') }}" class="btn rv-btn-ghost" title="Reset">
                            <i class="ri-refresh-line"></i>
                        </a>
                    </div>
                </form>
            </div>

            @if($tourGroups->count() === 0)
                <div class="rv-empty">
                    <i class="ri-coupon-3-line" style="font-size:2rem;opacity:.4;"></i>
                    <h5 class="mt-2 mb-1" style="color:#12263a;font-weight:700;">No redeemed restaurant vouchers</h5>
                    <p class="mb-0">No records match the selected filters.</p>
                </div>
            @else
                <div class="rv-results-meta">
                    <div>
                        Showing <strong style="color:var(--rv-ink);">{{ $tourGroups->firstItem() }}–{{ $tourGroups->lastItem() }}</strong>
                        of <strong style="color:var(--rv-ink);">{{ number_format($tourCount) }}</strong> tours
                        · {{ number_format($totalCount) }} vouchers
                    </div>
                    @if(($restaurantFilter ?? '') !== '')
                        <div>Restaurant: <strong style="color:var(--rv-ink);">{{ $restaurantFilter }}</strong></div>
                    @endif
                </div>

                @foreach($tourGroups as $group)
                    @php
                        $statusSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) $group['tour_status']));
                        $statusSlug = trim($statusSlug, '-');
                        $groupCurrency = $group['rows'][0]['currency'] ?? ($dmcCurrency['code'] ?? 'SGD');
                    @endphp
                    <section class="rv-tour-block">
                        <div class="rv-tour-head">
                            <div>
                                <h3 class="rv-tour-id">{{ $group['tour_display_id'] }}</h3>
                                <div class="rv-tour-meta mt-1">
                                    <span>Tour ID {{ $group['tour_id'] }}</span>
                                    <span class="rv-count-pill">{{ $group['voucher_count'] }} restaurant{{ $group['voucher_count'] === 1 ? '' : 's' }}</span>
                                    @if($group['latest_redeemed_at'])
                                        <span>Latest {{ \Carbon\Carbon::parse($group['latest_redeemed_at'])->format('d M Y, H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="rv-badge status-{{ $statusSlug }}">{{ $group['tour_status'] }}</span>
                                <span class="fw-bold" style="color:var(--rv-ink);font-size:.92rem;">
                                    {{ $symbol }}{{ number_format($group['tour_total'], 2) }}
                                    <span class="text-muted fw-normal" style="font-size:.72rem;">{{ $groupCurrency }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="rv-table-wrap">
                            <table class="rv-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Restaurant</th>
                                        <th>Guest</th>
                                        <th>Visit</th>
                                        <th>Meal</th>
                                        <th>Pax</th>
                                        <th>Tour status</th>
                                        <th>Redeemed</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group['rows'] as $idx => $row)
                                        @php
                                            $paxParts = [];
                                            if ($row['adults'] > 0) $paxParts[] = $row['adults'].'A';
                                            if ($row['children'] > 0) $paxParts[] = $row['children'].'C';
                                            if ($row['infants'] > 0) $paxParts[] = $row['infants'].'I';
                                            $phoneDisplay = trim(($row['country_code'] ? '+'.$row['country_code'].' ' : '').($row['phone'] ?? ''));
                                            $mealLabel = trim(implode(' / ', array_filter([
                                                $row['meal_type'] ?? null,
                                                $row['meal_specific'] ?? null,
                                            ])));
                                        @endphp
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <span class="rv-resto">{{ $row['restaurant_name'] }}</span>
                                                @if($row['destination'])
                                                    <span class="rv-sub">{{ $row['destination'] }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $row['guest_name'] }}
                                                @if($phoneDisplay)
                                                    <span class="rv-sub">{{ $phoneDisplay }}</span>
                                                @elseif($row['email'])
                                                    <span class="rv-sub">{{ $row['email'] }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $row['booking_date'] ? \Carbon\Carbon::parse($row['booking_date'])->format('d M Y') : '—' }}
                                                @if($row['visit_time'])
                                                    <span class="rv-sub">{{ $row['visit_time'] }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $mealLabel !== '' ? $mealLabel : '—' }}</td>
                                            <td>{{ $paxParts ? implode(' · ', $paxParts) : '—' }}</td>
                                            <td>
                                                <span class="rv-badge status-{{ $statusSlug }}">{{ $row['tour_status'] }}</span>
                                            </td>
                                            <td>
                                                {{ $row['redeemed_at'] ? \Carbon\Carbon::parse($row['redeemed_at'])->format('d M Y') : '—' }}
                                                @if($row['redeemed_at'])
                                                    <span class="rv-sub">{{ \Carbon\Carbon::parse($row['redeemed_at'])->format('H:i') }}</span>
                                                @endif
                                                @if($row['redeemed_by'])
                                                    <span class="rv-sub">by {{ $row['redeemed_by'] }}</span>
                                                @endif
                                            </td>
                                            <td class="rv-amt">
                                                {{ $symbol }}{{ number_format($row['total_price'], 2) }}
                                                <span class="rv-sub">{{ $row['currency'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach

                <div class="d-flex justify-content-center mt-3">
                    {{ $tourGroups->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
