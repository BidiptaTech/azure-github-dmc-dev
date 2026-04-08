@extends('layouts.layout')
@section('title', 'Package booking — ' . ($booking->booking_id ?? ''))

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
@php
    $travelDates = is_array($booking->travel_dates) ? $booking->travel_dates : (json_decode($booking->travel_dates, true) ?: []);
    $pkgLabel = data_get($booking->package, 'title') ?: data_get($booking->package, 'package_id');
    $bd = is_array($booking->booking_details) ? $booking->booking_details : (json_decode($booking->booking_details, true) ?: []);
    $packageItinerary = $packageItinerary ?? [
        'allDates' => [],
        'hotelsByDate' => [],
        'attractionsByDate' => [],
        'guidesByDate' => [],
        'restaurantsByDate' => [],
        'transfersByDate' => [],
        'arrivalByDate' => [],
        'departureByDate' => [],
        'defaultDate' => null,
    ];
    $allDates = $packageItinerary['allDates'] ?? [];
    $hotelsByDate = $packageItinerary['hotelsByDate'] ?? [];
    $attractionsByDate = $packageItinerary['attractionsByDate'] ?? [];
    $guidesByDate = $packageItinerary['guidesByDate'] ?? [];
    $restaurantsByDate = $packageItinerary['restaurantsByDate'] ?? [];
    $transfersByDate = $packageItinerary['transfersByDate'] ?? [];
    $arrivalByDate = $packageItinerary['arrivalByDate'] ?? [];
    $departureByDate = $packageItinerary['departureByDate'] ?? [];
@endphp

<style>
    :root {
        --pkg-itin-sticky-top: 63px;
        --pkg-itin-header-height: 90px;
        --pkg-itin-sidebar-gap: 18px;
        --pkg-itin-sidebar-top: calc(var(--pkg-itin-sticky-top) + var(--pkg-itin-header-height) + var(--pkg-itin-sidebar-gap));
        --pkg-primary: #2563eb;
        --pkg-border: #e2e8f0;
        --pkg-text: #0f172a;
        --pkg-muted: #64748b;
    }
    .pkg-pb-itinerary {
        position: relative;
        padding: 0;
        background: #f8fafc;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        /* Let content define height; avoid forced blank space */
        line-height: 1.4;
    }
    .pkg-pb-itinerary .itinerary-header {
        background: #fff;
        padding: 12px 16px;
        margin: 0 0 12px 0;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        border: 1px solid var(--pkg-border);
        position: sticky;
        top: var(--pkg-itin-sticky-top);
        z-index: 300;
    }
    .pkg-pb-itinerary .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
    }
    .pkg-pb-itinerary .header-info h4 { font-size: 16px; font-weight: 700; color: var(--pkg-text); margin: 0 0 4px 0; }
    .pkg-pb-itinerary .header-info h5 { font-size: 12px; font-weight: 500; color: var(--pkg-muted); margin: 0; }
    .pkg-pb-itinerary .itinerary-daywise-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 12px;
        align-items: start;
        /* Do not force grid to fill viewport */
    }
    .pkg-pb-itinerary .itinerary-daywise-sidebar {
        background: #fff;
        border: 1px solid var(--pkg-border);
        border-radius: 8px;
        padding: 8px;
        position: sticky;
        top: var(--pkg-itin-sidebar-top);
        z-index: 290;
    }
    .pkg-pb-itinerary .itinerary-daywise-sidebar-title {
        font-size: 15px;
        font-weight: 700;
        color: #3f3f46;
        margin-bottom: 6px;
    }
    .pkg-pb-itinerary .itinerary-day-btn {
        width: 100%;
        border: none;
        background: transparent;
        text-align: left;
        padding: 6px 8px;
        border-radius: 999px;
        font-size: 13px;
        color: #52525b;
        margin-bottom: 1px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pkg-pb-itinerary .itinerary-day-btn:hover { background: #f4f4f5; }
    .pkg-pb-itinerary .itinerary-day-btn.active {
        background: #3f3f46;
        color: #fff;
        font-weight: 700;
    }
    .pkg-pb-itinerary .itinerary-daywise-content {
        /* Only constrain when content exceeds viewport */
        max-height: calc(100vh - var(--pkg-itin-sidebar-top) - 6px);
        overflow-y: auto;
        padding-right: 6px;
    }

    /* Reduce perceived extra bottom space from Bootstrap container padding */
    .content-wrapper > .container-xxl.container-p-y {
        padding-bottom: 0 !important;
    }
    .pkg-pb-itinerary .date-container {
        position: relative;
        margin-bottom: 12px;
        overflow: visible;
        width: 100%;
    }
    .pkg-pb-itinerary .date-container.pkg-drop-target--over {
        outline: 2px dashed #6366f1;
        outline-offset: 2px;
        border-radius: 8px;
        background: #eef2ff;
    }
    .pkg-pb-itinerary .day-indicator {
        display: flex;
        align-items: center;
        margin-bottom: 2px;
        background: #fff;
        padding: 2px 3px;
        border-bottom: 1px solid var(--pkg-border);
        border-left: 3px solid var(--pkg-primary);
    }
    .pkg-pb-itinerary .day-circle {
        min-width: 40px;
        height: 20px;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 12px;
        margin-right: 8px;
    }
    .pkg-pb-itinerary .day-circle.day-1 { background: #FF6B6B; }
    .pkg-pb-itinerary .day-circle.day-2 { background: #4ECDC4; }
    .pkg-pb-itinerary .day-circle.day-3 { background: #45B7D1; }
    .pkg-pb-itinerary .day-circle.day-4 { background: #96CEB4; }
    .pkg-pb-itinerary .day-circle.day-5 { background: #FECA57; }
    .pkg-pb-itinerary .day-circle.day-6 { background: #FF9FF3; }
    .pkg-pb-itinerary .day-circle.day-7 { background: #54A0FF; }
    .pkg-pb-itinerary .day-title { font-size: 14px; font-weight: 600; color: var(--pkg-text); margin: 0; }
    .pkg-pb-itinerary .services-list {
        margin-left: 52px;
        margin-top: 4px;
        padding-left: 12px;
        position: relative;
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .pkg-pb-itinerary .service-item {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-left: 3px solid #9c27b0;
        border-radius: 12px;
        padding: 0;
        position: relative;
        transition: box-shadow 0.2s;
    }
    .pkg-pb-itinerary .service-item.hotel { border-left-color: #9c27b0; }
    .pkg-pb-itinerary .service-item.attraction { border-left-color: #ea580c; }
    .pkg-pb-itinerary .service-item.guide { border-left-color: #7c3aed; }
    .pkg-pb-itinerary .service-item.restaurant { border-left-color: #16a34a; }
    .pkg-pb-itinerary .service-item.transfer { border-left-color: #0891b2; }
    .pkg-pb-itinerary .service-item.arrival { border-left-color: #2563eb; }
    .pkg-pb-itinerary .service-item.departure { border-left-color: #be123c; }
    .pkg-pb-itinerary .service-item.draggable { cursor: grab; }
    .pkg-pb-itinerary .service-item.draggable:active { cursor: grabbing; }
    .pkg-pb-itinerary .service-item.pkg-dragging { opacity: 0.55; }
    .pkg-pb-itinerary .service-item-content {
        display: flex;
        align-items: flex-start;
        padding: 9px 11px;
        gap: 14px;
    }
    .pkg-pb-itinerary .service-media-panel { width: 164px; flex-shrink: 0; }
    .pkg-pb-itinerary .service-media-thumb {
        width: 100%;
        height: 84px;
        border-radius: 14px;
        border: 1px solid #d1d5db;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pkg-pb-itinerary .service-media-icon { font-size: 32px; color: #6b7280; }
    .pkg-pb-itinerary .service-main-content { flex: 1; min-width: 0; }
    .pkg-pb-itinerary .service-type-heading {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        color: #374151;
        margin-bottom: 4px;
    }
    .pkg-pb-itinerary .service-title { font-size: 15px; font-weight: 700; color: var(--pkg-text); margin: 0 0 6px 0; }
    .pkg-pb-itinerary .hotel-details-compact .service-detail-line {
        margin: 2px 0;
        font-size: 12px;
        color: #334155;
    }
    .pkg-pb-itinerary .service-detail-label { font-weight: 600; color: var(--pkg-text); margin-right: 4px; }
    .pkg-pb-itinerary .no-service {
        padding: 24px;
        color: var(--pkg-muted);
        text-align: center;
        background: #f1f5f9;
        border-radius: 8px;
        border: 2px dashed var(--pkg-border);
    }
    .pkg-pb-itinerary .drag-indicator {
        position: absolute;
        left: 8px;
        top: 10px;
        font-size: 14px;
        color: #94a3b8;
        cursor: grab;
    }
    @media (max-width: 992px) {
        .pkg-pb-itinerary .itinerary-daywise-layout { grid-template-columns: 1fr; }
        .pkg-pb-itinerary .itinerary-daywise-sidebar { position: static; }
        .pkg-pb-itinerary .itinerary-daywise-content { max-height: none; height: auto; }
        .pkg-pb-itinerary .services-list { margin-left: 0; padding-left: 0; }
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="pkg-pb-itinerary itinerary-container">
            <div class="itinerary-header">
                <div class="header-content">
                    <div class="header-info">
                        <h4>
                            <i class="ri-calendar-schedule-line me-1 text-primary"></i>
                            Package itinerary — {{ $booking->booking_id }}
                        </h4>
                        <h5>
                            @if(!empty($travelDates['start_date']) && !empty($travelDates['end_date']))
                                <i class="fas fa-calendar-alt" style="color: var(--pkg-primary);"></i>
                                {{ \Carbon\Carbon::parse($travelDates['start_date'])->format('d M Y') }}
                                &mdash;
                                {{ \Carbon\Carbon::parse($travelDates['end_date'])->format('d M Y') }}
                                @if(!empty($travelDates['duration_days']))
                                    · {{ $travelDates['duration_days'] }} day(s)
                                @endif
                            @endif
                            @if($pkgLabel !== null && $pkgLabel !== '')
                                · {{ $pkgLabel }}
                            @endif
                        </h5>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <a href="{{ route('predefined.package.booking.list') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Back to list
                        </a>
                    </div>
                </div>
            </div>

            @if(empty($allDates))
                <div class="alert alert-warning">
                    No travel date range could be loaded for this booking. Check <code>travel_dates</code> or itinerary in <code>booking_details</code>.
                </div>
            @else
                <p class="text-muted small mb-2 px-1">
                    Drag a card to another day to update date of services.
                </p>

                @php $dayCount = 1; @endphp
                <div class="itinerary-daywise-layout">
                    <div class="itinerary-daywise-sidebar">
                        <div class="itinerary-daywise-sidebar-title">Day Plan</div>
                        @foreach(array_keys($allDates) as $sidebarDate)
                            <button type="button"
                                    class="itinerary-day-btn {{ $loop->first ? 'active' : '' }}"
                                    data-day-index="{{ $loop->iteration }}">
                                {{ \Carbon\Carbon::parse($sidebarDate)->format('d M, D') }}
                            </button>
                        @endforeach
                    </div>

                    <div class="itinerary-daywise-content" id="pkgPbDayContent">
                        @foreach(array_keys($allDates) as $date)
                            @php
                                $dc = $dayCount > 7 ? (($dayCount - 1) % 7) + 1 : $dayCount;
                                $dayHotels = $hotelsByDate[$date] ?? [];
                                $dayAttractions = $attractionsByDate[$date] ?? [];
                                $dayGuides = $guidesByDate[$date] ?? [];
                                $dayRestaurants = $restaurantsByDate[$date] ?? [];
                                $dayTransfers = $transfersByDate[$date] ?? [];
                                $dayArrivals = $arrivalByDate[$date] ?? [];
                                $dayDepartures = $departureByDate[$date] ?? [];
                                $hasDayServices = count($dayArrivals) + count($dayHotels) + count($dayAttractions) + count($dayGuides) + count($dayRestaurants) + count($dayTransfers) + count($dayDepartures) > 0;
                            @endphp
                            <div class="date-container pkg-drop-zone {{ $loop->first ? 'active' : '' }}"
                                 data-date="{{ $date }}"
                                 data-day-index="{{ $dayCount }}">
                                <div class="day-indicator">
                                    <div class="day-circle day-{{ $dc }}">Day {{ $dayCount }}</div>
                                    <div>
                                        <h3 class="day-title">{{ \Carbon\Carbon::parse($date)->format('l') }}, {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</h3>
                                    </div>
                                </div>

                                <div class="services-list">
                                    @if(!$hasDayServices)
                                        <div class="no-service">No services for this day</div>
                                    @else
                                        @foreach($dayArrivals as $entry)
                                            @php
                                                $a = $entry['data'] ?? [];
                                                $veh = $a['vehicles'][0] ?? null;
                                            @endphp
                                            <div class="service-item arrival draggable pkg-pb-draggable-card"
                                                 draggable="true"
                                                 data-section="arrival"
                                                 data-index=""
                                                 data-current-date="{{ $date }}">
                                                <div class="drag-indicator">⋮⋮</div>
                                                <div class="service-item-content">
                                                    <div class="service-media-panel">
                                                        <div class="service-media-thumb">
                                                            <span class="service-media-icon"><i class="fas fa-plane-arrival"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-type-heading">Arrival</div>
                                                        <h4 class="service-title">{{ $a['pickup_port_name'] ?? 'Arrival transfer' }}</h4>
                                                        <div class="hotel-details-compact">
                                                            <p class="service-detail-line compact-line">
                                                                <span class="service-detail-label">To:</span> {{ $a['dropoff_hotel_name'] ?? 'Hotel' }}
                                                            </p>
                                                            @if($veh)
                                                                <p class="service-detail-line compact-line">
                                                                    <i class="fas fa-car me-1"></i>{{ $veh['vehicle_name'] ?? '' }} @if(!empty($veh['vehicle_type']))· {{ $veh['vehicle_type'] }}@endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @foreach($dayHotels as $entry)
                                            @php
                                                $h = $entry['data'];
                                                $hIndex = $entry['index'];
                                                $hotelName = $h['hotel_name'] ?? $h['name'] ?? 'Hotel';
                                                $nights = (int) ($h['nights'] ?? 0);
                                                $city = $h['city'] ?? '';
                                                $rooms = $h['rooms'] ?? [];
                                                $roomCount = is_array($rooms) ? count($rooms) : 0;
                                                $totalQty = 0;
                                                if (is_array($rooms)) {
                                                    foreach ($rooms as $rm) {
                                                        $totalQty += (int) ($rm['quantity'] ?? 1);
                                                    }
                                                }
                                                if ($totalQty === 0 && $roomCount > 0) {
                                                    $totalQty = $roomCount;
                                                }
                                                $optional = !empty($h['optional']);
                                                $compulsory = !empty($h['compulsory']);
                                            @endphp
                                            <div class="service-item hotel draggable pkg-pb-draggable-card"
                                                 draggable="true"
                                                 data-section="hotels"
                                                 data-index="{{ (int) $hIndex }}"
                                                 data-current-date="{{ $date }}">
                                                <div class="drag-indicator">⋮⋮</div>
                                                <div class="service-item-content">
                                                    <div class="service-media-panel">
                                                        <div class="service-media-thumb">
                                                            <span class="service-media-icon"><i class="fas fa-hotel"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-type-heading">Hotel</div>
                                                        @if($optional || $compulsory)
                                                            <div class="mb-1">
                                                                @if($compulsory)
                                                                    <span class="badge bg-secondary">Compulsory</span>
                                                                @endif
                                                                @if($optional)
                                                                    <span class="badge bg-info text-dark">Optional</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">{{ $hotelName }}</h4>
                                                        <div class="hotel-details-compact">
                                                            @if($nights > 0 || $city !== '' || $roomCount > 0)
                                                                <p class="service-detail-line compact-line">
                                                                    @if($nights > 0)
                                                                        <i class="fas fa-moon me-1"></i>{{ $nights }} {{ $nights > 1 ? 'nights' : 'night' }}
                                                                    @endif
                                                                    @if($city !== '')
                                                                        <span class="mx-1">•</span><i class="fas fa-map-marker-alt me-1"></i>{{ $city }}
                                                                    @endif
                                                                    @if($roomCount > 0)
                                                                        <span class="mx-1">•</span><i class="fas fa-bed me-1"></i>{{ $totalQty > 0 ? $totalQty : $roomCount }} {{ ($totalQty > 1 || $roomCount > 1) ? 'rooms' : 'room' }}
                                                                    @endif
                                                                </p>
                                                            @endif
                                                            @if(is_array($rooms) && count($rooms) > 0)
                                                                @foreach(array_slice($rooms, 0, 4) as $ri => $room)
                                                                    @php
                                                                        $rtName = $room['room_type_name'] ?? $room['room_type'] ?? 'Standard';
                                                                        $rqty = (int) ($room['quantity'] ?? 1);
                                                                    @endphp
                                                                    <p class="service-detail-line compact-line">
                                                                        <strong>Room {{ $ri + 1 }}:</strong> {{ $rtName }}
                                                                        @if($rqty > 1)
                                                                            × {{ $rqty }}
                                                                        @endif
                                                                    </p>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @foreach($dayAttractions as $entry)
                                            @php
                                                $a = $entry['data'];
                                                $aIndex = $entry['index'];
                                                $aName = $a['name'] ?? 'Attraction';
                                                $aLoc = $a['location'] ?? '';
                                                $aImg = $a['image'] ?? null;
                                                $optional = !empty($a['optional']);
                                                $compulsory = !empty($a['compulsory']);
                                                $guide = is_array($a['guide'] ?? null) ? $a['guide'] : null;
                                                $hasTransfer = !empty($a['transfer']);
                                                $tt = $a['transfer_type'] ?? '';
                                                $veh = $a['vehicle_name'] ?? '';
                                            @endphp
                                            <div class="service-item attraction draggable pkg-pb-draggable-card"
                                                 draggable="true"
                                                 data-section="attractions"
                                                 data-index="{{ (int) $aIndex }}"
                                                 data-current-date="{{ $date }}">
                                                <div class="drag-indicator">⋮⋮</div>
                                                <div class="service-item-content">
                                                    <div class="service-media-panel">
                                                        <div class="service-media-thumb">
                                                            @if(!empty($aImg))
                                                                <img src="{{ $aImg }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" draggable="false">
                                                            @else
                                                                <span class="service-media-icon"><i class="fas fa-camera"></i></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-type-heading">Attraction</div>
                                                        @if($optional || $compulsory)
                                                            <div class="mb-1">
                                                                @if($compulsory)
                                                                    <span class="badge bg-secondary">Compulsory</span>
                                                                @endif
                                                                @if($optional)
                                                                    <span class="badge bg-info text-dark">Optional</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">{{ $aName }}</h4>
                                                        <div class="hotel-details-compact">
                                                            @if($aLoc !== '')
                                                                <p class="service-detail-line compact-line">
                                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $aLoc }}
                                                                </p>
                                                            @endif
                                                            @if($guide)
                                                                <p class="service-detail-line compact-line">
                                                                    <i class="fas fa-user-tie me-1"></i><strong>{{ $guide['name'] ?? 'Guide' }}</strong>
                                                                    @if(!empty($guide['languages']) && is_array($guide['languages']))
                                                                        <span class="text-muted"> · {{ implode(', ', $guide['languages']) }}</span>
                                                                    @endif
                                                                    @if(!empty($guide['contact_no']))
                                                                        <span class="text-muted"> · {{ $guide['contact_no'] }}</span>
                                                                    @endif
                                                                </p>
                                                            @endif
                                                            @if($hasTransfer)
                                                                <p class="service-detail-line compact-line">
                                                                    <i class="fas fa-car me-1"></i>{{ ucfirst((string) $tt) }} transfer
                                                                    @if($veh !== '')
                                                                        · {{ $veh }}
                                                                    @endif
                                                                </p>
                                                                <p class="service-detail-line compact-line">
                                                                    <span class="service-detail-label">From:</span> {{ $a['pickup_name'] ?? '—' }}
                                                                    <span class="mx-1">→</span>
                                                                    <span class="service-detail-label">To:</span> {{ $a['dropoff_name'] ?? $aName }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @foreach($dayGuides as $entry)
                                            @php
                                                $g = $entry['data'];
                                                $gIndex = $entry['index'];
                                                $gName = $g['name'] ?? 'Guide';
                                                $optional = !empty($g['optional']);
                                                $compulsory = !empty($g['compulsory']);
                                                $langs = $g['languages'] ?? [];
                                                if (!is_array($langs)) { $langs = []; }
                                                $contact = $g['contact_no'] ?? null;
                                                $durLabel = $g['duration_label'] ?? null;
                                            @endphp
                                            <div class="service-item guide draggable pkg-pb-draggable-card"
                                                 draggable="true"
                                                 data-section="guides"
                                                 data-index="{{ (int) $gIndex }}"
                                                 data-current-date="{{ $date }}">
                                                <div class="drag-indicator">⋮⋮</div>
                                                <div class="service-item-content">
                                                    <div class="service-media-panel">
                                                        <div class="service-media-thumb">
                                                            <span class="service-media-icon"><i class="fas fa-user-tie"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-type-heading">Guide</div>
                                                        @if($optional || $compulsory)
                                                            <div class="mb-1">
                                                                @if($compulsory)
                                                                    <span class="badge bg-secondary">Compulsory</span>
                                                                @endif
                                                                @if($optional)
                                                                    <span class="badge bg-info text-dark">Optional</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">{{ $gName }}</h4>
                                                        <div class="hotel-details-compact">
                                                            @if($durLabel)
                                                                <p class="service-detail-line compact-line">
                                                                    <i class="fas fa-clock me-1"></i>{{ $durLabel }}
                                                                </p>
                                                            @endif
                                                            @if(count($langs) > 0 || $contact)
                                                                <p class="service-detail-line compact-line">
                                                                    @if(count($langs) > 0)
                                                                        <i class="fas fa-language me-1"></i>{{ implode(', ', $langs) }}
                                                                    @endif
                                                                    @if($contact)
                                                                        <span class="mx-1">•</span><i class="fas fa-phone me-1"></i>{{ $contact }}
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @foreach($dayRestaurants as $entry)
                                            @php
                                                $r = $entry['data'];
                                                $rIndex = $entry['index'];
                                                $rName = $r['restaurant_name'] ?? $r['name'] ?? 'Restaurant';
                                                $optional = !empty($r['optional']);
                                                $compulsory = !empty($r['compulsory']);
                                                $meals = $r['selected_meals'] ?? [];
                                                if (!is_array($meals)) {
                                                    $meals = [];
                                                }
                                                $hasTransfer = !empty($r['transfer']);
                                                $tt = $r['transfer_type'] ?? '';
                                                $veh = $r['vehicle_name'] ?? '';
                                            @endphp
                                            <div class="service-item restaurant draggable pkg-pb-draggable-card"
                                                 draggable="true"
                                                 data-section="restaurants"
                                                 data-index="{{ (int) $rIndex }}"
                                                 data-current-date="{{ $date }}">
                                                <div class="drag-indicator">⋮⋮</div>
                                                <div class="service-item-content">
                                                    <div class="service-media-panel">
                                                        <div class="service-media-thumb">
                                                            <span class="service-media-icon"><i class="fas fa-utensils"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-type-heading">Restaurant</div>
                                                        @if($optional || $compulsory)
                                                            <div class="mb-1">
                                                                @if($compulsory)
                                                                    <span class="badge bg-secondary">Compulsory</span>
                                                                @endif
                                                                @if($optional)
                                                                    <span class="badge bg-info text-dark">Optional</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">{{ $rName }}</h4>
                                                        <div class="hotel-details-compact">
                                                            @if(count($meals) > 0)
                                                                <p class="service-detail-line compact-line">
                                                                    @foreach($meals as $meal)
                                                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ ucfirst((string) $meal) }}</span>
                                                                    @endforeach
                                                                </p>
                                                            @endif
                                                            @if($hasTransfer)
                                                                <p class="service-detail-line compact-line">
                                                                    <i class="fas fa-car me-1"></i>{{ ucfirst((string) $tt) }} transfer
                                                                    @if($veh !== '')
                                                                        · {{ $veh }}
                                                                    @endif
                                                                </p>
                                                                <p class="service-detail-line compact-line">
                                                                    <span class="service-detail-label">From:</span> {{ $r['pickup_name'] ?? '—' }}
                                                                    <span class="mx-1">→</span>
                                                                    <span class="service-detail-label">To:</span> {{ $r['dropoff_name'] ?? $rName }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @foreach($dayTransfers as $entry)
                                            @php
                                                $t = $entry['data'];
                                                $tIndex = $entry['index'];
                                                $optional = !empty($t['optional']);
                                                $compulsory = !empty($t['compulsory']);
                                                $veh = $t['vehicles'][0] ?? null;
                                            @endphp
                                            <div class="service-item transfer draggable pkg-pb-draggable-card"
                                                 draggable="true"
                                                 data-section="transfers"
                                                 data-index="{{ (int) $tIndex }}"
                                                 data-current-date="{{ $date }}">
                                                <div class="drag-indicator">⋮⋮</div>
                                                <div class="service-item-content">
                                                    <div class="service-media-panel">
                                                        <div class="service-media-thumb">
                                                            <span class="service-media-icon"><i class="fas fa-car-side"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-type-heading">Transfer</div>
                                                        @if($optional || $compulsory)
                                                            <div class="mb-1">
                                                                @if($compulsory)
                                                                    <span class="badge bg-secondary">Compulsory</span>
                                                                @endif
                                                                @if($optional)
                                                                    <span class="badge bg-info text-dark">Optional</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">
                                                            {{ $t['pickup_display_name'] ?? ($t['pickup_label'] ?? 'Pickup') }}
                                                            <span class="text-muted">→</span>
                                                            {{ $t['dropoff_display_name'] ?? ($t['dropoff_label'] ?? 'Dropoff') }}
                                                        </h4>
                                                        <div class="hotel-details-compact">
                                                            <p class="service-detail-line compact-line">
                                                                <span class="service-detail-label">From:</span> {{ $t['pickup_display_name'] ?? ($t['pickup_label'] ?? '—') }}
                                                                <span class="mx-1">•</span>
                                                                <span class="service-detail-label">To:</span> {{ $t['dropoff_display_name'] ?? ($t['dropoff_label'] ?? '—') }}
                                                            </p>
                                                            @if($veh)
                                                                <p class="service-detail-line compact-line">
                                                                    <i class="fas fa-car me-1"></i>{{ $veh['vehicle_name'] ?? '' }} @if(!empty($veh['vehicle_type']))· {{ $veh['vehicle_type'] }}@endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @foreach($dayDepartures as $entry)
                                            @php
                                                $d = $entry['data'] ?? [];
                                                $veh = $d['vehicles'][0] ?? null;
                                            @endphp
                                            <div class="service-item departure draggable pkg-pb-draggable-card"
                                                 draggable="true"
                                                 data-section="departure"
                                                 data-index=""
                                                 data-current-date="{{ $date }}">
                                                <div class="drag-indicator">⋮⋮</div>
                                                <div class="service-item-content">
                                                    <div class="service-media-panel">
                                                        <div class="service-media-thumb">
                                                            <span class="service-media-icon"><i class="fas fa-plane-departure"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-type-heading">Departure</div>
                                                        <h4 class="service-title">{{ $d['dropoff_port_name'] ?? 'Departure transfer' }}</h4>
                                                        <div class="hotel-details-compact">
                                                            <p class="service-detail-line compact-line">
                                                                <span class="service-detail-label">From:</span> {{ $d['pickup_hotel_name'] ?? 'Hotel' }}
                                                            </p>
                                                            @if($veh)
                                                                <p class="service-detail-line compact-line">
                                                                    <i class="fas fa-car me-1"></i>{{ $veh['vehicle_name'] ?? '' }} @if(!empty($veh['vehicle_type']))· {{ $veh['vehicle_type'] }}@endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @php $dayCount++; @endphp
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($bd['notes']))
                <div class="card mt-3">
                    <div class="card-header py-2"><h6 class="mb-0">Notes</h6></div>
                    <div class="card-body py-2 small">{{ $bd['notes'] }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

@if(!empty($allDates))
<script>
(function () {
    const updateUrl = @json(route('package.booking.update-service-date', $booking->booking_id));
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const root = document.querySelector('.pkg-pb-itinerary');
    const dayNavButtons = document.querySelectorAll('.pkg-pb-itinerary .itinerary-day-btn[data-day-index]');
    const dayContainers = document.querySelectorAll('.pkg-pb-itinerary .date-container[data-day-index]');
    const scrollHost = document.getElementById('pkgPbDayContent');

    function syncStickyOffsets() {
        const hdr = document.querySelector('.pkg-pb-itinerary .itinerary-header');
        if (hdr) {
            const h = Math.ceil(hdr.getBoundingClientRect().height || 0);
            if (h > 0) {
                document.documentElement.style.setProperty('--pkg-itin-header-height', h + 'px');
            }
        }
    }
    syncStickyOffsets();
    window.addEventListener('resize', syncStickyOffsets);

    let activateDay = null;
    if (dayNavButtons.length && dayContainers.length) {
        activateDay = function (dayIndex) {
            dayNavButtons.forEach(function (btn) {
                btn.classList.toggle('active', btn.dataset.dayIndex === String(dayIndex));
            });
            dayContainers.forEach(function (c) {
                c.classList.toggle('active', c.dataset.dayIndex === String(dayIndex));
            });
        };
        dayNavButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx = this.dataset.dayIndex;
                var target = document.querySelector('.pkg-pb-itinerary .date-container[data-day-index="' + idx + '"]');
                if (target && scrollHost) {
                    scrollHost.scrollTo({ top: target.offsetTop - 6, behavior: 'smooth' });
                }
                dayNavButtons.forEach(function (b) {
                    b.classList.toggle('active', b.dataset.dayIndex === idx);
                });
            });
        });
        activateDay(dayNavButtons[0].dataset.dayIndex);
    }

    let draggedEl = null;

    document.querySelectorAll('.pkg-pb-draggable-card').forEach(function (card) {
        card.addEventListener('dragstart', function (e) {
            draggedEl = card;
            card.classList.add('pkg-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.section || '');
        });
        card.addEventListener('dragend', function () {
            card.classList.remove('pkg-dragging');
            draggedEl = null;
            document.querySelectorAll('.pkg-pb-itinerary .date-container.pkg-drop-zone').forEach(function (c) {
                c.classList.remove('pkg-drop-target--over');
            });
        });
    });

    document.querySelectorAll('.pkg-pb-itinerary .date-container.pkg-drop-zone').forEach(function (col) {
        col.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            col.classList.add('pkg-drop-target--over');
        });
        col.addEventListener('dragleave', function (e) {
            if (!col.contains(e.relatedTarget)) {
                col.classList.remove('pkg-drop-target--over');
            }
        });
        col.addEventListener('drop', function (e) {
            e.preventDefault();
            col.classList.remove('pkg-drop-target--over');
            if (!draggedEl) return;

            var targetDate = col.getAttribute('data-date');
            var currentDate = draggedEl.getAttribute('data-current-date');
            if (targetDate === currentDate) {
                return;
            }
            var section = draggedEl.getAttribute('data-section');
            var idxRaw = draggedEl.getAttribute('data-index');
            var payload = { section: section, tour_start_date: targetDate, _token: token };
            if (idxRaw !== '' && idxRaw !== null) {
                payload.index = parseInt(idxRaw, 10);
            }

            fetch(updateUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (res.ok && res.data && res.data.success) {
                    window.location.reload();
                } else {
                    var msg = (res.data && res.data.message) ? res.data.message : 'Could not update date.';
                    alert(msg);
                }
            })
            .catch(function () {
                alert('Network error while saving.');
            });
        });
    });

    if (dayNavButtons.length && scrollHost && dayContainers.length) {
        scrollHost.addEventListener('scroll', function () {
            var hostTop = scrollHost.getBoundingClientRect().top;
            var activeIdx = dayContainers[0] ? dayContainers[0].dataset.dayIndex : null;
            dayContainers.forEach(function (container) {
                var topDiff = container.getBoundingClientRect().top - hostTop;
                if (topDiff <= 20) {
                    activeIdx = container.dataset.dayIndex;
                }
            });
            if (activeIdx) {
                dayNavButtons.forEach(function (btn) {
                    btn.classList.toggle('active', btn.dataset.dayIndex === activeIdx);
                });
            }
        });
    }
})();
</script>
@endif
@endsection
