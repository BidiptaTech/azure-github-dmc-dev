@extends('layouts.layout')

@section('content')
@php
    $dmcCurrency = \App\Helpers\CommonHelper::getDmcCurrencyByCountry();
    $hasActiveFilters = ($search ?? '') !== '' || ($startDate ?? '') || ($endDate ?? '') || ($tourStatus ?? '') !== '';
@endphp
<style>
    .rv-page {
        --rv-ink: #12263a;
        --rv-muted: #6b7c8f;
        --rv-line: #e3e9ef;
        --rv-soft: #f4f7fa;
        --rv-accent: #1f6f78;
        --rv-good: #157347;
        --rv-warn: #9a6700;
        --rv-danger: #b42318;
        --rv-shadow: 0 1px 2px rgba(18, 38, 58, .04), 0 8px 24px rgba(18, 38, 58, .06);
        font-feature-settings: "ss01" on, "kern" on;
    }
    .rv-page .rv-shell {
        background: linear-gradient(180deg, #eef3f7 0%, #f8fafc 140px, #f8fafc 100%);
        min-height: calc(100vh - 120px);
        margin: -1.5rem -1.5rem 0;
        padding: 1.5rem 1.5rem 2.5rem;
    }
    .rv-page .rv-topbar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .rv-page .rv-eyebrow {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--rv-accent);
        margin-bottom: .35rem;
    }
    .rv-page .rv-title {
        margin: 0;
        color: var(--rv-ink);
        font-size: 1.55rem;
        font-weight: 750;
        letter-spacing: -.02em;
        line-height: 1.2;
    }
    .rv-page .rv-subtitle {
        margin: .35rem 0 0;
        color: var(--rv-muted);
        font-size: .92rem;
        max-width: 42rem;
    }
    .rv-page .rv-kpi-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
        margin-bottom: 1.1rem;
    }
    .rv-page .rv-kpi {
        background: #fff;
        border: 1px solid var(--rv-line);
        border-radius: 12px;
        padding: .95rem 1.05rem;
        box-shadow: var(--rv-shadow);
        position: relative;
        overflow: hidden;
    }
    .rv-page .rv-kpi::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: var(--rv-accent);
        opacity: .85;
    }
    .rv-page .rv-kpi .k {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--rv-muted);
    }
    .rv-page .rv-kpi .v {
        margin-top: .25rem;
        font-size: 1.4rem;
        font-weight: 750;
        color: var(--rv-ink);
        letter-spacing: -.02em;
    }
    .rv-page .rv-kpi .hint {
        margin-top: .15rem;
        font-size: .75rem;
        color: var(--rv-muted);
    }
    .rv-page .rv-filters {
        background: #fff;
        border: 1px solid var(--rv-line);
        border-radius: 14px;
        box-shadow: var(--rv-shadow);
        padding: 1rem 1.15rem 1.1rem;
        margin-bottom: 1.25rem;
    }
    .rv-page .rv-filters-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .85rem;
        padding-bottom: .7rem;
        border-bottom: 1px solid var(--rv-line);
    }
    .rv-page .rv-filters-head h6 {
        margin: 0;
        font-size: .92rem;
        font-weight: 700;
        color: var(--rv-ink);
    }
    .rv-page .rv-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .72rem;
        font-weight: 650;
        color: var(--rv-accent);
        background: #eaf5f6;
        border: 1px solid #c9e4e7;
        border-radius: 999px;
        padding: .2rem .55rem;
    }
    .rv-page .rv-filters .form-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--rv-muted);
        margin-bottom: .35rem;
    }
    .rv-page .rv-filters .form-control,
    .rv-page .rv-filters .form-select {
        border-color: #d5dee8;
        border-radius: 9px;
        min-height: 42px;
        font-size: .9rem;
        color: var(--rv-ink);
        background-color: #fcfdfe;
    }
    .rv-page .rv-filters .form-control:focus,
    .rv-page .rv-filters .form-select:focus {
        border-color: #7fb4b9;
        box-shadow: 0 0 0 .2rem rgba(31, 111, 120, .12);
    }
    .rv-page .rv-btn-primary {
        background: var(--rv-ink);
        border-color: var(--rv-ink);
        color: #fff;
        font-weight: 650;
        border-radius: 9px;
        min-height: 42px;
        padding-inline: 1rem;
    }
    .rv-page .rv-btn-primary:hover {
        background: #0c1b2a;
        border-color: #0c1b2a;
        color: #fff;
    }
    .rv-page .rv-btn-ghost {
        border: 1px solid #d5dee8;
        background: #fff;
        color: var(--rv-ink);
        border-radius: 9px;
        min-height: 42px;
        font-weight: 600;
    }
    .rv-page .rv-results-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .85rem;
        color: var(--rv-muted);
        font-size: .86rem;
    }
    .rv-page .rv-card {
        background: #fff;
        border: 1px solid var(--rv-line);
        border-radius: 14px;
        box-shadow: var(--rv-shadow);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .rv-page .rv-card:hover {
        transform: translateY(-2px);
        border-color: #cfd9e4;
        box-shadow: 0 2px 4px rgba(18,38,58,.05), 0 16px 32px rgba(18,38,58,.1);
    }
    .rv-page .rv-card-head {
        padding: 1.05rem 1.15rem .95rem;
        border-bottom: 1px solid var(--rv-line);
        background:
            linear-gradient(180deg, #fbfcfd 0%, #ffffff 100%);
    }
    .rv-page .rv-resto {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 750;
        color: var(--rv-ink);
        letter-spacing: -.015em;
        line-height: 1.25;
    }
    .rv-page .rv-meta {
        margin-top: .35rem;
        color: var(--rv-muted);
        font-size: .82rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .35rem .55rem;
    }
    .rv-page .rv-dot { opacity: .45; }
    .rv-page .rv-badge {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
        padding: .32rem .6rem;
        border-radius: 7px;
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
    .rv-page .rv-body { padding: 1rem 1.15rem; flex: 1; }
    .rv-page .rv-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .8rem 1rem;
    }
    .rv-page .rv-field {
        min-width: 0;
        padding: .55rem .65rem;
        border-radius: 9px;
        background: var(--rv-soft);
        border: 1px solid #e8eef4;
    }
    .rv-page .rv-field .k {
        display: block;
        font-size: .66rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--rv-muted);
        font-weight: 700;
        margin-bottom: .18rem;
    }
    .rv-page .rv-field .v {
        color: var(--rv-ink);
        font-size: .88rem;
        font-weight: 650;
        word-break: break-word;
        line-height: 1.35;
    }
    .rv-page .rv-field .v .sub {
        display: block;
        margin-top: .1rem;
        font-size: .78rem;
        font-weight: 500;
        color: var(--rv-muted);
    }
    .rv-page .rv-price {
        margin-top: .95rem;
        padding: .85rem 1rem;
        border-radius: 10px;
        background: linear-gradient(135deg, #12263a 0%, #1a3d4a 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }
    .rv-page .rv-price .label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 700;
        opacity: .78;
    }
    .rv-page .rv-price .curr {
        font-size: .75rem;
        opacity: .65;
        margin-top: .1rem;
    }
    .rv-page .rv-price .amt {
        font-size: 1.35rem;
        font-weight: 750;
        letter-spacing: -.02em;
        line-height: 1;
    }
    .rv-page .rv-redeem-line {
        margin-top: .7rem;
        font-size: .78rem;
        color: var(--rv-muted);
        display: flex;
        flex-wrap: wrap;
        gap: .25rem .5rem;
        align-items: center;
    }
    .rv-page .rv-media {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .75rem;
        padding: 0 1.15rem 1.15rem;
    }
    .rv-page .rv-media-box {
        border: 1px solid var(--rv-line);
        border-radius: 11px;
        background: #fafbfc;
        min-height: 128px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .rv-page .rv-media-box .cap {
        font-size: .66rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--rv-muted);
        padding: .45rem .65rem;
        border-bottom: 1px solid var(--rv-line);
        background: #fff;
        display: flex;
        align-items: center;
        gap: .3rem;
    }
    .rv-page .rv-media-box .preview {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: .55rem;
        min-height: 96px;
    }
    .rv-page .rv-media-box img {
        max-width: 100%;
        max-height: 112px;
        object-fit: contain;
        border-radius: 6px;
        cursor: zoom-in;
        background: #fff;
        border: 1px solid #edf1f5;
        padding: .2rem;
        transition: transform .15s ease;
    }
    .rv-page .rv-media-box img:hover { transform: scale(1.03); }
    .rv-page .rv-empty-media {
        color: #95a3b2;
        font-size: .78rem;
        text-align: center;
        padding: .5rem;
        line-height: 1.35;
    }
    .rv-page .rv-empty {
        border: 1px dashed #c9d4df;
        border-radius: 14px;
        background: #fff;
        padding: 3.25rem 1.5rem;
        text-align: center;
        color: var(--rv-muted);
        box-shadow: var(--rv-shadow);
    }
    .rv-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1090;
        background: rgba(10, 20, 30, .82);
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        backdrop-filter: blur(2px);
    }
    .rv-lightbox.open { display: flex; }
    .rv-lightbox img {
        max-width: min(920px, 94vw);
        max-height: 88vh;
        border-radius: 10px;
        box-shadow: 0 20px 50px rgba(0,0,0,.35);
        background: #fff;
    }
    @media (max-width: 991.98px) {
        .rv-page .rv-kpi-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
        .rv-page .rv-shell { margin: -1rem -1rem 0; padding: 1rem 1rem 2rem; }
        .rv-page .rv-kpi-row,
        .rv-page .rv-grid,
        .rv-page .rv-media { grid-template-columns: 1fr; }
        .rv-page .rv-title { font-size: 1.3rem; }
    }
</style>

<div class="content-wrapper rv-page">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="rv-shell">
            <div class="rv-topbar">
                <div>
                    <div class="rv-eyebrow">View Reports · Restaurant</div>
                    <h1 class="rv-title">Redeemed Voucher</h1>
                    <p class="rv-subtitle">Operational view of redeemed restaurant bookings with guest details, meal summary, tour status, total value, voucher and QR assets.</p>
                </div>
            </div>

            <div class="rv-kpi-row">
                <div class="rv-kpi">
                    <div class="k">Redeemed vouchers</div>
                    <div class="v">{{ number_format($totalCount) }}</div>
                    <div class="hint">Matching current filters</div>
                </div>
                <div class="rv-kpi">
                    <div class="k">Total value</div>
                    <div class="v">{{ $dmcCurrency['symbol'] ?? '' }}{{ number_format($totalValue, 2) }}</div>
                    <div class="hint">Restaurant totals</div>
                </div>
                <div class="rv-kpi">
                    <div class="k">Voucher images</div>
                    <div class="v">{{ number_format($withVoucher) }}</div>
                    <div class="hint">Available on record</div>
                </div>
                <div class="rv-kpi">
                    <div class="k">QR codes</div>
                    <div class="v">{{ number_format($withQr) }}</div>
                    <div class="hint">Available on record</div>
                </div>
            </div>

            <div class="rv-filters">
                <div class="rv-filters-head">
                    <h6><i class="ri-equalizer-line me-1"></i> Filtration</h6>
                    @if($hasActiveFilters)
                        <span class="rv-chip"><i class="ri-filter-fill"></i> Filters active</span>
                    @endif
                </div>
                <form method="GET" action="{{ route('reports.redeemed-restaurant-vouchers') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-xl-3 col-lg-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                               placeholder="Restaurant, guest, tour ID, phone…">
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
                    <div class="col-6 col-xl-2 col-lg-4">
                        <label class="form-label">Redeemed from</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                    </div>
                    <div class="col-6 col-xl-2 col-lg-4">
                        <label class="form-label">Redeemed to</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                    </div>
                    <div class="col-6 col-xl-3 col-lg-4 d-flex gap-2">
                        <button type="submit" class="btn rv-btn-primary flex-grow-1">
                            <i class="ri-search-line me-1"></i> Apply
                        </button>
                        <a href="{{ route('reports.redeemed-restaurant-vouchers') }}" class="btn rv-btn-ghost" title="Reset filters">
                            <i class="ri-refresh-line"></i>
                        </a>
                    </div>
                </form>
            </div>

            @if($vouchers->count() === 0)
                <div class="rv-empty">
                    <i class="ri-coupon-3-line" style="font-size:2.4rem;opacity:.4;"></i>
                    <h5 class="mt-2 mb-1" style="color:#12263a;font-weight:700;">No redeemed restaurant vouchers</h5>
                    <p class="mb-0">No records match the selected filters. Try clearing tour status or date range.</p>
                </div>
            @else
                <div class="rv-results-meta">
                    <div>
                        Showing <strong style="color:var(--rv-ink);">{{ $vouchers->firstItem() }}–{{ $vouchers->lastItem() }}</strong>
                        of <strong style="color:var(--rv-ink);">{{ number_format($totalCount) }}</strong> redeemed vouchers
                    </div>
                    @if(($tourStatus ?? '') !== '')
                        <div>Status: <strong style="color:var(--rv-ink);">{{ $tourStatus }}</strong></div>
                    @endif
                </div>

                <div class="row g-3">
                    @foreach($vouchers as $row)
                        @php
                            $statusSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) $row['tour_status']));
                            $statusSlug = trim($statusSlug, '-');
                            $phoneDisplay = trim(($row['country_code'] ? '+'.$row['country_code'].' ' : '').($row['phone'] ?? ''));
                            $paxParts = [];
                            if ($row['adults'] > 0) $paxParts[] = $row['adults'].' Adult'.($row['adults'] > 1 ? 's' : '');
                            if ($row['children'] > 0) $paxParts[] = $row['children'].' Child'.($row['children'] > 1 ? 'ren' : '');
                            if ($row['infants'] > 0) $paxParts[] = $row['infants'].' Infant'.($row['infants'] > 1 ? 's' : '');
                            $currency = $row['currency'] ?: ($dmcCurrency['code'] ?? 'SGD');
                            $symbol = $dmcCurrency['symbol'] ?? '';
                            $mealSpecificClean = $row['meal_specific']
                                ? trim(preg_replace('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', '', (string) $row['meal_specific']))
                                : null;
                        @endphp
                        <div class="col-12 col-xl-6">
                            <article class="rv-card">
                                <div class="rv-card-head">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="min-w-0">
                                            <h3 class="rv-resto text-truncate" title="{{ $row['restaurant_name'] }}">{{ $row['restaurant_name'] }}</h3>
                                            <div class="rv-meta">
                                                @if($row['destination'])
                                                    <span><i class="ri-map-pin-line"></i> {{ $row['destination'] }}</span>
                                                    <span class="rv-dot">•</span>
                                                @endif
                                                <span>Booking #{{ $row['booking_id'] }}</span>
                                            </div>
                                        </div>
                                        <span class="rv-badge status-{{ $statusSlug }}" title="Tour status">
                                            {{ $row['tour_status'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="rv-body">
                                    <div class="rv-grid">
                                        <div class="rv-field">
                                            <span class="k">Guest</span>
                                            <span class="v">{{ $row['guest_name'] }}</span>
                                        </div>
                                        <div class="rv-field">
                                            <span class="k">Tour</span>
                                            <span class="v">
                                                {{ $row['tour_display_id'] }}
                                                <span class="sub">ID {{ $row['tour_id'] }}</span>
                                            </span>
                                        </div>
                                        <div class="rv-field">
                                            <span class="k">Visit</span>
                                            <span class="v">
                                                {{ $row['booking_date'] ? \Carbon\Carbon::parse($row['booking_date'])->format('d M Y') : '—' }}
                                                @if($row['visit_time'])
                                                    <span class="sub">{{ $row['visit_time'] }}</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="rv-field">
                                            <span class="k">Meal</span>
                                            <span class="v">
                                                {{ $row['meal_type'] ?: '—' }}
                                                @if($mealSpecificClean)
                                                    <span class="sub">{{ $mealSpecificClean }}</span>
                                                @endif
                                                @if($row['meal_name'])
                                                    <span class="sub">{{ $row['meal_name'] }}</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="rv-field">
                                            <span class="k">Pax</span>
                                            <span class="v">{{ $paxParts ? implode(' · ', $paxParts) : '—' }}</span>
                                        </div>
                                        <div class="rv-field">
                                            <span class="k">Contact</span>
                                            <span class="v" style="font-weight:600;">
                                                @if($phoneDisplay){{ $phoneDisplay }}@endif
                                                @if($row['email'])
                                                    <span class="sub">{{ $row['email'] }}</span>
                                                @endif
                                                @if(!$phoneDisplay && !$row['email'])—@endif
                                            </span>
                                        </div>
                                    </div>

                                    <div class="rv-price">
                                        <div>
                                            <div class="label">Restaurant total</div>
                                            <div class="curr">{{ $currency }}</div>
                                        </div>
                                        <div class="amt">{{ $symbol }}{{ number_format($row['total_price'], 2) }}</div>
                                    </div>

                                    @if($row['redeemed_by'] || $row['remarks'] || $row['redeemed_at'])
                                        <div class="rv-redeem-line">
                                            <i class="ri-checkbox-circle-line" style="color:var(--rv-good);"></i>
                                            @if($row['redeemed_at'])
                                                <span>Redeemed {{ \Carbon\Carbon::parse($row['redeemed_at'])->format('d M Y, H:i') }}</span>
                                            @endif
                                            @if($row['redeemed_by'])
                                                <span class="rv-dot">•</span>
                                                <span>by {{ $row['redeemed_by'] }}</span>
                                            @endif
                                            @if($row['remarks'])
                                                <span class="rv-dot">•</span>
                                                <span><em>{{ $row['remarks'] }}</em></span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="rv-media">
                                    <div class="rv-media-box">
                                        <div class="cap"><i class="ri-ticket-2-line"></i> Voucher image</div>
                                        <div class="preview">
                                            @if(!empty($row['voucher_image']))
                                                <img src="{{ $row['voucher_image'] }}" alt="Voucher" loading="lazy"
                                                     onclick="rvOpenLightbox(this.src)">
                                            @else
                                                <div class="rv-empty-media">No voucher image uploaded</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="rv-media-box">
                                        <div class="cap"><i class="ri-qr-code-line"></i> QR code</div>
                                        <div class="preview">
                                            @if(!empty($row['qr_code']))
                                                <img src="{{ $row['qr_code'] }}" alt="QR code" loading="lazy"
                                                     onclick="rvOpenLightbox(this.src)">
                                            @else
                                                <div class="rv-empty-media">No QR code available</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $vouchers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="rv-lightbox" id="rvLightbox" onclick="rvCloseLightbox()">
    <img src="" alt="Preview" id="rvLightboxImg">
</div>

<script>
    function rvOpenLightbox(src) {
        var box = document.getElementById('rvLightbox');
        var img = document.getElementById('rvLightboxImg');
        if (!box || !img) return;
        img.src = src;
        box.classList.add('open');
    }
    function rvCloseLightbox() {
        var box = document.getElementById('rvLightbox');
        var img = document.getElementById('rvLightboxImg');
        if (!box || !img) return;
        box.classList.remove('open');
        img.src = '';
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') rvCloseLightbox();
    });
</script>
@endsection
