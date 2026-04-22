@extends('layouts.layout')
@section('title', 'Package booking — ' . ($booking->booking_id ?? ''))

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
@php
    // ---------------------------------------------------------------------
    // Data extraction (identical to previous version — only presentation changes)
    // ---------------------------------------------------------------------
    $travelDates = is_array($booking->travel_dates)
        ? $booking->travel_dates
        : (json_decode($booking->travel_dates, true) ?: []);

    $pkgLabel       = data_get($booking->package, 'title') ?: data_get($booking->package, 'package_id');
    $pkgDestination = data_get($booking->package, 'destination');
    $pkgCity        = data_get($booking->package, 'city');

    $bd = is_array($booking->booking_details)
        ? $booking->booking_details
        : (json_decode($booking->booking_details, true) ?: []);

    $packageItinerary = $packageItinerary ?? [
        'allDates'          => [],
        'hotelsByDate'      => [],
        'attractionsByDate' => [],
        'guidesByDate'      => [],
        'restaurantsByDate' => [],
        'transfersByDate'   => [],
        'arrivalByDate'     => [],
        'departureByDate'   => [],
        'defaultDate'       => null,
    ];
    $allDates           = $packageItinerary['allDates']           ?? [];
    $hotelsByDate       = $packageItinerary['hotelsByDate']       ?? [];
    $attractionsByDate  = $packageItinerary['attractionsByDate']  ?? [];
    $guidesByDate       = $packageItinerary['guidesByDate']       ?? [];
    $restaurantsByDate  = $packageItinerary['restaurantsByDate']  ?? [];
    $transfersByDate    = $packageItinerary['transfersByDate']    ?? [];
    $arrivalByDate      = $packageItinerary['arrivalByDate']      ?? [];
    $departureByDate    = $packageItinerary['departureByDate']    ?? [];

    // Summary chips
    $paxCount     = $bd['pax_count']   ?? $bd['total_pax'] ?? null;
    $totalPrice   = $bd['total_price'] ?? null;
    $currency     = $bd['currency']    ?? 'SGD';
    $startDate    = $travelDates['start_date']    ?? null;
    $endDate      = $travelDates['end_date']      ?? null;
    $durationDays = $travelDates['duration_days'] ?? (count($allDates) > 0 ? count($allDates) : null);

    $totalServices = 0;
    foreach ($allDates as $d => $_) {
        $totalServices += count($hotelsByDate[$d] ?? [])
            + count($attractionsByDate[$d] ?? [])
            + count($guidesByDate[$d] ?? [])
            + count($restaurantsByDate[$d] ?? [])
            + count($transfersByDate[$d] ?? [])
            + count($arrivalByDate[$d] ?? [])
            + count($departureByDate[$d] ?? []);
    }
@endphp

<style>
    /* ========================================================================
       Package Booking Itinerary – visual language mirrors bookingList/itinerary
       ========================================================================
       - Inter font, soft-slate background, sticky gradient-accented header
       - Day-wise 2-col layout with a sticky sidebar and scrollable day content
       - Timeline rail between cards with colored per-day spine
       - Service cards with left-border accent colors per type
       - Three view modes: Day-wise (default), List and Grid
    ======================================================================== */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --pkg-primary: #2563eb;
        --pkg-primary-light: #3b82f6;
        --pkg-primary-dark: #1d4ed8;
        --pkg-success: #10b981;
        --pkg-warning: #f59e0b;
        --pkg-error: #ef4444;
        --pkg-text: #0f172a;
        --pkg-text-soft: #475569;
        --pkg-muted: #64748b;
        --pkg-border: #e2e8f0;
        --pkg-bg: #ffffff;
        --pkg-bg-soft: #f8fafc;
        --pkg-bg-tertiary: #f1f5f9;
        --pkg-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --pkg-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);

        --pkg-itin-sticky-top: 63px;
        --pkg-itin-header-height: 90px;
        --pkg-itin-sidebar-gap: 18px;
        --pkg-itin-services-panel-offset: 90px;
        --pkg-itin-sidebar-top: calc(var(--pkg-itin-sticky-top) + var(--pkg-itin-header-height) + var(--pkg-itin-sidebar-gap));

        /* Day colors – identical to itinerary.blade */
        --pkg-day-1: #FF6B6B;
        --pkg-day-2: #4ECDC4;
        --pkg-day-3: #45B7D1;
        --pkg-day-4: #96CEB4;
        --pkg-day-5: #FECA57;
        --pkg-day-6: #FF9FF3;
        --pkg-day-7: #54A0FF;
    }

    .pkg-pb-itinerary {
        position: relative;
        padding: 0;
        background: var(--pkg-bg-soft);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        min-height: 100vh;
        line-height: 1.45;
        color: var(--pkg-text);
    }

    .pkg-pb-itinerary .mt-1 { margin-top: 0.15rem !important; }
    .pkg-pb-itinerary .mt-2 { margin-top: 0.25rem !important; }
    .pkg-pb-itinerary .mb-1 { margin-bottom: 0.15rem !important; }
    .pkg-pb-itinerary .mb-2 { margin-bottom: 0.25rem !important; }

    /* Reduce body padding Bootstrap adds to allow sticky layout */
    .content-wrapper > .container-xxl.container-p-y { padding-bottom: 0 !important; }

    /* ----------------------- Sticky gradient header ----------------------- */
    .pkg-pb-itinerary .itinerary-header {
        background: var(--pkg-bg);
        padding: 14px 18px;
        margin: 0 0 12px 0;
        border-radius: 8px;
        box-shadow: var(--pkg-shadow-sm);
        border: 1px solid var(--pkg-border);
        position: sticky;
        top: var(--pkg-itin-sticky-top);
        z-index: 300;
        overflow: visible;
    }
    .pkg-pb-itinerary .itinerary-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pkg-primary), var(--pkg-primary-light), var(--pkg-success));
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        z-index: 1;
    }
    .pkg-pb-itinerary .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
        position: relative;
        z-index: 2;
    }
    .pkg-pb-itinerary .header-info h4 {
        font-size: 17px;
        font-weight: 700;
        color: var(--pkg-text);
        margin: 2px 0 4px 0;
        letter-spacing: -0.02em;
    }
    .pkg-pb-itinerary .header-info h5 {
        font-size: 12.5px;
        font-weight: 500;
        color: var(--pkg-text-soft);
        margin: 0;
        display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }
    .pkg-pb-itinerary .header-info h5 .sep {
        display: inline-block; width: 4px; height: 4px; border-radius: 50%;
        background: #cbd5e1; margin: 0 4px;
    }
    .pkg-pb-itinerary .header-actions {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
        flex-wrap: wrap;
        align-items: center;
    }

    .pkg-pb-itinerary .btn-modern {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        letter-spacing: 0.02em;
        line-height: 1;
    }
    .pkg-pb-itinerary .btn-primary-modern {
        background: var(--pkg-primary);
        color: #fff;
        box-shadow: var(--pkg-shadow-sm);
    }
    .pkg-pb-itinerary .btn-primary-modern:hover {
        background: var(--pkg-primary-dark);
        color: #fff; text-decoration: none;
        transform: translateY(-1px);
        box-shadow: var(--pkg-shadow-md);
    }
    .pkg-pb-itinerary .btn-secondary-modern {
        background: var(--pkg-bg);
        color: var(--pkg-text-soft);
        border: 1px solid var(--pkg-border);
    }
    .pkg-pb-itinerary .btn-secondary-modern:hover {
        background: var(--pkg-bg-tertiary);
        color: var(--pkg-text);
        text-decoration: none;
        transform: translateY(-1px);
    }

    /* -------------------------- Summary chip strip ------------------------ */
    .pkg-pb-itinerary .summary-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
        padding: 0 0 10px 0;
    }
    .pkg-pb-itinerary .summary-chip {
        background: #fff;
        border: 1px solid var(--pkg-border);
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        gap: 10px;
        align-items: center;
        box-shadow: var(--pkg-shadow-sm);
    }
    .pkg-pb-itinerary .summary-chip .chip-icon {
        width: 34px; height: 34px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 14px; flex-shrink: 0;
    }
    .pkg-pb-itinerary .summary-chip .chip-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: var(--pkg-muted);
        line-height: 1.2;
    }
    .pkg-pb-itinerary .summary-chip .chip-value {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--pkg-text);
        line-height: 1.25;
        word-break: break-word;
    }
    .pkg-pb-itinerary .summary-chip.chip-dates .chip-icon  { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .pkg-pb-itinerary .summary-chip.chip-days .chip-icon   { background: linear-gradient(135deg, #0ea5e9, #06b6d4); }
    .pkg-pb-itinerary .summary-chip.chip-pax .chip-icon    { background: linear-gradient(135deg, #10b981, #059669); }
    .pkg-pb-itinerary .summary-chip.chip-price .chip-icon  { background: linear-gradient(135deg, #f59e0b, #f97316); }
    .pkg-pb-itinerary .summary-chip.chip-pkg .chip-icon    { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .pkg-pb-itinerary .summary-chip.chip-svc .chip-icon    { background: linear-gradient(135deg, #be123c, #e11d48); }

    /* -------------------------- View switcher ----------------------------- */
    .pkg-pb-itinerary .itinerary-view-switcher {
        display: flex;
        gap: 8px;
        padding: 0 0 12px 0;
        justify-content: flex-end;
        align-items: center;
    }
    .pkg-pb-itinerary .view-switcher-hint {
        font-size: 12px;
        color: var(--pkg-muted);
        margin-right: auto;
        display: flex; align-items: center; gap: 6px;
    }
    .pkg-pb-itinerary .itinerary-view-btn {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 12px;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.15s ease;
    }
    .pkg-pb-itinerary .itinerary-view-btn:hover {
        background: #f1f5f9;
    }
    .pkg-pb-itinerary .itinerary-view-btn.active {
        background: #334155;
        color: #fff;
        border-color: #334155;
    }

    /* -------------------------- Day-wise layout --------------------------- */
    .pkg-pb-itinerary .itinerary-daywise-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 12px;
        align-items: start;
        min-height: calc(100vh - var(--pkg-itin-sidebar-top) - 8px);
    }
    .pkg-pb-itinerary .itinerary-daywise-sidebar {
        background: #fff;
        border: 1px solid var(--pkg-border);
        border-radius: 8px;
        padding: 10px;
        position: sticky;
        top: var(--pkg-itin-sidebar-top);
        z-index: 290;
        max-height: none;
        overflow: visible;
    }
    .pkg-pb-itinerary .itinerary-daywise-sidebar-title {
        font-size: 14px;
        font-weight: 700;
        color: #3f3f46;
        margin-bottom: 6px;
        letter-spacing: -0.01em;
    }
    .pkg-pb-itinerary .itinerary-day-btn {
        width: 100%;
        border: none;
        background: transparent;
        text-align: left;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12.5px;
        line-height: 1.2;
        color: #52525b;
        margin-bottom: 2px;
        transition: all 0.18s ease;
        display: flex; align-items: center; gap: 8px;
        cursor: pointer;
    }
    .pkg-pb-itinerary .itinerary-day-btn::before {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #a1a1aa;
        flex-shrink: 0;
    }
    .pkg-pb-itinerary .itinerary-day-btn:hover {
        background: #f4f4f5;
        color: #27272a;
    }
    .pkg-pb-itinerary .itinerary-day-btn.active {
        background: #3f3f46;
        color: #fff;
        font-weight: 700;
    }
    .pkg-pb-itinerary .itinerary-day-btn.active::before {
        background: #fff;
    }
    .pkg-pb-itinerary .itinerary-day-btn.drag-over-day {
        outline: 2px dashed var(--pkg-primary);
        outline-offset: 2px;
        background: #eff6ff;
        color: var(--pkg-primary-dark);
    }
    .pkg-pb-itinerary .itinerary-day-btn .day-btn-sub {
        font-size: 10.5px;
        opacity: 0.75;
        margin-left: auto;
    }

    /* Scrollable day content */
    .pkg-pb-itinerary .itinerary-daywise-content {
        height: calc(100vh - var(--pkg-itin-sidebar-top) - var(--pkg-itin-services-panel-offset));
        max-height: calc(100vh - var(--pkg-itin-sidebar-top) - var(--pkg-itin-services-panel-offset));
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior: contain;
        padding-right: 6px;
    }
    .pkg-pb-itinerary .itinerary-daywise-content::-webkit-scrollbar { width: 8px; }
    .pkg-pb-itinerary .itinerary-daywise-content::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 999px; }
    .pkg-pb-itinerary .itinerary-daywise-content::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 999px; }
    .pkg-pb-itinerary .itinerary-daywise-content::-webkit-scrollbar-thumb:hover { background: #64748b; }

    /* Default day-wise mode: only active day is visible */
    .pkg-pb-itinerary .itinerary-daywise-content .date-container {
        display: none;
        margin-bottom: 0;
    }
    .pkg-pb-itinerary .itinerary-daywise-content .date-container.active {
        display: block;
    }

    /* List view mode */
    .pkg-pb-itinerary.view-list .itinerary-daywise-content .date-container {
        display: block !important;
        margin-bottom: 14px;
    }
    .pkg-pb-itinerary.view-list .itinerary-daywise-content .list-day-heading { display: block; }
    .pkg-pb-itinerary.view-list .itinerary-daywise-content .date-container .timeline-line { display: none; }
    .pkg-pb-itinerary.view-list .itinerary-daywise-content .date-container .day-indicator { display: none; }
    .pkg-pb-itinerary.view-list .itinerary-daywise-content .date-container .services-list {
        margin-left: 0; padding-left: 0; margin-top: 0;
    }
    .pkg-pb-itinerary.view-list .itinerary-daywise-content .services-list::before { display: none; }

    /* Grid view mode – 2 column card grid, no sidebar */
    .pkg-pb-itinerary.view-grid .itinerary-daywise-layout {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    .pkg-pb-itinerary.view-grid .itinerary-daywise-sidebar { display: none; }
    .pkg-pb-itinerary.view-grid .itinerary-daywise-content {
        height: auto; max-height: none; overflow: visible; padding-right: 0;
    }
    .pkg-pb-itinerary.view-grid .itinerary-daywise-content .date-container {
        display: block !important;
        margin-bottom: 0;
    }
    .pkg-pb-itinerary.view-grid .itinerary-daywise-content .timeline-line,
    .pkg-pb-itinerary.view-grid .itinerary-daywise-content .day-indicator,
    .pkg-pb-itinerary.view-grid .itinerary-daywise-content .services-list::before {
        display: none !important;
    }
    .pkg-pb-itinerary.view-grid .grid-day-heading { display: block; }
    .pkg-pb-itinerary.view-grid .itinerary-daywise-content .services-list {
        margin-left: 0;
        padding-left: 0;
        margin-bottom: 14px;
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 10px 12px !important;
    }
    .pkg-pb-itinerary.view-grid .itinerary-daywise-content .services-list > .service-item {
        grid-column: span 1 !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }
    .pkg-pb-itinerary.view-grid .service-media-panel { width: 140px; }
    .pkg-pb-itinerary.view-grid .service-media-thumb { height: 80px; }

    .pkg-pb-itinerary .list-day-heading,
    .pkg-pb-itinerary .grid-day-heading {
        display: none;
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
        margin: 2px 0 8px 0;
        padding: 7px 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 8px;
    }

    /* ---------------------------- Day containers ------------------------- */
    .pkg-pb-itinerary .date-container {
        position: relative;
        margin-bottom: 12px;
        overflow: visible;
        width: 100%;
    }
    .pkg-pb-itinerary .date-container.pkg-drop-target--over {
        outline: 2px dashed var(--pkg-primary);
        outline-offset: 2px;
        border-radius: 10px;
        background: rgba(37, 99, 235, 0.04);
    }

    .pkg-pb-itinerary .timeline-line {
        position: absolute;
        left: 43px;
        top: 50px;
        bottom: -8px;
        width: 2px;
        z-index: 0;
        border-radius: 2px;
        background: var(--pkg-border);
    }
    .pkg-pb-itinerary .date-container.day-1 .timeline-line { background: var(--pkg-day-1); }
    .pkg-pb-itinerary .date-container.day-2 .timeline-line { background: var(--pkg-day-2); }
    .pkg-pb-itinerary .date-container.day-3 .timeline-line { background: var(--pkg-day-3); }
    .pkg-pb-itinerary .date-container.day-4 .timeline-line { background: var(--pkg-day-4); }
    .pkg-pb-itinerary .date-container.day-5 .timeline-line { background: var(--pkg-day-5); }
    .pkg-pb-itinerary .date-container.day-6 .timeline-line { background: var(--pkg-day-6); }
    .pkg-pb-itinerary .date-container.day-7 .timeline-line { background: var(--pkg-day-7); }

    .pkg-pb-itinerary .day-indicator {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
        background: #fff;
        padding: 4px 6px;
        border-radius: 4px;
        border-bottom: 1px solid var(--pkg-border);
        border-left: 3px solid var(--pkg-primary);
        height: 28px;
    }
    .pkg-pb-itinerary .day-circle {
        min-width: 48px;
        height: 22px;
        border-radius: 3px;
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 11.5px;
        margin-right: 10px;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }
    .pkg-pb-itinerary .day-circle.day-1 { background: var(--pkg-day-1); }
    .pkg-pb-itinerary .day-circle.day-2 { background: var(--pkg-day-2); }
    .pkg-pb-itinerary .day-circle.day-3 { background: var(--pkg-day-3); }
    .pkg-pb-itinerary .day-circle.day-4 { background: var(--pkg-day-4); }
    .pkg-pb-itinerary .day-circle.day-5 { background: var(--pkg-day-5); }
    .pkg-pb-itinerary .day-circle.day-6 { background: var(--pkg-day-6); }
    .pkg-pb-itinerary .day-circle.day-7 { background: var(--pkg-day-7); }
    .pkg-pb-itinerary .day-info {
        flex-grow: 1;
        display: flex; align-items: center; justify-content: space-between;
    }
    .pkg-pb-itinerary .day-title {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--pkg-text);
        margin: 0;
        letter-spacing: -0.01em;
    }
    .pkg-pb-itinerary .day-count-chip {
        font-size: 11px;
        color: var(--pkg-muted);
        font-weight: 600;
        background: #f1f5f9;
        padding: 2px 10px;
        border-radius: 999px;
    }

    /* ---------------------------- Services list --------------------------- */
    .pkg-pb-itinerary .services-list {
        margin-left: 66px;
        margin-top: 6px;
        padding-left: 36px;
        padding-right: 4px;
        position: relative;
        display: grid !important;
        grid-template-columns: 1fr !important;
        grid-auto-flow: row !important;
        gap: 10px !important;
        align-items: start !important;
    }
    .pkg-pb-itinerary .services-list::before {
        content: '';
        position: absolute;
        left: -36px; top: 0; bottom: 0;
        width: 2px;
        background: #e2e8f0;
        z-index: 1;
    }
    .pkg-pb-itinerary .services-list > .service-item { grid-column: 1 !important; }

    /* ----------------------------- Service cards -------------------------- */
    .pkg-pb-itinerary .service-item {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-left: 3px solid #6b7280;
        border-radius: 12px;
        padding: 0;
        position: relative;
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04);
        transition: all 0.18s ease;
        overflow: visible;
        min-width: 0 !important;
        width: 100% !important;
    }
    .pkg-pb-itinerary .service-item:hover {
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.10);
        border-color: #94a3b8;
        transform: translateY(-1px);
    }

    /* Type accent colors */
    .pkg-pb-itinerary .service-item.hotel      { border-left-color: #9333ea; }
    .pkg-pb-itinerary .service-item.attraction { border-left-color: #ea580c; }
    .pkg-pb-itinerary .service-item.guide      { border-left-color: #7c3aed; }
    .pkg-pb-itinerary .service-item.restaurant { border-left-color: #16a34a; }
    .pkg-pb-itinerary .service-item.transfer   { border-left-color: #0891b2; }
    .pkg-pb-itinerary .service-item.arrival    { border-left-color: #2563eb; }
    .pkg-pb-itinerary .service-item.departure  { border-left-color: #be123c; }

    /* Timeline markers – colored circle w/ icon per type */
    .pkg-pb-itinerary .service-item::before {
        content: '';
        position: absolute;
        left: -44px;
        top: 14px;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: #111827;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e2e8f0;
        z-index: 20;
    }
    .pkg-pb-itinerary .service-item.hotel::before      { background: #9333ea; }
    .pkg-pb-itinerary .service-item.attraction::before { background: #ea580c; }
    .pkg-pb-itinerary .service-item.guide::before      { background: #7c3aed; }
    .pkg-pb-itinerary .service-item.restaurant::before { background: #16a34a; }
    .pkg-pb-itinerary .service-item.transfer::before   { background: #0891b2; }
    .pkg-pb-itinerary .service-item.arrival::before    { background: #2563eb; }
    .pkg-pb-itinerary .service-item.departure::before  { background: #be123c; }

    /* Drag states */
    .pkg-pb-itinerary .service-item.draggable { cursor: grab; }
    .pkg-pb-itinerary .service-item.draggable:active { cursor: grabbing; }
    .pkg-pb-itinerary .service-item.pkg-dragging {
        opacity: 0.55;
        transform: rotate(1deg) translateY(-1px);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.18);
    }

    .pkg-pb-itinerary .drag-indicator {
        position: absolute;
        left: 4px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: #cbd5e1;
        letter-spacing: -1px;
        cursor: grab;
        user-select: none;
        pointer-events: none;
    }

    .pkg-pb-itinerary .service-item-content {
        display: flex;
        align-items: flex-start;
        padding: 10px 12px 10px 22px;
        gap: 14px;
        position: relative;
        min-width: 0 !important;
    }

    /* Media panel */
    .pkg-pb-itinerary .service-media-panel {
        width: 160px;
        flex-shrink: 0;
    }
    .pkg-pb-itinerary .service-media-thumb {
        width: 100%;
        height: 88px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
    }
    .pkg-pb-itinerary .service-media-thumb img {
        width: 100%; height: 100%; object-fit: cover;
        pointer-events: none; -webkit-user-drag: none; user-select: none;
    }
    .pkg-pb-itinerary .service-media-icon { font-size: 30px; color: #94a3b8; }

    /* Main content */
    .pkg-pb-itinerary .service-main-content {
        flex-grow: 1;
        min-width: 0 !important;
        overflow-wrap: break-word;
        word-wrap: break-word;
    }

    .pkg-pb-itinerary .service-topline {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
        color: #475569;
    }
    .pkg-pb-itinerary .service-topline-icon {
        color: #475569;
        font-size: 12px;
        width: 14px;
        text-align: center;
        flex-shrink: 0;
    }
    .pkg-pb-itinerary .service-type-heading {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #374151;
        letter-spacing: 0.4px;
        line-height: 1.2;
    }
    .pkg-pb-itinerary .service-topline-dot {
        color: #cbd5e1;
        font-size: 10px;
    }
    .pkg-pb-itinerary .service-topline-subtitle {
        font-size: 11.5px;
        color: #334155;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 55%;
    }

    .pkg-pb-itinerary .service-badge-row {
        display: flex; gap: 4px; flex-wrap: wrap;
        margin-bottom: 4px;
    }
    .pkg-pb-itinerary .service-badge {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: 10.5px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 999px;
        letter-spacing: 0.2px;
        line-height: 1.4;
    }
    .pkg-pb-itinerary .badge-compulsory { background: #e2e8f0; color: #334155; }
    .pkg-pb-itinerary .badge-optional   { background: #e0f2fe; color: #075985; }
    .pkg-pb-itinerary .badge-addon      { background: #fef3c7; color: #92400e; }
    .pkg-pb-itinerary .badge-meal       { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }

    .pkg-pb-itinerary .service-title {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--pkg-text);
        margin: 0 0 4px 0;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }

    .pkg-pb-itinerary .service-detail-lines {
        margin-top: 2px;
    }
    .pkg-pb-itinerary .service-detail-line {
        margin: 2px 0;
        font-size: 12px;
        line-height: 1.4;
        color: #334155;
        display: flex; gap: 5px; align-items: baseline; flex-wrap: wrap;
    }
    .pkg-pb-itinerary .service-detail-line i { color: #64748b; width: 14px; text-align: center; font-size: 11px; }
    .pkg-pb-itinerary .service-detail-label {
        font-weight: 600;
        color: var(--pkg-text);
    }
    .pkg-pb-itinerary .service-detail-arrow {
        color: #94a3b8;
        padding: 0 4px;
    }

    /* ----------------------- Empty state / helper text -------------------- */
    .pkg-pb-itinerary .no-service {
        padding: 24px;
        color: var(--pkg-muted);
        text-align: center;
        background: #f1f5f9;
        border-radius: 10px;
        border: 2px dashed var(--pkg-border);
        font-size: 13px;
        grid-column: 1 / -1 !important;
    }
    .pkg-pb-itinerary .no-service i {
        font-size: 20px;
        opacity: 0.55;
        display: block;
        margin-bottom: 6px;
    }

    .pkg-pb-itinerary .helper-note {
        font-size: 12px;
        color: var(--pkg-muted);
        margin: 0 0 10px 2px;
        display: flex; align-items: center; gap: 6px;
    }
    .pkg-pb-itinerary .helper-note i { color: var(--pkg-primary); }

    /* ----------------------------- Responsive ----------------------------- */
    @media (max-width: 992px) {
        .pkg-pb-itinerary .itinerary-daywise-layout { grid-template-columns: 1fr; }
        .pkg-pb-itinerary .itinerary-daywise-sidebar { position: static; max-height: none; overflow: visible; }
        .pkg-pb-itinerary .itinerary-header { position: static; }
        .pkg-pb-itinerary .itinerary-daywise-content { max-height: none; height: auto; overflow: visible; padding-right: 0; }
        .pkg-pb-itinerary .itinerary-daywise-content .date-container.active { max-height: none; overflow: visible; padding-right: 0; }
        .pkg-pb-itinerary.view-grid .itinerary-daywise-content .services-list { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 768px) {
        .pkg-pb-itinerary .header-content { flex-direction: column; gap: 10px; }
        .pkg-pb-itinerary .services-list { margin-left: 0; padding-left: 0; }
        .pkg-pb-itinerary .services-list::before { display: none; }
        .pkg-pb-itinerary .service-item::before { display: none; }
        .pkg-pb-itinerary .timeline-line { display: none; }
        .pkg-pb-itinerary .service-media-panel { display: none; }
    }

    /* ------------------------------- Print -------------------------------- */
    @media print {
        .pkg-pb-itinerary { background: #fff; min-height: auto; max-width: none; }
        .pkg-pb-itinerary .itinerary-header { position: static; box-shadow: none; }
        .pkg-pb-itinerary .itinerary-daywise-sidebar,
        .pkg-pb-itinerary .itinerary-view-switcher,
        .pkg-pb-itinerary .helper-note,
        .pkg-pb-itinerary .header-actions { display: none !important; }
        .pkg-pb-itinerary .itinerary-daywise-layout { grid-template-columns: 1fr; }
        .pkg-pb-itinerary .itinerary-daywise-content { height: auto; max-height: none; overflow: visible; }
        .pkg-pb-itinerary .itinerary-daywise-content .date-container { display: block !important; page-break-inside: avoid; margin-bottom: 14px; }
        .pkg-pb-itinerary .service-item { box-shadow: none !important; page-break-inside: avoid; }
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="pkg-pb-itinerary itinerary-container">

            {{-- ------------------------ HEADER ------------------------ --}}
            <div class="itinerary-header">
                <div class="header-content">
                    <div class="header-info">
                        <h4>
                            <i class="ri-suitcase-2-line me-1 text-primary"></i>
                            Package Booking &mdash; {{ $booking->booking_id }}
                        </h4>
                        <h5>
                            @if($pkgLabel !== null && $pkgLabel !== '')
                                <i class="fas fa-box-open" style="color: var(--pkg-primary);"></i>
                                {{ $pkgLabel }}
                            @endif
                            @if(!empty($pkgDestination) || !empty($pkgCity))
                                <span class="sep"></span>
                                <i class="fas fa-map-marker-alt" style="color: var(--pkg-primary);"></i>
                                {{ trim(($pkgCity ? $pkgCity . ', ' : '') . ($pkgDestination ?? ''), ', ') }}
                            @endif
                            @if($startDate && $endDate)
                                <span class="sep"></span>
                                <i class="fas fa-calendar-alt" style="color: var(--pkg-primary);"></i>
                                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash;
                                {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                            @endif
                        </h5>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('predefined.package.booking.list') }}" class="btn-modern btn-secondary-modern">
                            <i class="ri-arrow-left-line"></i> Back to list
                        </a>
                        <button type="button" class="btn-modern btn-primary-modern" id="pkgPbPrintBtn">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            {{-- ------------------------ SUMMARY CHIPS ------------------------ --}}
            <div class="summary-strip">
                @if($startDate && $endDate)
                    <div class="summary-chip chip-dates">
                        <div class="chip-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div class="chip-label">Travel period</div>
                            <div class="chip-value">
                                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                                &rarr;
                                {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                @endif
                @if(!empty($durationDays))
                    <div class="summary-chip chip-days">
                        <div class="chip-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div>
                            <div class="chip-label">Duration</div>
                            <div class="chip-value">{{ $durationDays }} {{ $durationDays > 1 ? 'days' : 'day' }}</div>
                        </div>
                    </div>
                @endif
                @if(!empty($paxCount))
                    <div class="summary-chip chip-pax">
                        <div class="chip-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="chip-label">Pax</div>
                            <div class="chip-value">{{ $paxCount }}</div>
                        </div>
                    </div>
                @endif
                @if(!empty($totalPrice))
                    <div class="summary-chip chip-price">
                        <div class="chip-icon"><i class="fas fa-tag"></i></div>
                        <div>
                            <div class="chip-label">Total Price</div>
                            <div class="chip-value">{{ $currency }} {{ number_format((float) $totalPrice, 2) }}</div>
                        </div>
                    </div>
                @endif
                <div class="summary-chip chip-svc">
                    <div class="chip-icon"><i class="fas fa-list-check"></i></div>
                    <div>
                        <div class="chip-label">Services</div>
                        <div class="chip-value">{{ $totalServices }} scheduled</div>
                    </div>
                </div>
                @if(isset($booking->created_at))
                    <div class="summary-chip chip-pkg">
                        <div class="chip-icon"><i class="fas fa-bookmark"></i></div>
                        <div>
                            <div class="chip-label">Booked on</div>
                            <div class="chip-value">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ------------------------ VIEW SWITCHER ------------------------ --}}
            @if(!empty($allDates))
                <div class="itinerary-view-switcher">
                    <div class="view-switcher-hint">
                        <i class="fas fa-eye"></i> Switch layout:
                    </div>
                    <button type="button" class="itinerary-view-btn" data-view="daywise">
                        <i class="fas fa-columns"></i> Day View
                    </button>
                    <button type="button" class="itinerary-view-btn active" data-view="list">
                        <i class="fas fa-list"></i> List View
                    </button>
                    <button type="button" class="itinerary-view-btn" data-view="grid">
                        <i class="fas fa-th-large"></i> Grid View
                    </button>
                </div>

                <p class="helper-note">
                    <i class="fas fa-info-circle"></i>
                    Drag a card to another day (or onto a day button on the sidebar) to reschedule that service.
                </p>
            @endif

            @if(empty($allDates))
                <div class="alert alert-warning">
                    <i class="fas fa-triangle-exclamation me-1"></i>
                    No travel date range could be loaded for this booking.
                    Check <code>travel_dates</code> or the itinerary in <code>booking_details</code>.
                </div>
            @else
                @php $dayCount = 1; @endphp
                <div class="itinerary-daywise-layout">

                    {{-- --------------------- Sidebar --------------------- --}}
                    <div class="itinerary-daywise-sidebar">
                        <div class="itinerary-daywise-sidebar-title">Day Plan</div>
                        @foreach(array_keys($allDates) as $sidebarDate)
                            @php
                                $sdDate = \Carbon\Carbon::parse($sidebarDate);
                                $sdCount = $loop->iteration;
                                $sdHotels       = $hotelsByDate[$sidebarDate] ?? [];
                                $sdAttractions  = $attractionsByDate[$sidebarDate] ?? [];
                                $sdGuides       = $guidesByDate[$sidebarDate] ?? [];
                                $sdRestaurants  = $restaurantsByDate[$sidebarDate] ?? [];
                                $sdTransfers    = $transfersByDate[$sidebarDate] ?? [];
                                $sdArrivals     = $arrivalByDate[$sidebarDate] ?? [];
                                $sdDepartures   = $departureByDate[$sidebarDate] ?? [];
                                $sdTotal = count($sdArrivals) + count($sdHotels) + count($sdAttractions)
                                    + count($sdGuides) + count($sdRestaurants) + count($sdTransfers) + count($sdDepartures);
                            @endphp
                            <button type="button"
                                    class="itinerary-day-btn {{ $loop->first ? 'active' : '' }}"
                                    data-day-index="{{ $sdCount }}"
                                    data-date="{{ $sidebarDate }}">
                                <span>{{ $sdDate->format('d M, D') }}</span>
                                <span class="day-btn-sub">{{ $sdTotal }}</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- --------------------- Day content --------------------- --}}
                    <div class="itinerary-daywise-content" id="pkgPbDayContent">
                        @foreach(array_keys($allDates) as $date)
                            @php
                                $dc = $dayCount > 7 ? (($dayCount - 1) % 7) + 1 : $dayCount;
                                $dayHotels       = $hotelsByDate[$date] ?? [];
                                $dayAttractions  = $attractionsByDate[$date] ?? [];
                                $dayGuides       = $guidesByDate[$date] ?? [];
                                $dayRestaurants  = $restaurantsByDate[$date] ?? [];
                                $dayTransfers    = $transfersByDate[$date] ?? [];
                                $dayArrivals     = $arrivalByDate[$date] ?? [];
                                $dayDepartures   = $departureByDate[$date] ?? [];
                                $dayServiceCount = count($dayArrivals) + count($dayHotels) + count($dayAttractions)
                                    + count($dayGuides) + count($dayRestaurants) + count($dayTransfers) + count($dayDepartures);
                                $hasDayServices  = $dayServiceCount > 0;
                                $dObj = \Carbon\Carbon::parse($date);
                            @endphp
                            <div class="date-container pkg-drop-zone day-{{ $dc }} {{ $loop->first ? 'active' : '' }}"
                                 data-date="{{ $date }}"
                                 data-day-index="{{ $dayCount }}">
                                <div class="timeline-line"></div>

                                <div class="day-indicator">
                                    <div class="day-circle day-{{ $dc }}">Day {{ $dayCount }}</div>
                                    <div class="day-info">
                                        <h3 class="day-title">{{ $dObj->format('l') }}, {{ $dObj->format('F j, Y') }}</h3>
                                        <span class="day-count-chip">
                                            {{ $dayServiceCount }} {{ $dayServiceCount == 1 ? 'service' : 'services' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Headings only shown in list/grid views --}}
                                <div class="grid-day-heading">
                                    Day {{ $dayCount }} <span class="text-muted">({{ $dObj->format('d M Y') }})</span>
                                </div>
                                <div class="list-day-heading">
                                    Day {{ $dayCount }} &mdash; {{ $dObj->format('d M Y') }}
                                </div>

                                <div class="services-list">
                                    @if(!$hasDayServices)
                                        <div class="no-service">
                                            <i class="fas fa-calendar-xmark"></i>
                                            No services scheduled for this day.
                                        </div>
                                    @else

                                        {{-- ============== ARRIVAL ============== --}}
                                        @foreach($dayArrivals as $entry)
                                            @php
                                                $a   = $entry['data'] ?? [];
                                                $veh = $a['vehicles'][0] ?? null;
                                                $pickupPort = $a['pickup_port_name']  ?? $a['pickup_port_id']  ?? 'Arrival Port';
                                                $dropoffHtl = $a['dropoff_hotel_name'] ?? $a['dropoff_hotel_id'] ?? 'Hotel';
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
                                                        <div class="service-topline">
                                                            <span class="service-topline-icon"><i class="fas fa-plane-arrival"></i></span>
                                                            <div class="service-type-heading">Arrival</div>
                                                            <span class="service-topline-dot">•</span>
                                                            <div class="service-topline-subtitle">{{ $pickupPort }}</div>
                                                        </div>
                                                        <h4 class="service-title">Arrival transfer &mdash; {{ $pickupPort }}</h4>
                                                        <div class="service-detail-lines">
                                                            <p class="service-detail-line">
                                                                <i class="fas fa-route"></i>
                                                                <span class="service-detail-label">Route:</span>
                                                                {{ $pickupPort }}
                                                                <span class="service-detail-arrow">&rarr;</span>
                                                                {{ $dropoffHtl }}
                                                            </p>
                                                            @if($veh)
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-car-side"></i>
                                                                    <span class="service-detail-label">Vehicle:</span>
                                                                    {{ $veh['vehicle_name'] ?? 'Vehicle' }}
                                                                    @if(!empty($veh['vehicle_type'])) &middot; {{ $veh['vehicle_type'] }}@endif
                                                                    @if(!empty($veh['selected_transfer_type']))
                                                                        <span class="service-badge badge-optional">{{ ucfirst($veh['selected_transfer_type']) }}</span>
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- ============== HOTELS ============== --}}
                                        @foreach($dayHotels as $entry)
                                            @php
                                                $h = $entry['data'];
                                                $hIndex = $entry['index'];
                                                $hotelName = $h['hotel_name'] ?? $h['name'] ?? 'Hotel';
                                                $nights  = (int) ($h['nights'] ?? 0);
                                                $city    = $h['city'] ?? '';
                                                $image   = $h['image'] ?? $h['hotel_image'] ?? null;
                                                $rooms   = is_array($h['rooms'] ?? null) ? $h['rooms'] : [];
                                                $roomCnt = count($rooms);
                                                $totalQty = 0;
                                                foreach ($rooms as $rm) { $totalQty += (int) ($rm['quantity'] ?? 1); }
                                                if ($totalQty === 0 && $roomCnt > 0) { $totalQty = $roomCnt; }
                                                $optional   = !empty($h['optional']);
                                                $compulsory = !empty($h['compulsory']);
                                                $addon      = !empty($h['addon']);
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
                                                            @if(!empty($image))
                                                                <img src="{{ $image }}" alt="{{ $hotelName }}" draggable="false">
                                                            @else
                                                                <span class="service-media-icon"><i class="fas fa-hotel"></i></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-topline">
                                                            <span class="service-topline-icon"><i class="fas fa-hotel"></i></span>
                                                            <div class="service-type-heading">Hotel</div>
                                                            @if($nights > 0)
                                                                <span class="service-topline-dot">•</span>
                                                                <div class="service-topline-subtitle">
                                                                    {{ $nights }} {{ $nights > 1 ? 'nights stay' : 'night stay' }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        @if($compulsory || $optional || $addon)
                                                            <div class="service-badge-row">
                                                                @if($compulsory)<span class="service-badge badge-compulsory">Compulsory</span>@endif
                                                                @if($optional)  <span class="service-badge badge-optional">Optional</span>@endif
                                                                @if($addon)     <span class="service-badge badge-addon">Add-on</span>@endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">{{ $hotelName }}</h4>
                                                        <div class="service-detail-lines">
                                                            @if($nights > 0 || $city !== '' || $roomCnt > 0)
                                                                <p class="service-detail-line">
                                                                    @if($nights > 0)
                                                                        <i class="fas fa-moon"></i>{{ $nights }} {{ $nights > 1 ? 'nights' : 'night' }}
                                                                    @endif
                                                                    @if($city !== '')
                                                                        <span class="service-topline-dot">•</span>
                                                                        <i class="fas fa-map-marker-alt"></i>{{ $city }}
                                                                    @endif
                                                                    @if($roomCnt > 0)
                                                                        <span class="service-topline-dot">•</span>
                                                                        <i class="fas fa-bed"></i>{{ $totalQty > 0 ? $totalQty : $roomCnt }} {{ ($totalQty > 1 || $roomCnt > 1) ? 'rooms' : 'room' }}
                                                                    @endif
                                                                </p>
                                                            @endif
                                                            @foreach(array_slice($rooms, 0, 4) as $ri => $room)
                                                                @php
                                                                    $rtName = $room['room_type_name'] ?? $room['room_type'] ?? 'Standard';
                                                                    $rqty = (int) ($room['quantity'] ?? 1);
                                                                @endphp
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-key"></i>
                                                                    <span class="service-detail-label">Room {{ $ri + 1 }}:</span>
                                                                    {{ $rtName }}@if($rqty > 1) &times; {{ $rqty }}@endif
                                                                </p>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- ============== ATTRACTIONS ============== --}}
                                        @foreach($dayAttractions as $entry)
                                            @php
                                                $a = $entry['data'];
                                                $aIndex = $entry['index'];
                                                $aName = $a['name'] ?? 'Attraction';
                                                $aLoc  = $a['location'] ?? '';
                                                $aImg  = $a['image'] ?? null;
                                                $optional   = !empty($a['optional']);
                                                $compulsory = !empty($a['compulsory']);
                                                $addon      = !empty($a['addon']);
                                                $guide = is_array($a['guide'] ?? null) ? $a['guide'] : null;
                                                $hasTransfer = !empty($a['transfer']);
                                                $tt  = $a['transfer_type'] ?? '';
                                                $veh = $a['vehicle_name'] ?? '';
                                                $duration = $a['duration'] ?? $a['hours'] ?? null;
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
                                                                <img src="{{ $aImg }}" alt="{{ $aName }}" draggable="false">
                                                            @else
                                                                <span class="service-media-icon"><i class="fas fa-camera"></i></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="service-main-content">
                                                        <div class="service-topline">
                                                            <span class="service-topline-icon"><i class="fas fa-map-marked-alt"></i></span>
                                                            <div class="service-type-heading">Attraction</div>
                                                            @if(!empty($duration))
                                                                <span class="service-topline-dot">•</span>
                                                                <div class="service-topline-subtitle">{{ $duration }} hrs</div>
                                                            @elseif($aLoc !== '')
                                                                <span class="service-topline-dot">•</span>
                                                                <div class="service-topline-subtitle">{{ $aLoc }}</div>
                                                            @endif
                                                        </div>
                                                        @if($compulsory || $optional || $addon)
                                                            <div class="service-badge-row">
                                                                @if($compulsory)<span class="service-badge badge-compulsory">Compulsory</span>@endif
                                                                @if($optional)  <span class="service-badge badge-optional">Optional</span>@endif
                                                                @if($addon)     <span class="service-badge badge-addon">Add-on</span>@endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">{{ $aName }}</h4>
                                                        <div class="service-detail-lines">
                                                            @if($aLoc !== '')
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-map-marker-alt"></i>{{ $aLoc }}
                                                                </p>
                                                            @endif
                                                            @if($guide)
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-user-tie"></i>
                                                                    <span class="service-detail-label">Guide:</span>
                                                                    {{ $guide['name'] ?? 'Guide' }}
                                                                    @if(!empty($guide['languages']) && is_array($guide['languages']))
                                                                        <span class="text-muted">&middot; {{ implode(', ', $guide['languages']) }}</span>
                                                                    @endif
                                                                    @if(!empty($guide['contact_no']))
                                                                        <span class="text-muted">&middot; {{ $guide['contact_no'] }}</span>
                                                                    @endif
                                                                </p>
                                                            @endif
                                                            @if($hasTransfer)
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-car-side"></i>
                                                                    <span class="service-detail-label">Transfer:</span>
                                                                    {{ ucfirst((string) $tt) ?: 'Included' }}
                                                                    @if($veh !== '') &middot; {{ $veh }} @endif
                                                                </p>
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-route"></i>
                                                                    {{ $a['pickup_name'] ?? '—' }}
                                                                    <span class="service-detail-arrow">&rarr;</span>
                                                                    {{ $a['dropoff_name'] ?? $aName }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- ============== GUIDES ============== --}}
                                        @foreach($dayGuides as $entry)
                                            @php
                                                $g = $entry['data'];
                                                $gIndex = $entry['index'];
                                                $gName = $g['name'] ?? 'Guide';
                                                $optional   = !empty($g['optional']);
                                                $compulsory = !empty($g['compulsory']);
                                                $addon      = !empty($g['addon']);
                                                $langs = is_array($g['languages'] ?? null) ? $g['languages'] : [];
                                                $contact  = $g['contact_no'] ?? null;
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
                                                        <div class="service-topline">
                                                            <span class="service-topline-icon"><i class="fas fa-user-tie"></i></span>
                                                            <div class="service-type-heading">Guide</div>
                                                            @if($durLabel)
                                                                <span class="service-topline-dot">•</span>
                                                                <div class="service-topline-subtitle">{{ $durLabel }}</div>
                                                            @endif
                                                        </div>
                                                        @if($compulsory || $optional || $addon)
                                                            <div class="service-badge-row">
                                                                @if($compulsory)<span class="service-badge badge-compulsory">Compulsory</span>@endif
                                                                @if($optional)  <span class="service-badge badge-optional">Optional</span>@endif
                                                                @if($addon)     <span class="service-badge badge-addon">Add-on</span>@endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">{{ $gName }}</h4>
                                                        <div class="service-detail-lines">
                                                            @if($durLabel)
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-clock"></i>{{ $durLabel }}
                                                                </p>
                                                            @endif
                                                            @if(count($langs) > 0 || $contact)
                                                                <p class="service-detail-line">
                                                                    @if(count($langs) > 0)
                                                                        <i class="fas fa-language"></i>{{ implode(', ', $langs) }}
                                                                    @endif
                                                                    @if($contact)
                                                                        <span class="service-topline-dot">•</span>
                                                                        <i class="fas fa-phone"></i>{{ $contact }}
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- ============== RESTAURANTS ============== --}}
                                        @foreach($dayRestaurants as $entry)
                                            @php
                                                $r = $entry['data'];
                                                $rIndex = $entry['index'];
                                                $rName = $r['restaurant_name'] ?? $r['name'] ?? 'Restaurant';
                                                $optional   = !empty($r['optional']);
                                                $compulsory = !empty($r['compulsory']);
                                                $addon      = !empty($r['addon']);
                                                $meals = is_array($r['selected_meals'] ?? null) ? $r['selected_meals'] : [];
                                                $hasTransfer = !empty($r['transfer']);
                                                $tt  = $r['transfer_type'] ?? '';
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
                                                        <div class="service-topline">
                                                            <span class="service-topline-icon"><i class="fas fa-utensils"></i></span>
                                                            <div class="service-type-heading">Restaurant</div>
                                                            @if(count($meals) > 0)
                                                                <span class="service-topline-dot">•</span>
                                                                <div class="service-topline-subtitle">{{ ucfirst((string) $meals[0]) }}@if(count($meals) > 1) +{{ count($meals) - 1 }}@endif</div>
                                                            @endif
                                                        </div>
                                                        @if($compulsory || $optional || $addon)
                                                            <div class="service-badge-row">
                                                                @if($compulsory)<span class="service-badge badge-compulsory">Compulsory</span>@endif
                                                                @if($optional)  <span class="service-badge badge-optional">Optional</span>@endif
                                                                @if($addon)     <span class="service-badge badge-addon">Add-on</span>@endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">{{ $rName }}</h4>
                                                        <div class="service-detail-lines">
                                                            @if(count($meals) > 0)
                                                                <p class="service-detail-line">
                                                                    @foreach($meals as $meal)
                                                                        <span class="service-badge badge-meal">{{ ucfirst((string) $meal) }}</span>
                                                                    @endforeach
                                                                </p>
                                                            @endif
                                                            @if($hasTransfer)
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-car-side"></i>
                                                                    <span class="service-detail-label">Transfer:</span>
                                                                    {{ ucfirst((string) $tt) ?: 'Included' }}
                                                                    @if($veh !== '') &middot; {{ $veh }}@endif
                                                                </p>
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-route"></i>
                                                                    {{ $r['pickup_name'] ?? '—' }}
                                                                    <span class="service-detail-arrow">&rarr;</span>
                                                                    {{ $r['dropoff_name'] ?? $rName }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- ============== TRANSFERS ============== --}}
                                        @foreach($dayTransfers as $entry)
                                            @php
                                                $t = $entry['data'];
                                                $tIndex = $entry['index'];
                                                $optional   = !empty($t['optional']);
                                                $compulsory = !empty($t['compulsory']);
                                                $addon      = !empty($t['addon']);
                                                $veh = $t['vehicles'][0] ?? null;
                                                $fromName = $t['pickup_display_name']  ?? $t['pickup_label']  ?? 'Pickup';
                                                $toName   = $t['dropoff_display_name'] ?? $t['dropoff_label'] ?? 'Dropoff';
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
                                                        <div class="service-topline">
                                                            <span class="service-topline-icon"><i class="fas fa-car-side"></i></span>
                                                            <div class="service-type-heading">Transfer</div>
                                                            <span class="service-topline-dot">•</span>
                                                            <div class="service-topline-subtitle">{{ $fromName }} &rarr; {{ $toName }}</div>
                                                        </div>
                                                        @if($compulsory || $optional || $addon)
                                                            <div class="service-badge-row">
                                                                @if($compulsory)<span class="service-badge badge-compulsory">Compulsory</span>@endif
                                                                @if($optional)  <span class="service-badge badge-optional">Optional</span>@endif
                                                                @if($addon)     <span class="service-badge badge-addon">Add-on</span>@endif
                                                            </div>
                                                        @endif
                                                        <h4 class="service-title">
                                                            {{ $fromName }}
                                                            <span class="text-muted">&rarr;</span>
                                                            {{ $toName }}
                                                        </h4>
                                                        <div class="service-detail-lines">
                                                            <p class="service-detail-line">
                                                                <i class="fas fa-route"></i>
                                                                <span class="service-detail-label">From:</span> {{ $fromName }}
                                                                <span class="service-topline-dot">•</span>
                                                                <span class="service-detail-label">To:</span> {{ $toName }}
                                                            </p>
                                                            @if($veh)
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-car-side"></i>
                                                                    <span class="service-detail-label">Vehicle:</span>
                                                                    {{ $veh['vehicle_name'] ?? 'Vehicle' }}
                                                                    @if(!empty($veh['vehicle_type'])) &middot; {{ $veh['vehicle_type'] }}@endif
                                                                    @if(!empty($veh['selected_transfer_type']))
                                                                        <span class="service-badge badge-optional">{{ ucfirst($veh['selected_transfer_type']) }}</span>
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- ============== DEPARTURE ============== --}}
                                        @foreach($dayDepartures as $entry)
                                            @php
                                                $d = $entry['data'] ?? [];
                                                $veh = $d['vehicles'][0] ?? null;
                                                $pickupHtl  = $d['pickup_hotel_name'] ?? $d['pickup_hotel_id'] ?? 'Hotel';
                                                $dropoffPrt = $d['dropoff_port_name'] ?? $d['dropoff_port_id'] ?? 'Departure Port';
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
                                                        <div class="service-topline">
                                                            <span class="service-topline-icon"><i class="fas fa-plane-departure"></i></span>
                                                            <div class="service-type-heading">Departure</div>
                                                            <span class="service-topline-dot">•</span>
                                                            <div class="service-topline-subtitle">{{ $dropoffPrt }}</div>
                                                        </div>
                                                        <h4 class="service-title">Departure transfer &mdash; {{ $dropoffPrt }}</h4>
                                                        <div class="service-detail-lines">
                                                            <p class="service-detail-line">
                                                                <i class="fas fa-route"></i>
                                                                <span class="service-detail-label">Route:</span>
                                                                {{ $pickupHtl }}
                                                                <span class="service-detail-arrow">&rarr;</span>
                                                                {{ $dropoffPrt }}
                                                            </p>
                                                            @if($veh)
                                                                <p class="service-detail-line">
                                                                    <i class="fas fa-car-side"></i>
                                                                    <span class="service-detail-label">Vehicle:</span>
                                                                    {{ $veh['vehicle_name'] ?? 'Vehicle' }}
                                                                    @if(!empty($veh['vehicle_type'])) &middot; {{ $veh['vehicle_type'] }}@endif
                                                                    @if(!empty($veh['selected_transfer_type']))
                                                                        <span class="service-badge badge-optional">{{ ucfirst($veh['selected_transfer_type']) }}</span>
                                                                    @endif
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

            {{-- ------------------------ NOTES ------------------------ --}}
            @if(!empty($bd['notes']))
                <div class="card mt-3" style="border-radius: 10px;">
                    <div class="card-header py-2" style="background: #f8fafc; border-bottom: 1px solid var(--pkg-border);">
                        <h6 class="mb-0"><i class="fas fa-sticky-note me-1 text-warning"></i> Notes</h6>
                    </div>
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
    const viewButtons = document.querySelectorAll('.pkg-pb-itinerary .itinerary-view-btn[data-view]');
    const printBtn = document.getElementById('pkgPbPrintBtn');

    // -------- 1. Sticky sizing -----------------------------------------
    // Reads the real header height so the sidebar's sticky-top stays flush
    // beneath it regardless of how the title wraps on this screen.
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

    // -------- 2. Day navigation ----------------------------------------
    // In day-wise mode, only the active .date-container is visible. Clicking
    // a sidebar button swaps the active day. In list mode, the sidebar
    // instead scrolls the content pane to that date-container's position.
    function activateDay(dayIndex) {
        dayNavButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.dayIndex === String(dayIndex)));
        dayContainers.forEach(c => c.classList.toggle('active', c.dataset.dayIndex === String(dayIndex)));
    }

    if (dayNavButtons.length && dayContainers.length) {
        dayNavButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = this.dataset.dayIndex;
                if (root && root.classList.contains('view-list')) {
                    const target = document.querySelector('.pkg-pb-itinerary .date-container[data-day-index="' + idx + '"]');
                    if (target && scrollHost) {
                        scrollHost.scrollTo({ top: target.offsetTop - 6, behavior: 'smooth' });
                    }
                    dayNavButtons.forEach(b => b.classList.toggle('active', b.dataset.dayIndex === idx));
                } else {
                    activateDay(idx);
                }
            });
        });
        activateDay(dayNavButtons[0].dataset.dayIndex);
    }

    // -------- 3. View switcher (day-wise / list / grid) ----------------
    function applyView(viewType) {
        const isGrid = viewType === 'grid';
        const isList = viewType === 'list';
        root.classList.toggle('view-grid', isGrid);
        root.classList.toggle('view-list', isList);
        viewButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.view === viewType));
        // Day-wise mode: ensure first day is active
        if (!isGrid && !isList && dayNavButtons.length) {
            activateDay(dayNavButtons[0].dataset.dayIndex);
        }
    }
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function () { applyView(this.dataset.view); });
    });
    applyView('list');

    // -------- 4. Scroll-sync in list view ------------------------------
    // While the user scrolls in list view, highlight the sidebar button
    // whose date-container is currently in view.
    if (scrollHost && dayContainers.length && dayNavButtons.length) {
        scrollHost.addEventListener('scroll', function () {
            if (!root || !root.classList.contains('view-list')) return;
            const hostTop = scrollHost.getBoundingClientRect().top;
            let activeIdx = dayContainers[0] ? dayContainers[0].dataset.dayIndex : null;
            dayContainers.forEach(function (container) {
                const topDiff = container.getBoundingClientRect().top - hostTop;
                if (topDiff <= 20) activeIdx = container.dataset.dayIndex;
            });
            if (activeIdx) {
                dayNavButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.dayIndex === activeIdx));
            }
        });
    }

    // -------- 5. Drag & drop to reschedule a service -------------------
    // Dragging a card onto another day (or onto a sidebar day button)
    // POSTs the new date to package.booking.update-service-date.
    let draggedEl = null;

    function clearDropHighlights() {
        document.querySelectorAll('.pkg-pb-itinerary .date-container.pkg-drop-zone').forEach(c => c.classList.remove('pkg-drop-target--over'));
        dayNavButtons.forEach(b => b.classList.remove('drag-over-day'));
    }

    document.querySelectorAll('.pkg-pb-itinerary .pkg-pb-draggable-card').forEach(function (card) {
        card.addEventListener('dragstart', function (e) {
            draggedEl = card;
            card.classList.add('pkg-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.section || '');
        });
        card.addEventListener('dragend', function () {
            card.classList.remove('pkg-dragging');
            draggedEl = null;
            clearDropHighlights();
        });
    });

    function postDateChange(targetDate) {
        if (!draggedEl) return;
        const currentDate = draggedEl.getAttribute('data-current-date');
        if (targetDate === currentDate) return;
        const section = draggedEl.getAttribute('data-section');
        const idxRaw  = draggedEl.getAttribute('data-index');
        const payload = { section: section, tour_start_date: targetDate, _token: token };
        if (idxRaw !== '' && idxRaw !== null) payload.index = parseInt(idxRaw, 10);

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
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(res => {
            if (res.ok && res.data && res.data.success) {
                window.location.reload();
            } else {
                alert((res.data && res.data.message) || 'Could not update date.');
            }
        })
        .catch(() => alert('Network error while saving.'));
    }

    // Drop zones: the day containers themselves
    document.querySelectorAll('.pkg-pb-itinerary .date-container.pkg-drop-zone').forEach(function (col) {
        col.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            col.classList.add('pkg-drop-target--over');
        });
        col.addEventListener('dragleave', function (e) {
            if (!col.contains(e.relatedTarget)) col.classList.remove('pkg-drop-target--over');
        });
        col.addEventListener('drop', function (e) {
            e.preventDefault();
            col.classList.remove('pkg-drop-target--over');
            postDateChange(col.getAttribute('data-date'));
        });
    });

    // Drop zones: the sidebar day buttons (quick drop target for the
    // active-day view where the target date-container is hidden).
    dayNavButtons.forEach(function (btn) {
        btn.addEventListener('dragover', function (e) {
            if (!draggedEl) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            btn.classList.add('drag-over-day');
        });
        btn.addEventListener('dragleave', function () {
            btn.classList.remove('drag-over-day');
        });
        btn.addEventListener('drop', function (e) {
            e.preventDefault();
            btn.classList.remove('drag-over-day');
            postDateChange(btn.getAttribute('data-date'));
        });
    });

    // -------- 6. Print ------------------------------------------------
    if (printBtn) {
        printBtn.addEventListener('click', function () { window.print(); });
    }
})();
</script>
@endif
@endsection
