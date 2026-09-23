@extends('layouts.layout')
@php
    $mode = $mode ?? session('thank_you_mode', 'created');
    $isUpdated = $mode === 'updated';
@endphp
@section('title', $isUpdated ? 'Tour Updated Successfully' : 'Thank You - Enquiry Created')

@section('css')
<style>
.stp-ty-page {
    --ty-ink: #1e3a5f;
    --ty-teal: #0f766e;
    --ty-teal-soft: #e6f4f2;
    --ty-muted: #64748b;
    --ty-line: rgba(30, 58, 95, 0.10);
    --ty-radius: 14px;
    background:
        linear-gradient(180deg, #e8eef5 0%, #f4f7fb 42%, #f8fafc 100%);
    min-height: 70vh;
    padding-bottom: 2.5rem;
}
.stp-ty-shell {
    position: relative;
    background: #fff;
    border: 1px solid var(--ty-line);
    border-radius: calc(var(--ty-radius) + 4px);
    box-shadow:
        0 1px 0 rgba(255,255,255,0.8) inset,
        0 18px 50px rgba(30, 58, 95, 0.10);
    overflow: hidden;
    animation: stpTyRise 0.55s ease-out both;
}
@keyframes stpTyRise {
    from { opacity: 0; transform: translateY(18px); }
    to { opacity: 1; transform: translateY(0); }
}
.stp-ty-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #155e75 48%, #0f766e 100%);
    color: #fff;
    padding: 1.35rem 1.5rem 1.55rem;
    position: relative;
    overflow: hidden;
}
.stp-ty-banner::before {
    content: '';
    position: absolute;
    inset: auto -10% -55% auto;
    width: 280px;
    height: 280px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    pointer-events: none;
}
.stp-ty-banner::after {
    content: '';
    position: absolute;
    left: -40px;
    top: -60px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
    pointer-events: none;
}
.stp-ty-banner-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.1rem;
    position: relative;
    z-index: 1;
}
.stp-ty-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: rgba(255,255,255,0.88);
    text-decoration: none;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.18);
    transition: background 0.15s ease, color 0.15s ease;
}
.stp-ty-back:hover {
    color: #fff;
    background: rgba(255,255,255,0.18);
}
.stp-ty-mode-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0.28rem 0.65rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.22);
}
.stp-ty-hero {
    text-align: center;
    position: relative;
    z-index: 1;
    padding: 0.25rem 0 0.35rem;
}
.stp-ty-hero-icon {
    width: 3.6rem;
    height: 3.6rem;
    margin: 0 auto 0.85rem;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0f766e;
    background: #fff;
    font-size: 1.65rem;
    box-shadow: 0 10px 28px rgba(0,0,0,0.18);
    animation: stpTyPop 0.55s ease-out 0.15s both;
}
.stp-ty-hero-icon.is-updated { color: #1e3a5f; }
@keyframes stpTyPop {
    from { opacity: 0; transform: scale(0.7); }
    to { opacity: 1; transform: scale(1); }
}
.stp-ty-hero h1 {
    font-size: clamp(1.55rem, 2.8vw, 2rem);
    font-weight: 800;
    margin: 0 0 0.35rem;
    letter-spacing: -0.025em;
    color: #fff;
}
.stp-ty-hero p {
    margin: 0 auto;
    max-width: 28rem;
    color: rgba(255,255,255,0.86);
    font-size: 0.92rem;
    line-height: 1.45;
}
.stp-ty-body {
    padding: 1.35rem 1.5rem 1.5rem;
}
.stp-ty-block {
    margin-bottom: 1.25rem;
}
.stp-ty-block:last-of-type { margin-bottom: 0.35rem; }
.stp-ty-block-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-weight: 800;
    color: var(--ty-ink);
    margin-bottom: 0.85rem;
    font-size: 0.92rem;
    letter-spacing: -0.01em;
}
.stp-ty-block-title i {
    width: 1.7rem;
    height: 1.7rem;
    border-radius: 0.45rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--ty-teal-soft);
    color: var(--ty-teal);
    font-size: 0.95rem;
}
.stp-ty-meta {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0.65rem;
}
@media (max-width: 992px) {
    .stp-ty-meta { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 576px) {
    .stp-ty-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
.stp-ty-meta-item {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--ty-line);
    border-radius: 12px;
    padding: 0.75rem 0.8rem;
    min-height: 100%;
    transition: border-color 0.15s ease, transform 0.15s ease;
}
.stp-ty-meta-item:hover {
    border-color: rgba(15, 118, 110, 0.28);
    transform: translateY(-1px);
}
.stp-ty-meta-label {
    display: block;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    font-weight: 700;
    margin-bottom: 0.35rem;
}
.stp-ty-meta-value {
    color: #0f172a;
    font-weight: 800;
    font-size: 0.9rem;
    line-height: 1.25;
    word-break: break-word;
}
.stp-ty-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.22rem 0.58rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #0f766e, #0d9488);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 800;
    box-shadow: 0 4px 10px rgba(15, 118, 110, 0.22);
}
.stp-ty-badge.is-soft {
    background: #e0f2fe;
    color: #0369a1;
    box-shadow: none;
}
.stp-ty-city {
    background: #fff;
    border: 1px solid var(--ty-line);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 0.75rem;
    box-shadow: 0 4px 14px rgba(30, 58, 95, 0.04);
    animation: stpTyRise 0.45s ease-out both;
}
.stp-ty-city:nth-child(2) { animation-delay: 0.05s; }
.stp-ty-city:nth-child(3) { animation-delay: 0.1s; }
.stp-ty-city:last-child { margin-bottom: 0; }
.stp-ty-city-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.55rem 1rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--ty-line);
    border-left: 4px solid #0d9488;
}
.stp-ty-city[data-tint="0"] .stp-ty-city-head { background: #e6f4f2; border-left-color: #0d9488; }
.stp-ty-city[data-tint="1"] .stp-ty-city-head { background: #eef2f6; border-left-color: #334155; }
.stp-ty-city[data-tint="2"] .stp-ty-city-head { background: #f3eee8; border-left-color: #8a6a4a; }
.stp-ty-city[data-tint="3"] .stp-ty-city-head { background: #e9eef4; border-left-color: #3d5a80; }
.stp-ty-city[data-tint="4"] .stp-ty-city-head { background: #e8efe9; border-left-color: #4a6b5a; }
.stp-ty-city[data-tint="5"] .stp-ty-city-head { background: #ebedf3; border-left-color: #5b6b8a; }
.stp-ty-city-name {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-width: 0;
}
.stp-ty-city-mark {
    width: 2.35rem;
    height: 2.35rem;
    border-radius: 0.65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, 0.06);
    color: #0f766e;
    font-size: 1.15rem;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}
.stp-ty-city[data-tint="1"] .stp-ty-city-mark { color: #334155; }
.stp-ty-city[data-tint="2"] .stp-ty-city-mark { color: #8a6a4a; }
.stp-ty-city[data-tint="3"] .stp-ty-city-mark { color: #3d5a80; }
.stp-ty-city[data-tint="4"] .stp-ty-city-mark { color: #4a6b5a; }
.stp-ty-city[data-tint="5"] .stp-ty-city-mark { color: #5b6b8a; }
.stp-ty-city-name strong {
    display: block;
    color: #0f172a;
    font-size: 1.05rem;
    line-height: 1.2;
    letter-spacing: -0.01em;
}
.stp-ty-city-name small {
    display: block;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.1rem;
}
.stp-ty-city-dates {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: #1e293b;
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 999px;
    padding: 0.32rem 0.75rem;
    white-space: nowrap;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}
.stp-ty-svc-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
    gap: 0.65rem;
    padding: 1rem;
    background: linear-gradient(180deg, #fff 0%, #fafbfc 100%);
}
.stp-ty-svc {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.7rem 0.75rem;
    border-radius: 12px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: #fff;
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.stp-ty-svc:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(30, 58, 95, 0.08);
    border-color: rgba(15, 118, 110, 0.22);
}
.stp-ty-svc-icon {
    width: 2.35rem;
    height: 2.35rem;
    border-radius: 0.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.stp-ty-svc-copy {
    min-width: 0;
    flex: 1;
}
.stp-ty-svc-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.15;
}
.stp-ty-svc-sub {
    display: block;
    font-size: 0.68rem;
    color: #94a3b8;
    font-weight: 600;
    margin-top: 0.12rem;
}
.stp-ty-svc-count {
    min-width: 1.55rem;
    height: 1.55rem;
    padding: 0 0.4rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    font-weight: 800;
    background: #1e3a5f;
    color: #fff;
    flex-shrink: 0;
}
.stp-ty-svc--hotel .stp-ty-svc-icon { color: #475569; background: #f1f5f9; }
.stp-ty-svc--arrival .stp-ty-svc-icon { color: #1d4ed8; background: #eff6ff; }
.stp-ty-svc--attraction .stp-ty-svc-icon { color: #7c3aed; background: #f5f3ff; }
.stp-ty-svc--guide .stp-ty-svc-icon { color: #059669; background: #ecfdf5; }
.stp-ty-svc--restaurant .stp-ty-svc-icon { color: #d97706; background: #fffbeb; }
.stp-ty-svc--transport .stp-ty-svc-icon { color: #0d9488; background: #f0fdfa; }
.stp-ty-svc--departure .stp-ty-svc-icon { color: #2563eb; background: #eff6ff; }
.stp-ty-svc--miscellaneous .stp-ty-svc-icon { color: #db2777; background: #fdf2f8; }
.stp-ty-actions-wrap {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px dashed rgba(30, 58, 95, 0.14);
}
.stp-ty-actions-label {
    text-align: center;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
    margin-bottom: 0.75rem;
}
.stp-ty-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.65rem;
}
.stp-ty-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    min-height: 5.1rem;
    padding: 0.85rem 0.65rem;
    border-radius: 12px;
    border: 1px solid rgba(15, 23, 42, 0.10);
    background: #fff;
    color: #334155;
    text-decoration: none;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}
.stp-ty-action i {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    background: #f1f5f9;
    color: #1e3a5f;
}
.stp-ty-action span {
    font-size: 0.75rem;
    font-weight: 700;
    text-align: center;
    line-height: 1.25;
    color: #475569;
}
.stp-ty-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(30, 58, 95, 0.10);
    border-color: rgba(15, 118, 110, 0.28);
    color: #0f172a;
}
.stp-ty-action.is-primary {
    background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 100%);
    border-color: transparent;
    box-shadow: 0 8px 20px rgba(30, 58, 95, 0.22);
}
.stp-ty-action.is-primary i {
    background: rgba(255,255,255,0.16);
    color: #fff;
}
.stp-ty-action.is-primary span { color: #fff; }
.stp-ty-action.is-primary:hover {
    box-shadow: 0 12px 26px rgba(30, 58, 95, 0.28);
}
.stp-ty-action.is-info i {
    background: #e0f2fe;
    color: #0284c7;
}
.stp-ty-note {
    text-align: center;
    color: var(--ty-muted);
    font-size: 0.84rem;
    margin: 1.15rem 0 0;
    line-height: 1.55;
}
@media (max-width: 576px) {
    .stp-ty-banner { padding: 1.1rem 1.1rem 1.25rem; }
    .stp-ty-body { padding: 1.1rem; }
    .stp-ty-city-head { flex-direction: column; align-items: flex-start; }
}
</style>
@endsection

@section('content')
@php
    $tourDetails = session('tour_details');
    if (!is_array($tourDetails)) {
        $tourDetails = [];
    }
    $createdOrders = session('created_orders');
    if (!is_array($createdOrders)) {
        $createdOrders = [];
    }
    $tourId = $tourDetails['tour_id'] ?? null;
    $encryptedTourId = $tourId ? \Illuminate\Support\Facades\Crypt::encrypt($tourId) : null;

    $citySections = $tourDetails['city_sections'] ?? [];
    if (!is_array($citySections)) {
        $citySections = [];
    }

    foreach ($citySections as &$section) {
        $rawCity = (string) ($section['city'] ?? '');
        if (preg_match('/^(.+?)\s*\[/', $rawCity, $m)) {
            $rawCity = trim($m[1]);
        }
        if (preg_match('/^(.+?)\s*\([^)]*\)\s*$/u', $rawCity, $m)) {
            $rawCity = trim($m[1]);
        }
        $section['city'] = $rawCity !== '' ? $rawCity : 'Destination';
        $country = trim((string) ($section['country'] ?? ''));
        if ($country !== '' && strcasecmp($country, $section['city']) === 0) {
            $section['country'] = '';
        }
    }
    unset($section);

    if (empty($citySections) && !empty($createdOrders)) {
        $fallbackServices = [];
        foreach ($createdOrders as $order) {
            $raw = strtolower((string) ($order['type'] ?? 'service'));
            $key = match ($raw) {
                'entry_port', 'arrival' => 'arrival',
                'exit_port', 'departure' => 'departure',
                'travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'transport' => 'transport',
                'miscellaneous', 'misc' => 'miscellaneous',
                default => $raw,
            };
            $fallbackServices[$key] = ($fallbackServices[$key] ?? 0) + (int) ($order['data_count'] ?? 1);
        }
        $orderedFallback = [];
        foreach (['hotel', 'arrival', 'attraction', 'guide', 'restaurant', 'transport', 'departure', 'miscellaneous'] as $k) {
            if (!empty($fallbackServices[$k])) {
                $orderedFallback[] = ['key' => $k, 'count' => $fallbackServices[$k]];
                unset($fallbackServices[$k]);
            }
        }
        foreach ($fallbackServices as $k => $c) {
            $orderedFallback[] = ['key' => $k, 'count' => $c];
        }
        $cityName = (string) ($tourDetails['city'] ?? ($tourDetails['destination'] ?? 'Destination'));
        if (str_contains($cityName, ',')) {
            $cityName = trim(explode(',', $cityName)[0]);
        }
        $citySections = [[
            'city' => $cityName,
            'country' => '',
            'date_label' => trim(($tourDetails['check_in_date'] ?? '') . (($tourDetails['check_out_date'] ?? '') ? ' → ' . $tourDetails['check_out_date'] : '')),
            'services' => $orderedFallback,
        ]];
    }

    $serviceMeta = [
        'hotel' => ['label' => 'Hotel', 'sub' => 'Stay', 'icon' => 'ri-hotel-line', 'tint' => 'hotel'],
        'arrival' => ['label' => 'Arrival', 'sub' => 'Transfer', 'icon' => 'ri-login-circle-line', 'tint' => 'arrival'],
        'attraction' => ['label' => 'Attraction', 'sub' => 'Tickets', 'icon' => 'ri-coupon-3-line', 'tint' => 'attraction'],
        'guide' => ['label' => 'Guide', 'sub' => 'Service', 'icon' => 'ri-user-star-line', 'tint' => 'guide'],
        'restaurant' => ['label' => 'Restaurant', 'sub' => 'Meals', 'icon' => 'ri-restaurant-2-line', 'tint' => 'restaurant'],
        'transport' => ['label' => 'Transport', 'sub' => 'Transfers', 'icon' => 'ri-car-line', 'tint' => 'transport'],
        'departure' => ['label' => 'Departure', 'sub' => 'Transfer', 'icon' => 'ri-logout-circle-line', 'tint' => 'departure'],
        'miscellaneous' => ['label' => 'Misc', 'sub' => 'Extras', 'icon' => 'ri-file-list-3-line', 'tint' => 'miscellaneous'],
    ];

    $guestCount = (int) ($tourDetails['total_guests'] ?? 0);
    $totalServiceItems = 0;
    foreach ($citySections as $section) {
        foreach (($section['services'] ?? []) as $svc) {
            $totalServiceItems += (int) ($svc['count'] ?? 0);
        }
    }
@endphp

<div class="content-wrapper stp-ty-page">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9">
                <div class="stp-ty-shell">
                    <div class="stp-ty-banner">
                        <div class="stp-ty-banner-top">
                            <a href="{{ route('dashboard') }}" class="stp-ty-back">
                                <i class="ri-arrow-left-line" aria-hidden="true"></i>
                                <span>Dashboard</span>
                            </a>
                            <span class="stp-ty-mode-pill">
                                <i class="{{ $isUpdated ? 'ri-refresh-line' : 'ri-checkbox-circle-line' }}" aria-hidden="true"></i>
                                {{ $isUpdated ? 'Updated' : 'Created' }}
                            </span>
                        </div>

                        <div class="stp-ty-hero">
                            <div class="stp-ty-hero-icon {{ $isUpdated ? 'is-updated' : 'is-created' }}" aria-hidden="true">
                                <i class="{{ $isUpdated ? 'ri-refresh-line' : 'ri-check-line' }}"></i>
                            </div>
                            <h1>{{ $isUpdated ? 'Tour Updated Successfully' : 'Thank You!' }}</h1>
                            <p>
                                {{ $isUpdated
                                    ? 'Your tour enquiry has been updated and all selected services were saved.'
                                    : 'Your enquiry has been created successfully and is ready to manage.' }}
                            </p>
                        </div>
                    </div>

                    <div class="stp-ty-body">
                        @if(!empty($tourDetails))
                            <div class="stp-ty-block">
                                <div class="stp-ty-block-title">
                                    <i class="ri-map-pin-line" aria-hidden="true"></i>
                                    <span>Tour Package Details</span>
                                </div>
                                <div class="stp-ty-meta">
                                    <div class="stp-ty-meta-item">
                                        <span class="stp-ty-meta-label">Tour ID</span>
                                        <span class="stp-ty-badge">{{ $tourDetails['display_id'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="stp-ty-meta-item">
                                        <span class="stp-ty-meta-label">Destination</span>
                                        <div class="stp-ty-meta-value">{{ $tourDetails['destination'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="stp-ty-meta-item">
                                        <span class="stp-ty-meta-label">Check-in</span>
                                        <div class="stp-ty-meta-value">{{ $tourDetails['check_in_date'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="stp-ty-meta-item">
                                        <span class="stp-ty-meta-label">Check-out</span>
                                        <div class="stp-ty-meta-value">{{ $tourDetails['check_out_date'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="stp-ty-meta-item">
                                        <span class="stp-ty-meta-label">Guests</span>
                                        <span class="stp-ty-badge is-soft">
                                            {{ $guestCount }} {{ $guestCount === 1 ? 'Guest' : 'Guests' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($citySections))
                            <div class="stp-ty-block">
                                <div class="stp-ty-block-title">
                                    <i class="ri-building-4-line" aria-hidden="true"></i>
                                    <span>
                                        {{ $isUpdated ? 'Updated Services by City' : 'Services by City' }}
                                        @if($totalServiceItems > 0)
                                            <span style="margin-left:0.35rem;font-weight:700;color:#94a3b8;font-size:0.8rem;">
                                                · {{ $totalServiceItems }} item{{ $totalServiceItems === 1 ? '' : 's' }}
                                            </span>
                                        @endif
                                    </span>
                                </div>

                                @foreach($citySections as $idx => $section)
                                    @php $services = $section['services'] ?? []; @endphp
                                    <div class="stp-ty-city" data-tint="{{ $idx % 6 }}">
                                        <div class="stp-ty-city-head">
                                            <div class="stp-ty-city-name">
                                                <span class="stp-ty-city-mark"><i class="ri-map-pin-2-line" aria-hidden="true"></i></span>
                                                <div>
                                                    <strong>{{ $section['city'] ?? 'City' }}</strong>
                                                    @if(!empty($section['country']))
                                                        <small>{{ $section['country'] }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="stp-ty-city-dates">
                                                <i class="ri-calendar-line" aria-hidden="true"></i>
                                                <span>{{ $section['date_label'] ?? 'Dates TBD' }}</span>
                                            </div>
                                        </div>
                                        <div class="stp-ty-svc-row">
                                            @forelse($services as $svc)
                                                @php
                                                    $meta = $serviceMeta[$svc['key'] ?? ''] ?? [
                                                        'label' => ucfirst($svc['key'] ?? 'Service'),
                                                        'sub' => 'Booked',
                                                        'icon' => 'ri-service-line',
                                                        'tint' => 'hotel',
                                                    ];
                                                    $count = (int) ($svc['count'] ?? 0);
                                                @endphp
                                                <div class="stp-ty-svc stp-ty-svc--{{ $meta['tint'] }}">
                                                    <span class="stp-ty-svc-icon"><i class="{{ $meta['icon'] }}"></i></span>
                                                    <span class="stp-ty-svc-copy">
                                                        <span class="stp-ty-svc-label">{{ $meta['label'] }}</span>
                                                        <span class="stp-ty-svc-sub">{{ $meta['sub'] }}</span>
                                                    </span>
                                                    <span class="stp-ty-svc-count">{{ $count }}</span>
                                                </div>
                                            @empty
                                                <div class="text-muted small">No services in this city stay.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="stp-ty-actions-wrap">
                            <div class="stp-ty-actions-label">Quick actions</div>
                            <div class="stp-ty-actions">
                                <a
                                    href="{{ route('single-tour-package.create') }}"
                                    class="stp-ty-action is-primary"
                                    aria-label="Create Another Enquiry"
                                >
                                    <i class="ri-add-line"></i>
                                    <span>New Enquiry</span>
                                </a>

                                @if($encryptedTourId)
                                    <a
                                        href="{{ route('tour.itinerary.preview', ['encryptedTourId' => $encryptedTourId]) }}"
                                        class="stp-ty-action"
                                        target="_blank"
                                        aria-label="Packaged Quotation"
                                    >
                                        <i class="ri-file-list-3-line"></i>
                                        <span>Packaged Quotation</span>
                                    </a>
                                    <a
                                        href="{{ route('tour.detailed-quotation.preview', ['encryptedTourId' => $encryptedTourId]) }}"
                                        class="stp-ty-action"
                                        target="_blank"
                                        aria-label="Acco + Service Quotation"
                                    >
                                        <i class="ri-file-text-line"></i>
                                        <span>Acco + Service Quotation</span>
                                    </a>
                                    <a
                                        href="{{ route('tour.email.preview', ['encryptedTourId' => $encryptedTourId]) }}"
                                        class="stp-ty-action is-info"
                                        target="_blank"
                                        aria-label="Preview Email Template"
                                    >
                                        <i class="ri-mail-line"></i>
                                        <span>Email Preview</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <p class="stp-ty-note">
                            {{ $isUpdated
                                ? 'Your tour package was updated and all service orders have been saved. Manage it anytime from the dashboard.'
                                : 'Your tour package has been created and all service orders have been saved. Manage packages anytime from the dashboard.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
