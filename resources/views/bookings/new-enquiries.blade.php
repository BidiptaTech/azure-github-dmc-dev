@extends('layouts.layout')
@section('title', 'New Enquiries')
@extends('layouts.datatablecss')

<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
@include('bookings.partials.services')
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
    #toursTable tbody tr:has(.quotation-actions-flyout:hover),
    #toursTable tbody tr:has(.quotation-actions-flyout:focus-within),
    #toursTable tbody tr:has(.invoice-actions-flyout:hover),
    #toursTable tbody tr:has(.invoice-actions-flyout:focus-within) {
        z-index: 40;
    }
    
    /* Services column: professional soft-badge style (light bg, colored icon) */
    #toursTable td.services-column-cell {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        overflow: visible !important;
    }
    #toursTable td.services-column-cell .services-country-cell {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-width: 0;
        max-width: 100%;
    }
    #toursTable td.services-column-cell .services-country-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.2rem;
        align-items: center;
    }
    #toursTable td.services-column-cell .services-country-tab {
        appearance: none;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        font-size: 0.62rem;
        font-weight: 600;
        line-height: 1.2;
        letter-spacing: 0.01em;
        padding: 0.12rem 0.35rem;
        border-radius: 3px;
        cursor: pointer;
        max-width: 7.5rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    #toursTable td.services-column-cell .services-country-tab:hover {
        border-color: #94a3b8;
        background: #f1f5f9;
        color: #334155;
    }
    #toursTable td.services-column-cell .services-country-tab.is-active {
        border-color: #0f766e;
        background: #0f766e;
        color: #fff;
    }
    #toursTable td.services-column-cell .services-country-panel {
        display: none;
    }
    #toursTable td.services-column-cell .services-country-panel.is-active {
        display: grid;
    }
    #toursTable td.services-column-cell .services-icons-wrap {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        row-gap: 0.35rem;
        column-gap: 0.35rem;
        align-items: stretch;
        max-width: 100%;
    }
    #toursTable td.services-column-cell .service-icon-wrapper {
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
        overflow: visible;
        position: relative;
        display: grid;
        grid-template-columns: repeat(3, auto);
        row-gap: 0.5rem;
        column-gap: 0.5rem;
        align-items: center;
        justify-content: start;
        max-width: 100%;
    }
    #toursTable .actions-icons-wrap > a,
    #toursTable .actions-icons-wrap > form,
    #toursTable .actions-icons-wrap > button,
    #toursTable .actions-icons-wrap > .quotation-actions-flyout,
    #toursTable .actions-icons-wrap > .invoice-actions-flyout {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    /* Hover reveals stacked links immediately beside the trigger icon */
    #toursTable .quotation-actions-flyout,
    #toursTable .invoice-actions-flyout {
        position: relative;
        display: inline-flex;
        align-items: flex-start;
        justify-content: center;
        z-index: 1;
    }
    #toursTable .quotation-actions-flyout:hover,
    #toursTable .quotation-actions-flyout:focus-within,
    #toursTable .invoice-actions-flyout:hover,
    #toursTable .invoice-actions-flyout:focus-within {
        z-index: 20;
    }
    #toursTable .quotation-actions-flyout:hover .quotation-actions-flyout__trigger,
    #toursTable .quotation-actions-flyout:focus-within .quotation-actions-flyout__trigger,
    #toursTable .invoice-actions-flyout:hover .invoice-actions-flyout__trigger,
    #toursTable .invoice-actions-flyout:focus-within .invoice-actions-flyout__trigger {
        border-color: color-mix(in srgb, var(--action-color, #0f766e) 55%, #e2e8f0);
        background: color-mix(in srgb, var(--action-color, #0f766e) 14%, #fff);
        box-shadow: 0 0 0 1px color-mix(in srgb, var(--action-color, #0f766e) 30%, transparent),
                    0 0 12px color-mix(in srgb, var(--action-color, #0f766e) 38%, transparent);
    }
    #toursTable .quotation-actions-flyout__links,
    #toursTable .invoice-actions-flyout__links {
        position: absolute;
        left: calc(100% + 0.2rem);
        top: 0;
        right: auto;
        bottom: auto;
        transform: none;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.35rem;
        padding: 0.35rem;
        margin: 0;
        list-style: none;
        background: rgba(255, 255, 255, 0.97);
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.14);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.15s ease, visibility 0.15s ease, box-shadow 0.15s ease;
        white-space: nowrap;
        z-index: 30;
    }
    #toursTable .quotation-actions-flyout::before,
    #toursTable .invoice-actions-flyout::before {
        content: '';
        position: absolute;
        left: 100%;
        top: 0;
        width: 0.35rem;
        height: 100%;
        z-index: 29;
    }
    #toursTable .quotation-actions-flyout:hover .quotation-actions-flyout__links,
    #toursTable .quotation-actions-flyout:focus-within .quotation-actions-flyout__links {
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.14),
                    0 0 20px color-mix(in srgb, #0f766e 22%, transparent);
    }
    #toursTable .invoice-actions-flyout:hover .invoice-actions-flyout__links,
    #toursTable .invoice-actions-flyout:focus-within .invoice-actions-flyout__links {
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.14),
                    0 0 20px color-mix(in srgb, #0e7490 22%, transparent);
    }
    #toursTable .quotation-actions-flyout__links .action-icon-badge,
    #toursTable .invoice-actions-flyout__links .action-icon-badge {
        border-color: color-mix(in srgb, var(--action-color, #475569) 50%, #e2e8f0);
        background: color-mix(in srgb, var(--action-color, #475569) 16%, #fff);
        box-shadow: 0 0 0 1px color-mix(in srgb, var(--action-color, #475569) 28%, transparent),
                    0 0 14px color-mix(in srgb, var(--action-color, #475569) 42%, transparent);
    }
    #toursTable .quotation-actions-flyout__links .action-icon-badge:hover,
    #toursTable .invoice-actions-flyout__links .action-icon-badge:hover {
        background: color-mix(in srgb, var(--action-color, #475569) 24%, #fff);
        border-color: color-mix(in srgb, var(--action-color, #475569) 62%, #e2e8f0);
        box-shadow: 0 0 0 1px color-mix(in srgb, var(--action-color, #475569) 38%, transparent),
                    0 0 18px color-mix(in srgb, var(--action-color, #475569) 55%, transparent);
    }
    #toursTable .quotation-actions-flyout__links::after,
    #toursTable .invoice-actions-flyout__links::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        right: 100%;
        width: 0.35rem;
    }
    #toursTable .quotation-actions-flyout:hover .quotation-actions-flyout__links,
    #toursTable .quotation-actions-flyout:focus-within .quotation-actions-flyout__links,
    #toursTable .invoice-actions-flyout:hover .invoice-actions-flyout__links,
    #toursTable .invoice-actions-flyout:focus-within .invoice-actions-flyout__links {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
    #toursTable .actions-icons-wrap form {
        margin: 0;
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

    /* Negotiation modals – shared layout */
    .negotiation-modal-content {
        border-radius: 0.85rem;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    }
    .negotiation-modal-header {
        background: linear-gradient(135deg, #6366f1 0%, #7c83ed 55%, #818cf8 100%);
        color: #fff;
        padding: 1.05rem 1.35rem;
    }
    .negotiation-modal-header .modal-title {
        font-size: 1.02rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: #fff !important;
    }
    .negotiation-modal-header small {
        font-size: 0.74rem;
        color: rgba(255, 255, 255, 0.9) !important;
    }
    .negotiation-modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.7;
        transition: opacity 0.15s ease;
    }
    .negotiation-modal-header .btn-close:hover {
        opacity: 1;
    }
    .negotiation-pricing-summary {
        background: #fff;
        border: 1px solid #e5e9f0;
        border-radius: 0.7rem;
        padding: 0.95rem 1.05rem;
    }
    .negotiation-pricing-item .negotiation-label,
    .negotiation-pricing-summary .negotiation-label {
        display: block;
        font-size: 0.66rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #7c879b;
        margin-bottom: 0.2rem;
    }
    .negotiation-pricing-item .negotiation-value {
        font-size: 1.02rem;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.3;
        font-variant-numeric: tabular-nums;
    }
    .negotiation-pricing-item.negotiation-discount .negotiation-value {
        color: #dc2626;
    }
    .negotiation-pricing-item.negotiation-payable .negotiation-value {
        color: #2563eb;
        font-size: 1.12rem;
    }
    .negotiation-formula-hint {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-top: 0.65rem;
        padding-top: 0.55rem;
        border-top: 1px dashed #e5e9f0;
    }
    .negotiation-meta-block {
        background: #fbfcfe;
        border: 1px solid #e5e9f0;
        border-radius: 0.55rem;
        padding: 0.7rem 0.9rem;
    }
    .negotiation-meta-block .negotiation-label {
        display: block;
        font-size: 0.66rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #7c879b;
        margin-bottom: 0.2rem;
    }
    .negotiation-meta-block .negotiation-value {
        font-weight: 600;
        color: #0f172a;
        font-variant-numeric: tabular-nums;
    }
    .negotiation-modal-footer {
        background: #f7f9fc;
        border-top: 1px solid #e5e9f0;
        padding: 0.85rem 1.15rem;
    }
    .negotiation-modal-footer .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.86rem;
        padding: 0.45rem 1.1rem;
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
    @include('bookings.partials.booking-type-tabs', [
        'type' => 'tours',
        'toursUrl' => route('bookings.new-enquiries'),
        'packagesUrl' => route('package-bookings.new-enquiries'),
    ])
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
                    <input type="date" class="form-control form-control-sm" id="startDateFilter" value="{{ now()->toDateString() }}">
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <label class="form-label mb-0 small text-muted">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="endDateFilter" value="{{ now()->addDays(30)->toDateString() }}">
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
                            data-created-at="{{ optional($tour->destination_created_at ?? $tour->created_at)->toDateString() }}"
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
                                    
                                    @if($tour->reference_id)
                                        <small class="text-dark">Ref: {{ $tour->reference_id }}</small>
                                    @endif
                                    @include('bookings.partials.tour-detail-badges', ['tour' => $tour])
                                    <span class="fw-medium mt-1"><i class="ri-map-pin-line me-1"></i>{{ $tour->destination ?? 'N/A' }}</span>
                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <span title="Adults"><i class="ri-user-line text-success"></i> {{ $tour->adult ?? 0 }}</span>
                                        <span title="Children"><i class="ri-user-smile-line text-warning"></i> {{ $tour->child ?? 0 }}</span>
                                        <span title="Infants"><i class="ri-user-heart-line text-info"></i> {{ $tour->infant ?? 0 }}</span>
                                    </div>
                                    @if($tour->check_in_time || $tour->check_out_time)
                                        <small>
                                            @if($tour->check_in_time)<span><strong>In:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }}</span>@endif
                                            <br>
                                            @if($tour->check_out_time)<span><strong>Out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</span>@endif
                                        </small>
                                    @else
                                        <small class="text-muted">Check-in/out: Not specified</small>
                                    @endif

                                    @php
                                        $mainGuest = $tour->mainguest;
                                        if (is_string($mainGuest)) {
                                            $mainGuest = json_decode($mainGuest, true) ?: [];
                                        }

                                        $leadGuestName = null;
                                        if (is_array($mainGuest)) {
                                            $salutation = trim($mainGuest['salutation'] ?? '');
                                            // Prefer full_name if present (matches logged structure)
                                            $fullName   = trim($mainGuest['full_name'] ?? '');
                                            $firstName  = trim($mainGuest['first_name'] ?? '');
                                            $lastName   = trim($mainGuest['last_name'] ?? '');

                                            if (!empty($fullName)) {
                                                $leadGuestName = trim($salutation . ' ' . $fullName);
                                            } else {
                                                $leadGuestName = trim($salutation . ' ' . $firstName . ' ' . $lastName);
                                            }
                                        }

                                        if (empty($leadGuestName) && !empty($tour->customer_name)) {
                                            $leadGuestName = $tour->customer_name;
                                        }
                                    @endphp

                                    @if(!empty($leadGuestName))
                                        @php
                                            $tourTypeLower = strtolower($tour->tour_type ?? '');
                                            $bgColor = $tourTypeLower === 'group' ? '#7c3aed' : '#059669';
                                            $textColor = '#ffffff';
                                        @endphp
                                        <small>
                                            <i class="ri-user-line me-1"></i>
                                            <span class="d-inline-block px-2 py-1 rounded" style="background: {{ $bgColor }}; color: {{ $textColor }}; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.3px;">
                                                {{ $leadGuestName }}
                                            </span>
                                        </small>
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
                            <td class="align-top services-column-cell">
                                @include('bookings.partials.enquiry-services-cell', [
                                    'tour' => $tour,
                                    'serviceCountryScope' => $serviceCountryScope ?? ['restricted' => false, 'countries' => []],
                                ])
                            </td>
                            @php
                                $tourEnquiries = $enquary_comments->where('tour_id', $tour->tour_id)->sortByDesc('created_at')->values();
                                $latestComment = $tourEnquiries->first();
                                $latestAgentComment = $tourEnquiries->first(function ($comment) {
                                    return strtolower($comment->sender_type ?? '') === 'agent';
                                });
                                
                                // Enquiry id used for update-price-comment / Confirm in agent modal (prefer agent’s thread, then active, then latest)
                                $activeEnquiryRow = \App\Models\Enquiry::where('tour_id', $tour->tour_id)
                                    ->where('status', 1)
                                    ->orderByDesc('created_at')
                                    ->first();
                                $enquiryIdForPriceUpdate = $latestAgentComment?->enquiry_id
                                    ?? $activeEnquiryRow?->enquiry_id
                                    ?? null;
                                
                                // Get enquiry details from Enquiry table
                                $enquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)->latest()->first();
                                if ($enquiryIdForPriceUpdate === null && $enquiry) {
                                    $enquiryIdForPriceUpdate = $enquiry->enquiry_id;
                                }
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
                                // Hotel: use pickup total only (itemPrice) - transport/transfer is already included, do NOT add transfer price
                                // Other services: base price + transfer price + guide price
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
                                                    // PRO tours: prefer totalPrice (base × pax) when available
                                                    if ($tour->is_pro == 1 && isset($item['transfer_options']['totalPrice'])) {
                                                        $transferPrice = (float) $item['transfer_options']['totalPrice'];
                                                    } else {
                                                        $transferPrice = (float) $item['transfer_options']['cost'];
                                                    }
                                                }
                                                
                                                // Add guide price if exists (attractions, entry_port, exit_port, restaurant, etc.)
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
                                
                                $grossTourAmount = ceil($tourTotalPrice);

                                // Markup stored on the tour (markup_amount holds the % when type = percentage,
                                // otherwise a flat money value). Markup increases the payable amount.
                                $tourMarkupType = $tour->markup_type ?? null;
                                $tourMarkupRaw = (float) ($tour->getAttributes()['markup_amount'] ?? $tour->markup_amount ?? 0);
                                $tourMarkupOn = ((int) ($tour->markup ?? 0) === 1)
                                    && $tourMarkupRaw > 0
                                    && in_array($tourMarkupType, ['percentage', 'flat'], true);
                                $tourMarkupMoney = $tourMarkupOn
                                    ? ($tourMarkupType === 'percentage'
                                        ? ($grossTourAmount * $tourMarkupRaw / 100)
                                        : $tourMarkupRaw)
                                    : 0;
                                $tourMarkupMoney = max(0, $tourMarkupMoney);

                                // Discount stored on the tour. For percentage the column holds the %,
                                // for flat / foc it holds a money value. Discount is applied after markup.
                                $tourDiscountType = $tour->discount_type ?? null;
                                $tourDiscountRaw = (float) ($tour->getAttributes()['discount_amount'] ?? $tour->discount_amount ?? 0);
                                $discountBaseAmount = $grossTourAmount + $tourMarkupMoney;
                                if ($tourDiscountType === 'percentage') {
                                    $tourDiscountMoney = $discountBaseAmount * $tourDiscountRaw / 100;
                                } elseif (in_array($tourDiscountType, ['flat', 'foc'], true)) {
                                    $tourDiscountMoney = $tourDiscountRaw;
                                } else {
                                    $tourDiscountMoney = 0;
                                }
                                $tourDiscountMoney = max(0, $tourDiscountMoney);

                                // Payable = Gross + Markup − Discount (business calculation), rounded up.
                                $netNegotiationBase = max(0, ceil($grossTourAmount + $tourMarkupMoney - $tourDiscountMoney));

                                // Aliases kept for the existing data attributes / JS (now hold money values).
                                $tourDiscountAmount = $tourDiscountMoney;
                                $discount = $tourDiscountMoney;

                                $hasAgentComment = $latestComment && strtolower($latestComment->sender_type ?? '') === 'agent';
                                if ($hasAgentComment) {
                                    $agentOffer = (float) ($latestComment->amount ?? 0);
                                    $agentRowCap = (float) ($latestComment->actual_amount ?? 0);
                                    $capFromNegotiation = $agentRowCap > 0;
                                    $currentActualAmount = $capFromNegotiation ? $agentRowCap : $netNegotiationBase;
                                    $settlementFromNegotiation = $agentOffer > 0;
                                    $settlementAmount = $settlementFromNegotiation ? $agentOffer : $netNegotiationBase;
                                    $lastAgentAmount = $agentOffer > 0 ? $agentOffer : null;
                                    $lastAgentRemark = $latestComment->comment ?? '';
                                } else {
                                    $latestCounter = ($latestComment && (float) ($latestComment->amount ?? 0) > 0)
                                        ? (float) $latestComment->amount
                                        : 0;
                                    $capFromNegotiation = $latestCounter > 0;
                                    $currentActualAmount = $capFromNegotiation ? $latestCounter : $netNegotiationBase;
                                    $settlementFromNegotiation = $capFromNegotiation;
                                    $settlementAmount = $currentActualAmount;
                                    $lastAgentAmount = ($latestAgentComment && (float) ($latestAgentComment->amount ?? 0) > 0)
                                        ? (float) $latestAgentComment->amount
                                        : null;
                                    $lastAgentRemark = $latestAgentComment->comment ?? ($latestComment->comment ?? '');
                                }

                                // Services added after the last negotiation are added on top of the agreed
                                // amount. Baseline = gross captured when the negotiation was submitted.
                                $negotiationBaselineGross = (float) ($latestComment?->gross_amount ?? 0);
                                $addedSinceNegotiation = 0;
                                if ($negotiationBaselineGross > 0 && $grossTourAmount > $negotiationBaselineGross) {
                                    $addedGrossRaw = $grossTourAmount - $negotiationBaselineGross;
                                    $addedSinceNegotiation = ($tourMarkupOn && $tourMarkupType === 'percentage')
                                        ? $addedGrossRaw * (1 + $tourMarkupRaw / 100)
                                        : $addedGrossRaw;
                                    $addedSinceNegotiation = ceil($addedSinceNegotiation);
                                }
                                if ($addedSinceNegotiation > 0) {
                                    if (!empty($capFromNegotiation)) {
                                        $currentActualAmount += $addedSinceNegotiation;
                                    }
                                    if (!empty($settlementFromNegotiation)) {
                                        $settlementAmount += $addedSinceNegotiation;
                                    }
                                }

                                $agentNegotiationCap = $currentActualAmount;
                                $baseAmount = $netNegotiationBase;
                                $canCheckNegotiation = $hasAgentComment;

                                $agentNegotiationDetails = [];
                                if ($hasAgentComment && is_array($latestComment->negotiation_details ?? null)) {
                                    $agentNegotiationDetails = $latestComment->negotiation_details;
                                } elseif ($latestAgentComment && is_array($latestAgentComment->negotiation_details ?? null)) {
                                    $agentNegotiationDetails = $latestAgentComment->negotiation_details;
                                }
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
                                        data-enquiry-id="{{ $enquiryIdForPriceUpdate ?? '' }}"
                                        data-display-id="{{ e($tour->display_id) }}"
                                        data-actual="{{ $agentNegotiationCap ?? 0 }}"
                                        data-gross="{{ $grossTourAmount ?? 0 }}"
                                        data-discount-amount="{{ $tourDiscountAmount ?? 0 }}"
                                        data-markup-type="{{ $tourMarkupType ?? '' }}"
                                        data-markup-raw="{{ $tourMarkupRaw ?? 0 }}"
                                        data-markup-money="{{ $tourMarkupMoney ?? 0 }}"
                                        data-markup-on="{{ !empty($tourMarkupOn) ? 1 : 0 }}"
                                        data-discount-type="{{ $tourDiscountType ?? '' }}"
                                        data-discount-raw="{{ $tourDiscountRaw ?? 0 }}"
                                        data-discount-money="{{ $tourDiscountMoney ?? 0 }}"
                                        data-payable="{{ $netNegotiationBase ?? 0 }}"
                                        data-last-amount="{{ $agentNegotiationCap ?? '' }}"
                                        data-last-agent-offer="{{ $lastAgentAmount ?? '' }}"
                                        data-last-comment="{{ e($lastAgentRemark) }}"
                                        data-tour-status="{{ e($tour->tour_status) }}"
                                        data-negotiation-locked="{{ $canCheckNegotiation ? '1' : '0' }}"
                                        data-country-groups='@json($tour->negotiation_country_groups ?? [])'
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
                                        data-enquiry-id="{{ $enquiryIdForPriceUpdate ?? '' }}"
                                        data-price="{{ $settlementAmount }}"
                                        data-gross="{{ $grossTourAmount ?? 0 }}"
                                        data-actual="{{ $currentActualAmount }}"
                                        data-discount="{{ $discount }}"
                                        data-markup-type="{{ $tourMarkupType ?? '' }}"
                                        data-markup-raw="{{ $tourMarkupRaw ?? 0 }}"
                                        data-markup-money="{{ $tourMarkupMoney ?? 0 }}"
                                        data-discount-type="{{ $tourDiscountType ?? '' }}"
                                        data-discount-raw="{{ $tourDiscountRaw ?? 0 }}"
                                        data-discount-money="{{ $tourDiscountMoney ?? 0 }}"
                                        data-payable="{{ $netNegotiationBase ?? 0 }}"
                                        data-comment="{{ e($lastAgentRemark) }}"
                                        data-country-groups='@json($tour->negotiation_country_groups ?? [])'
                                        data-agent-offers='@json($agentNegotiationDetails ?? [])'
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
                                    <div class="quotation-actions-flyout">
                                        <button type="button" class="action-icon-badge quotation-actions-flyout__trigger" style="--action-color: #0f766e;" aria-label="Quotation" aria-haspopup="true">
                                            <i class="ri-bill-line"></i>
                                        </button>
                                        <div class="quotation-actions-flyout__links">
                                            <a href="{{ route('tour.itinerary.preview', ['encryptedTourId' => Crypt::encrypt($tour->tour_id)]) }}"
                                               class="action-icon-badge" style="--action-color: #0f766e;" data-tooltip="Acco + Service Quotation" target="_blank">
                                                <i class="ri-file-list-3-line"></i>
                                            </a>
                                            <a href="{{ route('tour.detailed-quotation.preview', ['encryptedTourId' => Crypt::encrypt($tour->tour_id)]) }}"
                                               class="action-icon-badge" style="--action-color: #7c3aed;" data-tooltip="Packaged Quotation" target="_blank">
                                                <i class="ri-stack-line"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <button type="button" class="action-icon-badge" style="--action-color: #dc2626;" data-tooltip="Cancel Tour" onclick="cancelTour('{{ Crypt::encrypt($tour->tour_id) }}', {{ json_encode($tour->display_id) }})" id="cancel-btn-{{ $tour->tour_id }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="d-flex flex-column">
                                    @if($tour->created_by)
                                        <span>{{ $tour->created_by_name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                @if($tour->created_at)
                                    @php
                                        $createdAt = $tour->destination_created_at ?? $tour->created_at;
                                    @endphp

                                    <span>{{ $createdAt->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ $createdAt->format('h:i A') }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
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
                        {{-- <tr>
                            <td colspan="{{ in_array(auth()->user()->role_id, [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138]) ? 8 : 7 }}" class="text-center text-muted py-4">No new enquiries found</td>
                        </tr> --}}
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
    
    <!-- DMC Check Negotiation Modal (New Enquiries) -->
    <div class="modal fade" id="newEnquiryUpdateModal" tabindex="-1" aria-labelledby="newEnquiryUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content negotiation-modal-content border-0 shadow-lg">
                <div class="modal-header negotiation-modal-header border-0">
                    <div>
                        <h5 class="modal-title mb-0" id="newEnquiryUpdateModalLabel">DMC Negotiation</h5>
                        <small class="text-white-50">Review agent offers and respond per country</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newEnquiryUpdateForm" method="POST" action="" data-update-price-url="{{ route('update-price-comment') }}">
                    @csrf
                    <div class="modal-body pt-3 pb-2">
                        <input type="hidden" name="enquiry_id" id="new_enquiry_modal_enquiry_id" />
                        <input type="hidden" name="tour_id" id="new_enquiry_modal_tour_id" value="" />
                        <input type="hidden" name="actual_amount" id="new_enquiry_modal_actual_amount" value="" />
                        <input type="hidden" name="price" id="new_enquiry_current_price" value="" />

                        <div id="newEnquiryCountryBlocks" class="d-flex flex-column gap-3 mb-3"></div>
                        <div class="alert alert-info py-2 px-3 mb-3">
                            Enter a counter offer for each country in that country's currency. Counter offers cannot exceed the payable amount for that country.
                        </div>

                        <div class="negotiation-meta-block mb-3">
                            <span class="negotiation-label">Last Comment</span>
                            <div class="negotiation-value fw-normal text-muted" id="new_enquiry_display_comment" style="font-size: 0.9rem;">—</div>
                        </div>

                        <div class="mb-0">
                            <label for="new_enquiry_comment" class="form-label fw-semibold">Remarks </label>
                            <textarea id="new_enquiry_comment" name="comment" rows="3" class="form-control" placeholder="Add remarks for this negotiation"></textarea>
                        </div>
                        <div id="new-enquiry-warning-message" class="alert alert-warning mt-2 py-2 px-3 d-none mb-0">
                            Counter price cannot exceed the payable amount.
                        </div>
                    </div>
                    <div class="modal-footer negotiation-modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="new_enquiry_cancel_btn">Close</button>
                        <button type="submit" class="btn btn-primary" id="new_enquiry_submit_btn">Submit Response</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Negotiate by Agent Modal -->
    <div class="modal fade" id="agentNegotiationModal" tabindex="-1" aria-labelledby="agentNegotiationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <form class="modal-content negotiation-modal-content border-0 shadow-lg" id="agentNegotiationForm" method="POST" action="{{ route('tours.agent-negotiation') }}" data-action-url="{{ route('tours.agent-negotiation') }}" data-update-price-url="{{ route('update-price-comment') }}">
                @csrf
                <input type="hidden" name="tour_id" id="agent_negotiation_tour_id">
                <input type="hidden" name="action" id="agent_negotiation_action" value="negotiate">
                <input type="hidden" name="actual_amount" id="agent_negotiation_actual_amount">
                <input type="hidden" name="amount" id="agentNegotiationAmount" value="">
                <input type="hidden" id="agent_negotiation_enquiry_id" value="">
                <div class="modal-header negotiation-modal-header border-0">
                    <div>
                        <h5 class="modal-title mb-0" id="agentNegotiationModalLabel">Negotiate by Agent</h5>
                        <small class="text-white-50">Negotiate separately for each country in its own currency</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body negotiation-split-body">
                    <div class="negotiation-main-scroll">
                        <div class="mb-3">
                            <span class="negotiation-label">Tour</span>
                            <div class="negotiation-value" id="agentNegotiationDisplayId">—</div>
                        </div>
                        <div id="agentNegotiationCountryBlocks" class="d-flex flex-column gap-3 mb-3"></div>
                        <div class="alert alert-info py-2 px-3 mb-3" id="agentNegotiationCurrencyHint">
                            Each country shows its booked services total in that country's currency. Enter an offer for every country.
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="negotiation-meta-block h-100">
                                    <span class="negotiation-label">Last Agent Offer</span>
                                    <div class="negotiation-value text-warning" id="agentNegotiationLastAmount">—</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="negotiation-meta-block h-100">
                                    <span class="negotiation-label">Last Remarks</span>
                                    <div class="negotiation-value fw-normal text-muted" id="agentNegotiationLastRemark" style="font-size: 0.9rem;">—</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="agentNegotiationRemark" class="form-label fw-semibold">Remarks </label>
                            <textarea class="form-control" id="agentNegotiationRemark" name="comment" rows="3" placeholder="Add remarks for this negotiation"></textarea>
                        </div>
                        <div class="alert alert-warning py-2 px-3 d-none mb-0" id="agentNegotiationWarning">
                            Negotiated amount cannot exceed the payable amount.
                        </div>
                    </div>
                    <div class="negotiation-profit-scroll" id="agentNegotiationProfitPanel">
                        <div class="nego-profit-empty">Open a tour to see country-wise sell, cost, and profit.</div>
                    </div>
                </div>
                <div class="modal-footer negotiation-modal-footer border-0 d-flex flex-wrap align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close without saving">Close</button>
                    <button type="button" class="btn btn-outline-success" id="agentNegotiationConfirmBtn" onclick="submitAgentNegotiation('confirm')">Confirm Tour</button>
                    <button type="button" class="btn btn-outline-danger" id="agentNegotiationCancelBtn" onclick="submitAgentNegotiation('cancel')">Cancel Tour</button>
                    <button type="button" class="btn btn-primary" id="agentNegotiationSubmitBtn" onclick="submitAgentNegotiation('negotiate')">Negotiate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Service Modals for each tour -->
    @foreach($tours as $tour)
    @php
    // Orders are preloaded in BookingsController::hydrateTourNegotiationCurrencyData()
    // (includes orders.country + orders.currency per booking_id for all service types)
    $scope = $serviceCountryScope ?? ['restricted' => false, 'countries' => []];
    $orders = collect($tour->booking ?? []);
    $pageCurrency = $currency ?? 'SGD';
    $tourCountries = \App\Helpers\CommonHelper::parseTourDestinationCountries($tour->destination ?? null);
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
    $orderServiceCountry = [];
    
    foreach($orders as $order) {
        if(!isset($svc[$order->type])) {
            continue;
        }

        $resolvedCountry = \App\Helpers\CommonHelper::resolveBookingServiceCountry($order, $tourCountries, []);
        if ($resolvedCountry === '' || $resolvedCountry === 'Other') {
            $resolvedCountry = $tourCountries[0] ?? 'Other';
        }
        $canonicalCountry = \App\Helpers\CommonHelper::matchTourCountryName($resolvedCountry, $tourCountries) ?? $resolvedCountry;
        if (!\App\Helpers\CommonHelper::isServiceCountryAllowed($canonicalCountry, $scope)) {
            continue;
        }

        $svc[$order->type]++;
        if(!isset($serviceData[$order->type])) {
            $serviceData[$order->type] = [];
        }
        $serviceData[$order->type][] = $order;
        $orderServiceCountry[$order->booking_id ?? spl_object_id($order)] = $canonicalCountry;
        // Also stash on the model for blade cards
        $order->resolved_service_country = $canonicalCountry;
    }
@endphp

<!-- Hotel Details Modal -->
@include('bookings.partials.enquiry-service-modal', [
        'tour' => $tour,
        'serviceKey' => 'hotel',
        'serviceData' => $serviceData,
        'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
    ])

<!-- Attraction Details Modal -->
@include('bookings.partials.enquiry-service-modal', [
        'tour' => $tour,
        'serviceKey' => 'attraction',
        'serviceData' => $serviceData,
        'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
    ])

<!-- Restaurant Details Modal -->
@include('bookings.partials.enquiry-service-modal', [
        'tour' => $tour,
        'serviceKey' => 'restaurant',
        'serviceData' => $serviceData,
        'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
    ])


<!-- Guide Details Modal -->
@include('bookings.partials.enquiry-service-modal', [
        'tour' => $tour,
        'serviceKey' => 'guide',
        'serviceData' => $serviceData,
        'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
    ])

<!-- Entry Port (Arrival) Details Modal -->
@include('bookings.partials.transport-service-modal', [
        'tour' => $tour,
        'serviceKey' => 'entry_port',
        'serviceData' => $serviceData,
        'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
        'showTransportActions' => false,
    ])

<!-- Exit Port (Departure) Details Modal -->
@include('bookings.partials.transport-service-modal', [
        'tour' => $tour,
        'serviceKey' => 'exit_port',
        'serviceData' => $serviceData,
        'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
        'showTransportActions' => false,
    ])

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
                        $currency = \App\Helpers\CommonHelper::resolveOrderDisplayCurrency($hourlyOrder, $pageCurrency ?? ($currency ?? 'SGD'));
                                $hourlyCountry = trim((string) ($hourlyOrder->resolved_service_country ?? $hourlyOrder->country ?? 'Other'));
                                $hourlyData = is_string($hourlyOrder->data) ? json_decode($hourlyOrder->data, true) : $hourlyOrder->data;
                            @endphp
                            
                            @if(is_array($hourlyData))
                                @php $actualBookingIndex = 0; @endphp
                                @foreach($hourlyData as $bookingIndex => $booking)
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-2">
                                    @endif
                            
                                    <div class="card mb-2 shadow-sm border-0 svc-country-item" data-service-country="{{ $hourlyCountry !== '' ? $hourlyCountry : 'Other' }}" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
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
                                                        {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
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
                        $currency = \App\Helpers\CommonHelper::resolveOrderDisplayCurrency($pointOrder, $pageCurrency ?? ($currency ?? 'SGD'));
                                $pointCountry = trim((string) ($pointOrder->resolved_service_country ?? $pointOrder->country ?? 'Other'));
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
                            
                                    <div class="card mb-2 shadow-sm border-0 svc-country-item" data-service-country="{{ $pointCountry !== '' ? $pointCountry : 'Other' }}" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
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
                                                        {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
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
                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
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
@include('bookings.partials.transport-service-modal', [
        'tour' => $tour,
        'serviceKey' => 'local_transport',
        'serviceData' => $serviceData,
        'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
        'showTransportActions' => false,
    ])

<!-- Miscellaneous Details Modal -->
@include('bookings.partials.miscellaneous-service-modal', [
        'tour' => $tour,
        'serviceData' => $serviceData,
        'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
    ])
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
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    const endPlus30 = new Date(today);
    endPlus30.setDate(endPlus30.getDate() + 30);
    const endPlus30Str = endPlus30.toISOString().split('T')[0];

    // Default range: today → today + 30 days
    if (startDateFilter && !startDateFilter.value) {
        startDateFilter.value = todayStr;
    }
    if (endDateFilter && !endDateFilter.value) {
        endDateFilter.value = endPlus30Str;
    }
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (countryFilter) countryFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    if (startDateFilter) {
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
    
    // Apply initial filter on page load (today → today + 30 days)
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

    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    const endPlus30 = new Date(today);
    endPlus30.setDate(endPlus30.getDate() + 30);
    const endPlus30Str = endPlus30.toISOString().split('T')[0];

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
        startDateInput.value = todayStr;
    }
    if (endDateInput) {
        endDateInput.value = endPlus30Str;
        endDateInput.setAttribute('min', todayStr);
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

// Service country tabs (Services column)
function selectServiceCountryTab(button) {
    if (!button) return;
    const cell = button.closest('.services-country-cell');
    if (!cell) return;

    const country = button.getAttribute('data-country') || '';
    cell.setAttribute('data-selected-country', country);

    cell.querySelectorAll('.services-country-tab').forEach(tab => {
        const isActive = tab === button;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    cell.querySelectorAll('.services-country-panel').forEach(panel => {
        const match = (panel.getAttribute('data-country') || '') === country;
        panel.classList.toggle('is-active', match);
        if (match) {
            panel.removeAttribute('hidden');
        } else {
            panel.setAttribute('hidden', 'hidden');
        }
    });
}

function getSelectedServiceCountryForTour(tourId) {
    const cell = document.querySelector(`.services-country-cell[data-tour-id="${tourId}"]`);
    return (cell?.getAttribute('data-selected-country') || '').trim();
}

function applyServiceModalCountryFilter(modalElement, country) {
    if (!modalElement) return;

    const items = modalElement.querySelectorAll('.svc-country-item');
    if (!items.length) return;

    const selected = (country || '').trim().toLowerCase();
    let visibleCount = 0;

    items.forEach(item => {
        const itemCountry = (item.getAttribute('data-service-country') || '').trim().toLowerCase();
        const show = !selected || itemCountry === selected;
        item.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    let emptyEl = modalElement.querySelector('.svc-country-empty-filter');
    if (visibleCount === 0) {
        if (!emptyEl) {
            emptyEl = document.createElement('div');
            emptyEl.className = 'text-center py-4 svc-country-empty-filter';
            emptyEl.innerHTML = '<i class="ri-map-pin-line text-muted" style="font-size:1.5rem;"></i>'
                + '<h6 class="text-dark mt-2 mb-1">No bookings for this country</h6>'
                + '<p class="text-muted mb-0" style="font-size:0.85rem;">Switch the country tab in the Services column to view other bookings.</p>';
            const body = modalElement.querySelector('.modal-body');
            if (body) body.appendChild(emptyEl);
        }
        emptyEl.style.display = '';
    } else if (emptyEl) {
        emptyEl.style.display = 'none';
    }
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

    // Filter modal cards to the country currently selected in the Services column
    const selectedCountry = getSelectedServiceCountryForTour(tourId);
    applyServiceModalCountryFilter(modalElement, selectedCountry);
    
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
@include('bookings.partials.negotiation-profit-breakdown')
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
        
        function syncDmcPrimaryNegotiationAmount() {
            const firstOffer = document.querySelector('#newEnquiryCountryBlocks .dmc-nego-offer-input');
            const priceInput = document.getElementById('new_enquiry_current_price');
            const actualAmtField = document.getElementById('new_enquiry_modal_actual_amount');
            if (!firstOffer) {
                return;
            }
            if (priceInput) priceInput.value = firstOffer.value || '';
            if (actualAmtField) actualAmtField.value = firstOffer.getAttribute('data-max') || '';
        }

        function findAgentOfferForGroup(agentOffers, group) {
            if (!Array.isArray(agentOffers) || agentOffers.length === 0) {
                return null;
            }
            const country = String(group.country || '').trim().toLowerCase();
            const currency = String(group.currency || '').trim().toUpperCase();
            let match = agentOffers.find(function (offer) {
                return String(offer.country || '').trim().toLowerCase() === country
                    && String(offer.currency || '').trim().toUpperCase() === currency;
            });
            if (!match) {
                match = agentOffers.find(function (offer) {
                    return String(offer.currency || '').trim().toUpperCase() === currency;
                });
            }
            return match || null;
        }

        function renderDmcNegotiationCountryBlocks(countryGroups, agentOffers, fallbackPrice) {
            const blocksEl = document.getElementById('newEnquiryCountryBlocks');
            const warningMessage = document.getElementById('new-enquiry-warning-message');
            if (!blocksEl) return;

            blocksEl.innerHTML = '';
            if (!Array.isArray(countryGroups) || countryGroups.length === 0) {
                blocksEl.innerHTML = '<div class="alert alert-warning mb-0">No country-wise order totals found for this tour.</div>';
                syncDmcPrimaryNegotiationAmount();
                return;
            }

            countryGroups.forEach(function (group, index) {
                const currency = group.currency || '';
                const country = group.country || currency || ('Country ' + (index + 1));
                const payable = Number(group.payable || 0);
                const gross = Number(group.gross || 0);
                const markup = Number(group.markup || 0);
                const discount = Number(group.discount || 0);
                const agentOffer = findAgentOfferForGroup(agentOffers, group);
                const agentAmount = agentOffer ? parseFloat(agentOffer.amount) : NaN;
                const defaultCounter = Number.isFinite(agentAmount) && agentAmount > 0
                    ? agentAmount
                    : (Number.isFinite(fallbackPrice) && fallbackPrice > 0 && index === 0 ? fallbackPrice : (payable > 0 ? payable : ''));

                const card = document.createElement('div');
                card.className = 'negotiation-pricing-summary';
                card.innerHTML =
                    '<div class="d-flex justify-content-between align-items-center mb-2">' +
                        '<strong>' + country + ' <span class="text-muted">(' + currency + ')</span></strong>' +
                        '<small class="text-muted">' + (group.order_count || 0) + ' service(s)</small>' +
                    '</div>' +
                    '<div class="row g-2 mb-2">' +
                        '<div class="col-6 col-md-3"><span class="negotiation-label">Gross</span><div class="negotiation-value">' + currency + ' ' + formatNegotiationAmount(gross) + '</div></div>' +
                        '<div class="col-6 col-md-3"><span class="negotiation-label">Markup</span><div class="negotiation-value text-info">' + (markup > 0 ? ('+' + currency + ' ' + formatNegotiationAmount(markup)) : (currency + ' 0.00')) + '</div></div>' +
                        '<div class="col-6 col-md-3"><span class="negotiation-label">Discount</span><div class="negotiation-value">' + (discount > 0 ? ('−' + currency + ' ' + formatNegotiationAmount(discount)) : (currency + ' 0.00')) + '</div></div>' +
                        '<div class="col-6 col-md-3"><span class="negotiation-label">Payable</span><div class="negotiation-value">' + currency + ' ' + formatNegotiationAmount(payable) + '</div></div>' +
                    '</div>' +
                    '<div class="mb-2"><span class="negotiation-label">Agent Offer</span>' +
                        '<div class="negotiation-value text-success">' +
                            (Number.isFinite(agentAmount) ? (currency + ' ' + formatNegotiationAmount(agentAmount)) : '—') +
                        '</div></div>' +
                    '<label class="form-label fw-semibold">Your Counter Price (' + currency + ') <span class="text-danger">*</span></label>' +
                    '<input type="number" class="form-control dmc-nego-offer-input" min="0" step="0.01" required ' +
                        'data-index="' + index + '" data-max="' + payable + '" data-country="' + String(country).replace(/"/g, '&quot;') + '" data-currency="' + currency + '" ' +
                        'value="' + defaultCounter + '" placeholder="Enter counter in ' + currency + '">' +
                    '<div class="form-text text-primary fw-semibold mt-1">Maximum allowed: ' + currency + ' ' + formatNegotiationAmount(payable) + '</div>' +
                    '<input type="hidden" name="offers[' + index + '][country]" value="' + String(country).replace(/"/g, '&quot;') + '">' +
                    '<input type="hidden" name="offers[' + index + '][currency]" value="' + currency + '">' +
                    '<input type="hidden" name="offers[' + index + '][actual_amount]" value="' + payable + '">' +
                    '<input type="hidden" name="offers[' + index + '][gross]" value="' + gross + '">' +
                    '<input type="hidden" name="offers[' + index + '][amount]" class="dmc-nego-offer-hidden" value="' + defaultCounter + '">';
                blocksEl.appendChild(card);
            });

            blocksEl.querySelectorAll('.dmc-nego-offer-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    const max = parseFloat(this.getAttribute('data-max'));
                    const val = parseFloat(this.value);
                    const hidden = this.parentElement.querySelector('.dmc-nego-offer-hidden');
                    if (hidden) hidden.value = this.value;
                    syncDmcPrimaryNegotiationAmount();
                    if (warningMessage) {
                        if (!isNaN(val) && !isNaN(max) && max > 0 && val > max) {
                            warningMessage.classList.remove('d-none');
                            warningMessage.textContent = 'Counter price for ' + (this.getAttribute('data-country') || 'a country') +
                                ' cannot exceed ' + (this.getAttribute('data-currency') || '') + ' ' + formatNegotiationAmount(max) + '.';
                        } else {
                            warningMessage.classList.add('d-none');
                        }
                    }
                });
                input.addEventListener('blur', function () {
                    const max = parseFloat(this.getAttribute('data-max'));
                    const val = parseFloat(this.value);
                    if (!isNaN(val) && !isNaN(max) && max > 0 && val > max) {
                        this.value = max;
                        const hidden = this.parentElement.querySelector('.dmc-nego-offer-hidden');
                        if (hidden) hidden.value = String(max);
                        syncDmcPrimaryNegotiationAmount();
                        if (warningMessage) warningMessage.classList.add('d-none');
                    }
                });
            });

            syncDmcPrimaryNegotiationAmount();
        }

        // Modal helper functions for Update Price (New Enquiries)
        window.openNewEnquiryModal = function(button, route) {
            var modalEl = document.getElementById('newEnquiryUpdateModal');
            var form = document.getElementById('newEnquiryUpdateForm');
            var commentInput = document.getElementById('new_enquiry_comment');
            var idInput = document.getElementById('new_enquiry_modal_enquiry_id');
            var displayComment = document.getElementById('new_enquiry_display_comment');
            var warningMessage = document.getElementById('new-enquiry-warning-message');

            form.action = route || '';
            idInput.value = button.getAttribute('data-enquiry-id') || '';
            var tourIdField = document.getElementById('new_enquiry_modal_tour_id');
            if (tourIdField) {
                tourIdField.value = button.getAttribute('data-tour-id') || '';
            }

            var prevPrice = parseNegotiationAttr(button.getAttribute('data-price'));
            var prevComment = button.getAttribute('data-comment') || '';
            if (displayComment) {
                displayComment.textContent = prevComment || '—';
            }
            commentInput.value = '';
            if (warningMessage) warningMessage.classList.add('d-none');

            let countryGroups = [];
            let agentOffers = [];
            try { countryGroups = JSON.parse(button.getAttribute('data-country-groups') || '[]'); } catch (e) { countryGroups = []; }
            try { agentOffers = JSON.parse(button.getAttribute('data-agent-offers') || '[]'); } catch (e) { agentOffers = []; }
            if (!Array.isArray(countryGroups)) countryGroups = [];
            if (!Array.isArray(agentOffers)) agentOffers = [];

            renderDmcNegotiationCountryBlocks(countryGroups, agentOffers, prevPrice);

            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        };

        window.validateNewEnquiryPrice = function(input) {
            var maxValue = parseFloat(input.getAttribute('data-max') || input.getAttribute('max'));
            var currentValue = parseFloat(input.value);
            var warningMessage = document.getElementById('new-enquiry-warning-message');
            var hidden = input.parentElement ? input.parentElement.querySelector('.dmc-nego-offer-hidden') : null;

            if (!isNaN(maxValue) && !isNaN(currentValue) && currentValue > maxValue) {
                input.value = maxValue;
                if (hidden) hidden.value = String(maxValue);
                syncDmcPrimaryNegotiationAmount();
                if (warningMessage) {
                    warningMessage.classList.remove('d-none');
                    setTimeout(function() {
                        warningMessage.classList.add('d-none');
                    }, 3000);
                }
            } else if (hidden) {
                hidden.value = input.value;
                syncDmcPrimaryNegotiationAmount();
            }
        };

        // Add form submission handler with loader
        $(document).ready(function() {
            $('#newEnquiryUpdateForm').on('submit', function(e) {
                const offerInputs = Array.from(document.querySelectorAll('#newEnquiryCountryBlocks .dmc-nego-offer-input'));
                const warningMessage = document.getElementById('new-enquiry-warning-message');
                for (let i = 0; i < offerInputs.length; i++) {
                    const input = offerInputs[i];
                    const val = parseFloat(input.value);
                    const max = parseFloat(input.getAttribute('data-max'));
                    if (isNaN(val) || val <= 0) {
                        e.preventDefault();
                        if (warningMessage) {
                            warningMessage.classList.remove('d-none');
                            warningMessage.textContent = 'Please enter a counter price for every country.';
                        }
                        input.focus();
                        return false;
                    }
                    if (!isNaN(max) && max > 0 && val > max) {
                        e.preventDefault();
                        input.value = max;
                        const hidden = input.parentElement.querySelector('.dmc-nego-offer-hidden');
                        if (hidden) hidden.value = String(max);
                        syncDmcPrimaryNegotiationAmount();
                        if (warningMessage) {
                            warningMessage.classList.remove('d-none');
                            warningMessage.textContent = 'Counter price cannot exceed the payable amount.';
                        }
                        return false;
                    }
                    const hidden = input.parentElement.querySelector('.dmc-nego-offer-hidden');
                    if (hidden) hidden.value = input.value;
                }
                if (offerInputs.length > 0) {
                    syncDmcPrimaryNegotiationAmount();
                } else {
                    // Confirm-from-agent path may post offers[] as hidden fields without DMC blocks.
                    const confirmOffer = document.querySelector('#newEnquiryUpdateForm input[name="offers[0][amount]"]');
                    const priceInput = document.getElementById('new_enquiry_current_price');
                    if (confirmOffer && priceInput && !String(priceInput.value || '').trim()) {
                        priceInput.value = confirmOffer.value || '';
                    }
                }

                const submitBtn = document.getElementById('new_enquiry_submit_btn');
                const cancelBtn = document.getElementById('new_enquiry_cancel_btn');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Submitting...';
                    submitBtn.disabled = true;
                }
                if (cancelBtn) cancelBtn.disabled = true;
            });
        });


        let agentNegotiationContext = null;
        let agentNegotiationModalInstance = null;
        let agentNegotiationActionsDisabled = false;

        function restoreAgentNegotiationFormToAgentNegotiation() {
            const form = document.getElementById('agentNegotiationForm');
            if (!form) return;
            const defaultAction = form.getAttribute('data-action-url');
            if (defaultAction) {
                form.action = defaultAction;
            }
            const tourIdInput = document.getElementById('agent_negotiation_tour_id');
            const actionInput = document.getElementById('agent_negotiation_action');
            const actualInput = document.getElementById('agent_negotiation_actual_amount');
            const amountInput = document.getElementById('agentNegotiationAmount');
            const enquiryIdEl = document.getElementById('agent_negotiation_enquiry_id');
            if (tourIdInput) tourIdInput.setAttribute('name', 'tour_id');
            if (actionInput) actionInput.setAttribute('name', 'action');
            if (actualInput) actualInput.setAttribute('name', 'actual_amount');
            if (amountInput) amountInput.setAttribute('name', 'amount');
            if (enquiryIdEl) enquiryIdEl.removeAttribute('name');
        }

        /** Close agent modal then run callback (avoids modal staying open under SweetAlert). */
        function hideAgentNegotiationModalThen(callback) {
            const modalEl = document.getElementById('agentNegotiationModal');
            if (!modalEl || !agentNegotiationModalInstance) {
                if (typeof callback === 'function') {
                    callback();
                }
                return;
            }
            if (!modalEl.classList.contains('show')) {
                if (typeof callback === 'function') {
                    callback();
                }
                return;
            }
            const onHidden = function () {
                modalEl.removeEventListener('hidden.bs.modal', onHidden);
                if (document.activeElement && modalEl.contains(document.activeElement)) {
                    document.activeElement.blur();
                }
                if (typeof callback === 'function') {
                    callback();
                }
            };
            modalEl.addEventListener('hidden.bs.modal', onHidden, { once: true });
            agentNegotiationModalInstance.hide();
        }

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
            const amountHidden = document.getElementById('agentNegotiationAmount');
            const enquiryIdHidden = document.getElementById('agent_negotiation_enquiry_id');
            const remarkInput = document.getElementById('agentNegotiationRemark');
            const warning = document.getElementById('agentNegotiationWarning');
            const displayEl = document.getElementById('agentNegotiationDisplayId');
            const lastAmountEl = document.getElementById('agentNegotiationLastAmount');
            const lastRemarkEl = document.getElementById('agentNegotiationLastRemark');
            const blocksEl = document.getElementById('agentNegotiationCountryBlocks');

            restoreAgentNegotiationFormToAgentNegotiation();

            const tourId = button.getAttribute('data-tour-id');
            const displayId = button.getAttribute('data-display-id') || '—';
            const tourStatus = button.getAttribute('data-tour-status') || '';
            const lastAgentOfferAttr = button.getAttribute('data-last-agent-offer');
            const isLocked = button.getAttribute('data-negotiation-locked') === '1';
            const lastAgentOffer = parseNegotiationAttr(lastAgentOfferAttr);
            const lastRemark = button.getAttribute('data-last-comment') || '';

            let countryGroups = [];
            try { countryGroups = JSON.parse(button.getAttribute('data-country-groups') || '[]'); } catch (e) { countryGroups = []; }
            if (!Array.isArray(countryGroups)) countryGroups = [];

            agentNegotiationContext = {
                groups: countryGroups,
                lastAgentOffer: lastAgentOffer,
                isLocked: isLocked
            };

            if (typeof renderAgentNegotiationProfitPanel === 'function') {
                renderAgentNegotiationProfitPanel(countryGroups);
            }

            if (blocksEl) {
                blocksEl.innerHTML = '';
                if (countryGroups.length === 0) {
                    blocksEl.innerHTML = '<div class="alert alert-warning mb-0">No country-wise order totals found for this tour.</div>';
                } else {
                    countryGroups.forEach(function (group, index) {
                        const currency = group.currency || '';
                        const country = group.country || currency || ('Country ' + (index + 1));
                        const payable = Number(group.payable || 0);
                        const gross = Number(group.gross || 0);
                        const markup = Number(group.markup || 0);
                        const discount = Number(group.discount || 0);
                        const card = document.createElement('div');
                        card.className = 'negotiation-pricing-summary';
                        card.innerHTML =
                            '<div class="d-flex justify-content-between align-items-center mb-2">' +
                                '<strong>' + country + ' <span class="text-muted">(' + currency + ')</span></strong>' +
                                '<small class="text-muted">' + (group.order_count || 0) + ' service(s)</small>' +
                            '</div>' +
                            '<div class="row g-2 mb-2">' +
                                '<div class="col-6 col-md-3"><span class="negotiation-label">Gross</span><div class="negotiation-value">' + currency + ' ' + formatNegotiationAmount(gross) + '</div></div>' +
                                '<div class="col-6 col-md-3"><span class="negotiation-label">Markup</span><div class="negotiation-value text-info">' + (markup > 0 ? ('+' + currency + ' ' + formatNegotiationAmount(markup)) : (currency + ' 0.00')) + '</div></div>' +
                                '<div class="col-6 col-md-3"><span class="negotiation-label">Discount</span><div class="negotiation-value">' + (discount > 0 ? ('−' + currency + ' ' + formatNegotiationAmount(discount)) : (currency + ' 0.00')) + '</div></div>' +
                                '<div class="col-6 col-md-3"><span class="negotiation-label">Payable</span><div class="negotiation-value">' + currency + ' ' + formatNegotiationAmount(payable) + '</div></div>' +
                            '</div>' +
                            '<label class="form-label fw-semibold">Offer Amount (' + currency + ') <span class="text-danger">*</span></label>' +
                            '<input type="number" class="form-control agent-nego-offer-input" min="0" step="0.01" ' +
                                'data-index="' + index + '" data-max="' + payable + '" data-country="' + String(country).replace(/"/g, '&quot;') + '" data-currency="' + currency + '" ' +
                                'data-gross="' + gross + '" data-payable="' + payable + '" value="' + (payable > 0 ? payable : '') + '" placeholder="Enter offer in ' + currency + '">' +
                            '<div class="agent-nego-offer-error text-danger small mt-1 d-none" role="alert"></div>' +
                            '<div class="form-text text-primary fw-semibold mt-1">Maximum allowed: ' + currency + ' ' + formatNegotiationAmount(payable) + '</div>' +
                            '<input type="hidden" name="offers[' + index + '][country]" value="' + String(country).replace(/"/g, '&quot;') + '">' +
                            '<input type="hidden" name="offers[' + index + '][currency]" value="' + currency + '">' +
                            '<input type="hidden" name="offers[' + index + '][actual_amount]" value="' + payable + '">' +
                            '<input type="hidden" name="offers[' + index + '][gross]" value="' + gross + '">' +
                            '<input type="hidden" name="offers[' + index + '][amount]" class="agent-nego-offer-hidden" value="' + (payable > 0 ? payable : '') + '">';
                        blocksEl.appendChild(card);
                    });

                    blocksEl.querySelectorAll('.agent-nego-offer-input').forEach(function (input) {
                        input.addEventListener('input', function () {
                            const hidden = this.parentElement.querySelector('.agent-nego-offer-hidden');
                            if (hidden) hidden.value = this.value;
                            syncPrimaryNegotiationAmount();
                            if (typeof syncAgentNegotiationProfitFromOffers === 'function') {
                                syncAgentNegotiationProfitFromOffers();
                            }
                            highlightExceededAgentOffer(this);
                        });
                        input.addEventListener('blur', function () {
                            if (isAgentOfferExceeded(this)) {
                                const max = getAgentOfferMax(this);
                                this.value = max;
                                const hidden = this.parentElement.querySelector('.agent-nego-offer-hidden');
                                if (hidden) hidden.value = String(max);
                                syncPrimaryNegotiationAmount();
                                if (typeof syncAgentNegotiationProfitFromOffers === 'function') {
                                    syncAgentNegotiationProfitFromOffers();
                                }
                                highlightExceededAgentOffer(this);
                            }
                        });
                    });
                    if (typeof syncAgentNegotiationProfitFromOffers === 'function') {
                        syncAgentNegotiationProfitFromOffers();
                    }
                    blocksEl.querySelectorAll('.agent-nego-offer-input').forEach(function (input) {
                        highlightExceededAgentOffer(input);
                    });
                }
            }

            form.dataset.enquiryId = button.getAttribute('data-enquiry-id') || '';
            if (enquiryIdHidden) {
                enquiryIdHidden.value = form.dataset.enquiryId;
            }

            form.dataset.currentStatus = tourStatus;
            tourIdInput.value = tourId;
            actionInput.value = 'negotiate';
            displayEl.textContent = displayId;
            warning.classList.add('d-none');
            remarkInput.value = '';
            lastRemarkEl.textContent = lastRemark || '—';

            if (Number.isFinite(lastAgentOffer) && lastAgentOffer > 0) {
                lastAmountEl.textContent = formatNegotiationAmount(lastAgentOffer);
            } else {
                lastAmountEl.textContent = '—';
            }

            syncPrimaryNegotiationAmount();
            toggleAgentNegotiationActions(isLocked);
            agentNegotiationModalInstance.show();
        };

        /** Max allowed offer for a country input (payable amount). */
        function getAgentOfferMax(input) {
            const max = parseFloat(input.getAttribute('data-max'));
            return Number.isFinite(max) && max > 0 ? max : NaN;
        }

        function isAgentOfferExceeded(input) {
            const value = parseFloat(input.value);
            const max = getAgentOfferMax(input);
            return Number.isFinite(value) && Number.isFinite(max) && value > max;
        }

        /** Inline feedback under that country's Offer Amount field. */
        function highlightExceededAgentOffer(input) {
            const card = input.closest('.negotiation-pricing-summary');
            const fieldError = card ? card.querySelector('.agent-nego-offer-error') : null;
            const globalWarning = document.getElementById('agentNegotiationWarning');
            const exceeded = isAgentOfferExceeded(input);

            input.classList.toggle('is-invalid', exceeded);

            if (fieldError) {
                if (exceeded) {
                    const country = input.getAttribute('data-country') || 'this country';
                    const currency = input.getAttribute('data-currency') || '';
                    const limit = (currency ? currency + ' ' : '') + formatNegotiationAmount(getAgentOfferMax(input));
                    fieldError.textContent = 'Price exceeded for ' + country + '. Offer cannot be more than the negotiated amount ' + limit + '.';
                    fieldError.classList.remove('d-none');
                } else {
                    fieldError.textContent = '';
                    fieldError.classList.add('d-none');
                }
            }

            if (globalWarning) {
                globalWarning.classList.add('d-none');
            }
        }

        /** Blocks submit when any country offer is above its payable amount. */
        function validateAgentOffersWithinPayable(offerInputs) {
            let firstExceeded = null;
            offerInputs.forEach(function (input) {
                highlightExceededAgentOffer(input);
                if (!firstExceeded && isAgentOfferExceeded(input)) {
                    firstExceeded = input;
                }
            });

            if (!firstExceeded) {
                return true;
            }

            const country = firstExceeded.getAttribute('data-country') || 'this country';
            const currency = firstExceeded.getAttribute('data-currency') || '';
            const limit = (currency ? currency + ' ' : '') + formatNegotiationAmount(getAgentOfferMax(firstExceeded));

            firstExceeded.focus();

            Swal.fire({
                icon: 'warning',
                title: 'Price exceeded',
                text: 'The offer amount for ' + country + ' is exceeding the negotiated payable amount ' + limit + '. Please enter ' + limit + ' or less.'
            });
            return false;
        }

        function syncPrimaryNegotiationAmount() {
            const firstOffer = document.querySelector('#agentNegotiationCountryBlocks .agent-nego-offer-input');
            const amountHidden = document.getElementById('agentNegotiationAmount');
            const actualInput = document.getElementById('agent_negotiation_actual_amount');
            if (!firstOffer) {
                if (amountHidden) amountHidden.value = '';
                if (actualInput) actualInput.value = '';
                return;
            }
            if (amountHidden) amountHidden.value = firstOffer.value || '';
            if (actualInput) actualInput.value = firstOffer.getAttribute('data-payable') || '';
        }
        window.submitAgentNegotiation = function(action) {
            if (agentNegotiationActionsDisabled) {
                hideAgentNegotiationModalThen(function () {
                    Swal.fire({
                        icon: 'info',
                        title: 'Negotiation locked',
                        text: 'Please respond via Check Negotiation.'
                    });
                });
                return;
            }

            const form = document.getElementById('agentNegotiationForm');
            const actionInput = document.getElementById('agent_negotiation_action');
            const tourIdInput = document.getElementById('agent_negotiation_tour_id');
            const actualInput = document.getElementById('agent_negotiation_actual_amount');
            const enquiryIdHidden = document.getElementById('agent_negotiation_enquiry_id');
            const amountInput = document.getElementById('agentNegotiationAmount');
            const remarkInput = document.getElementById('agentNegotiationRemark');
            const warning = document.getElementById('agentNegotiationWarning');
            const cancelBtn = document.getElementById('agentNegotiationCancelBtn');
            const confirmBtn = document.getElementById('agentNegotiationConfirmBtn');
            const submitBtn = document.getElementById('agentNegotiationSubmitBtn');
            warning.classList.add('d-none');

            if (action === 'negotiate') {
                const offerInputs = Array.from(document.querySelectorAll('#agentNegotiationCountryBlocks .agent-nego-offer-input'));
                if (offerInputs.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No country totals',
                        text: 'No country-wise booking totals are available to negotiate.'
                    });
                    return;
                }

                for (const input of offerInputs) {
                    const amountValue = parseFloat(input.value);
                    const country = input.getAttribute('data-country') || 'a country';
                    if (isNaN(amountValue) || amountValue <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Amount required',
                            text: 'Please enter a valid negotiation amount for ' + country + '.'
                        });
                        return;
                    }
                    const hidden = input.parentElement.querySelector('.agent-nego-offer-hidden');
                    if (hidden) hidden.value = input.value;
                }

                if (!validateAgentOffersWithinPayable(offerInputs)) {
                    return;
                }
                syncPrimaryNegotiationAmount();

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

            if (action === 'confirm') {
                const tidEarly = tourIdInput ? String(tourIdInput.value || '').trim() : '';
                const negotiationRowBtn = tidEarly
                    ? (document.querySelector('.check-negotiation-btn[data-tour-id="' + tidEarly + '"]')
                        || document.querySelector('.negotiate-by-agent[data-tour-id="' + tidEarly + '"]'))
                    : null;
                let enquiryId = negotiationRowBtn ? (negotiationRowBtn.getAttribute('data-enquiry-id') || '').trim() : '';
                if (!enquiryId) {
                    enquiryId = (form.dataset.enquiryId || '').trim();
                }
                if (enquiryId) {
                    form.dataset.enquiryId = enquiryId;
                    if (enquiryIdHidden) {
                        enquiryIdHidden.value = enquiryId;
                    }
                }
                const tourIdForConfirm = tidEarly || (tourIdInput ? String(tourIdInput.value || '').trim() : '');
                if (!tourIdForConfirm) {
                    hideAgentNegotiationModalThen(function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tour not found',
                            text: 'Cannot submit: missing tour reference. Close the modal and open Negotiate again.'
                        });
                    });
                    return;
                }
                const firstOfferInput = document.querySelector('#agentNegotiationCountryBlocks .agent-nego-offer-input');
                const confirmAmount = parseFloat(firstOfferInput ? firstOfferInput.value : (amountInput ? amountInput.value : NaN));
                if (isNaN(confirmAmount) || confirmAmount <= 0) {
                    hideAgentNegotiationModalThen(function () {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Amount required',
                            text: 'Please enter the negotiated price to save.'
                        });
                    });
                    return;
                }
                const offerInputs = Array.from(document.querySelectorAll('#agentNegotiationCountryBlocks .agent-nego-offer-input'));
                if (!validateAgentOffersWithinPayable(offerInputs)) {
                    return;
                }
                syncPrimaryNegotiationAmount();
            }

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
                    title: 'Save price and remarks?',
                    text: 'This will submit to the same enquiry update as Negotiation → Update Price (price, comment, and tour status rules on the server).',
                    icon: 'question',
                    confirmButtonText: 'Yes, save it',
                    confirmButtonColor: '#198754',
                    cancelButtonText: 'Review again'
                }
            };

            const prompt = prompts[action];
            if (!prompt) return;

            const modalEl = document.getElementById('agentNegotiationModal');
            const runCancelConfirmPrompt = function () {
                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
                Swal.fire({
                ...prompt,
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        if (action === 'confirm') {
                            const updateForm = document.getElementById('newEnquiryUpdateForm');
                            const updateUrl = updateForm ? updateForm.getAttribute('data-update-price-url') : '';
                            if (!updateForm || !updateUrl) {
                                Swal.showValidationMessage('Update Price form is not configured.');
                                resolve();
                                return;
                            }
                            const tid = tourIdInput ? String(tourIdInput.value || '').trim() : '';
                            const rowBtn = tid
                                ? (document.querySelector('.check-negotiation-btn[data-tour-id="' + tid + '"]')
                                    || document.querySelector('.negotiate-by-agent[data-tour-id="' + tid + '"]'))
                                : null;
                            const enquiryIdForSubmit = rowBtn
                                ? (rowBtn.getAttribute('data-enquiry-id') || '').trim()
                                : (form.dataset.enquiryId || '').trim();
                            if (!tid) {
                                Swal.showValidationMessage('Missing tour id. Close and open the negotiation modal again.');
                                resolve();
                                return;
                            }
                            const idEl = document.getElementById('new_enquiry_modal_enquiry_id');
                            const tourIdEl = document.getElementById('new_enquiry_modal_tour_id');
                            const actualAmtEl = document.getElementById('new_enquiry_modal_actual_amount');
                            const priceEl = document.getElementById('new_enquiry_current_price');
                            const commentEl = document.getElementById('new_enquiry_comment');
                            if (!idEl || !priceEl || !commentEl) {
                                Swal.showValidationMessage('Update Price form fields are missing.');
                                resolve();
                                return;
                            }
                            idEl.value = enquiryIdForSubmit;
                            if (tourIdEl) {
                                tourIdEl.value = tid;
                            }
                            commentEl.value = remarkInput.value;

                            // Prefer multi-country offers from the agent modal; fall back to legacy single amount.
                            const agentOfferInputs = Array.from(document.querySelectorAll('#agentNegotiationCountryBlocks .agent-nego-offer-input'));
                            updateForm.querySelectorAll('.dmc-confirm-offer-field').forEach(function (el) { el.remove(); });
                            const blocksEl = document.getElementById('newEnquiryCountryBlocks');
                            if (blocksEl) blocksEl.innerHTML = '';

                            if (agentOfferInputs.length > 0) {
                                agentOfferInputs.forEach(function (input, index) {
                                    const country = input.getAttribute('data-country') || '';
                                    const currency = input.getAttribute('data-currency') || '';
                                    const payable = input.getAttribute('data-payable') || input.getAttribute('data-max') || '0';
                                    const gross = input.getAttribute('data-gross') || '0';
                                    const amount = input.value || '';

                                    [
                                        ['country', country],
                                        ['currency', currency],
                                        ['actual_amount', payable],
                                        ['gross', gross],
                                        ['amount', amount]
                                    ].forEach(function (pair) {
                                        const hidden = document.createElement('input');
                                        hidden.type = 'hidden';
                                        hidden.className = 'dmc-confirm-offer-field';
                                        hidden.name = 'offers[' + index + '][' + pair[0] + ']';
                                        hidden.value = pair[1];
                                        updateForm.appendChild(hidden);
                                    });
                                });
                                const firstOffer = agentOfferInputs[0];
                                priceEl.value = firstOffer.value || amountInput.value;
                                if (actualAmtEl) {
                                    actualAmtEl.value = firstOffer.getAttribute('data-payable') || firstOffer.getAttribute('data-max') || '';
                                }
                            } else {
                                const actualCap = rowBtn
                                    ? (rowBtn.getAttribute('data-actual') || '')
                                    : (actualInput ? String(actualInput.value || '').trim() : '');
                                if (actualAmtEl) {
                                    actualAmtEl.value = actualCap;
                                }
                                priceEl.value = amountInput.value;
                            }

                            updateForm.action = updateUrl;
                            if (typeof updateForm.requestSubmit === 'function') {
                                updateForm.requestSubmit();
                            } else {
                                updateForm.submit();
                            }
                            resolve();
                            return;
                        }
                        // cancel → agent-negotiation
                        restoreAgentNegotiationFormToAgentNegotiation();
                        if (!amountInput.value.trim()) {
                            amountInput.removeAttribute('name');
                        }
                        actionInput.value = 'cancel';
                        form.submit();
                        resolve();
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(result => {
                if (!result.isConfirmed && agentNegotiationModalInstance) {
                    restoreAgentNegotiationFormToAgentNegotiation();
                    amountInput.setAttribute('name', 'amount');
                    remarkInput.setAttribute('name', 'comment');
                    agentNegotiationModalInstance.show();
                }
            });
            };

            const modalIsVisible = modalEl && modalEl.classList.contains('show');
            if (modalIsVisible && agentNegotiationModalInstance) {
                const onHidden = function () {
                    modalEl.removeEventListener('hidden.bs.modal', onHidden);
                    if (document.activeElement && modalEl.contains(document.activeElement)) {
                        document.activeElement.blur();
                    }
                    runCancelConfirmPrompt();
                };
                modalEl.addEventListener('hidden.bs.modal', onHidden);
                agentNegotiationModalInstance.hide();
            } else {
                runCancelConfirmPrompt();
            }
        };

        function parseNegotiationAttr(attr) {
            if (attr === null || attr === undefined || attr === '') {
                return null;
            }
            const n = parseFloat(attr);
            return Number.isFinite(n) ? n : null;
        }

        function formatNegotiationAmount(value) {
            if (!Number.isFinite(Number(value))) {
                return '—';
            }
            return new Intl.NumberFormat(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(Number(value));
        }

        // Build the label shown next to a markup/discount line, e.g. "Markup (10%)" / "Discount (Fixed)".
        function buildAdjustmentLabel(baseLabel, type, rawValue) {
            const t = (type || '').toString().toLowerCase();
            if (t === 'percentage') {
                const pct = parseNegotiationAttr(rawValue);
                return Number.isFinite(pct) && pct > 0 ? `${baseLabel} (${pct}%)` : `${baseLabel} (%)`;
            }
            if (t === 'flat') {
                return `${baseLabel} (Fixed)`;
            }
            if (t === 'foc') {
                return `${baseLabel} (FOC)`;
            }
            return baseLabel;
        }

        // Populate a markup/discount pricing cell with its typed label + signed money value.
        function applyAdjustmentDisplay(labelEl, valueEl, baseLabel, type, rawValue, moneyValue, sign) {
            const money = parseNegotiationAttr(moneyValue);
            if (labelEl) {
                labelEl.textContent = buildAdjustmentLabel(baseLabel, type, rawValue);
            }
            if (valueEl) {
                if (Number.isFinite(money) && money > 0) {
                    valueEl.textContent = `${sign}${formatNegotiationAmount(money)}`;
                } else {
                    valueEl.textContent = formatNegotiationAmount(0);
                }
            }
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
