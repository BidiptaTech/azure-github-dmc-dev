@extends('layouts.layout')
@section('title', 'New Enquiries')
@extends('layouts.datatablecss')

<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 Bootstrap Integration */
    .select2-container--default .select2-selection--single {
        height: 50px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 50px;
        padding-left: 12px;
        padding-right: 50px; /* Space for clear button and arrow */
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px;
        right: 10px;
    }
    /* Style and position the clear button (X icon) */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 35px; /* Position it before the dropdown arrow */
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
        color: #999;
        line-height: 1;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear:hover {
        color: #333;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #696cff;
    }
    .select2-dropdown {
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    
    /* Compact Table Styles - fixed grid so all columns fit and stay in one row */
    #toursTable {
        font-size: 0.875rem;
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0;
        background-color: #fff;
    }
    
    #toursTable thead th {
        padding: 0.5rem 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background-color: #f8f9fa;
    }
    
    #toursTable tbody td {
        padding: 0.5rem 0.5rem;
        vertical-align: top;
        overflow: hidden;
        background-color: #fff;
    }
    
    #toursTable tbody tr {
        height: auto;
        min-height: 50px;
    }
    /* When any service icon in this row is hovered, raise whole row above next rows so tooltip is visible (low z-index so modals stay on top) */
    #toursTable tbody tr:has(.service-icon-wrapper:hover),
    #toursTable tbody tr.service-tooltip-row-active {
        position: relative;
        z-index: 10;
    }
    
    /* Services column: professional soft-badge style (light bg, colored icon) */
    #toursTable td:nth-child(5) {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        overflow: visible !important;
    }
    #toursTable td:nth-child(5) .services-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        row-gap: 0.35rem;
        column-gap: 0.35rem;
        align-items: stretch;
        max-width: 100%;
    }
    #toursTable td:nth-child(5) .service-icon-wrapper {
        min-width: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #toursTable .service-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
    }
    #toursTable .service-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    #toursTable .service-icon-badge i {
        font-size: 1.05rem;
        color: var(--service-color, #475569);
        flex-shrink: 0;
        line-height: 1;
    }
    #toursTable .service-icon-badge[data-clickable="false"] {
        cursor: default;
    }
    #toursTable .service-icon-badge[data-clickable="false"]:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
        box-shadow: none;
    }
    /* Service icon wrapper for tooltip positioning and spacing */
    #toursTable .service-icon-wrapper {
        position: relative;
        display: inline-flex;
        z-index: 1;
        margin: 3px;
    }
    #toursTable .service-icon-wrapper:hover {
        z-index: 10;
    }
    /* In-cell tooltip hidden – we use a body-level tooltip so it’s never clipped */
    #toursTable .service-icon-tooltip {
        display: none !important;
    }
    
    /* Global tooltip (moved to body by JS) – always on top, never clipped by overflow */
    #service-icon-global-tooltip {
        position: fixed;
        padding: 0.4rem 0.65rem;
        background: #2d3748;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
        border-radius: 0.375rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        z-index: 1100;
        pointer-events: none;
        display: none;
        left: 0;
        top: 0;
        transform: translate(-50%, -100%);
    }
    
    /* Actions column: same soft-badge design as Services */
    #toursTable td.col-actions {
        white-space: nowrap;
        overflow: visible;
    }
    #toursTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, auto);
        row-gap: 0.5rem;
        column-gap: 0.5rem;
        align-items: center;
        justify-content: start;
        max-width: 100%;
    }
    #toursTable .action-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        min-width: 32px;
        padding: 0.35rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
        text-decoration: none;
        color: inherit;
    }
    #toursTable .action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        color: inherit;
    }
    #toursTable .action-icon-badge i {
        font-size: 1rem;
        color: var(--action-color, #475569);
    }
    #toursTable .action-icon-badge:hover i {
        color: var(--action-color, #475569);
    }
    #toursTable button.action-icon-badge {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    #toursTable button.action-icon-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    
    /* Compact badges in Services column */
    #toursTable .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        margin: 0.15rem;
        font-weight: 500;
    }
    
    /* Compact icons */
    #toursTable i {
        font-size: 1rem;
    }
    
    #toursTable .ri-user-line,
    #toursTable .ri-user-smile-line,
    #toursTable .ri-user-heart-line {
        font-size: 1rem;
    }
    
    /* Compact text in cells */
    #toursTable .fw-medium,
    #toursTable .fw-bold {
        font-size: 0.875rem;
    }
    
    #toursTable small {
        font-size: 0.75rem;
    }
    
    /* Default compact buttons (for icon-only actions) */
    #toursTable .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        width: 28px;
        height: 28px;
    }

    /* Professional Actions dropdown */
    #toursTable .dropdown-actions .btn-actions-toggle {
        padding: 0.4rem 0.75rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #566a7f;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        min-width: 100px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #toursTable .dropdown-actions .btn-actions-toggle:hover {
        border-color: #696cff;
        color: #696cff;
        background: rgba(105, 108, 255, 0.04);
    }
    #toursTable .dropdown-actions .btn-actions-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }
    #toursTable .dropdown-actions .dropdown-menu {
        min-width: 200px;
        padding: 0.35rem 0;
        border-radius: 0.5rem;
        border: 1px solid #d9dee3;
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.08);
    }
    #toursTable .dropdown-actions .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.15s ease;
    }
    #toursTable .dropdown-actions .dropdown-item i {
        width: 1.25rem;
        text-align: center;
        opacity: 0.85;
    }
    #toursTable .dropdown-actions .dropdown-item:hover {
        background: #f5f5f9;
    }
    #toursTable .dropdown-actions .dropdown-item.text-danger:hover {
        background: rgba(234, 84, 85, 0.08);
        color: #ea5455;
    }
    #toursTable .dropdown-actions .dropdown-divider {
        margin: 0.35rem 0;
        border-color: #e7e7ed;
    }

    /* Wider buttons for negotiation actions */
    #toursTable .btn-sm.negotiation-btn {
        min-width: 130px;
        height: auto;
        width: auto;
        font-size: 0.78rem;
        white-space: nowrap;
        padding: 0.3rem 0.75rem;
    }
    /* Negotiation column: keep button within column width */
    #toursTable td.col-negotiation {
        min-width: 0;
    }
    #toursTable .btn-sm.check-negotiation-btn {
        min-width: 0;
        width: 100%;
        max-width: 100%;
        white-space: normal;
        line-height: 1.3;
        padding: 0.45rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        text-align: center;
    }
    #toursTable .check-negotiation-btn .d-block {
        display: block !important;
    }
    /* Negotiate by Agent – professional modal-trigger button */
    #toursTable .btn-sm.negotiation-btn.negotiate-by-agent {
        white-space: normal;
        min-width: 88px;
        line-height: 1.25;
        padding: 0.5rem 0.65rem;
        border-radius: 0.5rem;
        border-width: 1px;
        font-weight: 500;
        transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }
    #toursTable .btn-sm.negotiation-btn.negotiate-by-agent:hover:not(:disabled) {
        box-shadow: 0 2px 8px rgba(105, 108, 255, 0.25);
    }
    #toursTable .btn-sm.negotiation-btn.negotiate-by-agent:focus {
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }
    #toursTable .btn-sm.negotiation-btn.negotiate-by-agent:disabled {
        opacity: 0.7;
    }
    #toursTable .negotiate-by-agent-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.15rem;
    }
    #toursTable .negotiate-by-agent-label .negotiate-by-agent-icon {
        font-size: 1.1rem;
        opacity: 0.95;
    }
    #toursTable .negotiate-by-agent-label .d-block {
        display: block !important;
        line-height: 1.25;
        font-size: 0.75rem;
    }
    #toursTable .negotiate-by-agent-label .d-block:first-of-type {
        font-size: 0.8125rem;
        font-weight: 600;
    }
    
    /* Compact guest icons section */
    #toursTable .d-flex.gap-3 {
        gap: 0.75rem !important;
    }
    
    /* Compact service badges container */
    #toursTable .d-flex.gap-2.flex-wrap {
        gap: 0.35rem !important;
    }
    
    /* Reduce spacing in tour details */
    #toursTable .d-flex.flex-column {
        gap: 0.15rem;
    }
    
    /* Compact agent info */
    #toursTable .text-muted {
        font-size: 0.7rem;
    }
    
    /* Compact check-in/check-out */
    #toursTable .d-flex.flex-column small {
        line-height: 1.3;
    }

    /* Compact Service Modals */
    .service-modal-compact .modal-dialog {
        max-width: 780px;
        width: 90%;
        margin: 1.25rem auto;
    }

    .service-modal-compact .modal-header {
        height: auto;
        min-height: 90px;
        padding: 0.5rem 0.9rem !important;
    }

    .service-modal-compact .modal-body {
        padding: 0.75rem 0.9rem !important;
    }

    .service-modal-compact .modal-footer {
        padding: 0.5rem 0.9rem !important;
    }

    .service-modal-compact h3 {
        font-size: 1.05rem;
        margin-bottom: 0.25rem;
    }

    .service-modal-compact h4,
    .service-modal-compact h5 {
        font-size: 0.95rem;
        margin-bottom: 0.2rem;
    }

    .service-modal-compact h6 {
        font-size: 0.85rem;
        margin-bottom: 0.15rem;
    }

    .service-modal-compact .card-header {
        padding: 0.45rem 0.75rem !important;
    }

    .service-modal-compact .card-body {
        padding: 0.6rem 0.75rem !important;
    }

    .service-modal-compact .row.mb-4 {
        margin-bottom: 0.55rem !important;
    }

    .service-modal-compact .bg-white.rounded.p-3,
    .service-modal-compact .bg-white.rounded-3.p-4,
    .service-modal-compact .bg-white.rounded.p-3.shadow-sm {
        padding: 0.6rem 0.75rem !important;
    }

    .service-modal-compact small {
        font-size: 0.7rem;
    }

    .service-modal-compact .fs-3,
    .service-modal-compact .fs-4 {
        font-size: 1rem !important;
    }

    .service-modal-compact .fs-5 {
        font-size: 0.9rem !important;
    }

    .service-modal-compact .fs-2 {
        font-size: 1.1rem !important;
    }

    .service-modal-compact .badge {
        padding: 0.2rem 0.45rem;
        font-size: 0.7rem;
    }

    .service-modal-compact .d-flex.align-items-center.mb-3 {
        margin-bottom: 0.4rem !important;
    }

    .service-modal-compact .mb-3 {
        margin-bottom: 0.45rem !important;
    }
</style>

@section('content')
@php
    if (!function_exists('extractOrderTotals')) {
        function extractOrderTotals($payload)
        {
            if (is_object($payload)) {
                $payload = (array) $payload;
            }

            if (!is_array($payload)) {
                return 0;
            }

            $priorityKeys = ['totalPrice', 'total_price', 'price', 'amount'];
            foreach ($priorityKeys as $key) {
                if (isset($payload[$key]) && is_numeric($payload[$key])) {
                    return (float) $payload[$key];
                }
            }

            $sum = 0;
            foreach ($payload as $value) {
                if (is_array($value) || is_object($value)) {
                    $sum += extractOrderTotals($value);
                }
            }

            return $sum;
        }
    }
@endphp
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: {!! json_encode(session('success')) !!},
                timer: 2500,
                showConfirmButton: false
            });
        });
    </script>
@endif
@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Oops',
                text: {!! json_encode(session('error')) !!},
                timer: 3000,
                showConfirmButton: false
            });
        });
    </script>
@endif
<style>
    .new-enq-header-bar { background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%); border-radius: 0.5rem; border: 1px solid rgba(105, 108, 255, 0.08); }
    .new-enq-stat-item { transition: transform 0.15s ease, box-shadow 0.15s ease; min-height: 100%; }
    .new-enq-stat-item:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .new-enq-stat-item .stat-value { font-size: 1.25rem; font-weight: 600; letter-spacing: -0.02em; }
    .new-enq-stat-item .stat-label { display: block; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; opacity: 0.85; margin-top: 0.15rem; }
    .new-enq-stats-grid .col { display: flex; }
    .new-enq-stats-grid .col > div { width: 100%; }
    .new-enq-filter-bar { background: #fff; border-radius: 0.5rem; border: 1px solid #e7e9ed; }
    /* All filter inputs same height (Search, Country, Agent, Start Date, End Date) */
    .new-enq-filter-bar .form-control,
    .new-enq-filter-bar .form-control-sm,
    .new-enq-filter-bar .form-select,
    .new-enq-filter-bar .form-select.form-select-sm { font-size: 0.8125rem; height: 38px; }
    .new-enq-filter-bar .select2-container--default .select2-selection--single { height: 38px !important; min-height: 38px !important; border-radius: 0.375rem; }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 10px; padding-right: 32px; }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; right: 8px; }
    .new-enq-filter-bar .select2-container--default .select2-selection--single .select2-selection__clear { right: 32px; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Compact Header + Stats Bar -->
    <div class="new-enq-header-bar p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0" style="font-size: 1.25rem;">
                    <span class="text-muted fw-light">Bookings /</span> New Enquiries
                </h4>
                <span class="text-muted d-none d-md-inline" style="font-size: 0.875rem;">Manage enquiries and convert to bookings</span>
                <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    <i class="ri-file-list-line me-1"></i><span id="rangeCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->count() }}</span> <span id="rangeLabel">{{ date('F') }}</span>
                </span>
            </div>
            <div class="row g-2 new-enq-stats-grid flex-grow-1">
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-center gap-2 px-3 py-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-primary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-questionnaire-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statEnquiriesCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->count() }}</span><span class="stat-label text-muted" id="statEnquiriesLabel">{{ date('F') }} Enquiries</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-center gap-2 px-3 py-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-success rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-calendar-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statTodayCount">{{ $tours->where('created_at', '>=', now()->today())->count() }}</span><span class="stat-label text-muted">Today</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-center gap-2 px-3 py-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-info rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-user-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statAdultsCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('adult', '>', 0)->sum('adult') }}</span><span class="stat-label text-muted" id="statAdultsLabel">Adults</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-center gap-2 px-3 py-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-warning rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-user-smile-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statChildrenCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('child', '>', 0)->sum('child') }}</span><span class="stat-label text-muted" id="statChildrenLabel">Children</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-center gap-2 px-3 py-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-secondary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-user-heart-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statInfantsCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('infant', '>', 0)->sum('infant') }}</span><span class="stat-label text-muted" id="statInfantsLabel">Infants</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compact Filters -->
    <div class="new-enq-filter-bar card mb-3 border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                    <span class="text-muted fw-medium d-flex align-items-center gap-1" style="font-size: 0.8rem;"><i class="ri-filter-3-line"></i> Filters</span>
                    <button class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="resetFilters()" title="Reset filters">
                        <i class="ri-refresh-line me-1"></i> Reset
                    </button>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Search</label>
                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Tour ID, Display ID, Destination...">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Country</label>
                    <select class="form-select form-select-sm" id="countryFilter">
                        <option value="">All Countries</option>
                        @php
                            $allCountries = [];
                            foreach($tours as $tour) {
                                if($tour->destination) {
                                    $countries = array_map('trim', explode(',', $tour->destination));
                                    $allCountries = array_merge($allCountries, $countries);
                                }
                            }
                            $uniqueCountries = array_unique(array_filter($allCountries));
                            sort($uniqueCountries);
                        @endphp
                        @foreach($uniqueCountries as $country)
                            <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Agent</label>
                    <select class="form-select form-select-sm" id="agentFilter">
                        <option value="">All Agents</option>
                        @foreach($tours->where('agent_name', '!=', null)->pluck('agent_name', 'agent_id')->unique() as $agentId => $agentName)
                            <option value="{{ $agentName }}">{{ $agentName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Start Date</label>
                    <input type="date" class="form-control form-control-sm" id="startDateFilter" max="{{ now()->toDateString() }}" value="{{ now()->startOfMonth()->toDateString() }}">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="endDateFilter" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">New Enquiries List <span id="filterResultsBadge" class="badge bg-primary ms-2" style="display: none;"></span></h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                    </ul>
                </div>
                {{-- <button class="btn btn-sm btn-primary" onclick="bulkActions()">
                    <i class="ri-settings-line me-1"></i> Bulk Actions
                </button> --}}
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <colgroup>
                        <col style="width:3%">
                        <col style="width:13%">
                        <col style="width:10%">
                        <col style="width:17%">
                        @php $role = [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138]; @endphp
                        @if(in_array(auth()->user()->role_id, $role))
                        <col style="width:14%">
                        <col style="width:14%">
                        @endif
                        <col style="width:14%">
                        <col style="width:10%">
                        <col style="width:8%">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            {{-- <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th> --}}
                            <th class="th-tooltip" data-tooltip="#">#</th>
                            <th class="th-tooltip" data-tooltip="Tour Details">Tour Details</th>
                            <th class="th-tooltip" data-tooltip="Agent">Agent</th>
                            <th class="th-tooltip" data-tooltip="Services">Services</th>
                            @if(in_array(auth()->user()->role_id, $role))
                            <th class="th-tooltip" data-tooltip="Agent Negotiation">Negotiation</th>
                            @endif
                            <th class="th-tooltip" data-tooltip="Actions">Actions</th>
                            <th class="th-tooltip" data-tooltip="Created">Created</th>
                            <th class="th-tooltip" data-tooltip="Auto Cancel Date">Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-adult="{{ (int)($tour->adult ?? 0) }}"
                            data-child="{{ (int)($tour->child ?? 0) }}"
                            data-infant="{{ (int)($tour->infant ?? 0) }}"
                            data-tour-status="{{ $tour->tour_status ?? '' }}"
                        >
                            {{-- <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td> --}}
                            <td class="align-top">{{ $key + 1 }}</td>
                            <td class="align-top" style="min-width: 200px;">
                                <div class="d-flex flex-column gap-1">
                                    <strong class="text-primary">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                    @if($tour->tour_type)
                                        <span class="text-white d-inline-block px-2 py-0 rounded" style="background: #3b82f6; font-weight: 500; font-size: 0.75rem;">{{ $tour->tour_type }}</span>
                                    @endif
                                    <span class="fw-medium mt-1"><i class="ri-map-pin-line me-1"></i>{{ $tour->destination ?? 'N/A' }}</span>
                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <span title="Adults"><i class="ri-user-line text-success"></i> {{ $tour->adult ?? 0 }}</span>
                                        <span title="Children"><i class="ri-user-smile-line text-warning"></i> {{ $tour->child ?? 0 }}</span>
                                        <span title="Infants"><i class="ri-user-heart-line text-info"></i> {{ $tour->infant ?? 0 }}</span>
                                    </div>
                                    @if($tour->check_in_time || $tour->check_out_time)
                                        <small>
                                            @if($tour->check_in_time)<span><strong>In:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }}</span>@endif
                                            @if($tour->check_out_time)<span><strong>Out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</span>@endif
                                        </small>
                                    @else
                                        <small class="text-muted">Check-in/out: Not specified</small>
                                    @endif
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="d-flex flex-column">
                                    @if($tour->agent_name)
                                        <span class="fw-medium text-primary">{{ $tour->agent_name }}</span>
                                        <small class="text-muted">
                                            <i class="fas fa-building me-1"></i>
                                            {{ $tour->agent_company_name ?? 'N/A' }}
                                        </small>
                                    @else
                                        <span class="text-muted">No agent assigned</span>
                                    @endif
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        // Fetch orders for this tour to get actual service data
                                        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->where('bookingType', 'enquiry')->get();
                                        $svc = [
                                            'hotel' => 0,
                                            'attraction' => 0,
                                            'restaurant' => 0,
                                            'guide' => 0,
                                            'entry_port' => 0,
                                            'exit_port' => 0,
                                            'travel_hourly' => 0,
                                            'travel_point' => 0,
                                            'local_transport' => 0,
                                            'miscellaneous' => 0,
                                        ];
                                        $serviceData = [];
                                        $ordersTotalAmount = 0;
                                        
                                        foreach($orders as $order) {
                                            if(isset($svc[$order->type])) {
                                                $svc[$order->type]++;
                                                if(!isset($serviceData[$order->type])) {
                                                    $serviceData[$order->type] = [];
                                                }
                                                $serviceData[$order->type][] = $order;
                                            }
                                            $orderPayload = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                                            $ordersTotalAmount += extractOrderTotals($orderPayload);
                                        }
                                        $ordersTotalAmount = round($ordersTotalAmount, 2);
                                        
                                        $icons = [
                                            'hotel' => 'ri-hotel-bed-line',
                                            'attraction' => 'ri-camera-line',
                                            'restaurant' => 'ri-restaurant-2-line',
                                            'guide' => 'ri-user-voice-line',
                                            'entry_port' => 'ri-flight-land-line',
                                            'exit_port' => 'ri-flight-takeoff-line',
                                            'travel_hourly' => 'ri-time-line',
                                            'travel_point' => 'ri-route-line',
                                            'local_transport' => 'ri-car-line',
                                            'miscellaneous' => 'ri-file-list-3-line',
                                        ];
                                        $serviceLabels = [
                                            'hotel' => 'Hotel',
                                            'attraction' => 'Attraction',
                                            'restaurant' => 'Restaurant',
                                            'guide' => 'Guide',
                                            'entry_port' => 'Arrival',
                                            'exit_port' => 'Departure',
                                            'travel_hourly' => 'Local-Tour Hourly',
                                            'travel_point' => 'Local-Tour Point to Point',
                                            'local_transport' => 'Local Transport',
                                            'miscellaneous' => 'Miscellaneous',
                                        ];
                                        /* Professional palette: cohesive, good contrast for white icons */
                                        $serviceColors = [
                                            'hotel' => '#4338ca',
                                            'attraction' => '#0f766e',
                                            'restaurant' => '#c2410c',
                                            'guide' => '#475569',
                                            'entry_port' => '#047857',
                                            'exit_port' => '#0369a1',
                                            'travel_hourly' => '#b45309',
                                            'travel_point' => '#5b21b6',
                                            'local_transport' => '#334155',
                                            'miscellaneous' => '#7c3aed',
                                        ];
                                        
                                        // For debugging
                                        $debugInfo = [
                                            'tour_id' => $tour->tour_id,
                                            'orders_count' => $orders->count(),
                                            'svc' => $svc,
                                            'serviceData_keys' => array_keys($serviceData)
                                        ];
                                    @endphp
                                    <div class="services-icons-wrap">
                                    @foreach($svc as $svcKey=>$count)
                                        @if(intval($count) > 0)
                                            @php
                                                $label = $serviceLabels[$svcKey] ?? ucfirst($svcKey);
                                                $tooltipText = $label . ': ' . $count;
                                                $bgColor = $serviceColors[$svcKey] ?? '#6c757d';
                                                $clickable = in_array($svcKey, ['hotel', 'attraction', 'restaurant', 'guide', 'entry_port', 'exit_port', 'travel_hourly', 'travel_point', 'local_transport', 'miscellaneous']);
                                            @endphp
                                            @if($clickable)
                                                <span class="service-icon-wrapper" data-tooltip="{{ $tooltipText }}">
                                                    <span class="service-icon-badge"
                                                          style="--service-color: {{ $bgColor }};"
                                                          data-clickable="true"
                                                          onclick="openServiceModal('{{ $svcKey }}', {{ $tour->tour_id }}, event)"
                                                          data-debug-info="{{ json_encode($debugInfo) }}"
                                                          role="button"
                                                          tabindex="0">
                                                        <i class="{{ $icons[$svcKey] }}"></i>
                                                    </span>
                                                    <span class="service-icon-tooltip">{{ $tooltipText }}</span>
                                                </span>
                                            @else
                                                <span class="service-icon-wrapper" data-tooltip="{{ $tooltipText }}">
                                                    <span class="service-icon-badge"
                                                          style="--service-color: {{ $bgColor }};"
                                                          data-clickable="false"
                                                          role="img">
                                                        <i class="{{ $icons[$svcKey] }}"></i>
                                                    </span>
                                                    <span class="service-icon-tooltip">{{ $tooltipText }}</span>
                                                </span>
                                            @endif
                                        @endif
                                    @endforeach
                                    @if(array_sum(array_map('intval', $svc)) === 0)
                                        <span class="text-muted small">No services</span>
                                    @endif
                                    </div>
                                </div>
                            </td>
                            @php
                                $tourEnquiries = $enquary_comments->where('tour_id', $tour->tour_id)->sortByDesc('created_at')->values();
                                $latestComment = $tourEnquiries->first();
                                $latestAgentComment = $tourEnquiries->first(function ($comment) {
                                    return strtolower($comment->sender_type ?? '') === 'agent';
                                });
                                
                                // Get enquiry details from Enquiry table
                                $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->latest()->first();
                                $enquiry_status = '';
                                $edit_off = 0;
                                if ($enquiry) {
                                    $enquiry_status = $enquiry->status;
                                    $edit_off = 1;
                                }
                                
                                // Get first enquiry for discount calculation
                                $frstenquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->first();
                                $first_enquiry_actual_amount = $frstenquiry->actual_amount ?? 0;
                                
                                // Calculate total tour price from ALL bookings with status 1 or 3
                                // Includes: base price + transfer price + guide price (for attractions)
                                $tourTotalPrice = 0;
                                foreach ($tour->booking as $booking) {
                                    if (in_array($booking->status, [1, 3])) {
                                        $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
                                        if (is_array($data)) {
                                            foreach ($data as $item) {
                                                $itemPrice = (float) ($item['totalPrice'] ?? $item['price'] ?? 0);
                                                
                                                // Add transfer price if exists
                                                $transferPrice = 0;
                                                if (isset($item['transfer_options']['cost']) && $item['transfer_options']['cost'] > 0) {
                                                    $transferPrice = (float) $item['transfer_options']['cost'];
                                                }
                                                
                                                // Add guide price if exists (attractions, entry_port, exit_port, etc.)
                                                $guidePrice = 0;
                                                if (isset($item['guide_options']) && is_array($item['guide_options'])) {
                                                    $gv = $item['guide_options']['total_price'] ?? $item['guide_options']['cost'] ?? $item['guide_options']['Cost'] ?? $item['guide_options']['sell'] ?? $item['guide_options']['Sell'] ?? 0;
                                                    if ($gv > 0) $guidePrice = (float) $gv;
                                                }
                                                
                                                $tourTotalPrice += $itemPrice + $transferPrice + $guidePrice;
                                            }
                                        }
                                    }
                                }
                                
                                // Calculate discount from enquiry
                                $enquiry_amount = $enquiry->amount ?? 0;
                                $discount = $first_enquiry_actual_amount - $enquiry_amount;
                                
                                // Actual Amount = Total of all booking prices (updates when service added)
                                $currentActualAmount = ceil($tourTotalPrice);
                                
                                // Negotiated Amount = Total booking prices - discount
                                $settlementAmount = ceil($tourTotalPrice) - $discount;
                                $baseAmount = ceil($tourTotalPrice) - $discount;
                                
                                $lastAgentAmount = $settlementAmount;
                                $lastAgentRemark = $latestAgentComment->comment ?? '';
                                $canCheckNegotiation = $latestAgentComment !== null;
                            @endphp
                            @php
                                $role = [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138];
                            @endphp
                            @if(in_array(auth()->user()->role_id, $role))
                            <td class="align-top">
                                <div class="d-flex flex-column gap-2">
                                    <button 
                                        type="button"
                                        class="btn btn-sm btn-outline-primary negotiation-btn negotiate-by-agent"
                                        data-tour-id="{{ $tour->tour_id }}"
                                        data-display-id="{{ e($tour->display_id) }}"
                                        data-actual="{{ $currentActualAmount ?? 0 }}"
                                        data-last-amount="{{ $lastAgentAmount ?? '' }}"
                                        data-last-comment="{{ e($lastAgentRemark) }}"
                                        data-tour-status="{{ e($tour->tour_status) }}"
                                        data-negotiation-locked="{{ $canCheckNegotiation ? '1' : '0' }}"
                                        onclick="openAgentNegotiationModal(this)"
                                        {{ $canCheckNegotiation ? 'disabled' : '' }}
                                    >
                                        <span class="negotiate-by-agent-label">
                                            <i class="ri-handshake-line negotiate-by-agent-icon" aria-hidden="true"></i>
                                            <span class="d-block">Negotiate</span>
                                            <span class="d-block small text-muted">By Dmc</span>
                                        </span>
                                    </button>
                                    <button 
                                        type="button"
                                        class="btn btn-sm btn-warning negotiation-btn check-negotiation-btn"
                                        data-tour-id="{{ $tour->tour_id }}"
                                        data-enquiry-id="{{ $enquiry->enquiry_id ?? '' }}"
                                        data-price="{{ $settlementAmount }}"
                                        data-actual="{{ $currentActualAmount }}"
                                        data-discount="{{ $discount }}"
                                        data-comment="{{ e($lastAgentRemark) }}"
                                        onclick="openNewEnquiryModal(this, '{{ route('update-price-comment') }}')"
                                        {{ $canCheckNegotiation ? '' : 'disabled' }}
                                    >
                                        Negotiation
                                    </button>
                                    @if(!$canCheckNegotiation)
                                        <small class="text-muted" style="font-size: 0.7rem;">Awaiting agent</small>
                                    @endif
                                </div>
                            </td>
                            @endif
                            <td class="align-top col-actions">
                                <div class="actions-icons-wrap">
                                    @if($tour->is_pro == 0)
                                    <a href="{{ route('single-tour-package.edit', Crypt::encrypt($tour->tour_id)) }}"
                                       class="action-icon-badge" style="--action-color: #047857;" data-tooltip="Edit Tour">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    @else
                                    <a href="{{ route('enquiry-form-pro.edit', Crypt::encrypt($tour->tour_id)) }}"
                                       class="action-icon-badge" style="--action-color: #047857;" data-tooltip="Edit Tour">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('bookings.view-tour', Crypt::encrypt($tour->tour_id)) }}"
                                       class="action-icon-badge" style="--action-color: #0369a1;" data-tooltip="Audit Trail">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('tour.itinerary.preview', ['encryptedTourId' => Crypt::encrypt($tour->tour_id)]) }}"
                                       class="action-icon-badge" style="--action-color: #0f766e;" data-tooltip="Quotation Preview" target="_blank">
                                        <i class="ri-file-download-line"></i>
                                    </a>
                                    <button type="button" class="action-icon-badge" style="--action-color: #dc2626;" data-tooltip="Cancel Tour" onclick="cancelTour('{{ Crypt::encrypt($tour->tour_id) }}', {{ json_encode($tour->display_id) }})" id="cancel-btn-{{ $tour->tour_id }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                            @php
                                $tz = auth()->user()->timezone ?? 'UTC';
                                $time = $tour->created_at->timezone($tz);
                            @endphp                            
                            <td class="align-top">
                                <div class="d-flex flex-column gap-0">
                                    <span class="fw-medium">{{ $tour->created_by_name ?? 'N/A' }}</span>
                                    <small class="text-muted">{{ $time->format('D, M d, Y') }} · {{ $time->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="d-flex flex-column">
                                    @if($tour->auto_cancel_date)
                                        <span class="fw-semibold">
                                            <i class="fas fa-calendar-times text-warning me-1"></i>
                                            {{ \Carbon\Carbon::parse($tour->auto_cancel_date)->format('D, M d, Y') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($tour->auto_cancel_date)->format('h:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <span class="text-muted">No new enquiries found</span>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="service-icon-global-tooltip" aria-hidden="true"></div>

            {{-- <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $tours->firstItem() ?? 0 }} to {{ $tours->lastItem() ?? 0 }} of {{ $tours->total() }} results
                    </p>
                </div>
                <div>
                    {{ $tours->links() }}
                </div>
            </div> --}}
        </div>
    </div>
    
    <!-- Update Price Modal (New Enquiries) -->
    <div class="modal fade" id="newEnquiryUpdateModal" tabindex="-1" aria-labelledby="newEnquiryUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newEnquiryUpdateModalLabel">Update Price & Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newEnquiryUpdateForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="enquiry_id" id="new_enquiry_modal_enquiry_id" />
                        
                        <!-- Current details display -->
                        <div class="border rounded p-3 bg-light mb-3">
                            <div class="row g-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">Actual Amount</small>
                                    <div class="fw-semibold" id="new_enquiry_display_actual">—</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Discount</small>
                                    <div class="fw-semibold text-danger" id="new_enquiry_display_discount">—</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Previous Negotiated Amount</small>
                                    <div class="fw-semibold text-success" id="new_enquiry_display_price">—</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Last Comment</small>
                                    <div class="fw-semibold" id="new_enquiry_display_comment">—</div>
                                </div>
                            </div>
                        </div>

                        <!-- New update inputs -->
                        <div class="mb-3">
                            <label for="new_enquiry_current_price" class="form-label">New Price</label>
                            <input id="new_enquiry_current_price" type="number" name="price" class="form-control" placeholder="Enter new price" onkeyup="validateNewEnquiryPrice(this)" required />
                            <div id="new-enquiry-warning-message" class="alert alert-warning mt-2 py-2 px-3 d-none">
                                Enquiry price cannot exceed the actual amount.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_enquiry_comment" class="form-label">New Comment</label>
                            <textarea id="new_enquiry_comment" name="comment" rows="3" class="form-control" placeholder="Enter new comment" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="new_enquiry_cancel_btn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="new_enquiry_submit_btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Negotiate by Agent Modal -->
    <div class="modal fade" id="agentNegotiationModal" tabindex="-1" aria-labelledby="agentNegotiationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="agentNegotiationForm" method="POST" action="{{ route('tours.agent-negotiation') }}" data-action-url="{{ route('tours.agent-negotiation') }}">
                @csrf
                <input type="hidden" name="tour_id" id="agent_negotiation_tour_id">
                <input type="hidden" name="action" id="agent_negotiation_action" value="negotiate">
                <input type="hidden" name="actual_amount" id="agent_negotiation_actual_amount">
                <div class="modal-header">
                    <h5 class="modal-title" id="agentNegotiationModalLabel">Negotiate by Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="border rounded p-3 bg-light mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Tour</small>
                                <div class="fw-semibold" id="agentNegotiationDisplayId">—</div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted d-block">Current Amount</small>
                                <div class="fw-semibold text-primary" id="agentNegotiationCurrentAmount">—</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block">Last Agent Offer</small>
                                <div class="fw-semibold text-warning" id="agentNegotiationLastAmount">—</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block">Last Remarks</small>
                                <div class="text-muted" id="agentNegotiationLastRemark">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="agentNegotiationAmount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="agentNegotiationAmount" name="amount" min="0" step="0.01" placeholder="Enter negotiated amount">
                        <div class="form-text text-primary fw-semibold" id="agentNegotiationMaxMessage">Maximum allowed amount: <span id="agentNegotiationMaxValue">—</span></div>
                    </div>
                    <div class="mb-3">
                        <label for="agentNegotiationRemark" class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="agentNegotiationRemark" name="comment" rows="3" placeholder="Add remarks for this negotiation" required></textarea>
                        <div class="invalid-feedback d-none" id="agentNegotiationRemarkError">Please fill the input.</div>
                    </div>
                    <div class="alert alert-warning py-2 px-3 d-none" id="agentNegotiationWarning">
                        Negotiated amount cannot exceed the current amount.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2 pb-3 px-3 px-md-4 d-flex flex-nowrap align-items-center justify-content-end gap-2" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close without saving">
                        Close
                    </button>
                    <button type="button" class="btn btn-outline-success" id="agentNegotiationConfirmBtn" onclick="submitAgentNegotiation('confirm')">
                        Confirm tour
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="agentNegotiationCancelBtn" onclick="submitAgentNegotiation('cancel')">
                        Cancel tour
                    </button>
                    <button type="button" class="btn btn-primary" id="agentNegotiationSubmitBtn" onclick="submitAgentNegotiation('negotiate')">
                        Negotiate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Service Modals for each tour -->
    @foreach($tours as $tour)
    @php
    // Re-fetch orders for this tour for modal rendering
    $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->get();
    $svc = [
        'hotel' => 0,
        'attraction' => 0,
        'restaurant' => 0,
        'guide' => 0,
        'entry_port' => 0,
        'exit_port' => 0,
        'travel_hourly' => 0,
        'travel_point' => 0,
        'local_transport' => 0,
        'miscellaneous' => 0,
    ];
    $serviceData = [];
    
    foreach($orders as $order) {
        if(isset($svc[$order->type])) {
            $svc[$order->type]++;
            if(!isset($serviceData[$order->type])) {
                $serviceData[$order->type] = [];
            }
            $serviceData[$order->type][] = $order;
        }
    }
@endphp

<!-- Hotel Details Modal -->
@if(isset($svc['hotel']) && $svc['hotel'] > 0)
<div class="modal fade" id="hotelDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="hotelDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <!-- Compact Header -->
            <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-hotel-line me-1" style="font-size: 0.9rem;"></i>Hotel Enquiries - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                    @foreach($serviceData['hotel'] as $index => $hotelOrder)
                    @php
                        $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                    @endphp
                    
                    @if(is_array($hotelData))
                        @foreach($hotelData as $bookingIndex => $booking)
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #74b9ff !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-hotel-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel Bookings' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">Enquiry {{ $index + 1 }} • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2" style="background-color: #ffffff;">
                                    <!-- Customer Details & Address -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-user-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Customer Details</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Full Name</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Email Address</small>
                                                        <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Phone Number</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Address</h6>
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    @if($booking['address1'] ?? false)
                                                        <div>{{ $booking['address1'] }}</div>
                                                    @endif
                                                    @if($booking['address2'] ?? false)
                                                        <div>{{ $booking['address2'] }}</div>
                                                    @endif
                                                    @if($booking['state'] ?? false)
                                                        <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                    @endif
                                                    @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                        <div class="text-muted">Address not provided</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stay Information & Hotel Details -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-check-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Stay Schedule</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Check-in</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.75rem;">
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0)
                                                                {{ \Carbon\Carbon::parse($booking['bookingDate'][0])->format('M d, Y') }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['checkInTime']))
                                                            <small class="text-primary fw-medium" style="font-size: 0.65rem;">{{ $booking['hotelDetails']['checkInTime'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Check-out</small>
                                                        <div class="fw-bold text-danger" style="font-size: 0.75rem;">
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                {{ \Carbon\Carbon::parse(end($booking['bookingDate']))->format('M d, Y') }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['checkOutTime']))
                                                            <small class="text-danger fw-medium" style="font-size: 0.65rem;">{{ $booking['hotelDetails']['checkOutTime'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Total Nights</small>
                                                        <div>
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                @php
                                                                    $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                                                    $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                                                    $nights = $checkIn->diffInDays($checkOut);
                                                                @endphp
                                                                <span class="badge" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); color: white; font-size: 0.65rem; padding: 2px 4px;">{{ $nights }} Night{{ $nights > 1 ? 's' : '' }}</span>
                                                            @else
                                                                <span class="badge bg-secondary" style="font-size: 0.65rem; padding: 2px 4px;">Duration TBD</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-building-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Hotel Details</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Location</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['hotelDetails']['location'] ?? 'Location not specified' }}</div>
                                                    </div>
                                                    @if(isset($booking['hotelDetails']['cancellation_charge']) && !empty($booking['hotelDetails']['cancellation_charge']))
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Cancellation Policy</small>
                                                        <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $booking['hotelDetails']['cancellation_charge'] }}</div>
                                                    </div>
                                                    @endif
                                                </div>
                                                @if(isset($booking['hotelDetails']['image']))
                                                    <div class="mt-1">
                                                        <img src="{{ $booking['hotelDetails']['image'] }}" 
                                                             alt="{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel' }}" 
                                                             class="img-fluid rounded shadow-sm" 
                                                             style="height: 60px; width: 100%; object-fit: cover;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Room & Accommodation Details -->
                                    @if(isset($booking['rooms']) && is_array($booking['rooms']))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-door-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Room & Accommodation Details</h6>
                                            </div>
                                            
                                            @foreach($booking['rooms'] as $roomIndex => $room)
                                                <div class="bg-white rounded p-1 mb-1 border" style="border-color: #74b9ff !important;">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <div>
                                                            <small class="fw-bold text-dark" style="font-size: 0.75rem;">Room {{ $roomIndex + 1 }}: {{ $room['room_type'] ?? 'Standard Room' }}</small>
                                                        </div>
                                                        @if(isset($room['beds']) && is_array($room['beds']))
                                                            @php $totalRoomPrice = collect($room['beds'])->sum('price'); @endphp
                                                            <span class="badge bg-success" style="font-size: 0.7rem;">SGD {{ number_format($totalRoomPrice, 2) }}</span>
                                                        @endif
                                                    </div>
                                                    
                                                    @if(isset($room['beds']) && is_array($room['beds']))
                                                        @foreach($room['beds'] as $bedIndex => $bed)
                                                            <div class="bg-light rounded p-1 mb-1">
                                                                <div class="row g-1">
                                                                    <div class="col-6">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Bed Type: {{ $bed['bed_type'] ?? 'Bed' }}</small>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Guests</small>
                                                                        <div class="fw-medium text-primary" style="font-size: 0.7rem;">{{ $bed['head_count'] ?? 0 }}</div>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Price</small>
                                                                        <div class="fw-bold text-success" style="font-size: 0.7rem;">SGD {{ number_format($bed['price'] ?? 0, 2) }}</div>
                                                                    </div>
                                                                    @if(isset($bed['selectedMeals']) && is_array($bed['selectedMeals']) && count($bed['selectedMeals']) > 0)
                                                                        <div class="col-12">
                                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Meals:</small>
                                                                            @foreach($bed['selectedMeals'] as $mealKey => $meal)
                                                                                <span class="badge bg-success me-1" style="font-size: 0.6rem;">{{ $meal['type'] ?? 'Meal' }} (SGD {{ number_format((float)($meal['price'] ?? 0), 2) }})</span>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                            
                                            <!-- Booking Summary -->
                                            <div class="bg-white rounded p-1 mt-1 border" style="border-color: #74b9ff !important;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="fw-bold text-dark" style="font-size: 0.75rem;">Hotel Booking Summary</small>
                                                        <div><small class="text-muted" style="font-size: 0.65rem;">{{ count($booking['rooms']) }} room(s) • {{ ucfirst($booking['bookingType'] ?? 'Standard') }} booking</small></div>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Total Amount</small>
                                                        <div class="fw-bold" style="font-size: 0.9rem; color: #74b9ff;">SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Child Accommodation (child_with_bed / child_without_bed) -->
                                    @php
                                        $hotelNights = 0;
                                        if (isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1) {
                                            $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                            $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                            $hotelNights = $checkIn->diffInDays($checkOut);
                                        }
                                    @endphp
                                    @if((isset($booking['child_with_bed']['enabled']) && $booking['child_with_bed']['enabled']) || (isset($booking['child_without_bed']['enabled']) && $booking['child_without_bed']['enabled']))
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-user-add-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Child Accommodation</h6>
                                        </div>
                                        <div class="row g-2">
                                            @if(isset($booking['child_with_bed']['enabled']) && $booking['child_with_bed']['enabled'])
                                            @php
                                                $cwb = $booking['child_with_bed'];
                                                $cwbPrice = (float)($cwb['price'] ?? 0);
                                                $cwbChildren = (int)($cwb['children'] ?? 0);
                                                $cwbTotal = isset($cwb['total_cost']) ? (float)$cwb['total_cost'] : ($cwbPrice * $cwbChildren * $hotelNights);
                                            @endphp
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-2 border h-100" style="border-color: #74b9ff !important;">
                                                    <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;"><i class="ri-bed-line me-1" style="font-size: 0.8rem;"></i>Child with Bed</div>
                                                    <div class="row g-1">
                                                        <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Status</small><div class="fw-medium text-success" style="font-size: 0.75rem;">Yes</div></div>
                                                        <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">SGD {{ number_format($cwbPrice, 2) }}</div></div>
                                                        <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Children</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $cwbChildren }}</div></div>
                                                        <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNights }}</div></div>
                                                        <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total (Price × Children × Nights)</small><div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($cwbTotal, 2) }}</div></div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(isset($booking['child_without_bed']['enabled']) && $booking['child_without_bed']['enabled'])
                                            @php
                                                $cwob = $booking['child_without_bed'];
                                                $cwobPrice = (float)($cwob['price'] ?? 0);
                                                $cwobChildren = (int)($cwob['children'] ?? 0);
                                                $cwobTotal = isset($cwob['total_cost']) ? (float)$cwob['total_cost'] : ($cwobPrice * $cwobChildren * $hotelNights);
                                            @endphp
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-2 border h-100" style="border-color: #74b9ff !important;">
                                                    <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;"><i class="ri-user-smile-line me-1" style="font-size: 0.8rem;"></i>Child without Bed</div>
                                                    <div class="row g-1">
                                                        <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Status</small><div class="fw-medium text-success" style="font-size: 0.75rem;">Yes</div></div>
                                                        <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">SGD {{ number_format($cwobPrice, 2) }}</div></div>
                                                        <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Children</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $cwobChildren }}</div></div>
                                                        <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNights }}</div></div>
                                                        <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total (Price × Children × Nights)</small><div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format($cwobTotal, 2) }}</div></div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Transfer Options -->
                                    @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-car-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Details</h6>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                <span class="badge bg-primary" style="font-size: 0.65rem;">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Way</small>
                                                                <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                            </div>
                                                            @if(isset($booking['transfer_options']['destination_name']) && !empty($booking['transfer_options']['destination_name']))
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Destination</small>
                                                                <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                    <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['destination_name'] }}
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']))
                                                            <div class="row g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">
                                                                        <i class="ri-car-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] ?? 'N/A' }}
                                                                    </div>
                                                                    @if(isset($booking['transfer_options']['vehicle_details']['vehicle_type']))
                                                                        <small class="text-muted" style="font-size: 0.6rem;">Type: {{ $booking['transfer_options']['vehicle_details']['vehicle_type'] }}</small>
                                                                    @endif
                                                                </div>
                                                                @if(isset($booking['transfer_options']['vehicle_details']['seating_capacity']))
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Capacity</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_details']['seating_capacity'] }} passengers</div>
                                                                </div>
                                                                @endif
                                                                @if(isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0)
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                    <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format((float)($booking['transfer_options']['cost'] ?? 0), 2) }}</div>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle ID</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                <div class="col-12">
                                                    <div class="bg-info bg-opacity-10 rounded p-1 mt-1">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                        <div class="fw-medium text-info" style="font-size: 0.75rem;">{{ $booking['transfer_options']['pickup_location_name'] }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Special Requests -->
                                    @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background-color: #6f42c1; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-message-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Special Requests</h6>
                                            </div>
                                            <div class="bg-white rounded p-1">
                                                <p class="mb-0 text-dark" style="font-size: 0.75rem;">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-hotel-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Hotel Data Available</h4>
                        <p class="text-muted mb-4">Hotel services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <!-- Compact Footer -->
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
 </div>
@endif

<!-- Attraction Details Modal -->
@if(isset($svc['attraction']) && $svc['attraction'] > 0)
<div class="modal fade" id="attractionDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="attractionDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <!-- Compact Header -->
            <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-building-2-line me-1" style="font-size: 0.9rem;"></i>Attraction Enquiries - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['attraction']) && count($serviceData['attraction']) > 0)
                    @foreach($serviceData['attraction'] as $index => $attractionOrder)
                    @php
                        $attractionData = is_string($attractionOrder->data) ? json_decode($attractionOrder->data, true) : $attractionOrder->data;
                    @endphp
                    
                    @if(is_array($attractionData))
                        @php $actualBookingIndex = 0; @endphp
                        @foreach($attractionData as $originalKey => $booking)
                            @php $bookingIndex = $actualBookingIndex; @endphp
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #fd9853 !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd9853 0%, #fe7854 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-building-2-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['AttractionName'] ?? 'Attraction Booking' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['ticketName'] ?? 'Standard Ticket' }} • Enquiry {{ $index + 1 }}</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2" style="background-color: #ffffff;">
                                    <!-- Visit & Guest Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Visit Schedule</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Visit Date</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Visit Time</small>
                                                        <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['visitTime'] ?? 'Full Day' }}</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Selection Type</small>
                                                        <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ ucfirst($booking['Selection'] ?? 'Standard') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guest Information</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-4 text-center">
                                                        <div class="bg-white rounded p-1">
                                                            <div class="fw-bold text-success" style="font-size: 0.85rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="bg-white rounded p-1">
                                                            <div class="fw-bold text-warning" style="font-size: 0.85rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="bg-white rounded p-1">
                                                            <div class="fw-bold text-info" style="font-size: 0.85rem;">{{ $booking['seniorCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Seniors</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center mt-1">
                                                    <span class="badge" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                        Total: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) + ($booking['seniorCount'] ?? 0) }} Guests
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Attraction Details -->
                                    <div class="bg-light rounded p-1 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-building-2-line text-white" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Attraction Details</h6>
                                        </div>
                                        <div class="bg-white rounded p-1">
                                            <div class="row g-1">
                                                <div class="col-4">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Ticket ID</small>
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['ticketId'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">NRI Status</small>
                                                    <span class="badge bg-info" style="font-size: 0.65rem;">{{ ucfirst($booking['nri'] ?? 'N/A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ticket & Pricing Details -->
                                    @if(isset($booking['ticket_details']))
                                    <div class="bg-light rounded p-1 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-ticket-line text-white" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Ticket & Pricing</h6>
                                        </div>
                                        
                                        <!-- Pricing Cards -->
                                        <div class="row g-1 mb-1">
                                            <div class="col-4">
                                                <div class="bg-white border rounded p-1 text-center" style="border-color: #28a745 !important;">
                                                    <small class="text-success fw-bold d-block" style="font-size: 0.7rem;">Adult</small>
                                                    <div class="fw-bold text-success" style="font-size: 0.75rem;">SGD {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="bg-white border rounded p-1 text-center" style="border-color: #ffc107 !important;">
                                                    <small class="text-warning fw-bold d-block" style="font-size: 0.7rem;">Child</small>
                                                    <div class="fw-bold text-warning" style="font-size: 0.75rem;">SGD {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="bg-white border rounded p-1 text-center" style="border-color: #17a2b8 !important;">
                                                    <small class="text-info fw-bold d-block" style="font-size: 0.7rem;">Senior</small>
                                                    <div class="fw-bold text-info" style="font-size: 0.75rem;">SGD {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Booking Summary -->
                                        <div class="bg-white rounded p-1 border" style="border-color: #fd9853 !important;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="fw-bold text-dark" style="font-size: 0.75rem;">Booking Summary</small>
                                                    <div class="d-flex gap-1 flex-wrap">
                                                        @if($booking['adultCount'] ?? 0 > 0)
                                                            <span class="badge bg-success" style="font-size: 0.6rem;">{{ $booking['adultCount'] }} × {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</span>
                                                        @endif
                                                        @if($booking['childCount'] ?? 0 > 0)
                                                            <span class="badge bg-warning" style="font-size: 0.6rem;">{{ $booking['childCount'] }} × {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</span>
                                                        @endif
                                                        @if($booking['seniorCount'] ?? 0 > 0)
                                                            <span class="badge bg-info" style="font-size: 0.6rem;">{{ $booking['seniorCount'] }} × {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Total</small>
                                                    <div class="fw-bold" style="font-size: 0.9rem; color: #fd9853;">SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(isset($booking['ticket_details']['description']) && !empty($booking['ticket_details']['description']))
                                        <!-- Ticket Description -->
                                        <div class="bg-white rounded p-1 mt-1 border-start border-3" style="border-color: #fd9853 !important;">
                                            <small class="fw-bold text-dark d-block" style="font-size: 0.75rem;">Ticket Info</small>
                                            <div class="text-muted" style="font-size: 0.7rem;">{!! $booking['ticket_details']['description'] !!}</div>
                                        </div>
                                        @endif
                                    </div>
                                    @endif

                                    <!-- Transfer Options -->
                                    @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-car-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Details</h6>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                <span class="badge bg-primary" style="font-size: 0.65rem;">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Way</small>
                                                                <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                            </div>
                                                            @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                    <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['pickup_location_name'] }}
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']))
                                                            <div class="row g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">
                                                                        <i class="ri-car-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] ?? 'N/A' }}
                                                                    </div>
                                                                    @if(isset($booking['transfer_options']['vehicle_details']['vehicle_type']))
                                                                        <small class="text-muted" style="font-size: 0.6rem;">Type: {{ $booking['transfer_options']['vehicle_details']['vehicle_type'] }}</small>
                                                                    @endif
                                                                </div>
                                                                @if(isset($booking['transfer_options']['vehicle_details']['seating_capacity']))
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Capacity</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_details']['seating_capacity'] }} passengers</div>
                                                                </div>
                                                                @endif
                                                                @if(isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0)
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                    <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format((float)($booking['transfer_options']['cost'] ?? 0), 2) }}</div>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle ID</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Guide Options -->
                                    @if(isset($booking['guide_options']) && is_array($booking['guide_options']) && isset($booking['guide_options']['guide_required']) && ($booking['guide_options']['guide_required'] === true || $booking['guide_options']['guide_required'] === 'true' || $booking['guide_options']['guide_required'] === 'Yes'))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-user-star-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Details</h6>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <div class="row g-1">
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                                <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                    <i class="ri-user-line me-1"></i>{{ $booking['guide_options']['guide_name'] ?? 'N/A' }}
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Duration</small>
                                                                <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['guide_options']['package_hours'] ?? 'N/A' }} Hrs</span>
                                                            </div>
                                                            @if(isset($booking['guide_options']['pickup_time']) && !empty($booking['guide_options']['pickup_time']))
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Time</small>
                                                                <div class="fw-medium text-success" style="font-size: 0.75rem;">
                                                                    @php
                                                                        $displayPickupTime = $booking['guide_options']['pickup_time'] ?? '';
                                                                        if (str_contains($displayPickupTime, ' - ')) {
                                                                            $parts = explode(' - ', $displayPickupTime);
                                                                            $displayPickupTime = $parts[0];
                                                                        }
                                                                        $formattedPickupTime = $displayPickupTime;
                                                                        if (!empty($displayPickupTime)) {
                                                                            try {
                                                                                $timeObj = \Carbon\Carbon::createFromFormat('H:i', $displayPickupTime);
                                                                                $formattedPickupTime = $timeObj->format('h:i A');
                                                                            } catch (\Exception $e) {
                                                                                try {
                                                                                    $timeObj = \Carbon\Carbon::createFromFormat('h:i A', $displayPickupTime);
                                                                                    $formattedPickupTime = $timeObj->format('h:i A');
                                                                                } catch (\Exception $e2) {
                                                                                    try {
                                                                                        $timeObj = \Carbon\Carbon::parse($displayPickupTime);
                                                                                        $formattedPickupTime = $timeObj->format('h:i A');
                                                                                    } catch (\Exception $e3) {}
                                                                                }
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <i class="ri-time-line me-1"></i>{{ $formattedPickupTime }}
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                                <div class="fw-medium text-primary" style="font-size: 0.75rem;">SGD {{ number_format($booking['guide_options']['base_price'] ?? 0, 2) }}</div>
                                                            </div>
                                                            @if(isset($booking['guide_options']['surcharge']) && $booking['guide_options']['surcharge'] > 0)
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                                <div class="fw-medium text-warning" style="font-size: 0.75rem;">SGD {{ number_format($booking['guide_options']['surcharge'], 2) }}</div>
                                                            </div>
                                                            @endif
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Total Guide Cost</small>
                                                                <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($booking['guide_options']['total_price'] ?? 0, 2) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                            @php $actualBookingIndex++; @endphp
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="ri-building-2-line text-muted" style="font-size: 1.5rem;"></i>
                        </div>
                        <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Attraction Data Available</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Attraction services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <!-- Compact Footer -->
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Restaurant Details Modal -->
@if(isset($svc['restaurant']) && $svc['restaurant'] > 0)
<div class="modal fade" id="restaurantDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="restaurantDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <!-- Compact Header -->
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h5 class="mb-0 fw-bold">
                            <i class="ri-restaurant-2-line me-2"></i>Restaurant Bookings
                        </h5>
                        <small class="opacity-90">Tour #{{ $tour->tour_id }}</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" aria-label="Close"></button>
                </div>
            </div>
            
            <div class="modal-body p-3" style="background-color: #f8f9fa;">
                @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                    @foreach($serviceData['restaurant'] as $index => $restaurantOrder)
                    @php
                        $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data;
                    @endphp
                    
                    @if(is_array($restaurantData))
                        @php $actualBookingIndex = 0; @endphp
                        @foreach($restaurantData as $originalKey => $booking)
                            @php $bookingIndex = $actualBookingIndex; @endphp
                            <div class="card mb-3 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; border-left: 4px solid #fd79a8 !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-2 px-3" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%);">
                                    <div class="row align-items-center g-2">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white">
                                                <i class="ri-restaurant-2-line me-1"></i>{{ $booking['restaurantName'] ?? 'Restaurant Booking' }}
                                            </h6>
                                            <small class="text-white opacity-90">{{ ucfirst($booking['mealType'] ?? 'Meal') }} • {{ $booking['mealSpecificType'] ?? 'Standard' }}</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-3 py-2" style="font-size: 0.95rem;">
                                                SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-3" style="background-color: #ffffff;">
                                    <!-- Customer & Reservation Details -->
                                    <div class="row mb-3 g-3">
                                      
                                        <div class="col-md-12">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-line text-white" style="font-size: 0.9rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Reservation Details</h6>
                                                </div>
                                                <div class="mb-1">
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Dining Date</small>
                                                    <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                </div>
                                                <div class="mb-1">
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Dining Time</small>
                                                    <div class="fw-medium text-primary" style="font-size: 0.85rem;">{{ $booking['visitTime'] ?? 'TBC' }}</div>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #fd79a8 !important;">
                                                            <div class="fw-bold text-success" style="font-size: 1rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #fd79a8 !important;">
                                                            <div class="fw-bold text-warning" style="font-size: 1rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); color: white; font-size: 0.8rem; padding: 2px 6px;">
                                                        Party: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                      <!-- Menu & Meal Details -->
                                      @if(isset($booking['MealDescription']) && is_array($booking['MealDescription']))
                                    <div class="bg-light rounded p-2 mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-restaurant-line text-white" style="font-size: 0.9rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Menu Items ({{ count($booking['MealDescription']) }})</h6>
                                        </div>
                                        
                                        @foreach($booking['MealDescription'] as $index => $meal)
                                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 3px solid #fd79a8 !important;">
                                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%);">
                                                    <div class="row align-items-center g-1">
                                                        <div class="col-md-8">
                                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                                <i class="ri-restaurant-2-line me-1"></i>Menu Item {{ $index + 1 }}
                                                            </h6>
                                                            <div class="d-flex gap-1 mt-1">
                                                                <span class="badge bg-white bg-opacity-20 text-white border-0 px-2 py-0" style="font-size: 0.65rem;">
                                                                    {{ $meal['category'] ?? 'Category' }}
                                                                </span>
                                                                <span class="badge bg-white bg-opacity-20 text-white border-0 px-2 py-0" style="font-size: 0.65rem;">
                                                                    {{ $meal['item_type'] ?? 'Type' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 text-end">
                                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                                SGD {{ number_format((float)($meal['price'] ?? 0), 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="card-body p-2" style="background-color: #ffffff;">
                                                    <div class="row g-2">
                                                        <div class="col-md-4">
                                                            <div class="bg-light rounded p-1 text-center">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Quantity</small>
                                                                <div class="fw-bold text-primary" style="font-size: 1.2rem;">{{ $meal['quantity'] ?? 1 }}</div>
                                                                <small class="text-muted" style="font-size: 0.65rem;">{{ ($meal['quantity'] ?? 1) == 1 ? 'item' : 'items' }}</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="bg-light rounded p-1 text-center">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Unit Price</small>
                                                                <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format((float)($meal['price'] ?? 0), 2) }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="bg-light rounded p-1 text-center" style="background: linear-gradient(135deg, rgba(253,121,168,0.1) 0%, rgba(253,203,110,0.1) 100%) !important;">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Subtotal</small>
                                                                <div class="fw-bold" style="font-size: 1rem; color: #fd79a8;">SGD {{ number_format((float)(($meal['price'] ?? 0) * ($meal['quantity'] ?? 1)), 2) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        <!-- Order Summary -->
                                        <div class="bg-light rounded p-2 mt-2">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                                                        <i class="ri-receipt-line me-1"></i>Order Summary
                                                    </h6>
                                                    <small class="text-muted" style="font-size: 0.7rem;">
                                                        {{ count($booking['MealDescription']) }} item(s) • {{ $booking['mealType'] ?? 'Meal' }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Grand Total</small>
                                                    <div class="fw-bold" style="font-size: 1.2rem; color: #fd79a8;">SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Transfer Options -->
                                    @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-car-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Transfer Details</h6>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="bg-light rounded p-3 h-100">
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Transfer Type</small>
                                                            <div class="fw-medium">
                                                                <span class="badge bg-primary">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Transfer Way</small>
                                                            <div class="fw-medium">
                                                                <span class="badge bg-info">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                        <div class="mb-0">
                                                            <small class="text-muted d-block">Pickup Location</small>
                                                            <div class="fw-medium text-primary">
                                                                <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['pickup_location_name'] }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <div class="bg-light rounded p-3 h-100">
                                                        @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']))
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Vehicle</small>
                                                                <div class="fw-medium">
                                                                    <i class="ri-car-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] ?? 'N/A' }}
                                                                </div>
                                                                @if(isset($booking['transfer_options']['vehicle_details']['vehicle_type']))
                                                                    <small class="text-muted">Type: {{ $booking['transfer_options']['vehicle_details']['vehicle_type'] }}</small>
                                                                @endif
                                                            </div>
                                                            @if(isset($booking['transfer_options']['vehicle_details']['seating_capacity']))
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Seating Capacity</small>
                                                                <div class="fw-medium">
                                                                    <i class="ri-user-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['seating_capacity'] }} passengers
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Vehicle ID</small>
                                                                <div class="fw-medium">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                            </div>
                                                        @endif
                                                        
                                                        @if(isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0)
                                                        <div class="mb-0">
                                                            <small class="text-muted d-block">Transfer Cost</small>
                                                            <div class="fs-5 fw-bold text-success">
                                                                <i class="ri-money-dollar-circle-line me-1"></i>SGD {{ number_format((float)($booking['transfer_options']['cost'] ?? 0), 2) }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Guide Options -->
                                    @if(isset($booking['guide_options']) && is_array($booking['guide_options']) && (isset($booking['guide_options']['guideId']) || isset($booking['guide_options']['guide_id']) || isset($booking['guide_options']['guideName']) || isset($booking['guide_options']['guide_name']) || isset($booking['guide_options']['name'])))
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-info rounded-circle p-2 me-3">
                                                    <i class="ri-user-voice-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Guide Details</h6>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="bg-light rounded p-3 h-100">
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Guide Name</small>
                                                            <div class="fw-medium">
                                                                <i class="ri-user-voice-line me-1"></i>{{ $booking['guide_options']['guideName'] ?? $booking['guide_options']['guide_name'] ?? $booking['guide_options']['name'] ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Service Type</small>
                                                            <div class="fw-medium">
                                                                <span class="badge bg-info">{{ $booking['guide_options']['serviceType'] ?? $booking['guide_options']['service_type'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Language</small>
                                                            <div class="fw-medium">
                                                                <span class="badge bg-success">{{ $booking['guide_options']['language'] ?? $booking['guide_options']['languages'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        @if(isset($booking['guide_options']['tourActivity']) || isset($booking['guide_options']['tour_activity']) || isset($booking['guide_options']['Activity']))
                                                        <div class="mb-0">
                                                            <small class="text-muted d-block">Tour Activity</small>
                                                            <div class="fw-medium text-primary">
                                                                <i class="ri-map-pin-line me-1"></i>{{ $booking['guide_options']['tourActivity'] ?? $booking['guide_options']['tour_activity'] ?? $booking['guide_options']['Activity'] ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <div class="bg-light rounded p-3 h-100">
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Service Hours</small>
                                                            <div class="fw-medium">
                                                                <i class="ri-time-line me-1"></i>{{ $booking['guide_options']['hours'] ?? $booking['guide_options']['service_hours'] ?? 'N/A' }} Hours
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Group Size</small>
                                                            <div class="row g-1">
                                                                <div class="col-6">
                                                                    <div class="bg-white rounded p-1 text-center border">
                                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['guide_options']['adultsQty'] ?? $booking['guide_options']['adults_qty'] ?? 0 }}</div>
                                                                        <small class="text-muted" style="font-size: 0.65rem;">Adults</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="bg-white rounded p-1 text-center border">
                                                                        <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['guide_options']['childQty'] ?? $booking['guide_options']['child_qty'] ?? 0 }}</div>
                                                                        <small class="text-muted" style="font-size: 0.65rem;">Children</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(isset($booking['guide_options']['cost']) && $booking['guide_options']['cost'] > 0)
                                                        <div class="mb-0">
                                                            <small class="text-muted d-block">Guide Cost</small>
                                                            <div class="fs-5 fw-bold text-info">
                                                                <i class="ri-money-dollar-circle-line me-1"></i>SGD {{ number_format((float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0), 2) }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Pricing Overview -->
                                    <div class="bg-light rounded p-2 mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.9rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Pricing Overview</h6>
                                        </div>
                                        @php
                                            $vehicleCost = 0;
                                            if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                                                $vehicleCost = (float) $booking['transfer_options']['cost'];
                                            }
                                            $guideCost = 0;
                                            if (isset($booking['guide_options']) && is_array($booking['guide_options'])) {
                                                $guideCostValue = $booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0;
                                                if ($guideCostValue > 0) {
                                                    $guideCost = (float) $guideCostValue;
                                                }
                                            }
                                            $mealPrice = $booking['mealPrice'] ?? $booking['totalPrice'] ?? 0;
                                            $totalPrice = $mealPrice + $vehicleCost + $guideCost;
                                            $hasTransfer = $vehicleCost > 0;
                                            $hasGuide = $guideCost > 0;
                                        @endphp
                                        <div class="row g-2">
                                            <div class="col-md-{{ $hasTransfer && $hasGuide ? '3' : ($hasTransfer || $hasGuide ? '4' : '6') }}">
                                                <div class="text-center p-2 border rounded bg-white" style="border-color: #28a745 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Meal Price</small>
                                                    <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format($mealPrice, 2) }}</div>
                                                </div>
                                            </div>
                                            @if($hasTransfer)
                                            <div class="col-md-{{ $hasGuide ? '3' : '4' }}">
                                                <div class="text-center p-2 border rounded bg-white" style="border-color: #17a2b8 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle Price</small>
                                                    <div class="fw-bold text-info" style="font-size: 0.8rem;">SGD {{ number_format($vehicleCost, 2) }}</div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($hasGuide)
                                            <div class="col-md-{{ $hasTransfer ? '3' : '4' }}">
                                                <div class="text-center p-2 border rounded bg-white" style="border-color: #00cec9 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Guide Price</small>
                                                    <div class="fw-bold" style="font-size: 0.8rem; color: #00cec9;">SGD {{ number_format($guideCost, 2) }}</div>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="col-md-{{ $hasTransfer && $hasGuide ? '3' : ($hasTransfer || $hasGuide ? '4' : '6') }}">
                                                <div class="text-center p-2 border rounded bg-white" style="border-color: #fd79a8 !important; background: linear-gradient(135deg, rgba(253,121,168,0.1) 0%, rgba(253,203,110,0.1) 100%) !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Grand Total</small>
                                                    <div class="fw-bold" style="font-size: 1.1rem; color: #fd79a8;">SGD {{ number_format($totalPrice, 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-center">
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i class="ri-information-line me-1"></i>
                                                Total Price includes Restaurant Price@if($hasTransfer) + Vehicle Price@endif@if($hasGuide) + Guide Price@endif
                                            </small>
                                        </div>
                                    </div>

                                  

                               
                                </div>
                            </div>
                            @php $actualBookingIndex++; @endphp
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-restaurant-2-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Restaurant Data Available</h4>
                        <p class="text-muted mb-4">Restaurant services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <!-- Compact Footer with Buttons in One Row -->
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif


<!-- Guide Details Modal -->
@if(isset($svc['guide']) && $svc['guide'] > 0)
<div class="modal fade" id="guideDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="guideDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <!-- Compact Header -->
            <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-user-voice-line me-1" style="font-size: 0.9rem;"></i>Guide Bookings - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['guide']) && count($serviceData['guide']) > 0)
                    @foreach($serviceData['guide'] as $index => $guideOrder)
                    @php
                        $guideData = is_string($guideOrder->data) ? json_decode($guideOrder->data, true) : $guideOrder->data;
                    @endphp
                    
                    @if(is_array($guideData))
                        @foreach($guideData as $booking)
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #00cec9 !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #00cec9 0%, #55a3ff 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-user-voice-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['guide_name'] ?? 'Guide Booking' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">Guide Service • {{ $booking['hours'] ?? 'N/A' }}H</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2" style="background-color: #ffffff;">
                                    <!-- Guide Information & Image -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-8">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-user-voice-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Information</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                        <div class="fw-medium text-success" style="font-size: 0.75rem;">SGD {{ number_format((float)($booking['basePrice'] ?? 0), 2) }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                        <div class="fw-medium text-warning" style="font-size: 0.75rem;">SGD {{ number_format((float)($booking['surcharge'] ?? 0), 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @if(isset($booking['image']))
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <img src="{{ $booking['image'] }}" 
                                                         alt="{{ $booking['guide_name'] ?? 'Guide' }}" 
                                                         class="rounded-circle shadow-sm" 
                                                         style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #00cec9;">
                                                </div>
                                            @else
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef;">
                                                        <i class="ri-user-voice-line text-muted" style="font-size: 2rem;"></i>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Service Schedule & Group Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Duration</small>
                                                        <span class="badge" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); color: white; font-size: 0.65rem; padding: 2px 4px;">{{ $booking['hours'] ?? 'N/A' }}H</span>
                                                    </div>
                                                </div>
                                                @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                <div class="bg-white rounded p-1 mt-1">
                                                    <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Night Service</small>
                                                    <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #00cec9 !important;">
                                                            <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #00cec9 !important;">
                                                            <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                        Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pricing Breakdown -->
                                    <div class="bg-light rounded p-1 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing Breakdown</h6>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-md-4">
                                                <div class="text-center p-1 border rounded bg-white" style="border-color: #28a745 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                    <div class="fw-bold text-success" style="font-size: 0.8rem;">SGD {{ number_format((float)($booking['basePrice'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center p-1 border rounded bg-white" style="border-color: #ffc107 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                    <div class="fw-bold text-warning" style="font-size: 0.8rem;">SGD {{ number_format((float)($booking['surcharge'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center p-1 border rounded bg-white" style="border-color: #00cec9 !important; background: linear-gradient(135deg, rgba(0,206,201,0.1) 0%, rgba(85,163,255,0.1) 100%) !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Total Amount</small>
                                                    <div class="fw-bold" style="font-size: 0.9rem; color: #00cec9;">SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-user-voice-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Guide Data Available</h4>
                        <p class="text-muted mb-4">Guide services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <!-- Compact Footer -->
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Entry Port (Arrival) Details Modal -->
@if(isset($svc['entry_port']) && $svc['entry_port'] > 0)
<div class="modal fade" id="entry_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="entry_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header p-2 border-0" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-flight-land-line me-1" style="font-size: 0.9rem;"></i>Arrival Transfer - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['entry_port']) && count($serviceData['entry_port']) > 0)
                    @foreach($serviceData['entry_port'] as $index => $entryOrder)
                    @php
                        $entryData = is_string($entryOrder->data) ? json_decode($entryOrder->data, true) : $entryOrder->data;
                    @endphp
                    
                    @if(is_array($entryData))
                        @foreach($entryData as $booking)
                            @php
                                $entryTransferPrice = (float)($booking['totalPrice'] ?? 0);
                                $entryGuidePrice = 0;
                                if (isset($booking['guide_options']) && is_array($booking['guide_options'])) {
                                    $entryGuidePrice = (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0);
                                }
                                $entryCardTotal = $entryTransferPrice + $entryGuidePrice;
                            @endphp
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #00b894 !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #00b894 0%, #55a3ff 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">Arrival {{ $index + 1 }} • {{ ucfirst($booking['type'] ?? 'Standard') }}@if($entryGuidePrice > 0) • With Guide @endif</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                SGD {{ number_format($entryCardTotal, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2" style="background-color: #ffffff;">
                                    <!-- Service Schedule & Group Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                        <div><span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer</small>
                                                        <div><span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">Arrival</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #00b894 !important;">
                                                            <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #00b894 !important;">
                                                            <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                        Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Route Information -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-route-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Information</h6>
                                        </div>
                                        <div class="row g-1 mb-1">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup</small>
                                                    <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                        <i class="ri-map-pin-line text-success me-1" style="font-size: 0.7rem;"></i>
                                                        <span class="text-truncate">{{ $booking['entrypickup'] ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Dropoff</small>
                                                    <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                        <i class="ri-map-pin-2-line text-danger me-1" style="font-size: 0.7rem;"></i>
                                                        <span class="text-truncate">{{ $booking['entrydropoff'] ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Compact Route Direction Visual -->
                                        <div class="d-flex align-items-center justify-content-center p-1 bg-white rounded">
                                            <span class="badge bg-success me-1" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['entrypickup'] ?? 'Pickup', 15) }}</span>
                                            <i class="ri-arrow-right-line text-primary mx-1" style="font-size: 0.8rem;"></i>
                                            <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['entrydropoff'] ?? 'Dropoff', 15) }}</span>
                                        </div>
                                    </div>

                                    <!-- Vehicle & Location Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                        <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Details</h6>
                                                </div>
                                                <div class="row g-1 mb-2">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Vehicle</small>
                                                        <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['vehicles_name'] ?? 'N/A' }}">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Service</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <!-- Compact Vehicle Image Display -->
                                                <div class="d-flex justify-content-center align-items-center" style="min-height: 80px; width: 100%; overflow: hidden; position: relative;">
                                                    @if(isset($booking['image']) && $booking['image'])
                                                        <div class="position-relative" style="width: 80px; height: 80px; flex-shrink: 0; overflow: hidden;">
                                                            <img src="{{ $booking['image'] }}" 
                                                                 alt="Vehicle Image" 
                                                                 class="rounded-circle shadow-sm" 
                                                                 style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #00b894; cursor: pointer; display: block; margin: 0; padding: 0; background: #f8f9fa;"
                                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\' style=\'width: 80px; height: 80px; border: 2px solid #e9ecef;\'><i class=\'ri-car-line text-muted\' style=\'font-size: 2rem;\'></i></div>';">
                                                        </div>
                                                    @else
                                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef; flex-shrink: 0;">
                                                            <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Location Information</h6>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                <div class="bg-white rounded p-1 mt-1">
                                                    <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Night Service</small>
                                                    <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Arrival: Price breakdown (Transfer + Guide then Total) -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing</h6>
                                        </div>
                                        <div class="bg-white rounded p-2">
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <span class="text-muted" style="font-size: 0.8rem;">Transfer</span>
                                                <span class="fw-semibold text-dark">SGD {{ number_format($entryTransferPrice, 2) }}</span>
                                            </div>
                                            @if($entryGuidePrice > 0)
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <span class="text-muted" style="font-size: 0.8rem;">Guide</span>
                                                <span class="fw-semibold text-info">SGD {{ number_format($entryGuidePrice, 2) }}</span>
                                            </div>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center py-2 mt-1">
                                                <span class="fw-bold text-dark" style="font-size: 0.9rem;">Total</span>
                                                <span class="fw-bold text-success" style="font-size: 1rem;">SGD {{ number_format($entryCardTotal, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $hasEntryGuide = isset($booking['guide_options']) && is_array($booking['guide_options']) && (
                                            !empty($booking['guide_options']['guide_required']) ||
                                            !empty($booking['guide_options']['guideName']) ||
                                            !empty($booking['guide_options']['guide_name']) ||
                                            !empty($booking['guide_options']['name']) ||
                                            (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['sell'] ?? 0) > 0
                                        );
                                    @endphp
                                    @if($hasEntryGuide)
                                    <!-- Guide Details (Arrival) -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-user-voice-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Details</h6>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_options']['guideName'] ?? $booking['guide_options']['guide_name'] ?? $booking['guide_options']['name'] ?? 'N/A' }}</div>
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">Service</small>
                                                    <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['guide_options']['serviceType'] ?? $booking['guide_options']['service_type'] ?? 'N/A' }}</span>
                                                    <span class="badge bg-success ms-1" style="font-size: 0.65rem;">{{ $booking['guide_options']['language'] ?? $booking['guide_options']['languages'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Hours / Activity</small>
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_options']['hours'] ?? $booking['guide_options']['service_hours'] ?? 'N/A' }} H</div>
                                                    @php $arrivalGuideCost = (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0); @endphp
                                                    @if($arrivalGuideCost > 0)
                                                    <div class="fw-bold text-success mt-1" style="font-size: 0.85rem;">SGD {{ number_format($arrivalGuideCost, 2) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100%;">
                            <i class="ri-flight-land-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Arrival Transfer Data Available</h4>
                        <p class="text-muted mb-4">Entry port services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Exit Port (Departure) Details Modal -->
@if(isset($svc['exit_port']) && $svc['exit_port'] > 0)
<div class="modal fade" id="exit_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="exit_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header p-2 border-0" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-flight-takeoff-line me-1" style="font-size: 0.9rem;"></i>Departure Transfer - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['exit_port']) && count($serviceData['exit_port']) > 0)
                    @foreach($serviceData['exit_port'] as $index => $exitOrder)
                    @php
                        $exitData = is_string($exitOrder->data) ? json_decode($exitOrder->data, true) : $exitOrder->data;
                    @endphp
                    
                    @if(is_array($exitData))
                        @foreach($exitData as $bookingIndex => $booking)
                            @php
                                $exitTransferPrice = (float)($booking['totalPrice'] ?? 0);
                                $exitGuidePrice = 0;
                                if (isset($booking['guide_options']) && is_array($booking['guide_options'])) {
                                    $exitGuidePrice = (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0);
                                }
                                $exitCardTotal = $exitTransferPrice + $exitGuidePrice;
                                $tourTypeIs1 = isset($tour->tour_type) && ((int)$tour->tour_type === 1 || $tour->tour_type == 1 || $tour->tour_type === '1');
                                $exitPickup = $tourTypeIs1 ? ($booking['pickupLocation'] ?? $booking['exitpickup'] ?? 'N/A') : ($booking['exitpickup'] ?? 'N/A');
                                $exitDropoff = $tourTypeIs1 ? ($booking['dropoffLocation'] ?? $booking['exitdropoff'] ?? 'N/A') : ($booking['exitdropoff'] ?? 'N/A');
                            @endphp
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #fd7f6f !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd7f6f 0%, #feb47b 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">Departure {{ $index + 1 }} • {{ ucfirst($booking['type'] ?? 'Standard') }}@if($exitGuidePrice > 0) • With Guide @endif</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                SGD {{ number_format($exitCardTotal, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2" style="background-color: #ffffff;">
                                    <!-- Service Schedule & Group Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                        <div><span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer</small>
                                                        <div><span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">Departure</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #fd7f6f !important;">
                                                            <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #fd7f6f !important;">
                                                            <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                        Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Route Information (tour_type=1: pickupLocation/dropoffLocation) -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-route-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Information</h6>
                                        </div>
                                        <div class="row g-1 mb-1">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup</small>
                                                    <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                        <i class="ri-map-pin-line text-success me-1" style="font-size: 0.7rem;"></i>
                                                        <span class="text-truncate">{{ $exitPickup }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Dropoff</small>
                                                    <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                        <i class="ri-map-pin-2-line text-danger me-1" style="font-size: 0.7rem;"></i>
                                                        <span class="text-truncate">{{ $exitDropoff }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Compact Route Direction Visual -->
                                        <div class="d-flex align-items-center justify-content-center p-1 bg-white rounded">
                                            <span class="badge bg-success me-1" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($exitPickup, 15) }}</span>
                                            <i class="ri-arrow-right-line text-primary mx-1" style="font-size: 0.8rem;"></i>
                                            <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($exitDropoff, 15) }}</span>
                                        </div>
                                    </div>

                                    <!-- Vehicle & Location Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                        <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Details</h6>
                                                </div>
                                                <div class="row g-1 mb-2">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Vehicle</small>
                                                        <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['vehicles_name'] ?? 'N/A' }}">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Service</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <!-- Compact Vehicle Image Display -->
                                                <div class="d-flex justify-content-center align-items-center" style="min-height: 80px; width: 100%; overflow: hidden; position: relative;">
                                                    @if(isset($booking['image']) && $booking['image'])
                                                        <div class="position-relative" style="width: 80px; height: 80px; flex-shrink: 0; overflow: hidden;">
                                                            <img src="{{ $booking['image'] }}" 
                                                                 alt="Vehicle Image" 
                                                                 class="rounded-circle shadow-sm" 
                                                                 style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #fd7f6f; cursor: pointer; display: block; margin: 0; padding: 0; background: #f8f9fa;"
                                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\' style=\'width: 80px; height: 80px; border: 2px solid #e9ecef;\'><i class=\'ri-car-line text-muted\' style=\'font-size: 2rem;\'></i></div>';">
                                                        </div>
                                                    @else
                                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef; flex-shrink: 0;">
                                                            <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Location Information</h6>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Departure: Price breakdown (Transfer + Guide then Total) -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing</h6>
                                        </div>
                                        <div class="bg-white rounded p-2">
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <span class="text-muted" style="font-size: 0.8rem;">Transfer</span>
                                                <span class="fw-semibold text-dark">SGD {{ number_format($exitTransferPrice, 2) }}</span>
                                            </div>
                                            @if($exitGuidePrice > 0)
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <span class="text-muted" style="font-size: 0.8rem;">Guide</span>
                                                <span class="fw-semibold text-info">SGD {{ number_format($exitGuidePrice, 2) }}</span>
                                            </div>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center py-2 mt-1">
                                                <span class="fw-bold text-dark" style="font-size: 0.9rem;">Total</span>
                                                <span class="fw-bold text-success" style="font-size: 1rem;">SGD {{ number_format($exitCardTotal, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $hasExitGuide = isset($booking['guide_options']) && is_array($booking['guide_options']) && (
                                            !empty($booking['guide_options']['guide_required']) ||
                                            !empty($booking['guide_options']['guideName']) ||
                                            !empty($booking['guide_options']['guide_name']) ||
                                            !empty($booking['guide_options']['name']) ||
                                            (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['sell'] ?? 0) > 0
                                        );
                                    @endphp
                                    @if($hasExitGuide)
                                    <!-- Guide Details (Departure) -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-user-voice-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Details</h6>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_options']['guideName'] ?? $booking['guide_options']['guide_name'] ?? $booking['guide_options']['name'] ?? 'N/A' }}</div>
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">Service</small>
                                                    <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['guide_options']['serviceType'] ?? $booking['guide_options']['service_type'] ?? 'N/A' }}</span>
                                                    <span class="badge bg-success ms-1" style="font-size: 0.65rem;">{{ $booking['guide_options']['language'] ?? $booking['guide_options']['languages'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Hours / Activity</small>
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_options']['hours'] ?? $booking['guide_options']['service_hours'] ?? 'N/A' }} H</div>
                                                    @php $depGuideCost = (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0); @endphp
                                                    @if($depGuideCost > 0)
                                                    <div class="fw-bold text-success mt-1" style="font-size: 0.85rem;">SGD {{ number_format($depGuideCost, 2) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100%;">
                            <i class="ri-flight-takeoff-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Departure Transfer Data Available</h4>
                        <p class="text-muted mb-4">Exit port services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Travel Hourly Details Modal -->
@if(isset($svc['travel_hourly']) && $svc['travel_hourly'] > 0)
    <div class="modal fade" id="travel_hourlyDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_hourlyModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                @php
                    $firstOrder = $serviceData['travel_hourly'][0] ?? null;
                    $firstBookingData = null;
                    if ($firstOrder) {
                        $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                        $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                    }
                @endphp
                
                <!-- Compact Modal Header -->
                <div class="modal-header p-2 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-time-line me-1" style="font-size: 0.9rem;"></i>Local-Tour Hourly - Tour #{{ $tour->tour_id }} • {{ $firstBookingData['city'] ?? 'Location not specified' }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>

                <!-- Compact Modal Body -->
                <div class="modal-body p-2" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['travel_hourly']) && count($serviceData['travel_hourly']) > 0)
                        @foreach($serviceData['travel_hourly'] as $index => $hourlyOrder)
                            @php
                                $hourlyData = is_string($hourlyOrder->data) ? json_decode($hourlyOrder->data, true) : $hourlyOrder->data;
                            @endphp
                            
                            @if(is_array($hourlyData))
                                @php $actualBookingIndex = 0; @endphp
                                @foreach($hourlyData as $bookingIndex => $booking)
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-2">
                                    @endif
                            
                                    <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                        <!-- Compact Card Header -->
                                        <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                            <div class="row align-items-center g-1">
                                                <div class="col-md-8">
                                                    <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                        <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Hourly Tour Booking' }}
                                                    </h6>
                                                    <small class="text-white opacity-90" style="font-size: 0.7rem;">Hourly Tour {{ $index + 1 }} • {{ ucfirst($booking['type'] ?? 'Standard') }}</small>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                        SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body p-2" style="background-color: #ffffff;">
                                            <!-- Service Schedule & Group Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Hours</small>
                                                                <span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">{{ $booking['selectedHours'] ?? 'N/A' }}H</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                <span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                        </div>
                                                        <div class="row g-1 mb-1">
                                                            <div class="col-6 text-center">
                                                                <div class="bg-white rounded p-1 border" style="border-color: #667eea !important;">
                                                                    <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 text-center">
                                                                <div class="bg-white rounded p-1 border" style="border-color: #667eea !important;">
                                                                    <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-center">
                                                            <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                                Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                            </span>
                                                        </div>
                                                        @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                        <div class="bg-white rounded p-1 mt-1">
                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Night Service</small>
                                                            <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pickup Location & Vehicle Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pickup Location</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Point</small>
                                                                <div class="fw-medium text-truncate" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                                <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Details</h6>
                                                        </div>
                                                        <div class="row g-1 mb-2">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Vehicle</small>
                                                                <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['vehicles_name'] ?? 'N/A' }}">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Service</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                        <!-- Compact Vehicle Image Display -->
                                                        <div class="d-flex justify-content-center align-items-center" style="min-height: 80px; width: 100%; overflow: hidden; position: relative;">
                                                            @if(isset($booking['image']) && $booking['image'])
                                                                <div class="position-relative" style="width: 80px; height: 80px; flex-shrink: 0; overflow: hidden;">
                                                                    <img src="{{ $booking['image'] }}" 
                                                                         alt="Vehicle Image" 
                                                                         class="rounded-circle shadow-sm" 
                                                                         style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #667eea; cursor: pointer; display: block; margin: 0; padding: 0; background: #f8f9fa;"
                                                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\' style=\'width: 80px; height: 80px; border: 2px solid #e9ecef;\'><i class=\'ri-car-line text-muted\' style=\'font-size: 2rem;\'></i></div>';">
                                                                </div>
                                                            @else
                                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef; flex-shrink: 0;">
                                                                    <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php $actualBookingIndex++; @endphp
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="ri-time-line text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-muted mb-0" style="font-size: 0.9rem;">No hourly tour data available</h6>
                        </div>
                    @endif
                </div>

                <!-- Compact Modal Footer -->
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                    <div class="d-flex gap-2 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Travel Point Details Modal -->
@if(isset($svc['travel_point']) && $svc['travel_point'] > 0)
    <div class="modal fade" id="travel_pointDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_pointModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                @php
                    $firstOrder = $serviceData['travel_point'][0] ?? null;
                    $firstBookingData = null;
                    $headerFromZone = 'N/A';
                    $headerToZone = 'N/A';
                    
                    if ($firstOrder) {
                        $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                        $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                        
                        // Get zone names for header
                        if(isset($firstBookingData['from_zone_id']) && $firstBookingData['from_zone_id']) {
                            $fromZone = \DB::table('zones')->where('zone_id', $firstBookingData['from_zone_id'])->first();
                            $headerFromZone = $fromZone ? $fromZone->zone_type : 'Zone ' . $firstBookingData['from_zone_id'];
                        }
                        
                        if(isset($firstBookingData['to_zone_id']) && $firstBookingData['to_zone_id']) {
                            $toZone = \DB::table('zones')->where('zone_id', $firstBookingData['to_zone_id'])->first();
                            $headerToZone = $toZone ? $toZone->zone_type : 'Zone ' . $firstBookingData['to_zone_id'];
                        }
                    }
                @endphp
                
                <!-- Modal Header -->
                <div class="modal-header p-2 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-route-line me-1" style="font-size: 0.9rem;"></i>Local-Tour Point to Point - Tour #{{ $tour->tour_id }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-2" style="background: #f8fafc;">
                    @if(isset($serviceData['travel_point']) && count($serviceData['travel_point']) > 0)
                        @foreach($serviceData['travel_point'] as $index => $pointOrder)
                            @php
                                $pointData = is_string($pointOrder->data) ? json_decode($pointOrder->data, true) : $pointOrder->data;
                            @endphp
                            
                            @if(is_array($pointData))
                                @foreach($pointData as $bookingIndex => $booking)
                                    @php
                                        // Fetch zone information
                                        $fromZoneName = 'N/A';
                                        $toZoneName = 'N/A';
                                        
                                        if(isset($booking['from_zone_id']) && $booking['from_zone_id']) {
                                            $fromZone = \DB::table('zones')->where('zone_id', $booking['from_zone_id'])->first();
                                            $fromZoneName = $fromZone ? $fromZone->zone_type : 'Zone ' . $booking['from_zone_id'];
                                        }
                                        
                                        if(isset($booking['to_zone_id']) && $booking['to_zone_id']) {
                                            $toZone = \DB::table('zones')->where('zone_id', $booking['to_zone_id'])->first();
                                            $toZoneName = $toZone ? $toZone->zone_type : 'Zone ' . $booking['to_zone_id'];
                                        }
                                    @endphp
                                    
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-2">
                                    @endif
                            
                                    <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                        <!-- Compact Card Header -->
                                        <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                            <div class="row align-items-center g-1">
                                                <div class="col-md-8">
                                                    <h6 class="card-title mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                        <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Point to Point Transfer' }}
                                                    </h6>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                        SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Compact Card Body -->
                                        <div class="card-body p-2" style="background: #ffffff;">
                                            <!-- Transfer Schedule & Group Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-primary rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Schedule</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Distance</small>
                                                                <span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                                <span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ $booking['type'] ?? 'Standard' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-success rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Adults</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Children</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['children'] ?? 0 }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Total Guests</small>
                                                                <span class="badge bg-primary px-1 py-0" style="font-size: 0.65rem;">{{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }}</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Night Service Timing</small>
                                                                <div class="fw-medium text-muted" style="font-size: 0.7rem;">{{ $booking['Night_Start_Time'] ?? 'N/A' }} - {{ $booking['Night_End_Time'] ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Route Details -->
                                            <div class="bg-light rounded p-2 mb-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="bg-warning rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-direction-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Details</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-start">
                                                            <div class="bg-success rounded-circle p-1 me-2 mt-1" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-play-circle-line text-white" style="font-size: 0.75rem;"></i>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                                <small class="text-success" style="font-size: 0.65rem;">Origin</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-start">
                                                            <div class="bg-danger rounded-circle p-1 me-2 mt-1" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-flag-line text-white" style="font-size: 0.75rem;"></i>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Drop-off Location</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                                <small class="text-danger" style="font-size: 0.65rem;">Destination</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Vehicle & Pricing Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-8">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-warning rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Vehicle Details</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle Name</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="bg-light rounded p-2 h-100 d-flex align-items-center justify-content-center">
                                                        @if(isset($booking['image']))
                                                            <img src="{{ $booking['image'] }}" 
                                                                 alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                                 class="img-fluid rounded shadow-sm" 
                                                                 style="height: 80px; width: 100%; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                                <i class="ri-car-line ri-32px text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pricing & Zone Summary -->
                                            <div class="bg-light rounded p-2">
                                                <div class="row g-2 align-items-center">
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Total Price</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">From Zone</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $fromZoneName }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">To Zone</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $toZoneName }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="ri-route-line text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-muted mb-0" style="font-size: 0.9rem;">No point to point transfer data available</h6>
                        </div>
                    @endif
                </div>

                <!-- Compact Modal Footer -->
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                    <div class="d-flex gap-2 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Local Transport Details Modal -->
@if(isset($svc['local_transport']) && $svc['local_transport'] > 0)
    <div class="modal fade" id="local_transportDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="local_transportModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                @php
                    $firstOrder = $serviceData['local_transport'][0] ?? null;
                    $firstBookingData = null;
                    $headerFromZone = 'N/A';
                    $headerToZone = 'N/A';
                    
                    if ($firstOrder) {
                        $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                        $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                        
                        // Get zone names for header
                        if(isset($firstBookingData['from_zone_id']) && $firstBookingData['from_zone_id']) {
                            $fromZone = \DB::table('zones')->where('zone_id', $firstBookingData['from_zone_id'])->first();
                            $headerFromZone = $fromZone ? $fromZone->zone_type : 'Zone ' . $firstBookingData['from_zone_id'];
                        }
                        
                        if(isset($firstBookingData['to_zone_id']) && $firstBookingData['to_zone_id']) {
                            $toZone = \DB::table('zones')->where('zone_id', $firstBookingData['to_zone_id'])->first();
                            $headerToZone = $toZone ? $toZone->zone_type : 'Zone ' . $firstBookingData['to_zone_id'];
                        }
                    }
                @endphp
                
                <!-- Compact Modal Header -->
                <div class="modal-header p-2 border-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-car-line me-1" style="font-size: 0.9rem;"></i>Local Transport - Tour #{{ $tour->tour_id }} • {{ $headerFromZone }} → {{ $headerToZone }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>

                <!-- Compact Modal Body -->
                <div class="modal-body p-2" style="background: #f8fafc;">
                    @if(isset($serviceData['local_transport']) && count($serviceData['local_transport']) > 0)
                        @foreach($serviceData['local_transport'] as $index => $transportOrder)
                            @php
                                $transportData = is_string($transportOrder->data) ? json_decode($transportOrder->data, true) : $transportOrder->data;
                            @endphp
                            
                            @if(is_array($transportData))
                                @foreach($transportData as $bookingIndex => $booking)
                                    @php
                                        // Fetch zone information
                                        $fromZoneName = 'N/A';
                                        $toZoneName = 'N/A';
                                        
                                        if(isset($booking['from_zone_id']) && $booking['from_zone_id']) {
                                            $fromZone = \DB::table('zones')->where('zone_id', $booking['from_zone_id'])->first();
                                            $fromZoneName = $fromZone ? $fromZone->zone_type : 'Zone ' . $booking['from_zone_id'];
                                        }
                                        
                                        if(isset($booking['to_zone_id']) && $booking['to_zone_id']) {
                                            $toZone = \DB::table('zones')->where('zone_id', $booking['to_zone_id'])->first();
                                            $toZoneName = $toZone ? $toZone->zone_type : 'Zone ' . $booking['to_zone_id'];
                                        }
                                    @endphp
                                    
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-2">
                                    @endif
                            
                                    <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; background: #ffffff; border-left: 4px solid #4facfe !important;">
                                        <!-- Compact Card Header -->
                                        <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                            <div class="row align-items-center g-1">
                                                <div class="col-md-8">
                                                    <h6 class="card-title mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                        <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Local Transport Service' }}
                                                    </h6>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                        SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Compact Card Body -->
                                        <div class="card-body p-2" style="background: #ffffff;">
                                            <!-- Service Schedule & Passenger Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-primary rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transport Schedule</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Distance</small>
                                                                <span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                                <span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ $booking['type'] ?? 'Standard' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-success rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Passenger Information</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Adults</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Children</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['children'] ?? 0 }}</div>
                                                            </div>
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Total Guests</small>
                                                                <span class="badge bg-primary px-1 py-0" style="font-size: 0.65rem;">
                                                                    {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passenger{{ (($booking['adults'] ?? 0) + ($booking['children'] ?? 0)) == 1 ? '' : 's' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Route Details -->
                                            <div class="bg-light rounded p-2 mb-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="bg-warning rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-direction-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Details</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-start">
                                                            <div class="bg-success rounded-circle p-1 me-2 mt-1" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-play-circle-line text-white" style="font-size: 0.75rem;"></i>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                                <small class="text-success" style="font-size: 0.65rem;">Origin</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-start">
                                                            <div class="bg-danger rounded-circle p-1 me-2 mt-1" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-flag-line text-white" style="font-size: 0.75rem;"></i>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Drop-off Location</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['dropoffLocation'] ?? 'N/A' }}</div>
                                                                <small class="text-danger" style="font-size: 0.65rem;">Destination</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Vehicle Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-8">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-warning rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Vehicle Details</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle Name</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }} Transport</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="bg-light rounded p-2 h-100 d-flex align-items-center justify-content-center">
                                                        @if(isset($booking['image']))
                                                            <img src="{{ $booking['image'] }}" 
                                                                 alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                                 class="img-fluid rounded shadow-sm" 
                                                                 style="height: 80px; width: 100%; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                                <i class="ri-car-line ri-32px text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pricing & Zone Summary -->
                                            <div class="bg-light rounded p-2">
                                                <div class="row g-2 align-items-center">
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Total Price</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">From Zone</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $fromZoneName }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">To Zone</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $toZoneName }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="ri-car-line text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-muted mb-0" style="font-size: 0.9rem;">No local transport data available</h6>
                        </div>
                    @endif
                </div>

                <!-- Compact Modal Footer -->
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8fafc;">
                    <div class="d-flex gap-2 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Miscellaneous Details Modal -->
@if(isset($svc['miscellaneous']) && $svc['miscellaneous'] > 0)
    <div class="modal fade" id="miscellaneousDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="miscellaneousDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header border-0 py-2 px-3" style="background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-file-list-3-line me-1" style="font-size: 0.9rem;"></i>Miscellaneous - Tour #{{ $tour->tour_id }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('miscellaneous', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>
                <div class="modal-body p-2" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['miscellaneous']) && count($serviceData['miscellaneous']) > 0)
                        @foreach($serviceData['miscellaneous'] as $index => $miscOrder)
                            @php
                                $miscData = is_string($miscOrder->data) ? json_decode($miscOrder->data, true) : $miscOrder->data;
                            @endphp
                            @if(is_array($miscData))
                                @foreach($miscData as $booking)
                                    <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #7c3aed !important;">
                                        <div class="card-header border-0 py-2 px-3" style="background: linear-gradient(90deg, #7c3aed 0%, #a78bfa 100%);">
                                            <div class="row align-items-center g-1">
                                                <div class="col-md-8">
                                                    <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                        <i class="ri-file-list-3-line me-1"></i>{{ $booking['itemName'] ?? 'Miscellaneous Item' }}
                                                    </h6>
                                                    <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</small>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-2" style="background-color: #ffffff;">
                                            <div class="row g-2 mb-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Item</small>
                                                        <div class="fw-medium" style="font-size: 0.85rem;">{{ $booking['itemName'] ?? 'N/A' }}</div>
                                                        <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">Date</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Pax</small>
                                                        <div class="row g-1">
                                                            <div class="col-4 text-center">
                                                                <div class="bg-white rounded p-1 border">
                                                                    <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adultsQty'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-4 text-center">
                                                                <div class="bg-white rounded p-1 border">
                                                                    <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['childQty'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.6rem;">Child</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-4 text-center">
                                                                <div class="bg-white rounded p-1 border">
                                                                    <div class="fw-bold text-info" style="font-size: 0.9rem;">{{ $booking['infantQty'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.6rem;">Infant</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-light rounded p-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="ri-money-dollar-circle-line text-primary me-2" style="font-size: 0.9rem;"></i>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing</h6>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center py-1">
                                                    <span class="text-muted" style="font-size: 0.8rem;">Total</span>
                                                    <span class="fw-bold text-success" style="font-size: 1rem;">SGD {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</span>
                                                </div>
                                                @if(isset($booking['adultSell']) || isset($booking['childSell']) || isset($booking['infantSell']))
                                                <small class="text-muted" style="font-size: 0.7rem;">Adult: {{ $booking['adultSell'] ?? 0 }} / Child: {{ $booking['childSell'] ?? 0 }} / Infant: {{ $booking['infantSell'] ?? 0 }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ri-file-list-3-line text-muted" style="font-size: 2.5rem;"></i>
                            <h6 class="text-muted mt-2 mb-0">No miscellaneous data available</h6>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                    <div class="d-flex gap-2 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('miscellaneous', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endforeach
</div>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const countryFilter = document.getElementById('countryFilter');
    const agentFilter = document.getElementById('agentFilter');
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const today = new Date().toISOString().split('T')[0];
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (countryFilter) countryFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    if (startDateFilter) {
        startDateFilter.setAttribute('max', today);
        startDateFilter.addEventListener('change', function() {
            if (endDateFilter) {
                if (startDateFilter.value) {
                    endDateFilter.setAttribute('min', startDateFilter.value);
                    if (endDateFilter.value && endDateFilter.value < startDateFilter.value) {
                        endDateFilter.value = startDateFilter.value;
                    }
                } else {
                    endDateFilter.removeAttribute('min');
                }
            }
            filterTable();
        });
    }
    if (endDateFilter) {
        endDateFilter.setAttribute('max', today);
        if (startDateFilter && startDateFilter.value) {
            endDateFilter.setAttribute('min', startDateFilter.value);
        }
        endDateFilter.addEventListener('change', function() {
            if (startDateFilter && endDateFilter.value && startDateFilter.value && endDateFilter.value < startDateFilter.value) {
                startDateFilter.value = endDateFilter.value;
                startDateFilter.dispatchEvent(new Event('change'));
                return;
            }
            filterTable();
        });
    }
    
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Apply initial filter on page load to show today's data
    filterTable();
});

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const countryFilter = document.getElementById('countryFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const startDateValue = document.getElementById('startDateFilter')?.value || '';
    const endDateValue = document.getElementById('endDateFilter')?.value || '';
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    const totalRows = Array.from(rows).filter(r => r.cells.length > 1).length;
    if (typeof table !== 'undefined' && table && typeof table.rows === 'function') {
        table.rows('.dt-hasChild').every(function() {
            if (this.child.isShown()) this.child.hide();
            $(this.node()).removeClass('dt-hasChild');
        });
    }
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[2]?.querySelector('.fw-medium')?.textContent || '';
        const agent = row.cells[4]?.querySelector('.fw-medium')?.textContent || '';
        const createdBy = row.cells[5]?.querySelector('.fw-medium')?.textContent || '';
        const createdAt = row.getAttribute('data-created-at');
        const updatedAt = row.getAttribute('data-updated-at');
        
        let show = true;
        
        // Search filter - check tour details, destination, agent, and created by
        if (searchTerm && !tourDetails.includes(searchTerm) && 
            !destination.toLowerCase().includes(searchTerm) &&
            !agent.toLowerCase().includes(searchTerm) &&
            !createdBy.toLowerCase().includes(searchTerm)) {
            show = false;
        }
        
        // Country filter - use LIKE operator logic (contains)
        // This works for multi-country destinations like "India, Singapore"
        if (countryFilter) {
            // Split destination by comma and trim spaces
            const destinationCountries = destination.split(',').map(c => c.trim());
            // Check if the selected country is in the destination list
            if (!destinationCountries.includes(countryFilter)) {
                show = false;
            }
        }
        
        // Agent filter
        if (agentFilter && agent !== agentFilter) {
            show = false;
        }
        
        // Date filtering (check both created_at and updated_at)
        if ((startDateValue || endDateValue) && (createdAt || updatedAt)) {
            const startDate = startDateValue ? new Date(startDateValue + 'T00:00:00') : null;
            const endDate = endDateValue ? new Date(endDateValue + 'T23:59:59') : null;
            let dateInRange = false;
            
            // Check created_at if available
            if (createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if ((!startDate || createdDate >= startDate) && (!endDate || createdDate <= endDate)) {
                    dateInRange = true;
                }
            }
            
            // Check updated_at if available and created_at didn't match
            if (!dateInRange && updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if ((!startDate || updatedDate >= startDate) && (!endDate || updatedDate <= endDate)) {
                    dateInRange = true;
                }
            }
            
            if (!dateInRange) {
                show = false;
            }
        }
        
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    // Update header/cards counts based on visible rows
    const visibleRows = Array.from(document.querySelectorAll('#toursTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
    const rangeCount = visibleCount;
    const adults = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-adult') || '0', 10), 0);
    const children = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-child') || '0', 10), 0);
    const infants = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-infant') || '0', 10), 0);
    
    // Count today's enquiries from visible rows
    const today = new Date().toISOString().split('T')[0];
    const todayCount = visibleRows.filter(r => {
        const createdAt = r.getAttribute('data-created-at');
        return createdAt === today;
    }).length;

    // Update counts and labels
    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statEnquiries = document.getElementById('statEnquiriesCount');
    const statEnquiriesLabel = document.getElementById('statEnquiriesLabel');
    const statToday = document.getElementById('statTodayCount');
    const statAdults = document.getElementById('statAdultsCount');
    const statAdultsLabel = document.getElementById('statAdultsLabel');
    const statChildren = document.getElementById('statChildrenCount');
    const statChildrenLabel = document.getElementById('statChildrenLabel');
    const statInfants = document.getElementById('statInfantsCount');
    const statInfantsLabel = document.getElementById('statInfantsLabel');

    if (countEl) countEl.textContent = rangeCount;
    if (statEnquiries) statEnquiries.textContent = rangeCount;
    if (statToday) statToday.textContent = todayCount;
    if (statAdults) statAdults.textContent = adults;
    if (statChildren) statChildren.textContent = children;
    if (statInfants) statInfants.textContent = infants;

    // Update filter results badge
    updateFilterResults(visibleCount, totalRows);

    if (startDateValue || endDateValue) {
        const start = startDateValue ? new Date(startDateValue) : null;
        const end = endDateValue ? new Date(endDateValue) : null;
        let label = '';

        if (start && end) {
            if (start.getTime() === end.getTime()) {
                label = start.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' });
            } else if (start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()) {
                if (start.getDate() === 1 && end.getDate() === new Date(end.getFullYear(), end.getMonth() + 1, 0).getDate()) {
                    label = start.toLocaleString('default', { month: 'long', year: 'numeric' });
                } else {
                    label = `${start.getDate()}-${end.getDate()} ${start.toLocaleString('default', { month: 'short' })}, ${start.getFullYear()}`;
                }
            } else {
                label = `${start.toLocaleString('default', { month: 'short' })} ${start.getDate()} - ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
            }
        } else if (start) {
            label = `From ${start.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' })}`;
        } else if (end) {
            label = `Up to ${end.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' })}`;
        }

        if (label && labelEl) labelEl.textContent = label;
        if (label && statEnquiriesLabel) statEnquiriesLabel.textContent = `Enquiries - ${label}`;
        if (label && statAdultsLabel) statAdultsLabel.textContent = `Adults - ${label}`;
        if (label && statChildrenLabel) statChildrenLabel.textContent = `Children - ${label}`;
        if (label && statInfantsLabel) statInfantsLabel.textContent = `Infants - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statEnquiriesLabel) statEnquiriesLabel.textContent = `${month} Enquiries`;
        if (statAdultsLabel) statAdultsLabel.textContent = `${month} Adults`;
        if (statChildrenLabel) statChildrenLabel.textContent = `${month} Children`;
        if (statInfantsLabel) statInfantsLabel.textContent = `${month} Infants`;
    }
}

function resetFilters() {
    const searchInput = document.getElementById('searchInput');
    const countrySelect = document.getElementById('countryFilter');
    const agentSelect = document.getElementById('agentFilter');
    const startDateInput = document.getElementById('startDateFilter');
    const endDateInput = document.getElementById('endDateFilter');

    if (searchInput) searchInput.value = '';
    // Reset Select2 dropdowns properly
    if (countrySelect && $('#countryFilter').hasClass('select2-hidden-accessible')) {
        $('#countryFilter').val('').trigger('change');
    } else if (countrySelect) {
        countrySelect.value = '';
    }
    if (agentSelect && $('#agentFilter').hasClass('select2-hidden-accessible')) {
        $('#agentFilter').val('').trigger('change');
    } else if (agentSelect) {
        agentSelect.value = '';
    }
    if (startDateInput) {
        startDateInput.value = '';
    }
    if (endDateInput) {
        endDateInput.value = '';
        endDateInput.removeAttribute('min');
    }
    filterTable();
    
    // Show success message
    showFilterResetMessage();
}

function updateFilterResults(visibleCount, totalCount) {
    const filterResultsBadge = document.getElementById('filterResultsBadge');
    if (filterResultsBadge) {
        if (visibleCount < totalCount) {
            // filterResultsBadge.textContent = `${visibleCount} of ${totalCount} shown`;
            // filterResultsBadge.style.display = 'inline-block';
        } else {
            filterResultsBadge.style.display = 'none';
        }
    }
}

function showFilterResetMessage() {
    // Show SweetAlert success message
    Swal.fire({
        title: 'Filters Reset!',
        text: 'All filters have been cleared successfully.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

function convertToProspect(tourId) {
    Swal.fire({
        title: 'Move to Follow Up?',
        text: 'Are you sure you want to move this enquiry to Follow Up?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, move it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementation for status update
            console.log('Converting tour', tourId, 'to Prospect status');
            // Add AJAX call here
            Swal.fire({
                title: 'Moved!',
                text: 'Enquiry has been moved to Follow Up.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
    });
}

function convertToTentative(tourId) {
    Swal.fire({
        title: 'Mark as Tentative?',
        text: 'Are you sure you want to mark this enquiry as Tentative?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, mark it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementation for status update
            console.log('Converting tour', tourId, 'to Tentative status');
            // Add AJAX call here
            Swal.fire({
                title: 'Updated!',
                text: 'Enquiry has been marked as Tentative.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
    });
}

function deleteTour(tourId) {
    Swal.fire({
        title: 'Delete Tour?',
        text: 'Are you sure you want to delete this tour? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementation for deletion
            console.log('Deleting tour', tourId);
            // Add AJAX call here
            Swal.fire({
                title: 'Deleted!',
                text: 'Tour has been deleted successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
    });
}

function exportData() {
    // Implementation for data export
    console.log('Exporting data...');
}

// Service Modal Functions
function openServiceModal(serviceType, tourId, event) {
    console.log('Opening service modal:', serviceType, 'for tour:', tourId);
    
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Construct modal ID
    const modalId = `${serviceType}DetailsModal${tourId}`;
    console.log('Looking for modal element:', modalId);
    
    // Find the modal element
    const modalElement = document.getElementById(modalId);
    console.log('Modal element found:', !!modalElement);
    
    if (!modalElement) {
        console.error('Modal element not found:', modalId);
        
        // Log available modals for debugging
        const availableModals = Array.from(document.querySelectorAll('[id*="DetailsModal"]')).map(el => el.id);
        console.log('Available service modals on page:', availableModals);
        
        // Show user-friendly error
        Swal.fire({
            title: 'Modal Not Found',
            text: `Could not find ${serviceType} details modal for tour ${tourId}. Please refresh the page and try again.`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    try {
        // Method 1: Try Bootstrap 5 method
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            console.log('Using Bootstrap 5 modal method');
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
            console.log('Bootstrap 5 modal show called');
            return;
        }
        
        // Method 2: Try jQuery method (fallback)
        if (typeof $ !== 'undefined' && $.fn.modal) {
            console.log('Using jQuery modal method');
            $(modalElement).modal({
                backdrop: 'static',
                keyboard: false
            });
            $(modalElement).modal('show');
            console.log('jQuery modal show called');
            return;
        }
        
        // Method 3: Manual modal display (last resort)
        console.log('Using manual modal display');
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = `backdrop-${modalId}`;
        document.body.appendChild(backdrop);
        
        // Prevent body scroll
        document.body.classList.add('modal-open');
        
        console.log('Manual modal display applied');
        
    } catch (error) {
        console.error('Error opening modal:', error);
        
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while opening the modal. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

function closeServiceModal(serviceType, tourId) {
    const modalId = `${serviceType}DetailsModal${tourId}`;
    const modalElement = document.getElementById(modalId);
    
    if (!modalElement) {
        console.error('Modal element not found for closing:', modalId);
        return;
    }
    
    try {
        // Method 1: Bootstrap 5
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        
        // Method 2: jQuery
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $(modalElement).modal('hide');
        }
        
        // Method 3: Manual
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        
        // Remove backdrop
        const backdrop = document.getElementById(`backdrop-${modalId}`);
        if (backdrop) {
            backdrop.remove();
        }
        
        // Remove any orphaned backdrops
        const allBackdrops = document.querySelectorAll('.modal-backdrop');
        allBackdrops.forEach(backdrop => backdrop.remove());
        
        // Re-enable body scroll
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
    } catch (error) {
        console.error('Error closing modal:', error);
    }
}

// Legacy function for backwards compatibility
function openHotelModal(tourId) {
    openServiceModal('hotel', tourId);
}

function closeHotelModal(tourId) {
    closeServiceModal('hotel', tourId);
}

// Test function for services
function testServices() {
    console.log('Testing services functionality...');
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    console.log('Total rows found:', rows.length);
    
    // Test first few rows
    rows.forEach((row, index) => {
        if (index < 3) { // Only test first 3 rows
            const tourId = row.querySelector('[data-tour-id]')?.getAttribute('data-tour-id') || 'N/A';
            const servicesCell = row.cells[6]; // Services column
            const serviceBadges = servicesCell?.querySelectorAll('.badge') || [];
            
            console.log(`Row ${index + 1}:`, {
                tourId,
                servicesCell: servicesCell?.textContent,
                serviceBadgesCount: serviceBadges.length,
                serviceBadges: Array.from(serviceBadges).map(badge => badge.textContent)
            });
        }
    });
    
    // Test modal opening
    const firstRow = rows[0];
    if (firstRow) {
        const firstServiceBadge = firstRow.querySelector('.badge[onclick*="openServiceModal"]');
        if (firstServiceBadge) {
            console.log('Testing first service badge click...');
            const onclick = firstServiceBadge.getAttribute('onclick');
            console.log('onclick attribute:', onclick);
        } else {
            console.log('No clickable service badges found');
        }
    }
}

// function bulkActions() {
//     const selectedTours = document.querySelectorAll('.row-checkbox:checked');
//     if (selectedTours.length === 0) {
//         alert('Please select at least one tour for bulk actions.');
//         return;
//     }
    
//     // Implementation for bulk actions
//     console.log('Bulk actions for', selectedTours.length, 'tours');
// }
</script>
@endsection
@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    // Wait for all scripts to load before initializing
    $(document).ready(function() {
        // Small delay to ensure all scripts are loaded
        setTimeout(function() {
            initializeSelect2();
            initializeDataTable();
            filterTable();
        }, 200);
    });
    
    function initializeSelect2() {
        // Initialize Select2 for Country filter
        $('#countryFilter').select2({
            placeholder: 'All Countries',
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Select2 for Agent filter
        $('#agentFilter').select2({
            placeholder: 'All Agents',
            allowClear: true,
            width: '100%'
        });
        
        // Trigger filterTable when Select2 values change (including when cleared)
        $('#countryFilter, #agentFilter').on('change', function() {
            // When cleared, the value will be empty string, which shows all results
            filterTable();
        });
    }
    var table;
    function initializeDataTable() {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }
        
        const headerTexts = $('#toursTable thead th').map(function() {
            return $(this).text().trim();
        }).get();
        const colIndex = (name) => headerTexts.findIndex(t => t === name);

        const guestsIdx = colIndex('Guests');
        const servicesIdx = colIndex('Services');
        const agentNegotiationIdx = colIndex('Agent Negotiation');
        const negotiationIdx = colIndex('Negotiation');
        const actionsIdx = colIndex('Actions');

        const nonOrderableTargets = [guestsIdx, servicesIdx, agentNegotiationIdx, negotiationIdx, actionsIdx].filter(i => i >= 0);
        const nonSearchableTargets = [agentNegotiationIdx, negotiationIdx, actionsIdx].filter(i => i >= 0);

        // Initialize DataTable with export buttons (responsive: false so all columns stay in one row)
        table = $('.datatables-basic').DataTable({
            responsive: false,
            dom: 'lrtip', // Removed 'B' to hide the buttons, keeping l=length, r=processing, t=table, i=info, p=pagination
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print' // Keep buttons for functionality but don't show them
            ],
            searching: false, // Disable built-in searching since we use custom filters
            language: {
                search: "DataTable Search:",
                searchPlaceholder: "Search all columns...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
            pageLength: 50,
            // order: [[7, 'desc']], // Sort by Created Date column (index 7) in descending order
            columnDefs: [
                {
                    targets: nonOrderableTargets,
                    orderable: false,
                },
                {
                    targets: nonSearchableTargets,
                    searchable: false,
                },
                {
                    targets: [guestsIdx, servicesIdx].filter(i => i >= 0),
                    orderable: false,
                }
            ],
            initComplete: function() {
                console.log('DataTable initialized successfully');
                setTimeout(initServiceTooltips, 150);
            },
            drawCallback: function() {
                setTimeout(initServiceTooltips, 150);
            }
        });
        
        /* Body-level tooltip: show above icon so it’s never clipped by table/overflow */
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        } else {
            $globalTooltip.appendTo('body'); /* ensure it’s in body, not inside card/table */
        }
        $(document).on('mouseenter', '#toursTable .service-icon-wrapper', function() {
            var $w = $(this);
            var text = $w.attr('data-tooltip') || $w.find('.service-icon-tooltip').text();
            if (!text) return;
            var el = this;
            var rect = el.getBoundingClientRect();
            $globalTooltip.css({
                display: 'block',
                left: (rect.left + rect.width / 2) + 'px',
                top: (rect.top - 6) + 'px',
                transform: 'translate(-50%, -100%)'
            }).text(text);
        });
        $(document).on('mouseleave', '#toursTable .service-icon-wrapper', function() {
            $globalTooltip.hide();
        });
        /* Same body-level tooltip for Actions column */
        $(document).on('mouseenter', '#toursTable .action-icon-badge', function() {
            var $w = $(this);
            var text = $w.attr('data-tooltip') || $w.attr('title') || '';
            if (!text) return;
            var el = this;
            var rect = el.getBoundingClientRect();
            $globalTooltip.css({
                display: 'block',
                left: (rect.left + rect.width / 2) + 'px',
                top: (rect.top - 6) + 'px',
                transform: 'translate(-50%, -100%)'
            }).text(text);
        });
        $(document).on('mouseleave', '#toursTable .action-icon-badge', function() {
            $globalTooltip.hide();
        });
        /* Same body-level tooltip for column headers */
        $(document).on('mouseenter', '#toursTable thead .th-tooltip', function() {
            var $w = $(this);
            var text = $w.attr('data-tooltip') || $w.attr('title') || '';
            if (!text) return;
            var el = this;
            var rect = el.getBoundingClientRect();
            $globalTooltip.css({
                display: 'block',
                left: (rect.left + rect.width / 2) + 'px',
                top: (rect.bottom + 6) + 'px',
                transform: 'translate(-50%, 0)'
            }).text(text);
        });
        $(document).on('mouseleave', '#toursTable thead .th-tooltip', function() {
            $globalTooltip.hide();
        });
        
        function initServiceTooltips() {
            var selector = '#toursTable .service-icon-circle';
            var elList = document.querySelectorAll(selector);
            elList.forEach(function(el) {
                var titleText = el.getAttribute('data-bs-title') || el.getAttribute('title') || '';
                if (!titleText) return;
                try {
                    var t = window.bootstrap && bootstrap.Tooltip ? bootstrap.Tooltip.getInstance(el) : null;
                    if (t) t.dispose();
                    if (window.bootstrap && bootstrap.Tooltip) {
                        new bootstrap.Tooltip(el, { title: titleText, placement: (el.getAttribute('data-bs-placement') || 'top') });
                    }
                } catch (e) { console.warn('Tooltip init:', e); }
            });
            if (typeof $ !== 'undefined' && $.fn.tooltip && !window.bootstrap) {
                $(selector).each(function() {
                    var $el = $(this);
                    if ($el.data('bs.tooltip')) $el.tooltip('dispose');
                    $el.tooltip({ title: $el.attr('title') || $el.data('bs-title'), placement: 'top' });
                });
            }
        }

        // Custom export button functionality (for the dropdown)
        $('#exportCopy').on('click', function() {
            table.button('.buttons-copy').trigger();
        });

        $('#exportCSV').on('click', function() {
            table.button('.buttons-csv').trigger();
        });

        $('#exportExcel').on('click', function() {
            table.button('.buttons-excel').trigger();
        });

        $('#exportPDF').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });

        $('#exportPrint').on('click', function() {
            table.button('.buttons-print').trigger();
        });
        
        // Modal helper functions for Update Price (New Enquiries)
        window.openNewEnquiryModal = function(button, route) {
            var modalEl = document.getElementById('newEnquiryUpdateModal');
            var form = document.getElementById('newEnquiryUpdateForm');
            var priceInput = document.getElementById('new_enquiry_current_price');
            var commentInput = document.getElementById('new_enquiry_comment');
            var idInput = document.getElementById('new_enquiry_modal_enquiry_id');
            var displayActual = document.getElementById('new_enquiry_display_actual');
            var displayPrice = document.getElementById('new_enquiry_display_price');
            var displayDiscount = document.getElementById('new_enquiry_display_discount');
            var displayComment = document.getElementById('new_enquiry_display_comment');

            form.action = route || '';
            idInput.value = button.getAttribute('data-enquiry-id') || '';
            var actual = button.getAttribute('data-actual') || '';
            var prevPrice = button.getAttribute('data-price') || '';
            var discount = button.getAttribute('data-discount') || '';
            var prevComment = button.getAttribute('data-comment') || '';

            // Set displays
            displayActual.textContent = actual !== '' ? actual : '—';
            displayDiscount.textContent = discount !== '' ? discount : '—';
            displayPrice.textContent = prevPrice !== '' ? prevPrice : '—';
            displayComment.textContent = prevComment !== '' ? prevComment : '—';

            // Prefill price with previous negotiated amount; comment left blank
            priceInput.value = prevPrice;
            commentInput.value = '';
            if (actual !== '') priceInput.setAttribute('max', actual); else priceInput.removeAttribute('max');

            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        };

        window.validateNewEnquiryPrice = function(input) {
            var maxValue = parseFloat(input.getAttribute('max'));
            var currentValue = parseFloat(input.value);
            var warningMessage = document.getElementById('new-enquiry-warning-message');
            
            if (!isNaN(maxValue) && !isNaN(currentValue) && currentValue > maxValue) {
                input.value = maxValue; // Reset to maximum allowed value
                warningMessage.classList.remove('d-none');
                
                setTimeout(function() {
                    warningMessage.classList.add('d-none');
                }, 3000);
            }
        };

        // Add form submission handler with loader
        $(document).ready(function() {
            $('#newEnquiryUpdateForm').on('submit', function(e) {
                const submitBtn = document.getElementById('new_enquiry_submit_btn');
                const cancelBtn = document.getElementById('new_enquiry_cancel_btn');
                
                // Show loader
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Submitting...';
                submitBtn.disabled = true;
                cancelBtn.disabled = true;
                
                // Form will submit naturally, no need to prevent default
            });
        });

        let agentNegotiationModalInstance = null;
        let agentNegotiationActionsDisabled = false;

        function toggleAgentNegotiationActions(disabled) {
            agentNegotiationActionsDisabled = !!disabled;
            const buttons = [
                document.getElementById('agentNegotiationCancelBtn'),
                document.getElementById('agentNegotiationConfirmBtn'),
                document.getElementById('agentNegotiationSubmitBtn')
            ];
            buttons.forEach(btn => {
                if (btn) {
                    btn.disabled = agentNegotiationActionsDisabled;
                    btn.classList.toggle('disabled', agentNegotiationActionsDisabled);
                }
            });
        }

        window.openAgentNegotiationModal = function(button) {
            const modalEl = document.getElementById('agentNegotiationModal');
            if (!modalEl) return;

            if (!agentNegotiationModalInstance) {
                agentNegotiationModalInstance = new bootstrap.Modal(modalEl);
            }

            const form = document.getElementById('agentNegotiationForm');
            const tourIdInput = document.getElementById('agent_negotiation_tour_id');
            const actionInput = document.getElementById('agent_negotiation_action');
            const actualInput = document.getElementById('agent_negotiation_actual_amount');
            const amountInput = document.getElementById('agentNegotiationAmount');
            const remarkInput = document.getElementById('agentNegotiationRemark');
            const warning = document.getElementById('agentNegotiationWarning');
            const displayEl = document.getElementById('agentNegotiationDisplayId');
            const currentAmountEl = document.getElementById('agentNegotiationCurrentAmount');
            const lastAmountEl = document.getElementById('agentNegotiationLastAmount');
            const lastRemarkEl = document.getElementById('agentNegotiationLastRemark');
            const maxValueEl = document.getElementById('agentNegotiationMaxValue');

            const tourId = button.getAttribute('data-tour-id');
            const displayId = button.getAttribute('data-display-id') || '—';
            const tourStatus = button.getAttribute('data-tour-status') || '';
            const actualAttr = button.getAttribute('data-actual');
            const lastAttr = button.getAttribute('data-last-amount');
            const isLocked = button.getAttribute('data-negotiation-locked') === '1';
            const actualAmount = actualAttr !== null && actualAttr !== '' ? parseFloat(actualAttr) : null;
            const lastAmount = lastAttr !== null && lastAttr !== '' ? parseFloat(lastAttr) : null;
            const lastRemark = button.getAttribute('data-last-comment') || '';

            form.dataset.currentStatus = tourStatus;
            tourIdInput.value = tourId;
            actualInput.value = Number.isFinite(actualAmount) ? actualAmount : '';
            actionInput.value = 'negotiate';
            displayEl.textContent = displayId;
            warning.classList.add('d-none');
            remarkInput.classList.remove('is-invalid');
            const remarkErrEl = document.getElementById('agentNegotiationRemarkError');
            if (remarkErrEl) remarkErrEl.classList.add('d-none');

            // Determine the maximum allowed amount
            // If there's a last negotiated amount, use that as max; otherwise use current amount
            let maxAllowedAmount = null;
            if (Number.isFinite(lastAmount) && lastAmount > 0) {
                maxAllowedAmount = lastAmount;
            } else if (Number.isFinite(actualAmount) && actualAmount > 0) {
                maxAllowedAmount = actualAmount;
            }

            // Set max attribute and display max value
            if (maxAllowedAmount !== null && maxAllowedAmount > 0) {
                amountInput.setAttribute('max', maxAllowedAmount);
                maxValueEl.textContent = formatNegotiationAmount(maxAllowedAmount);
            } else {
                amountInput.removeAttribute('max');
                maxValueEl.textContent = '—';
            }

            // Display current amount
            if (Number.isFinite(actualAmount) && actualAmount > 0) {
                currentAmountEl.textContent = formatNegotiationAmount(actualAmount);
            } else {
                currentAmountEl.textContent = '—';
            }

            // Set last amount value and display
            if (Number.isFinite(lastAmount) && lastAmount > 0) {
                amountInput.value = lastAmount;
                lastAmountEl.textContent = formatNegotiationAmount(lastAmount);
            } else {
                amountInput.value = '';
                lastAmountEl.textContent = '—';
            }

            remarkInput.value = '';
            lastRemarkEl.textContent = lastRemark || '—';
            toggleAgentNegotiationActions(isLocked);

            // Add real-time validation for amount input (remove old listener first)
            const oldHandler = amountInput.oninput;
            amountInput.oninput = null;
            amountInput.addEventListener('input', function validateAmount() {
                const enteredValue = parseFloat(this.value);
                const maxValue = parseFloat(this.getAttribute('max'));
                
                if (!isNaN(enteredValue) && !isNaN(maxValue) && enteredValue > maxValue) {
                    warning.classList.remove('d-none');
                    warning.textContent = `Negotiated amount cannot exceed ${formatNegotiationAmount(maxValue)}.`;
                } else {
                    warning.classList.add('d-none');
                }
            });
            
            // Auto-revert to max value when user leaves the field (blur event)
            amountInput.addEventListener('blur', function revertToMax() {
                const enteredValue = parseFloat(this.value);
                const maxValue = parseFloat(this.getAttribute('max'));
                
                if (!isNaN(enteredValue) && !isNaN(maxValue) && enteredValue > maxValue) {
                    this.value = maxValue;
                    warning.classList.add('d-none');
                }
            });

            agentNegotiationModalInstance.show();
        };

        window.submitAgentNegotiation = function(action) {
            if (agentNegotiationActionsDisabled) {
                Swal.fire({
                    icon: 'info',
                    title: 'Negotiation locked',
                    text: 'Please respond via Check Negotiation.'
                });
                return;
            }

            const form = document.getElementById('agentNegotiationForm');
            const actionInput = document.getElementById('agent_negotiation_action');
            const amountInput = document.getElementById('agentNegotiationAmount');
            const remarkInput = document.getElementById('agentNegotiationRemark');
            const warning = document.getElementById('agentNegotiationWarning');
            const cancelBtn = document.getElementById('agentNegotiationCancelBtn');
            const confirmBtn = document.getElementById('agentNegotiationConfirmBtn');
            const submitBtn = document.getElementById('agentNegotiationSubmitBtn');
            warning.classList.add('d-none');

            if (action === 'negotiate') {
                const amountValue = parseFloat(amountInput.value);
                if (isNaN(amountValue) || amountValue <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Amount required',
                        text: 'Please enter a valid negotiation amount.'
                    });
                    return;
                }
                const remarkError = document.getElementById('agentNegotiationRemarkError');
                if (remarkInput.value.trim() === '') {
                    remarkInput.classList.add('is-invalid');
                    remarkError.classList.remove('d-none');
                    return;
                }
                remarkInput.classList.remove('is-invalid');
                remarkError.classList.add('d-none');

                const max = parseFloat(amountInput.getAttribute('max'));
                if (!isNaN(max) && max > 0 && amountValue > max) {
                    warning.classList.remove('d-none');
                    warning.textContent = `Negotiated amount cannot exceed ${formatNegotiationAmount(max)}.`;
                    return;
                }

                // Show loader on negotiate button
                const originalSubmitText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Submitting...';
                submitBtn.disabled = true;
                cancelBtn.disabled = true;
                confirmBtn.disabled = true;

                actionInput.value = action;
                form.submit();
                return;
            }

            // For Cancel and Confirm actions - remarks required
            const remarkError = document.getElementById('agentNegotiationRemarkError');
            if (remarkInput.value.trim() === '') {
                remarkInput.classList.add('is-invalid');
                remarkError.classList.remove('d-none');
                return;
            }
            remarkInput.classList.remove('is-invalid');
            remarkError.classList.add('d-none');

            const prompts = {
                cancel: {
                    title: 'Cancel this tour?',
                    text: 'Status will be updated to a cancelled state.',
                    icon: 'warning',
                    confirmButtonText: 'Yes, cancel it',
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Keep tour'
                },
                confirm: {
                    title: 'Confirm this tour?',
                    text: 'This will move the tour to Confirmed status.',
                    icon: 'question',
                    confirmButtonText: 'Yes, confirm it',
                    confirmButtonColor: '#198754',
                    cancelButtonText: 'Review again'
                }
            };

            const prompt = prompts[action];
            if (!prompt) return;

            if (agentNegotiationModalInstance) {
                agentNegotiationModalInstance.hide();
            }

            Swal.fire({
                ...prompt,
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        // Clear amount field if empty for cancel/confirm (remarks are required and already validated)
                        if (!amountInput.value.trim()) {
                            amountInput.removeAttribute('name');
                        }
                        actionInput.value = action;
                        form.submit();
                        resolve();
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(result => {
                if (!result.isConfirmed && agentNegotiationModalInstance) {
                    // Restore name attributes if user cancels
                    amountInput.setAttribute('name', 'amount');
                    remarkInput.setAttribute('name', 'comment');
                    agentNegotiationModalInstance.show();
                }
            });
        };

        function formatNegotiationAmount(value) {
            if (isNaN(value)) {
                return '—';
            }
            return new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }
        
        // Tour cancellation function
        window.cancelTour = function(encryptedTourId, displayId) {
            // Show SweetAlert confirmation dialog
            Swal.fire({
                title: 'Cancel Tour?',
                text: `Are you sure you want to cancel tour ${displayId}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Extract tour ID from the encrypted string to match the button ID
                    const tourIdMatch = document.querySelector(`[onclick*="${encryptedTourId}"]`);
                    const button = tourIdMatch;
                    const originalContent = button.innerHTML;
                    
                    // Show loading state
                    button.innerHTML = '<i class="ri-loader-4-line spin"></i> Cancelling...';
                    button.disabled = true;
                    
                    // Send AJAX request to cancel tour
                    fetch(`{{ route('bookings.cancel-tour', '') }}/${encryptedTourId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            Swal.fire({
                                title: 'Cancelled!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                            
                            // Update button to show cancelled state
                            button.innerHTML = '<i class="ri-check-line"></i> Cancelled';
                            button.classList.remove('btn-outline-danger');
                            button.classList.add('btn-success');
                            button.disabled = true;
                            
                            // Refresh the page after a short delay to show updated data
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                            
                        } else {
                            // Show error message
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to cancel tour',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            
                            // Reset button state
                            button.innerHTML = originalContent;
                            button.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error cancelling tour:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error cancelling tour. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        
                        // Reset button state
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    });
                }
            });
        };
        
        // Notification helper function
        window.showNotification = function(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'error' ? 'alert-danger' : 'alert-info';
            
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        };
    }
</script>

<style>
/* Loading spinner animation for cancel button */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Tour status badge styling */
.tour-status .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}
</style>

@endsection

@extends('layouts.datatablejs')
