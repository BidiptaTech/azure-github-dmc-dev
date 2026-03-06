@extends('layouts.layout')
@section('title', 'Follow Ups')
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
    /* Style and position the clear button (X icon) – match new-enquiries */
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

    /* Compact Table Styles - fixed grid; stable widths so no jump when DataTables inits */
    #toursTable {
        font-size: 0.875rem;
        table-layout: fixed;
        width: 100% !important;
        margin-bottom: 0;
        background-color: #fff;
    }
    /* Prevent DataTables wrapper from forcing width recalc on init */
    .dataTables_wrapper .dataTables_scroll .dataTables_scrollBody #toursTable,
    .dataTables_wrapper #toursTable {
        width: 100% !important;
        table-layout: fixed;
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
    /* Tour Details column: taller rows for readability */
    #toursTable td:nth-child(2) {
        min-height: 72px;
        vertical-align: top;
    }
    /* Actions column: taller rows so icon badges have room; wider for 2 icons per row */
    #toursTable thead th.col-actions,
    #toursTable td.col-actions {
        min-width: 200px;
        width: 200px;
    }
    #toursTable td.col-actions {
        min-height: 72px;
        white-space: nowrap;
        overflow: visible;
    }
    /* When any service icon in this row is hovered, raise whole row so tooltip is visible (low z-index so modals stay on top) */
    #toursTable tbody tr:has(.service-icon-wrapper:hover),
    #toursTable tbody tr.service-tooltip-row-active {
        position: relative;
        z-index: 10;
    }

    /* Services column: professional soft-badge style; wider column */
    #toursTable thead th:nth-child(4),
    #toursTable td:nth-child(4) {
        min-width: 140px;
    }
    #toursTable td:nth-child(4) {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        overflow: visible !important;
    }
    #toursTable td:nth-child(4) .services-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        row-gap: 0.35rem;
        column-gap: 0.35rem;
        align-items: stretch;
        max-width: 100%;
    }
    #toursTable td:nth-child(4) .service-icon-wrapper {
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
    #toursTable .service-icon-wrapper {
        position: relative;
        display: inline-flex;
        z-index: 1;
        margin: 3px;
    }
    #toursTable .service-icon-wrapper:hover {
        z-index: 10;
    }
    #toursTable .service-icon-tooltip {
        display: none !important;
    }
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

    /* Actions column: same soft-badge design; 2 icons per row */
    #toursTable .actions-icons-wrap {
        display: grid;
        grid-template-columns: repeat(2, auto);
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

    /* Compact badges in Services / status columns */
    #toursTable .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        margin: 0.1rem 0.15rem;
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

    /* Compact buttons in table */
    #toursTable .btn-sm {
        padding: 0.25rem 0.55rem;
        font-size: 0.78rem;
        height: auto;
        white-space: nowrap;
    }
    #toursTable .btn-sm.negotiation-btn {
        min-width: 0;
        height: auto;
        width: auto;
        font-size: 0.78rem;
        white-space: normal;
        padding: 0.3rem 0.75rem;
    }
    /* Agent column: smaller text */
    #toursTable thead th:nth-child(3),
    #toursTable td:nth-child(3) {
        font-size: 0.8rem;
    }
    #toursTable td:nth-child(3) .fw-medium,
    #toursTable td:nth-child(3) small {
        font-size: 0.75rem;
    }
    /* Negotiation column: allow text to wrap inside column and give it more width */
    #toursTable td.col-negotiation {
        min-width: 0;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        width: 16%;
    }
    #toursTable td.col-negotiation .btn-sm.negotiation-btn,
    #toursTable td.col-negotiation .check-negotiation-btn,
    #toursTable td.col-negotiation .badge,
    #toursTable td.col-negotiation .text-muted {
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        text-align: center;
        width: 100%;
    }
    #toursTable td.col-negotiation .btn-sm.negotiation-btn,
    #toursTable td.col-negotiation .check-negotiation-btn {
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
        min-height: 36px;
    }
    /* Cancel action button: larger hit area, smaller label text */
    #toursTable .action-icon-badge[data-tooltip="Cancel Tour"],
    #toursTable button.action-icon-badge[data-tooltip="Cancel Tour"] {
        min-width: 38px;
        min-height: 38px;
    }
    #toursTable .action-icon-badge[data-tooltip="Cancel Tour"] .cancel-label,
    #toursTable button.action-icon-badge[data-tooltip="Cancel Tour"] .cancel-label {
        font-size: 0.65rem;
        display: block;
        line-height: 1.1;
    }
    #toursTable .btn-sm.negotiation-btn.negotiate-by-agent {
        min-width: 0;
        width: 100%;
        flex-direction: column;
        gap: 0.15rem;
        padding: 0.4rem 0.5rem;
    }
    #toursTable .btn-sm.negotiation-btn.negotiate-by-agent:hover:not(:disabled) {
        background: rgba(105, 108, 255, 0.08);
        border-color: #696cff;
        color: #696cff;
    }
    #toursTable .negotiate-by-agent-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.1rem;
    }
    #toursTable .negotiate-by-agent-label .negotiate-by-agent-icon {
        font-size: 1.1rem;
    }
    #toursTable .negotiate-by-agent-label .d-block:first-of-type {
        font-weight: 600;
        font-size: 0.8rem;
    }
    /* Agent column: clear hierarchy for name + company */
    #toursTable td.col-agent .agent-name-line {
        font-weight: 600;
        font-size: 0.875rem;
        color: #0d6efd;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        line-height: 1.3;
    }
    #toursTable td.col-agent .agent-name-line i {
        font-size: 1rem;
        opacity: 0.9;
    }
    #toursTable td.col-agent .agent-company-line {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.2rem;
        line-height: 1.3;
    }
    #toursTable td.col-agent .agent-company-line i {
        font-size: 0.8rem;
        opacity: 0.85;
        flex-shrink: 0;
    }
    #toursTable td.col-agent .agent-empty {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8rem;
        color: #6c757d;
        font-style: italic;
    }
    #toursTable td.col-agent .agent-empty i {
        font-size: 1rem;
        opacity: 0.7;
    }
    /* Created column: text wrap + icons (no text labels) */
    #toursTable td.col-created {
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        min-width: 0;
    }
    #toursTable td.col-created .created-by-line,
    #toursTable td.col-created .created-at-line {
        display: flex;
        align-items: flex-start;
        gap: 0.35rem;
        line-height: 1.35;
    }
    #toursTable td.col-created .created-by-line i,
    #toursTable td.col-created .created-at-line i {
        flex-shrink: 0;
        margin-top: 0.1rem;
        opacity: 0.85;
    }
    #toursTable td.col-created .created-by-line {
        font-weight: 600;
        font-size: 0.8rem;
        word-break: break-word;
    }
    #toursTable td.col-created .created-at-line {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.2rem;
    }
    #toursTable td.col-created .created-at-line i {
        font-size: 0.8rem;
    }
    /* Auto Cancel column: smaller text */
    #toursTable td.col-auto-cancel {
        font-size: 0.75rem !important;
        width: 6%;
        white-space: normal;
    }
    #toursTable td.col-auto-cancel .fw-semibold,
    #toursTable td.col-auto-cancel small,
    #toursTable td.col-auto-cancel .text-muted {
        font-size: 0.75rem !important;
    }
    /* Status column: professional soft badges (no bright solid backgrounds) */
    #toursTable .status-badge-prospect,
    #toursTable .status-badge-tentative,
    #toursTable .status-badge-overdue,
    #toursTable .status-badge-due-soon,
    #toursTable .status-badge-on-track,
    #toursTable .negotiation-waiting-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }
    #toursTable .status-badge-prospect {
        background: rgba(13, 110, 253, 0.12);
        color: #0a58ca;
        border: 1px solid rgba(13, 110, 253, 0.3);
        font-weight: 500;
        font-size: 0.75rem;
    }
    #toursTable .status-badge-tentative {
        background: rgba(255, 193, 7, 0.15);
        color: #997404;
        border: 1px solid rgba(255, 193, 7, 0.4);
        font-weight: 500;
        font-size: 0.75rem;
    }
    #toursTable .status-badge-overdue {
        background: rgba(220, 53, 69, 0.12);
        color: #b02a37;
        border: 1px solid rgba(220, 53, 69, 0.3);
        font-weight: 500;
        font-size: 0.75rem;
    }
    #toursTable .status-badge-due-soon {
        background: rgba(253, 126, 20, 0.12);
        color: #c45a0a;
        border: 1px solid rgba(253, 126, 20, 0.35);
        font-weight: 500;
        font-size: 0.75rem;
    }
    #toursTable .status-badge-on-track {
        background: rgba(25, 135, 84, 0.12);
        color: #146c43;
        border: 1px solid rgba(25, 135, 84, 0.3);
        font-weight: 500;
        font-size: 0.75rem;
    }
    /* Negotiation: standard style for "Waiting for agent response" */
    #toursTable .negotiation-waiting-badge {
        background: rgba(108, 117, 125, 0.12);
        color: #495057;
        border: 1px solid rgba(108, 117, 125, 0.3);
        font-weight: 500;
        font-size: 0.75rem;
    }

    /* Compact guests icons section */
    #toursTable .d-flex.gap-3 {
        gap: 0.75rem !important;
    }

    /* Compact services badges container */
    #toursTable .d-flex.gap-2.flex-wrap {
        gap: 0.35rem !important;
    }

    /* Reduce spacing in tour details */
    #toursTable .d-flex.flex-column {
        gap: 0.15rem;
    }

    /* Compact muted text */
    #toursTable .text-muted {
        font-size: 0.7rem;
    }

    /* Compact check-in/check-out / last contact */
    #toursTable .d-flex.flex-column small {
        line-height: 1.3;
    }

    /* Compact Service Modals (same as new-enquiries) */
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

    /* Loading spinner animation for cancel button */
    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
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
    .new-enq-stat-item { transition: transform 0.15s ease, box-shadow 0.15s ease; min-height: 72px; padding: 0.65rem 0.75rem !important; }
    .new-enq-stat-item:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .new-enq-stat-item .stat-value { font-size: 1.25rem; font-weight: 600; letter-spacing: -0.02em; line-height: 1; display: block; min-height: 1.5rem; }
    .new-enq-stat-item .stat-label { display: block; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; opacity: 0.85; margin-top: 0.15rem; line-height: 1.3; }
    .new-enq-stats-grid .col { display: flex; }
    .new-enq-stats-grid .col > div { width: 100%; }
    .new-enq-filter-bar { background: #fff; border-radius: 0.5rem; border: 1px solid #e7e9ed; }
    .new-enq-filter-bar .form-control, .new-enq-filter-bar .form-control-sm,
    .new-enq-filter-bar .form-select, .new-enq-filter-bar .form-select.form-select-sm { font-size: 0.8125rem; height: 38px; }
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
                    <span class="text-muted fw-light">Bookings /</span> Follow Ups
                </h4>
                <span class="text-muted d-none d-md-inline" style="font-size: 0.875rem;">Manage prospect enquiries, tentative bookings and follow up communications</span>
                <span class="badge bg-light text-info border border-info border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                    <i class="ri-phone-line me-1"></i><span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span> <span id="rangeLabel">{{ date('F') }}</span>
                </span>
            </div>
            <div class="row g-2 new-enq-stats-grid flex-grow-1">
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-info rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-phone-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statFollowUpsCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span><span class="stat-label text-muted" id="statFollowUpsLabel">{{ date('F') }} Follow Ups</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-primary rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-user-search-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statProspectsCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'Prospect')->count() }}</span><span class="stat-label text-muted" id="statProspectsLabel">{{ date('F') }} Prospects</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-warning rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-bookmark-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statTentativeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('tour_status', 'Tentative')->count() }}</span><span class="stat-label text-muted" id="statTentativeLabel">{{ date('F') }} Tentative</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="new-enq-stat-item d-flex align-items-start gap-2 rounded bg-white border shadow-sm h-100">
                        <div class="avatar-initial bg-danger rounded flex-shrink-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i class="ri-alarm-warning-line text-white"></i></div>
                        <div class="min-w-0"><span class="stat-value d-block lh-1" id="statOverdueCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->where('updated_at', '<', now()->subDays(7))->count() }}</span><span class="stat-label text-muted" id="statOverdueLabel">{{ date('F') }} Overdue</span></div>
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
                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Tour ID, Display ID...">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Status</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Prospect">Prospect</option>
                        <option value="Tentative">Tentative</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">Destination</label>
                    <select class="form-select form-select-sm" id="destinationFilter">
                        <option value="">All Destinations</option>
                        @php
                            $allDestinations = [];
                            foreach($tours as $tour) {
                                if($tour->destination) {
                                    $destinations = array_map('trim', explode(',', $tour->destination));
                                    $allDestinations = array_merge($allDestinations, $destinations);
                                }
                            }
                            $uniqueDestinations = array_unique(array_filter($allDestinations));
                            sort($uniqueDestinations);
                        @endphp
                        @foreach($uniqueDestinations as $destination)
                            <option value="{{ $destination }}">{{ $destination }}</option>
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
            <h5 class="mb-0">Follow Up List <span id="filterResultsBadge" class="badge bg-primary ms-2" style="display: none;"></span></h5>
            <div class="d-flex gap-2">
                {{-- <button class="btn btn-sm btn-outline-warning" onclick="scheduleFollowUp()">
                    <i class="ri-calendar-schedule-line me-1"></i> Schedule Follow Up
                </button> --}}
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
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <colgroup>
                        <col style="width: 2%">
                        <col style="width: 13%">
                        <col style="width: 9%">
                        <col style="width: 13%">
                        @php $role = [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138]; @endphp
                        @if(in_array(auth()->user()->role_id, $role))
                        {{-- Negotiation column - give more width --}}
                        <col style="width: 12%">
                        {{-- Actions column - keep comfortable width --}}
                        <col style="width: 10%">
                        @endif
                        <col style="width: 12%">
                        <col style="width: 11%">
                        <col style="width: 8%">
                        <col style="width: 8%">
                        {{-- Auto Cancel column - narrower --}}
                        <col style="width: 4%">
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
                            <th class="th-tooltip" data-tooltip="Negotiation">Negotiation</th>
                            @endif
                            <th class="th-tooltip" data-tooltip="Actions">Actions</th>
                            <th class="th-tooltip" data-tooltip="Status">Status</th>
                            <th class="th-tooltip" data-tooltip="Last Contact">Last Contact</th>
                            <th class="th-tooltip" data-tooltip="Created">Created</th>
                            <th class="th-tooltip" data-tooltip="Auto Cancel Date">Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            class="{{ $tour->updated_at < now()->subDays(7) ? 'table-warning' : '' }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-tour-status="{{ $tour->tour_status ?? '' }}"
                            data-destination="{{ $tour->destination ?? '' }}"
                            data-adult="{{ (int)($tour->adult ?? 0) }}"
                            data-child="{{ (int)($tour->child ?? 0) }}"
                            data-infant="{{ (int)($tour->infant ?? 0) }}"
                        >
                            {{-- <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td> --}}
                            <td>{{ $key + 1 }}</td>
                            <td class="align-top">
                                <div class="d-flex flex-column gap-1">
                                    <strong class="text-primary">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                    @if($tour->tour_type)
                                        @php
                                            $tourTypeLower = strtolower($tour->tour_type);
                                            $bgColor = $tourTypeLower === 'group' ? '#7c3aed' : '#059669';
                                            $textColor = '#ffffff';
                                            $badgeWidth = $tourTypeLower === 'group' ? '60px' : '40px';
                                        @endphp
                                        <span class="d-inline-block px-2 py-1 rounded"
                                              style="background: {{ $bgColor }}; color: {{ $textColor }}; font-weight: 600; font-size: 0.7rem; text-align: left; letter-spacing: 0.3px; text-transform: uppercase; width: {{ $badgeWidth }}; display: inline-block;">
                                            {{ $tour->tour_type }}
                                        </span>
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
                                           
                                        </small>
                                        <small>
                                            @if($tour->check_out_time)<span><strong>Out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</span>@endif
                                        </small>
                                    @else
                                        <small class="text-muted">Check-in/out: Not specified</small>
                                    @endif
                                </div>
                            </td>
                            <td class="col-agent">
                                <div class="d-flex flex-column">
                                    @if($tour->agent_name)
                                        <span class="agent-name-line">
                                            <i class="ri-user-line"></i>
                                            <span>{{ $tour->agent_name }}</span>
                                        </span>
                                        <span class="agent-company-line">
                                            <i class="ri-building-line"></i>
                                            <span>{{ $tour->agent_company_name ?? 'N/A' }}</span>
                                        </span>
                                    @else
                                        <span class="agent-empty">
                                            <i class="ri-user-unfollow-line"></i>
                                            <span>No agent assigned</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                        // Fetch orders for this tour with bookingType = enquiry
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
                            </td>
                            @php
                                $latestCommentAmount = $tour->enquiry_comment_amount ?? null;
                                $latestCommentRemark = $tour->enquiry_comment ?? '';
                                $hasAgentComment = $tour->enquiry_comment && strtolower($tour->enquiry_comment_sender_type ?? '') === 'agent';
                                
                                // Get first enquiry for discount calculation
                                $frstenquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->first();
                                $first_enquiry_actual_amount = $frstenquiry->actual_amount ?? 0;
                                
                                // Get latest enquiry
                                $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->latest()->first();
                                
                                // Calculate total tour price from ALL bookings with status 1 or 3
                                $tourTotalPrice = 0;
                                foreach ($tour->booking as $booking) {
                                    if (in_array($booking->status, [1, 3])) {
                                        $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
                                        if (is_array($data)) {
                                            $orderType = $booking->type ?? '';
                                            foreach ($data as $item) {
                                                $itemPrice = (float) ($item['totalPrice'] ?? $item['price'] ?? 0);
                                                
                                                // For hotel: pickup total only - do NOT add transfer (transport added automatically)
                                                $transferPrice = 0;
                                                if ($orderType !== 'hotel' && isset($item['transfer_options']['cost']) && $item['transfer_options']['cost'] > 0) {
                                                    // For PRO tours, prefer totalPrice (base × pax) when available; otherwise use base cost
                                                    if (isset($tour) && $tour->is_pro == 1 && isset($item['transfer_options']['totalPrice'])) {
                                                        $transferPrice = (float) $item['transfer_options']['totalPrice'];
                                                    } else {
                                                        $transferPrice = (float) $item['transfer_options']['cost'];
                                                    }
                                                }
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
                                $enquiry_amount = $enquiry->amount ?? 0;
                                $discount = $first_enquiry_actual_amount - $enquiry_amount;
                                $currentActualAmount = ceil($tourTotalPrice);
                                $settlementAmount = ceil($tourTotalPrice) - $discount;
                                $lastAgentAmount = $hasAgentComment ? $settlementAmount : null;
                                $lastOfferAmount = $lastAgentAmount ?? $settlementAmount;
                                $lastOfferRemark = $latestCommentRemark;
                            @endphp
                            @if(in_array(auth()->user()->role_id, $role))
                            <td class="align-top col-negotiation">
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-outline-primary negotiation-btn negotiate-by-agent"
                                    data-tour-id="{{ $tour->tour_id }}"
                                    data-display-id="{{ e($tour->display_id) }}"
                                    data-actual="{{ $currentActualAmount ?? 0 }}"
                                    data-last-amount="{{ $lastOfferAmount ?? '' }}"
                                    data-last-comment="{{ e($lastOfferRemark) }}"
                                    data-tour-status="{{ e($tour->tour_status) }}"
                                    data-negotiation-locked="{{ $hasAgentComment ? '1' : '0' }}"
                                    onclick="openAgentNegotiationModal(this)"
                                    {{ $hasAgentComment ? 'disabled' : '' }}
                                >
                                    <span class="negotiate-by-agent-label">
                                        <i class="ri-handshake-line negotiate-by-agent-icon" aria-hidden="true"></i>
                                        <span class="d-block">Negotiate</span>
                                        <span class="d-block small text-muted">By Agent</span>
                                    </span>
                                </button>
                                @if($hasAgentComment)
                                    <button 
                                        type="button"
                                        class="btn btn-sm btn-warning negotiation-btn check-negotiation-btn"
                                        data-tour-id="{{ $tour->tour_id }}"
                                        data-enquiry-id="{{ $enquiry->enquiry_id ?? '' }}"
                                        data-price="{{ $settlementAmount }}"
                                        data-actual="{{ $currentActualAmount }}"
                                        data-discount="{{ $discount }}"
                                        data-comment="{{ e($tour->enquiry_comment ?? '') }}"
                                        onclick="openFollowupModal(this, '{{ route('update-price-comment') }}')"
                                    >
                                        Negotiation
                                    </button>
                                @elseif($tour->enquiry_comment && strtolower($tour->enquiry_comment_sender_type ?? '') === "om")
                                    <span class="badge negotiation-waiting-badge">Waiting for agent response</span>
                                @else
                                    <span class="text-muted">No negotiation</span>
                                @endif
                            </td>
                            @endif
                            <td class="align-top col-actions">
                                <div class="actions-icons-wrap">
                                    @if(auth()->user()->role_id == 33 || auth()->user()->role_id == 11 || auth()->user()->role_id == 34 || auth()->user()->role_id == 37 || auth()->user()->role_id == 38 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125 || in_array(auth()->user()->role_id, [128, 129, 130, 131, 132, 134, 135, 136, 137, 138]))
                                    @if($tour->is_pro == 1)
                                    <a href="{{ route('enquiry-form-pro.edit', Crypt::encrypt($tour->tour_id)) }}"
                                       class="action-icon-badge" style="--action-color: #047857;" data-tooltip="Edit Tour">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    @else
                                    <a href="{{ route('single-tour-package.edit', Crypt::encrypt($tour->tour_id)) }}"
                                       class="action-icon-badge" style="--action-color: #047857;" data-tooltip="Edit Tour">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    @endif
                                    @endif
                                    <a href="{{ route('bookings.view-tour', Crypt::encrypt($tour->tour_id)) }}" 
                                       class="action-icon-badge" style="--action-color: #0369a1;" data-tooltip="Audit Trail">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('tour.itinerary.preview', ['encryptedTourId' => Crypt::encrypt($tour->tour_id)]) }}" 
                                       class="action-icon-badge" style="--action-color: #0f766e;" data-tooltip="Quotation Preview" target="_blank">
                                        <i class="ri-file-download-line"></i>
                                    </a>
                                    @if($tour->tour_status == 'Tentative')
                                        @php
                                            $proformaInvoice = \App\Models\Invoice::where('tour_id', $tour->tour_id)
                                                ->where('invoice_type', 'proforma')
                                                ->whereNull('deleted_at')
                                                ->first();
                                        @endphp
                                        @if($proformaInvoice)
                                            <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($proformaInvoice->invoice_id), 'mode' => 'full']) }}" 
                                               class="action-icon-badge" style="--action-color: #0e7490;" data-tooltip="Proforma Invoice (Price Breakup)" target="_blank">
                                                <i class="ri-file-paper-line"></i>
                                            </a>
                                            <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($proformaInvoice->invoice_id), 'mode' => 'price-only']) }}" 
                                               class="action-icon-badge" style="--action-color: #7c3aed;" data-tooltip="Proforma Invoice (Package Price Only)" target="_blank">
                                                <i class="ri-file-download-line"></i>
                                            </a>
                                        @else
                                            <form action="{{ route('invoices.generate-proforma', $tour->tour_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="action-icon-badge" style="--action-color: #0e7490;" data-tooltip="Generate Proforma Invoice">
                                                    <i class="ri-file-add-line"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                    <button type="button" class="action-icon-badge d-flex flex-column align-items-center gap-0 py-1" style="--action-color: #dc2626;" data-tooltip="Cancel Tour" onclick="cancelTour('{{ Crypt::encrypt($tour->tour_id) }}', '{{ $tour->display_id }}')" id="cancel-btn-{{ $tour->tour_id }}">
                                        <i class="ri-delete-bin-line"></i>
                                       
                                    </button>
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="d-flex flex-column gap-1 small">
                                    <div>
                                        <span class="text-muted d-block" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.03em;">Booking</span>
                                        @if($tour->tour_status == 'Prospect')
                                            <span class="badge status-badge-prospect">
                                                <i class="ri-user-search-line me-1"></i>Prospect
                                            </span>
                                        @else
                                            <span class="badge status-badge-tentative">
                                                <i class="ri-bookmark-line me-1"></i>Tentative
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-muted d-block" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.03em;">Follow up</span>
                                        @if($tour->updated_at < now()->subDays(7))
                                            <span class="badge status-badge-overdue">
                                                <i class="ri-alarm-warning-line me-1"></i>Overdue
                                            </span>
                                        @elseif($tour->updated_at < now()->subDays(3))
                                            <span class="badge status-badge-due-soon">
                                                <i class="ri-time-line me-1"></i>Due Soon
                                            </span>
                                        @else
                                            <span class="badge status-badge-on-track">
                                                <i class="ri-check-line me-1"></i>On Track
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->updated_at->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->updated_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            <td class="col-created align-top">
                                <div class="d-flex flex-column">
                                    <span class="created-by-line fw-medium" title="Created by">
                                        <i class="ri-user-line"></i>
                                        <span>{{ $tour->created_by_name ?? 'N/A' }}</span>
                                    </span>
                                    <span class="created-at-line" title="Created at">
                                        <i class="ri-calendar-line"></i>
                                        <span>{{ $tour->created_at->format('D, M d, Y') }} · {{ $tour->created_at->format('h:i A') }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="col-auto-cancel">
                                <div class="d-flex flex-column">
                                    @if($tour->auto_cancel_date)
                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($tour->auto_cancel_date)->format('D, M d, Y') }}</span>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($tour->auto_cancel_date)->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        {{-- <tr>
                            <td colspan="12" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-phone-line ri-48px text-muted mb-2"></i>
                                    <h6 class="text-muted">No follow-ups required</h6>
                                    <p class="text-muted mb-0">All prospects have been contacted or converted.</p>
                                </div>
                            </td>
                        </tr> --}}
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{-- <div class="d-flex justify-content-between align-items-center mt-4">
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
    
    <!-- Update Price Modal (Follow Ups) -->
    <div class="modal fade" id="followupUpdateModal" tabindex="-1" aria-labelledby="followupUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="followupUpdateModalLabel">Update Price & Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="followupUpdateForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="enquiry_id" id="followup_modal_enquiry_id" />
                        
                        <!-- Current details display -->
                        <div class="border rounded p-3 bg-light mb-3">
                            <div class="row g-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">Actual Amount</small>
                                    <div class="fw-semibold" id="followup_display_actual">—</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Discount</small>
                                    <div class="fw-semibold text-danger" id="followup_display_discount">—</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Previous Negotiated Amount</small>
                                    <div class="fw-semibold text-success" id="followup_display_price">—</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Last Comment</small>
                                    <div class="fw-semibold" id="followup_display_comment">—</div>
                                </div>
                            </div>
                        </div>

                        <!-- New update inputs -->
                        <div class="mb-3">
                            <label for="followup_current_price" class="form-label">New Price</label>
                            <input id="followup_current_price" type="number" name="price" class="form-control" placeholder="Enter new price" onkeyup="validateFollowupPrice(this)" required />
                            <div id="followup-warning-message" class="alert alert-warning mt-2 py-2 px-3 d-none">
                                Enquiry price cannot exceed the actual amount.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="followup_comment" class="form-label">New Comment</label>
                            <textarea id="followup_comment" name="comment" rows="3" class="form-control" placeholder="Enter new comment" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="followup_cancel_btn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="followup_submit_btn">Submit</button>
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
    <div class="modal fade service-modal-compact" id="hotelDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="hotelDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                @php
                    $firstHotelOrder = $serviceData['hotel'][0] ?? null;
                    $firstHotelData = $firstHotelOrder ? (is_string($firstHotelOrder->data) ? json_decode($firstHotelOrder->data, true) : $firstHotelOrder->data) : null;
                    $firstBooking = ($firstHotelData && is_array($firstHotelData) && isset($firstHotelData[0])) ? $firstHotelData[0] : ($firstHotelData && is_array($firstHotelData) ? $firstHotelData : null);
                @endphp
                <!-- Compact Header -->
                <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-hotel-line me-1" style="font-size: 0.9rem;"></i>Hotel Enquiries - Tour #{{ $tour->tour_id }}
                            </h6>
                            @if($firstBooking && isset($firstBooking['bookingDate']) && is_array($firstBooking['bookingDate']) && count($firstBooking['bookingDate']) > 0)
                                <small class="opacity-90" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($firstBooking['bookingDate'][0])->format('M d') }} - {{ \Carbon\Carbon::parse(end($firstBooking['bookingDate']))->format('M d, Y') }}</small>
                            @endif
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
                            @foreach($hotelData as $booking)
                                @php
                                                // Hotel: use pickup total only - transport added automatically, do NOT add transfer
                                                    $hotelPrice = $booking['price'] ?? $booking['totalPrice'] ?? 0;
                                                    $guidePrice = isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0 ? $booking['guide_options']['total_price'] : 0;
                                                    $grandTotal = $hotelPrice + $guidePrice;
                                @endphp
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
                                                    {{ $currency }} {{ ceil($grandTotal) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-2" style="background-color: #ffffff;">
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
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                @php
                                                                    $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                                                    $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                                                    $nights = $checkIn->diffInDays($checkOut);
                                                                @endphp
                                                                <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ $nights }} Night{{ $nights > 1 ? 's' : '' }}</span>
                                                            @else
                                                                <span class="badge bg-secondary" style="font-size: 0.65rem; padding: 2px 6px;">Duration TBD</span>
                                                            @endif
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
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Cancellation</small>
                                                            <div class="fw-medium text-warning" style="font-size: 0.7rem;">{{ $booking['hotelDetails']['cancellation_charge'] }}</div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @if(isset($booking['hotelDetails']['image']))
                                                        <div class="mt-1">
                                                            <img src="{{ $booking['hotelDetails']['image'] }}" alt="{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel' }}" class="img-fluid rounded shadow-sm" style="height: 60px; width: 100%; object-fit: cover;">
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
                                                    @php
                                                        $numberOfRooms = $room['number_of_rooms'] ?? 1;
                                                        $bedPrice = 0;
                                                        $mealCount = 0;
                                                        if(isset($room['beds']) && is_array($room['beds']) && count($room['beds']) > 0) {
                                                            $bedPrice = $room['beds'][0]['price'] ?? 0;
                                                            if(isset($room['beds'][0]['selectedMeals'])) {
                                                                $selectedMeals = $room['beds'][0]['selectedMeals'];
                                                                if(is_array($selectedMeals) || is_object($selectedMeals)) {
                                                                    $mealCount = count($selectedMeals);
                                                                }
                                                            }
                                                        }
                                                        $roomTotalPrice = $bedPrice * $numberOfRooms * ($mealCount > 0 ? $mealCount : 1);
                                                    @endphp
                                                    <div class="bg-white rounded p-1 mb-1 border" style="border-color: #74b9ff !important;">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <div>
                                                                <small class="fw-bold text-dark" style="font-size: 0.75rem;">Room {{ $roomIndex + 1 }}: {{ $room['room_type'] ?? 'Standard Room' }}</small>
                                                                <div><small class="text-muted" style="font-size: 0.65rem;">{{ $numberOfRooms }} Room{{ $numberOfRooms > 1 ? 's' : '' }}</small></div>
                                                            </div>
                                                            <span class="badge bg-success" style="font-size: 0.7rem;">{{ $currency }} {{ ceil($roomTotalPrice) }}</span>
                                                        </div>
                                                        @if(isset($room['beds']) && is_array($room['beds']))
                                                            @foreach($room['beds'] as $bedIndex => $bed)
                                                                <div class="bg-light rounded p-1 mb-1">
                                                                    <div class="row g-1">
                                                                        <div class="col-6">
                                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">{{ $bed['bed_type'] ?? 'Bed' }}</small>
                                                                            <small class="text-muted" style="font-size: 0.6rem;">Guests: {{ $bed['head_count'] ?? 0 }} • Max: {{ $bed['max_occupancy'] ?? 'N/A' }}</small>
                                                                        </div>
                                                                        <div class="col-3">
                                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Price</small>
                                                                            <div class="fw-bold text-success" style="font-size: 0.7rem;">{{ $currency }} {{ ceil($bed['price'] ?? 0) }}</div>
                                                                        </div>
                                                                        @if(isset($bed['selectedMeals']) && is_array($bed['selectedMeals']) && count($bed['selectedMeals']) > 0)
                                                                        <div class="col-12">
                                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Meals:</small>
                                                                            @foreach($bed['selectedMeals'] as $mealKey => $meal)
                                                                                <span class="badge bg-success me-1" style="font-size: 0.6rem;">{{ $meal['type'] ?? 'Meal' }} ({{ $currency }} {{ number_format((float)($meal['price'] ?? 0), 2) }})</span>
                                                                            @endforeach
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @endforeach
                                                <div class="bg-white rounded p-1 mt-1 border" style="border-color: #74b9ff !important;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <small class="fw-bold text-dark" style="font-size: 0.75rem;">Hotel Booking Summary</small>
                                                            @php $totalRooms = collect($booking['rooms'])->sum('number_of_rooms'); @endphp
                                                            <div><small class="text-muted" style="font-size: 0.65rem;">{{ $totalRooms }} room(s) • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</small></div>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Total Amount</small>
                                                            <div class="fw-bold" style="font-size: 0.9rem; color: #74b9ff;">{{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}</div>
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
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($cwbPrice, 2) }}</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Children</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $cwbChildren }}</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNights }}</div></div>
                                                            <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total (Price × Children × Nights)</small><div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($cwbTotal, 2) }}</div></div>
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
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($cwobPrice, 2) }}</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Children</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $cwobChildren }}</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNights }}</div></div>
                                                            <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total (Price × Children × Nights)</small><div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($cwobTotal, 2) }}</div></div>
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
                                                                    @php
                                                                        $hotelTransferCostDisplay = $booking['transfer_options']['cost'] ?? 0;
                                                                        if (isset($tour) && $tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                                                            $hotelTransferCostDisplay = $booking['transfer_options']['totalPrice'];
                                                                        }
                                                                    @endphp
                                                                    @if($hotelTransferCostDisplay > 0)
                                                                    <div class="col-12">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                        <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)$hotelTransferCostDisplay, 2) }}</div>
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

                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="ri-hotel-line text-muted" style="font-size: 1.5rem;"></i>
                        </div>
                        <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Hotel Data Available</h6>
                        <p class="text-muted mb-2" style="font-size: 0.75rem;">Hotel services are booked but detailed information is not available.</p>
                        <div class="alert alert-primary border-0 shadow-sm py-2 px-2" style="max-width: 360px; margin: 0 auto; font-size: 0.8rem;">
                            <div class="d-flex align-items-center">
                                <i class="ri-information-line text-primary me-2"></i>
                                <div><strong>Note:</strong> {{ $svc['hotel'] }} hotel service(s) are associated with this tour.</div>
                            </div>
                        </div>
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
   <div class="modal fade service-modal-compact" id="attractionDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="attractionDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
       <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
           <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
               <!-- Compact Header -->
               <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%);">
                   <div class="d-flex align-items-center justify-content-between w-100">
                       <div class="text-white">
                           <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                               <i class="ri-building-2-line me-1" style="font-size: 0.9rem;"></i>Attraction Enquiries
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
                           @foreach($attractionData as $booking)
                               @php
                                   $attractionPrice = $booking['price'] ?? $booking['totalPrice'] ?? 0;
                                   $transferPrice = 0;
                                   if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                                       if (isset($tour) && $tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                           $transferPrice = (float) $booking['transfer_options']['totalPrice'];
                                       } else {
                                           $transferPrice = (float) $booking['transfer_options']['cost'];
                                       }
                                   }
                                   $guidePrice = isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0 ? $booking['guide_options']['total_price'] : 0;
                                   $grandTotal = $attractionPrice + $transferPrice + $guidePrice;
                               @endphp
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
                                                   {{ $currency }} {{ ceil($grandTotal) }}
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
                                                   <div class="col-md-6">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Attraction Name</small>
                                                       <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['AttractionName'] ?? 'N/A' }}</div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Ticket Type</small>
                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['ticketName'] ?? 'Standard Ticket' }}</div>
                                                   </div>
                                                   <div class="col-12">
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
                                                       <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['ticket_details']['adult_price'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #ffc107 !important;">
                                                       <small class="text-warning fw-bold d-block" style="font-size: 0.7rem;">Child</small>
                                                       <div class="fw-bold text-warning" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['ticket_details']['child_price'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #17a2b8 !important;">
                                                       <small class="text-info fw-bold d-block" style="font-size: 0.7rem;">Senior</small>
                                                       <div class="fw-bold text-info" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['ticket_details']['senior_price'] ?? 0) }}</div>
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
                                                               <span class="badge bg-success" style="font-size: 0.6rem;">{{ $booking['adultCount'] }} × {{ ceil($booking['ticket_details']['adult_price'] ?? 0) }}</span>
                                                           @endif
                                                           @if($booking['childCount'] ?? 0 > 0)
                                                               <span class="badge bg-warning" style="font-size: 0.6rem;">{{ $booking['childCount'] }} × {{ ceil($booking['ticket_details']['child_price'] ?? 0) }}</span>
                                                           @endif
                                                           @if($booking['seniorCount'] ?? 0 > 0)
                                                               <span class="badge bg-info" style="font-size: 0.6rem;">{{ $booking['seniorCount'] }} × {{ ceil($booking['ticket_details']['senior_price'] ?? 0) }}</span>
                                                           @endif
                                                       </div>
                                                   </div>
                                                   <div class="text-end">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Total</small>
                                                       <div class="fw-bold" style="font-size: 0.9rem; color: #fd9853;">{{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}</div>
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
                                                                   @php
                                                                       $attractionTransferCostDisplay = $booking['transfer_options']['cost'] ?? 0;
                                                                       if (isset($tour) && $tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                                                           $attractionTransferCostDisplay = $booking['transfer_options']['totalPrice'];
                                                                       }
                                                                   @endphp
                                                                   @if($attractionTransferCostDisplay > 0)
                                                                   <div class="col-12">
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                       <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)$attractionTransferCostDisplay, 2) }}</div>
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
                                                                           $pickupTime = $booking['guide_options']['pickup_time'];
                                                                           if (strpos($pickupTime, ' - ') !== false) {
                                                                               $pickupTime = trim(explode(' - ', $pickupTime)[0]);
                                                                           }
                                                                           $formattedPickupTime = $pickupTime;
                                                                           if (!empty($pickupTime)) {
                                                                               try {
                                                                                   $timeObj = \Carbon\Carbon::createFromFormat('H:i', $pickupTime);
                                                                                   $formattedPickupTime = $timeObj->format('h:i A');
                                                                               } catch (\Exception $e) {
                                                                                   try {
                                                                                       $timeObj = \Carbon\Carbon::createFromFormat('h:i A', $pickupTime);
                                                                                       $formattedPickupTime = $timeObj->format('h:i A');
                                                                                   } catch (\Exception $e2) {
                                                                                       try {
                                                                                           $timeObj = \Carbon\Carbon::parse($pickupTime);
                                                                                           $formattedPickupTime = $timeObj->format('h:i A');
                                                                                       } catch (\Exception $e3) {
                                                                                           $formattedPickupTime = $pickupTime;
                                                                                       }
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
                                                                   <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['guide_options']['base_price'] ?? 0) }}</div>
                                                               </div>
                                                               @if(isset($booking['guide_options']['surcharge']) && $booking['guide_options']['surcharge'] > 0)
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                                   <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['guide_options']['surcharge']) }}</div>
                                                               </div>
                                                               @endif
                                                               @if(isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0)
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Total</small>
                                                                   <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ ceil($booking['guide_options']['total_price']) }}</div>
                                                               </div>
                                                               @endif
                                                           </div>
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
   <div class="modal fade service-modal-compact" id="restaurantDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="restaurantDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
       <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
           <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
               <!-- Compact Header -->
               <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                   <div class="d-flex align-items-center justify-content-between w-100">
                       <div class="text-white">
                           <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                               <i class="ri-restaurant-2-line me-1" style="font-size: 0.9rem;"></i>Restaurant Enquiries 
                           </h6>
                       </div>
                       <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                   </div>
               </div>
               
               <div class="modal-body p-2" style="background-color: #f8f9fa;">
                   @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                       @foreach($serviceData['restaurant'] as $index => $restaurantOrder)
                       @php
                           $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data;
                       @endphp
                       
                       @if(is_array($restaurantData))
                           @foreach($restaurantData as $booking)
                               @php
                                   $restaurantPrice = $booking['totalPrice'] ?? $booking['mealPrice'] ?? 0;
                                   $transferPrice = 0;
                                   if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                                       if (isset($tour) && $tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                           $transferPrice = (float) $booking['transfer_options']['totalPrice'];
                                       } else {
                                           $transferPrice = (float) $booking['transfer_options']['cost'];
                                       }
                                   }
                                   $guidePrice = 0;
                                   if (isset($booking['guide_options']) && is_array($booking['guide_options'])) {
                                       $guidePriceValue = $booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0;
                                       if ($guidePriceValue > 0) {
                                           $guidePrice = (float) $guidePriceValue;
                                       }
                                   }
                                   $restaurantGrandTotal = round($restaurantPrice + $transferPrice + $guidePrice);
                               @endphp
                               <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #fd79a8 !important;">
                                   <!-- Compact Card Header -->
                                   <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%);">
                                       <div class="row align-items-center g-1">
                                           <div class="col-md-8">
                                               <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                   <i class="ri-restaurant-2-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['restaurantName'] ?? 'Restaurant Booking' }}
                                               </h6>
                                               <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ ucfirst($booking['mealType'] ?? 'Meal') }} • {{ $booking['mealSpecificType'] ?? 'Standard' }}</small>
                                           </div>
                                           <div class="col-md-4 text-end">
                                               <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                   {{ $currency }} {{ ceil($restaurantGrandTotal) }}
                                               </span>
                                           </div>
                                       </div>
                                   </div>
                                   
                                   <div class="card-body p-2" style="background-color: #ffffff;">
                                       <!-- Reservation Details -->
                                       <div class="row mb-2 g-2">
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Reservation Details</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Dining Date</small>
                                                           <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Dining Time</small>
                                                           <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['visitTime'] ?? 'TBC' }}</div>
                                                       </div>
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-success" style="font-size: 0.85rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-warning" style="font-size: 0.85rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-12 text-center mt-1">
                                                           <span class="badge" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                               Party of {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }}
                                                           </span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="bg-light rounded p-2 h-100">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-information-line text-white" style="font-size: 0.8rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Restaurant Overview</h6>
                                                   </div>
                                                   <div class="row g-1">
                                                       <div class="col-{{ $guidePrice > 0 ? '4' : '6' }}">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Meal Price</small>
                                                           <div class="fw-medium text-success" style="font-size: 0.8rem;">{{ $currency }} {{ ceil($booking['mealPrice'] ?? 0) }}</div>
                                                       </div>
                                                       <div class="col-{{ $guidePrice > 0 ? '4' : '6' }}">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer</small>
                                                           <div class="fw-medium text-info" style="font-size: 0.8rem;">{{ $currency }} {{ ceil($transferPrice) }}</div>
                                                       </div>
                                                       @if($guidePrice > 0)
                                                       <div class="col-4">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Guide</small>
                                                           <div class="fw-medium" style="font-size: 0.8rem; color: #00cec9;">{{ $currency }} {{ ceil($guidePrice) }}</div>
                                                       </div>
                                                       @endif
                                                       <div class="col-12 mt-1 pt-1 border-top">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Total Price</small>
                                                           <div class="fw-bold" style="font-size: 0.95rem; color: #fd79a8;">{{ $currency }} {{ ceil($restaurantGrandTotal) }}</div>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>

                                       <!-- Transfer Options -->
                                       @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                           <div class="bg-light rounded p-1 mb-2">
                                               <div class="d-flex align-items-center mb-1">
                                                   <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
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
                                                                   @php
                                                                       $restaurantTransferCostDisplay = $booking['transfer_options']['cost'] ?? 0;
                                                                       if (isset($tour) && $tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                                                           $restaurantTransferCostDisplay = $booking['transfer_options']['totalPrice'];
                                                                       }
                                                                   @endphp
                                                                   @if($restaurantTransferCostDisplay > 0)
                                                                   <div class="col-12">
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                       <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)$restaurantTransferCostDisplay, 2) }}</div>
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
                                       @if(isset($booking['guide_options']) && is_array($booking['guide_options']) && (isset($booking['guide_options']['guideId']) || isset($booking['guide_options']['guide_id']) || isset($booking['guide_options']['guideName']) || isset($booking['guide_options']['guide_name']) || isset($booking['guide_options']['name'])))
                                           <div class="bg-light rounded p-1 mb-2">
                                               <div class="d-flex align-items-center mb-1">
                                                   <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                       <i class="ri-user-voice-line text-white" style="font-size: 0.7rem;"></i>
                                                   </div>
                                                   <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Details</h6>
                                               </div>
                                               <div class="row g-1">
                                                   <div class="col-md-6">
                                                       <div class="bg-white rounded p-1">
                                                           <div class="row g-1">
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                                   <div class="fw-medium" style="font-size: 0.75rem;">
                                                                       <i class="ri-user-voice-line me-1"></i>{{ $booking['guide_options']['guideName'] ?? $booking['guide_options']['guide_name'] ?? $booking['guide_options']['name'] ?? 'N/A' }}
                                                                   </div>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                                   <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['guide_options']['serviceType'] ?? $booking['guide_options']['service_type'] ?? 'N/A' }}</span>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Language</small>
                                                                   <span class="badge bg-success" style="font-size: 0.65rem;">{{ $booking['guide_options']['language'] ?? $booking['guide_options']['languages'] ?? 'N/A' }}</span>
                                                               </div>
                                                               @if(isset($booking['guide_options']['tourActivity']) || isset($booking['guide_options']['tour_activity']) || isset($booking['guide_options']['Activity']))
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Tour Activity</small>
                                                                   <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                       <i class="ri-map-pin-line me-1"></i>{{ $booking['guide_options']['tourActivity'] ?? $booking['guide_options']['tour_activity'] ?? $booking['guide_options']['Activity'] ?? 'N/A' }}
                                                                   </div>
                                                               </div>
                                                               @endif
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <div class="bg-white rounded p-1">
                                                           <div class="row g-1">
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Service Hours</small>
                                                                   <div class="fw-medium" style="font-size: 0.75rem;">
                                                                       <i class="ri-time-line me-1"></i>{{ $booking['guide_options']['hours'] ?? $booking['guide_options']['service_hours'] ?? 'N/A' }} Hours
                                                                   </div>
                                                               </div>
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Group Size</small>
                                                                   <div class="row g-1">
                                                                       <div class="col-6">
                                                                           <div class="bg-light rounded p-1 text-center border">
                                                                               <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $booking['guide_options']['adultsQty'] ?? $booking['guide_options']['adults_qty'] ?? $booking['guide_options']['adultQty'] ?? $booking['guide_options']['adult_qty'] ?? 0 }}</div>
                                                                               <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                                           </div>
                                                                       </div>
                                                                       <div class="col-6">
                                                                           <div class="bg-light rounded p-1 text-center border">
                                                                               <div class="fw-bold text-warning" style="font-size: 0.8rem;">{{ $booking['guide_options']['childQty'] ?? $booking['guide_options']['child_qty'] ?? $booking['guide_options']['childrenQty'] ?? $booking['guide_options']['children_qty'] ?? 0 }}</div>
                                                                               <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                                           </div>
                                                                       </div>
                                                                   </div>
                                                               </div>
                                                               @if(isset($booking['guide_options']['cost']) && $booking['guide_options']['cost'] > 0)
                                                               <div class="col-12">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Cost</small>
                                                                   <div class="fw-bold" style="font-size: 0.85rem; color: #00cec9;">{{ $currency }} {{ number_format((float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0), 2) }}</div>
                                                               </div>
                                                               @endif
                                                           </div>
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
                   <div class="text-center py-3">
                       <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                           <i class="ri-restaurant-2-line text-muted" style="font-size: 1.5rem;"></i>
                       </div>
                       <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Restaurant Data Available</h6>
                       <p class="text-muted mb-0" style="font-size: 0.75rem;">Restaurant services are booked but detailed information is not available.</p>
                   </div>
               @endif
               </div>
               <!-- Compact Footer -->
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
   <div class="modal fade service-modal-compact" id="guideDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="guideDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
       <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
           <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
               <!-- Compact Header -->
               <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%);">
                   <div class="d-flex align-items-center justify-content-between w-100">
                       <div class="text-white">
                           <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                               <i class="ri-user-voice-line me-1" style="font-size: 0.9rem;"></i>Guide Enquiries - Tour #{{ $tour->tour_id }}
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
                                               <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['hours'] ?? 'N/A' }} Hours Service</small>
                                           </div>
                                           <div class="col-md-4 text-end">
                                               <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                   {{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}
                                               </span>
                                           </div>
                                       </div>
                                   </div>
                                   
                                   <div class="card-body p-2" style="background-color: #ffffff;">
                                       <!-- Guide Information with Image -->
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
                                                           <div class="fw-medium text-success" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['basePrice'] ?? 0) }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                           <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['surcharge'] ?? 0) }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Total</small>
                                                           <div class="fw-bold" style="font-size: 0.8rem; color: #00cec9;">{{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}</div>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                           <div class="col-md-4">
                                               @if(isset($booking['image']))
                                                   <img src="{{ $booking['image'] }}" 
                                                       alt="{{ $booking['guide_name'] ?? 'Guide' }}" 
                                                       class="img-fluid rounded shadow-sm" 
                                                       style="height: 100px; width: 100%; object-fit: cover;">
                                               @else
                                                   <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                                       <i class="ri-user-voice-line text-muted" style="font-size: 2rem;"></i>
                                                   </div>
                                               @endif
                                           </div>
                                       </div>

                                       <!-- Service Schedule & Group Info -->
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
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Service Date</small>
                                                           <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                       </div>
                                                       <div class="col-6">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Start Time</small>
                                                           <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                       </div>
                                                       <div class="col-12">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Duration</small>
                                                           <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['hours'] ?? 'N/A' }} Hours</span>
                                                       </div>
                                                       @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                       <div class="col-12">
                                                           <small class="text-muted d-block" style="font-size: 0.65rem;">Night Service</small>
                                                           <div class="fw-medium text-warning" style="font-size: 0.7rem;">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                       </div>
                                                       @endif
                                                   </div>
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
                                                   <div class="row g-1">
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-6 text-center">
                                                           <div class="bg-white rounded p-1">
                                                               <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                               <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                           </div>
                                                       </div>
                                                       <div class="col-12 text-center mt-1">
                                                           <span class="badge" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                               Group: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} People
                                                           </span>
                                                       </div>
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
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #28a745 !important;">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                       <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['basePrice'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #ffc107 !important;">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                       <div class="fw-bold text-warning" style="font-size: 0.75rem;">{{ $currency }} {{ ceil($booking['surcharge'] ?? 0) }}</div>
                                                   </div>
                                               </div>
                                               <div class="col-4">
                                                   <div class="bg-white border rounded p-1 text-center" style="border-color: #00cec9 !important;">
                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Total</small>
                                                       <div class="fw-bold" style="font-size: 0.8rem; color: #00cec9;">{{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}</div>
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
                       <div class="text-center py-3">
                           <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                               <i class="ri-user-voice-line text-muted" style="font-size: 1.5rem;"></i>
                           </div>
                           <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Guide Data Available</h6>
                           <p class="text-muted mb-0" style="font-size: 0.75rem;">Guide services are booked but detailed information is not available.</p>
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
                                                   {{ $currency }} {{ number_format($entryCardTotal, 2) }}
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
                                                   <span class="fw-semibold text-dark">{{ $currency }} {{ number_format($entryTransferPrice, 2) }}</span>
                                               </div>
                                               @if($entryGuidePrice > 0)
                                               <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                   <span class="text-muted" style="font-size: 0.8rem;">Guide</span>
                                                   <span class="fw-semibold text-info">{{ $currency }} {{ number_format($entryGuidePrice, 2) }}</span>
                                               </div>
                                               @endif
                                               <div class="d-flex justify-content-between align-items-center py-2 mt-1">
                                                   <span class="fw-bold text-dark" style="font-size: 0.9rem;">Total</span>
                                                   <span class="fw-bold text-success" style="font-size: 1rem;">{{ $currency }} {{ number_format($entryCardTotal, 2) }}</span>
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
                                                       <div class="fw-bold text-success mt-1" style="font-size: 0.85rem;">{{ $currency }} {{ number_format($arrivalGuideCost, 2) }}</div>
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
                                                   {{ $currency }} {{ number_format($exitCardTotal, 2) }}
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
                                                   <span class="fw-semibold text-dark">{{ $currency }} {{ number_format($exitTransferPrice, 2) }}</span>
                                               </div>
                                               @if($exitGuidePrice > 0)
                                               <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                   <span class="text-muted" style="font-size: 0.8rem;">Guide</span>
                                                   <span class="fw-semibold text-info">{{ $currency }} {{ number_format($exitGuidePrice, 2) }}</span>
                                               </div>
                                               @endif
                                               <div class="d-flex justify-content-between align-items-center py-2 mt-1">
                                                   <span class="fw-bold text-dark" style="font-size: 0.9rem;">Total</span>
                                                   <span class="fw-bold text-success" style="font-size: 1rem;">{{ $currency }} {{ number_format($exitCardTotal, 2) }}</span>
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
                                                       <div class="fw-bold text-success mt-1" style="font-size: 0.85rem;">{{ $currency }} {{ number_format($depGuideCost, 2) }}</div>
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
                                                    <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</span>
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
                                                    <span class="fw-bold text-success" style="font-size: 1rem;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</span>
                                                </div>
                                                @if(isset($booking['adultSell']) || isset($booking['childSell']) || isset($booking['infantSell']))
                                                <small class="text-muted" style="font-size: 0.7rem;">Adult: {{ $booking['adultSell'] ?? 0 }} / Child: {{ $booking['childSell'] ?? 0 }} / Infant: {{ $booking['infantSell'] ?? 0 }}</small>
                                                @endif
                                            </div>
                                            @if(!empty($booking['city']) || !empty($booking['country']))
                                            <div class="mt-2">
                                                <small class="text-muted" style="font-size: 0.65rem;">Location</small>
                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? '' }}{{ !empty($booking['city']) && !empty($booking['country']) ? ', ' : '' }}{{ $booking['country'] ?? '' }}</div>
                                            </div>
                                            @endif
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

    <!-- Travel Hourly Details Modal -->
    @if(isset($svc['travel_hourly']) && $svc['travel_hourly'] > 0)
        <div class="modal fade service-modal-compact" id="travel_hourlyDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_hourlyModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    @php
                        $firstOrder = $serviceData['travel_hourly'][0] ?? null;
                        $firstBookingData = null;
                        if ($firstOrder) {
                            $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                            $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                        }
                    @endphp
                    
                    <!-- Compact Header -->
                    <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div class="text-white">
                                <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                    <i class="ri-time-line me-1" style="font-size: 0.9rem;"></i>Local-Tour Hourly - Tour #{{ $tour->tour_id }}
                                </h6>
                                <small class="opacity-90" style="font-size: 0.75rem;">{{ $firstBookingData['city'] ?? 'Location not specified' }} • {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('M d, Y') : 'Date TBC' }}</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-2" style="background: #f8f9fa;">
                        @if(isset($serviceData['travel_hourly']) && count($serviceData['travel_hourly']) > 0)
                            @foreach($serviceData['travel_hourly'] as $index => $hourlyOrder)
                                @php
                                    $hourlyData = is_string($hourlyOrder->data) ? json_decode($hourlyOrder->data, true) : $hourlyOrder->data;
                                @endphp
                                
                                @if(is_array($hourlyData))
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
                                                        <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['selectedHours'] ?? 'N/A' }} Hour(s) • {{ $booking['type'] ?? 'Standard' }}</small>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                            {{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body p-2" style="background-color: #ffffff;">
                                                <!-- Service Schedule & Pricing -->
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
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Booking Date</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                    <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Hours</small>
                                                                    <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['selectedHours'] ?? 'N/A' }} Hr(s)</span>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                    <span class="badge bg-warning" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['type'] ?? 'Standard' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100 d-flex align-items-center justify-content-center">
                                                            <div class="text-center">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Total Price</small>
                                                                <div class="fw-bold" style="font-size: 1rem; color: #667eea;">{{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Pickup Location & Vehicle -->
                                                <div class="row mb-2 g-2">
                                                    <div class="col-md-6">
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-map-pin-line text-white" style="font-size: 0.7rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pickup Location</h6>
                                                            </div>
                                                            <div class="row g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Point</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
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
                                                        <div class="bg-light rounded p-2 h-100">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="ri-car-line text-white" style="font-size: 0.7rem;"></i>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Vehicle Details</h6>
                                                            </div>
                                                            <div class="row g-1 align-items-center">
                                                                <div class="col-8">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle Name</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                                    <small class="text-muted" style="font-size: 0.6rem;">{{ $booking['type'] ?? 'N/A' }} Transfer</small>
                                                                </div>
                                                                <div class="col-4">
                                                                    @if(isset($booking['image']) && !empty($booking['image']))
                                                                        <img src="{{ $booking['image'] }}" alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" class="img-fluid rounded shadow-sm" style="height: 60px; width: 100%; object-fit: cover;">
                                                                    @else
                                                                        <div class="bg-white rounded d-flex align-items-center justify-content-center" style="height: 60px;">
                                                                            <i class="ri-car-line text-muted" style="font-size: 1.5rem;"></i>
                                                                        </div>
                                                                    @endif
                                                                </div>
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
                            <div class="text-center py-3">
                                <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="ri-time-line text-muted" style="font-size: 1.5rem;"></i>
                                </div>
                                <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Hourly Tour Data Available</h6>
                                <p class="text-muted mb-0" style="font-size: 0.75rem;">Hourly tour services are booked but detailed information is not available.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Compact Footer -->
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
        <div class="modal fade service-modal-compact" id="travel_pointDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_pointModalLabel{{ $tour->tour_id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow-lg">
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
                    <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                            <div class="text-white">
                                <h3 class="mb-1 fw-bold">
                                    <i class="ri-route-line me-2"></i>Local-Tour Point to Point
                                </h3>
                                <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Point to Point Transfer</p>
                                <div class="mt-2">
                                    <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('D, M d, Y') : 'Date not specified' }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4" style="background: #f8fafc;">
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
                                            <hr class="my-4">
                                        @endif
                                
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <div class="card-header bg-transparent border-0 text-white">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <h5 class="card-title mb-0 fw-bold">
                                                            <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Point to Point Transfer' }}
                                                        </h5>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                            <span class="text-success fw-bold fs-5">{{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transfer Schedule -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                    <i class="ri-calendar-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Transfer Schedule</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Date</small>
                                                    <div class="fw-medium">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') : 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Time</small>
                                                    <div class="fw-medium">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Distance</small>
                                                    <span class="badge bg-info">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <small class="text-muted">Service Type</small>
                                                    <span class="badge bg-warning">{{ $booking['type'] ?? 'Standard' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Route Details -->
                                <div class="bg-white rounded p-3 shadow-sm mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning rounded-circle p-2 me-3">
                                            <i class="ri-direction-line text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                    <i class="ri-play-circle-line text-white"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Pickup Location</small>
                                                    <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                    <small class="text-success">Origin</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                    <i class="ri-flag-line text-white"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Drop-off Location</small>
                                                    <div class="fw-medium">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                    <small class="text-danger">Destination</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted">City</small>
                                            <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted">Country</small>
                                            <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle Information -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <div class="bg-white rounded p-3 shadow-sm h-100">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-warning rounded-circle p-2 me-3">
                                                    <i class="ri-car-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <small class="text-muted">Vehicle Name</small>
                                                    <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <small class="text-muted">Service Type</small>
                                                    <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @if(isset($booking['image']))
                                            <img src="{{ $booking['image'] }}" 
                                                alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                class="img-fluid rounded shadow-sm" 
                                                style="height: 150px; width: 100%; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <i class="ri-car-line ri-48px text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="ri-route-line ri-48px text-muted mb-3"></i>
                            <h5 class="text-muted">No point to point transfer data available</h5>
                        </div>
                    @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light border-0" style="border-radius: 0 0 8px 8px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

<!-- Local Transport Details Modal -->
   @if(isset($svc['local_transport']) && $svc['local_transport'] > 0)
       <div class="modal fade service-modal-compact" id="local_transportDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="local_transportModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
           <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
               <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                   @php
                       $firstOrder = $serviceData['local_transport'][0] ?? null;
                       $firstBookingData = null;
                       $headerFromZone = 'N/A';
                       $headerToZone = 'N/A';
                       
                       if ($firstOrder) {
                           $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                           $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                           
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
                   
                   <!-- Compact Header -->
                   <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                       <div class="d-flex align-items-center justify-content-between w-100">
                           <div class="text-white">
                               <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                   <i class="ri-car-line me-1" style="font-size: 0.9rem;"></i>Local Transport - Tour #{{ $tour->tour_id }}
                               </h6>
                               <small class="opacity-90" style="font-size: 0.75rem;">{{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('M d, Y') : 'Date not specified' }}</small>
                           </div>
                           <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                       </div>
                   </div>

                   <!-- Modal Body -->
                   <div class="modal-body p-2" style="background: #f8f9fa;">
                       @if(isset($serviceData['local_transport']) && count($serviceData['local_transport']) > 0)
                           @foreach($serviceData['local_transport'] as $index => $transportOrder)
                               @php
                                   $transportData = is_string($transportOrder->data) ? json_decode($transportOrder->data, true) : $transportOrder->data;
                               @endphp
                               
                               @if(is_array($transportData))
                                   @foreach($transportData as $bookingIndex => $booking)
                                       @php
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
                               
                                       <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #4facfe !important;">
                                           <!-- Compact Card Header -->
                                           <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);">
                                               <div class="row align-items-center g-1">
                                                   <div class="col-md-8">
                                                       <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                           <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Local Transport Service' }}
                                                       </h6>
                                                       <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['distance'] ?? 'N/A' }} km • {{ $booking['type'] ?? 'Standard' }}</small>
                                                   </div>
                                                   <div class="col-md-4 text-end">
                                                       <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                           {{ $currency }} {{ ceil($booking['totalPrice'] ?? 0) }}
                                                       </span>
                                                   </div>
                                               </div>
                                           </div>

                                           <div class="card-body p-2" style="background-color: #ffffff;">
                                               <!-- Transport Schedule & Passengers -->
                                               <div class="row mb-2 g-2">
                                                   <div class="col-md-6">
                                                       <div class="bg-light rounded p-2 h-100">
                                                           <div class="d-flex align-items-center mb-1">
                                                               <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
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
                                                                   <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Distance</small>
                                                                   <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                                               </div>
                                                               <div class="col-6">
                                                                   <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                   <span class="badge bg-warning" style="font-size: 0.65rem; padding: 2px 6px;">{{ $booking['type'] ?? 'Standard' }}</span>
                                                               </div>
                                                           </div>
                                                       </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                       <div class="bg-light rounded p-2 h-100">
                                                           <div class="d-flex align-items-center mb-1">
                                                               <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                   <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                               </div>
                                                               <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Passengers</h6>
                                                           </div>
                                                           <div class="row g-1">
                                                               <div class="col-6 text-center">
                                                                   <div class="bg-white rounded p-1">
                                                                       <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                       <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                                   </div>
                                                               </div>
                                                               <div class="col-6 text-center">
                                                                   <div class="bg-white rounded p-1">
                                                                       <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                       <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                                   </div>
                                                               </div>
                                                               <div class="col-12 text-center mt-1">
                                                                   <span class="badge" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                                       Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passenger{{ (($booking['adults'] ?? 0) + ($booking['children'] ?? 0)) == 1 ? '' : 's' }}
                                                                   </span>
                                                               </div>
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>

                                               <!-- Route Details -->
                                               <div class="bg-light rounded p-1 mb-2">
                                                   <div class="d-flex align-items-center mb-1">
                                                       <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                           <i class="ri-direction-line text-white" style="font-size: 0.7rem;"></i>
                                                       </div>
                                                       <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Details</h6>
                                                   </div>
                                                   <div class="bg-white rounded p-1">
                                                       <div class="row g-1">
                                                           <div class="col-md-6">
                                                               <div class="d-flex align-items-start">
                                                                   <div class="rounded-circle p-1 me-2 mt-1" style="background-color: #28a745; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                                                       <i class="ri-play-circle-line text-white" style="font-size: 0.6rem;"></i>
                                                                   </div>
                                                                   <div>
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                                       <small class="text-success" style="font-size: 0.6rem;">Origin</small>
                                                                   </div>
                                                               </div>
                                                           </div>
                                                           <div class="col-md-6">
                                                               <div class="d-flex align-items-start">
                                                                   <div class="rounded-circle p-1 me-2 mt-1" style="background-color: #dc3545; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                                                       <i class="ri-flag-line text-white" style="font-size: 0.6rem;"></i>
                                                                   </div>
                                                                   <div>
                                                                       <small class="text-muted d-block" style="font-size: 0.65rem;">Drop-off Location</small>
                                                                       <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['dropoffLocation'] ?? 'N/A' }}</div>
                                                                       <small class="text-danger" style="font-size: 0.6rem;">Destination</small>
                                                                   </div>
                                                               </div>
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

                                               <!-- Vehicle Information -->
                                               <div class="row mb-2 g-2">
                                                   <div class="col-md-8">
                                                       <div class="bg-light rounded p-2 h-100">
                                                           <div class="d-flex align-items-center mb-1">
                                                               <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
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
                                                       @if(isset($booking['image']))
                                                           <img src="{{ $booking['image'] }}" alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" class="img-fluid rounded shadow-sm" style="height: 80px; width: 100%; object-fit: cover;">
                                                       @else
                                                           <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                               <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                           </div>
                                                       @endif
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
                               <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Local Transport Data Available</h6>
                               <p class="text-muted mb-0" style="font-size: 0.75rem;">Local transport services are booked but detailed information is not available.</p>
                           </div>
                       @endif
                   </div>

                   <!-- Compact Footer -->
                   <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
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
@endforeach
</div>

<script>
function followUpNow(tourId) {
    // Implementation for immediate follow up
    console.log('Following up tour', tourId);
    // Open follow up modal or redirect to communication page
}

function convertToTentative(tourId) {
    if (confirm('Are you sure you want to mark this prospect as Tentative?')) {
        console.log('Converting tour', tourId, 'to Tentative status');
    }
}

function convertToConfirmed(tourId) {
    if (confirm('Are you sure you want to mark this prospect as Confirmed?')) {
        console.log('Converting tour', tourId, 'to Confirmed status');
    }
}

function scheduleCallback(tourId) {
    // Implementation for scheduling callback
    console.log('Scheduling callback for tour', tourId);
}

function markAsLost(tourId) {
    if (confirm('Are you sure you want to mark this prospect as lost? This will remove it from follow-ups.')) {
        console.log('Marking tour', tourId, 'as lost');
    }
}

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
                    
                    // Restore button state
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error cancelling tour:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while cancelling the tour. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                
                // Restore button state
                button.innerHTML = originalContent;
                button.disabled = false;
            });
        }
    });
};

function scheduleFollowUp() {
    const selectedTours = document.querySelectorAll('.row-checkbox:checked');
    if (selectedTours.length === 0) {
        alert('Please select at least one prospect to schedule follow-up.');
        return;
    }
    console.log('Scheduling follow-up for', selectedTours.length, 'prospects');
}

function requestPayment(tourId) {
    console.log('Requesting payment for tour', tourId);
    // Implementation for payment request
}

function extendDeadline(tourId) {
    const newDeadline = prompt('Enter new deadline (YYYY-MM-DD):');
    if (newDeadline) {
        console.log('Extending deadline for tour', tourId, 'to', newDeadline);
    }
}

function exportData() {
    console.log('Exporting follow-up data...');
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
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Modal Not Found',
                text: `Could not find ${serviceType} details modal for tour ${tourId}. Please refresh the page and try again.`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert(`Could not find ${serviceType} details modal for tour ${tourId}. Please refresh the page and try again.`);
        }
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
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while opening the modal. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('An error occurred while opening the modal. Please try again.');
        }
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

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const destinationFilter = document.getElementById('destinationFilter');
    const agentFilter = document.getElementById('agentFilter');
    // const followUpFilter = document.getElementById('followUpFilter'); // Commented out since element is not available
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const today = new Date().toISOString().split('T')[0];
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (destinationFilter) destinationFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    // if (followUpFilter) followUpFilter.addEventListener('change', filterTable); // Commented out since element is not available
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
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
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

        const headerTexts = Array.from(document.querySelectorAll('#toursTable thead th')).map(th => th.textContent.trim());
        const colIdx = (name) => headerTexts.indexOf(name);
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = (row.getAttribute('data-destination') || '').trim();
        const agentIdx = colIdx('Agent');
        const createdIdx = colIdx('Created');
        const statusIdx = colIdx('Status');
        const agent = (agentIdx >= 0 && row.cells[agentIdx]) ? (row.cells[agentIdx].querySelector('.fw-medium')?.textContent || '') : '';
        const createdBy = (createdIdx >= 0 && row.cells[createdIdx]) ? (row.cells[createdIdx].querySelector('.fw-medium')?.textContent || '') : '';
        const status = (statusIdx >= 0 && row.cells[statusIdx]) ? (row.cells[statusIdx].querySelector('.badge')?.textContent || '') : '';
        const updatedAt = row.getAttribute('data-updated-at');
        const createdAt = row.getAttribute('data-created-at');

        let show = true;

        if (searchTerm &&
            !tourDetails.includes(searchTerm) &&
            !destination.toLowerCase().includes(searchTerm) &&
            !agent.toLowerCase().includes(searchTerm) &&
            !createdBy.toLowerCase().includes(searchTerm)) {
            show = false;
        }

        if (statusFilter && !status.toLowerCase().includes(statusFilter.toLowerCase())) {
            show = false;
        }

        // Destination filter - use LIKE operator logic (contains)
        // This works for multi-country destinations like "India, Singapore"
        if (destinationFilter) {
            // Split destination by comma and trim spaces
            const destinationCountries = destination.split(',').map(c => c.trim());
            // Check if the selected destination is in the destination list
            if (!destinationCountries.includes(destinationFilter)) {
                show = false;
            }
        }

        if (agentFilter && agent !== agentFilter) {
            show = false;
        }

        if ((startDateValue || endDateValue) && (updatedAt || createdAt)) {
            const startDate = startDateValue ? new Date(startDateValue + 'T00:00:00') : null;
            const endDate = endDateValue ? new Date(endDateValue + 'T23:59:59') : null;
            let dateInRange = false;

            if (updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if ((!startDate || updatedDate >= startDate) && (!endDate || updatedDate <= endDate)) {
                    dateInRange = true;
                }
            }

            if (!dateInRange && createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if ((!startDate || createdDate >= startDate) && (!endDate || createdDate <= endDate)) {
                    dateInRange = true;
                }
            }

            if (!dateInRange) {
                show = false;
            }
        } else if (startDateValue || endDateValue) {
            // If dates are selected but no timestamps available, hide row
            show = false;
        }

        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    const visibleRows = Array.from(document.querySelectorAll('#toursTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
    const rangeCount = visibleCount;
    const prospectCount = visibleRows.filter(r => r.getAttribute('data-tour-status') === 'Prospect').length;
    const tentativeCount = visibleRows.filter(r => r.getAttribute('data-tour-status') === 'Tentative').length;

    const sevenDaysAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    const overdueCount = visibleRows.filter(r => {
        const updated = r.getAttribute('data-updated-at');
        return updated && updated < sevenDaysAgo;
    }).length;

    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statFollowUps = document.getElementById('statFollowUpsCount');
    const statFollowUpsLabel = document.getElementById('statFollowUpsLabel');
    const statProspects = document.getElementById('statProspectsCount');
    const statProspectsLabel = document.getElementById('statProspectsLabel');
    const statTentative = document.getElementById('statTentativeCount');
    const statTentativeLabel = document.getElementById('statTentativeLabel');
    const statOverdue = document.getElementById('statOverdueCount');
    const statOverdueLabel = document.getElementById('statOverdueLabel');

    if (countEl) countEl.textContent = rangeCount;
    if (statFollowUps) statFollowUps.textContent = rangeCount;
    if (statProspects) statProspects.textContent = prospectCount;
    if (statTentative) statTentative.textContent = tentativeCount;
    if (statOverdue) statOverdue.textContent = overdueCount;

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

        if (labelEl && label) labelEl.textContent = label;
        if (statFollowUpsLabel && label) statFollowUpsLabel.textContent = `Follow Ups - ${label}`;
        if (statProspectsLabel && label) statProspectsLabel.textContent = `Prospects - ${label}`;
        if (statTentativeLabel && label) statTentativeLabel.textContent = `Tentative - ${label}`;
        if (statOverdueLabel && label) statOverdueLabel.textContent = `Overdue - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statFollowUpsLabel) statFollowUpsLabel.textContent = `${month} Follow Ups`;
        if (statProspectsLabel) statProspectsLabel.textContent = `${month} Prospects`;
        if (statTentativeLabel) statTentativeLabel.textContent = `${month} Tentative`;
        if (statOverdueLabel) statOverdueLabel.textContent = `${month} Overdue`;
    }
}

function resetFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusSelect = document.getElementById('statusFilter');
    const destinationSelect = document.getElementById('destinationFilter');
    const agentSelect = document.getElementById('agentFilter');
    const startDateInput = document.getElementById('startDateFilter');
    const endDateInput = document.getElementById('endDateFilter');

    if (searchInput) searchInput.value = '';
    if (statusSelect) statusSelect.value = '';
    // Reset Select2 dropdowns properly
    if (destinationSelect && $('#destinationFilter').hasClass('select2-hidden-accessible')) {
        $('#destinationFilter').val('').trigger('change');
    } else if (destinationSelect) {
        destinationSelect.value = '';
    }
    if (agentSelect && $('#agentFilter').hasClass('select2-hidden-accessible')) {
        $('#agentFilter').val('').trigger('change');
    } else if (agentSelect) {
        agentSelect.value = '';
    }
    if (startDateInput) startDateInput.value = '';
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
        // Only show badge if there are meaningful results and filters are actually applied
        const hasActiveFilters = checkActiveFilters();
        if (hasActiveFilters && visibleCount < totalCount && totalCount > 1) {
            filterResultsBadge.textContent = `${visibleCount} of ${totalCount} shown`;
            filterResultsBadge.style.display = 'inline-block';
        } else {
            filterResultsBadge.style.display = 'none';
        }
    }
}

function checkActiveFilters() {
    const searchInput = document.getElementById('searchInput')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const destinationFilter = document.getElementById('destinationFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const dateStart = document.getElementById('startDateFilter')?.value || '';
    const dateEnd = document.getElementById('endDateFilter')?.value || '';
    
    return searchInput || statusFilter || destinationFilter || agentFilter || dateStart || dateEnd;
}

function showFilterResetMessage() {
    // Create a temporary success message
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="ri-check-circle-line me-2"></i>
        <strong>Filters Reset!</strong> All filters have been cleared successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}
</script>
@endsection

@section('scripts')
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

        /* Body-level tooltip for service icons and action icons (match new-enquiries) */
        var $globalTooltip = $('#service-icon-global-tooltip');
        if (!$globalTooltip.length) {
            $globalTooltip = $('<div id="service-icon-global-tooltip" aria-hidden="true"></div>').appendTo('body');
        } else {
            $globalTooltip.appendTo('body');
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
        /* Tooltip for table header columns (same as new-enquiries) */
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
    });
    
    function initializeSelect2() {
        // Initialize Select2 for Destination filter
        $('#destinationFilter').select2({
            placeholder: 'All Destinations',
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
        $('#destinationFilter, #agentFilter').on('change', function() {
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

        const servicesIdx = colIndex('Services');
        const statusIdx = colIndex('Status');
        const agentNegotiationIdx = colIndex('Agent Negotiation');
        const negotiationIdx = colIndex('Negotiation');
        const actionsIdx = colIndex('Actions');

        const nonOrderableTargets = [servicesIdx, statusIdx, agentNegotiationIdx, negotiationIdx, actionsIdx].filter(i => i >= 0);
        const nonSearchableTargets = [agentNegotiationIdx, negotiationIdx, actionsIdx].filter(i => i >= 0);

        // Initialize DataTable with export buttons
        // autoWidth: false + colgroup in HTML = same column widths before and after init (no jump)
        // responsive: false avoids responsive extension redraws that can shift layout
        table = $('.datatables-basic').DataTable({
            autoWidth: false,
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
            pageLength: 25,
            // order: [[8, 'desc']], // Sort by Last Contact column (index 8) in descending order
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
                    targets: [servicesIdx].filter(i => i >= 0),
                    orderable: false,
                }
            ],
            initComplete: function() {
                console.log('DataTable initialized successfully');
            }
        });

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
        
        // Modal helper functions for Update Price
        window.openFollowupModal = function(button, route) {
            var modalEl = document.getElementById('followupUpdateModal');
            var form = document.getElementById('followupUpdateForm');
            var priceInput = document.getElementById('followup_current_price');
            var commentInput = document.getElementById('followup_comment');
            var idInput = document.getElementById('followup_modal_enquiry_id');
            var displayActual = document.getElementById('followup_display_actual');
            var displayPrice = document.getElementById('followup_display_price');
            var displayDiscount = document.getElementById('followup_display_discount');
            var displayComment = document.getElementById('followup_display_comment');

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

        window.validateFollowupPrice = function(input) {
            var maxValue = parseFloat(input.getAttribute('max'));
            var currentValue = parseFloat(input.value);
            var warningMessage = document.getElementById('followup-warning-message');
            
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
            $('#followupUpdateForm').on('submit', function(e) {
                const submitBtn = document.getElementById('followup_submit_btn');
                const cancelBtn = document.getElementById('followup_cancel_btn');
                
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
    };
</script>
@endsection

@extends('layouts.datatablejs')
