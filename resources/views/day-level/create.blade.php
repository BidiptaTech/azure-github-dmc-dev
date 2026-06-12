@extends('layouts.layout')

@section('title', 'Day Level')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --form-brand: #2f55d4;
            --form-brand-end: #1e3a8a;
            --form-brand-hover: #2545b8;
            --form-gradient-primary: linear-gradient(135deg, #1e3a8a 0%, #2f55d4 100%);
            --form-gradient-page: linear-gradient(135deg, #0f2557 0%, #1e3a8a 55%, #2f55d4 100%);
            --form-gradient-teal: linear-gradient(135deg, #134e4a 0%, #0f766e 100%);
            --form-gradient-info: linear-gradient(135deg, #155e75 0%, #0e7490 100%);
            --form-gradient-success: linear-gradient(135deg, #14532d 0%, #166534 100%);
            --form-header-bg: #1e3a8a;
            --form-danger: #dc3545;
            --form-danger-hover: #bb2d3b;
            --form-secondary: #64748b;
            --form-secondary-hover: #475569;
            --form-label-color: #334155;
            --form-border: #d8dee9;
            --form-panel-bg: linear-gradient(135deg, #f8fafc 0%, #eef3fb 100%);
            /* Compact density (~75% feel at 100% zoom) */
            --dl-field-h: 32px;
            --dl-field-fs: 0.8125rem;
            --dl-label-fs: 0.75rem;
            --dl-btn-fs: 0.75rem;
            --dl-radius: 6px;
            --dl-card-pad: 0.95rem;
            --dl-section-pad: 0.75rem 1rem;
            --dl-label-offset: calc(0.2rem + 1.05em);
        }
        #dayForm {
            font-size: var(--dl-field-fs);
        }
        #dayForm .g-2,
        #dayForm .g-3 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }
        #dayForm .form-label {
            color: var(--form-label-color);
            font-size: var(--dl-label-fs);
            font-weight: 600;
            margin-bottom: 0.2rem;
            line-height: 1.2;
        }
        #dayForm .form-control,
        #dayForm .form-select {
            min-height: var(--dl-field-h);
            height: var(--dl-field-h);
            padding: 0.25rem 0.5rem;
            border: 1px solid var(--form-border);
            border-radius: var(--dl-radius);
            font-size: var(--dl-field-fs);
            line-height: 1.25;
        }
        #dayForm .input-group.price-input-group {
            height: var(--dl-field-h);
            min-height: var(--dl-field-h);
            flex-wrap: nowrap;
        }
        #dayForm .input-group.price-input-group .input-group-text {
            min-height: var(--dl-field-h);
            height: var(--dl-field-h);
            padding: 0 0.45rem;
            font-size: 0.72rem;
            font-weight: 600;
            border-radius: var(--dl-radius) 0 0 var(--dl-radius);
            display: flex;
            align-items: center;
        }
        #dayForm .input-group.price-input-group .form-control {
            min-height: var(--dl-field-h);
            height: var(--dl-field-h);
            border-radius: 0 var(--dl-radius) var(--dl-radius) 0;
            padding: 0.25rem 0.45rem;
        }
        #dayForm .select2-container {
            width: 100% !important;
        }
        #dayForm .select2-container .select2-selection--single {
            min-height: var(--dl-field-h) !important;
            height: var(--dl-field-h) !important;
            border: 1px solid var(--form-border) !important;
            border-radius: var(--dl-radius) !important;
            font-size: var(--dl-field-fs);
            padding: 0 !important;
        }
        #dayForm .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(var(--dl-field-h) - 2px) !important;
            padding-left: 0.5rem !important;
            padding-right: 1.5rem !important;
            font-size: var(--dl-field-fs);
        }
        #dayForm .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(var(--dl-field-h) - 2px) !important;
            top: 1px !important;
        }
        #dayForm .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 1.25rem;
            font-size: 0.85rem;
        }
        #dayForm .btn {
            min-height: var(--dl-field-h);
            padding: 0.2rem 0.65rem;
            font-size: var(--dl-btn-fs);
            line-height: 1.2;
        }
        #dayForm .align-items-end > [class*="col-"].d-flex > .btn,
        #dayForm .align-items-end > [class*="col-"] > .btn.w-100.mt-4,
        #dayForm .align-items-end > [class*="col-"] > .btn.w-100 {
            margin-top: var(--dl-label-offset) !important;
            width: 100%;
        }
        #dayForm .hotels-add-btn,
        #dayForm .align-items-end > [class*="col-"]:not(.d-flex) > .btn.w-100 {
            margin-top: 0 !important;
        }
        .section-header-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.95rem;
        }
        .section-header-icon--light {
            background: rgba(255, 255, 255, 0.2);
        }
        .section-header-icon--muted {
            background: rgba(73, 80, 87, 0.12);
            color: #495057;
        }
        .day-tab-btn.active {
            background: var(--form-brand);
            color: #fff;
            border-color: var(--form-brand);
        }
        .data-table-sm {
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 0.75rem;
        }
        .data-table-sm td, .data-table-sm th {
            font-size: 0.82rem;
            vertical-align: middle;
        }
        .data-table-sm thead th {
            background: #f5f6f8 !important;
            color: #404e67 !important;
            border-color: #e9ecef;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        .data-table-sm tbody tr {
            background: #fff;
            box-shadow: inset 0 -1px 0 rgba(67, 89, 113, 0.08);
        }
        .data-table-sm tbody tr:nth-child(odd) {
            background: #ffffff;
        }
        .data-table-sm tbody tr:nth-child(even) {
            background: #f8f9ff;
        }
        .data-table-sm tbody tr:hover {
            background: #f0f4ff;
        }
        .modern-table-wrap {
            border: 1px solid rgba(47, 85, 212, 0.15);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(47, 85, 212, 0.08);
            overflow: hidden;
        }
        .sketch-card {
            border: 0;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }
        #dayForm .sketch-card > .card-body {
            padding: var(--dl-card-pad);
            background: #fff;
        }
        .stp-page-banner {
            margin-bottom: 0.75rem !important;
        }
        .stp-page-banner .card-header {
            background: var(--form-gradient-page);
            border: none;
            padding: 0.75rem 1rem;
        }
        .stp-page-banner h4 {
            font-size: 1.1rem;
        }
        .stp-page-banner h4,
        .stp-page-banner .banner-subtitle {
            color: #fff !important;
        }
        .stp-page-banner .banner-subtitle {
            color: rgba(255, 255, 255, 0.88) !important;
            font-size: 0.85rem;
        }
        .stp-back-btn {
            border-radius: var(--dl-radius);
            font-weight: 600;
            padding: 0.3rem 0.85rem;
            font-size: var(--dl-btn-fs);
            min-height: var(--dl-field-h);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.6);
            color: #495057;
        }
        .stp-back-btn:hover {
            background: #fff;
            color: var(--form-brand);
            border-color: #fff;
        }
        #dayForm .sketch-card .card-header,
        #dayForm .modern-section-header {
            border: none;
            padding: var(--dl-section-pad);
            color: #fff;
        }
        #dayForm .sketch-card .card-header strong,
        #dayForm .modern-section-header strong {
            font-size: 0.9rem;
        }
        #dayForm .dmc-form-section .card-header,
        #dayForm .card.attraction-day-section > .card-header {
            background: var(--form-gradient-primary);
        }
        #dayForm .multi-city-section .card-header {
            background: var(--form-gradient-teal);
        }
        #dayForm .hotels-section .card-header {
            background: #e9ecef;
            color: #212529 !important;
            border-bottom: 1px solid var(--form-border);
        }
        #dayForm .hotels-section .card-header strong,
        #dayForm .hotels-section .card-header .section-subtitle {
            color: #212529 !important;
        }
        #dayForm .hotels-section .card-header .section-subtitle {
            opacity: 0.75;
        }
        /* Light professional day headers */
        #dayForm .day-card .day-header-primary,
        #dayForm .day-card .day-header-success,
        #dayForm .day-card .day-header-warning,
        #dayForm .day-card .day-header-danger,
        #dayForm .day-card .day-header-info,
        #dayForm .day-card .day-header-purple { background: #f6f8fc; }
        #dayForm .day-card .card-header { letter-spacing: 0.01em; }
        #dayForm .day-card-header {
            color: #1f2d4d;
            border-bottom: 1px solid #e6ebf4;
            border-left: 3px solid var(--form-brand);
        }
        #dayForm .day-card .card-header strong { color: #1f2d4d !important; }
        #dayForm .day-card .card-header .small { color: #64748b !important; }
        #dayForm .modern-section-header strong,
        #dayForm .card-header strong,
        #dayForm .card-header .text-white {
            color: #fff !important;
        }
        #dayForm .card-header .section-subtitle {
            color: rgba(255, 255, 255, 0.92);
        }
        .day-card {
            border: 0;
            box-shadow: 0 0.25rem 0.75rem rgba(67, 89, 113, 0.08);
            overflow: hidden;
            margin-bottom: 0.75rem !important;
        }
        .day-card > .card-body {
            padding: var(--dl-card-pad) !important;
        }
        #dayWiseServiceBlocks {
            padding-top: 0.5rem;
        }
        /* Grouped service sections: light, compact panels (service + its transfer share one grid) */
        .day-service-group {
            border: 1px solid #e6ebf4;
            border-radius: 10px;
            background: #fff;
            padding: 0.6rem 0.7rem 0.65rem;
            margin-bottom: 0.6rem;
        }
        .day-service-group__header {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            margin-bottom: 0.5rem;
            padding-bottom: 0.4rem;
            border-bottom: 1px dashed #e6ebf4;
        }
        .day-service-group__header strong {
            font-size: 0.82rem;
            color: #1f2d4d;
            letter-spacing: 0.01em;
        }
        .day-service-group__hint {
            font-size: 0.68rem;
            color: #8492a6;
            margin-left: auto;
        }
        .day-service-group__badge {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            flex-shrink: 0;
        }
        .group-attraction { border-left: 3px solid #93a8f4; }
        .group-attraction .day-service-group__badge { background: #eef2ff; }
        .group-restaurant { border-left: 3px solid #7fd8c4; }
        .group-restaurant .day-service-group__badge { background: #e6f7f1; }
        .group-arrival { border-left: 3px solid #8fc9f2; }
        .group-arrival .day-service-group__badge { background: #e8f4fd; }
        .group-departure { border-left: 3px solid #f2c98f; }
        .group-departure .day-service-group__badge { background: #fdf3e4; }
        .day-service-transfer-panel {
            border: 1px dashed #dbe3f0;
            border-radius: var(--dl-radius);
            background: #f8fafd;
            padding: 0.55rem 0.65rem 0.6rem;
            margin-top: 0.55rem;
        }
        .day-service-transfer-panel__header {
            display: flex;
            align-items: flex-start;
            gap: 0.45rem;
            margin-bottom: 0.45rem;
        }
        .day-service-transfer-panel__header strong {
            font-size: 0.78rem;
            color: #3c4d6d;
        }
        .day-service-transfer-panel__header .small {
            font-size: 0.68rem !important;
        }
        .day-service-transfer-panel__icon {
            width: 1.4rem;
            height: 1.4rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
        }
        .attraction-transfer-panel .day-service-transfer-panel__icon {
            background: #eef2ff;
        }
        .restaurant-transfer-panel .day-service-transfer-panel__icon {
            background: #e6f7f1;
        }
        .day-group-msg {
            display: none;
            margin-top: 0.5rem;
            padding: 0.35rem 0.6rem;
            border-radius: 8px;
            background: #fff8e6;
            border: 1px solid #f3dfae;
            color: #8a6116;
            font-size: 0.74rem;
            font-weight: 600;
        }
        .day-group-msg.show { display: block; }
        .day-airport-transfer-wrap {
            margin-top: 0.6rem;
        }
        /* Added-services listing */
        .day-items-table td { vertical-align: middle; }
        .item-type-badge {
            display: inline-block;
            padding: 0.22rem 0.55rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }
        .item-type-badge--attraction { background: #eef2ff; color: #3a56c5; }
        .item-type-badge--restaurant { background: #e6f7f1; color: #0f766e; }
        .item-type-badge--arrival { background: #e8f4fd; color: #0b6aa2; }
        .item-type-badge--departure { background: #fdf3e4; color: #9a6b1a; }
        .item-type-badge--transfer { background: #eef1f6; color: #51607a; }
        .item-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1f2d4d;
            line-height: 1.25;
        }
        .item-meta {
            font-size: 0.72rem;
            color: #8492a6;
            margin-top: 0.1rem;
        }
        .item-price-tag {
            display: inline-block;
            margin-top: 0.25rem;
            margin-right: 0.25rem;
            padding: 0.12rem 0.45rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .item-price-tag--meal { background: #eef2ff; color: #3a56c5; }
        .item-price-tag--transfer { background: #e6f7f1; color: #0f766e; }
        .item-route {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.3rem;
            font-size: 0.74rem;
            color: #3c4d6d;
            line-height: 1.3;
        }
        .item-route-stop { max-width: 16rem; }
        .item-route-arrow {
            color: #9aa7bd;
            flex-shrink: 0;
        }
        #dayForm .btn,
        #packagePreviewModal .btn {
            border-radius: 0.55rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            transition: background-color 0.18s ease-in-out, border-color 0.18s ease-in-out, color 0.18s ease-in-out, box-shadow 0.18s ease-in-out, transform 0.18s ease-in-out;
        }
        #dayForm .btn-outline-primary,
        #packagePreviewModal .btn-outline-primary {
            color: var(--form-brand) !important;
            border-color: var(--form-brand) !important;
            background-color: #fff !important;
            box-shadow: none;
        }
        #dayForm .btn-outline-primary:hover,
        #dayForm .btn-outline-primary:focus,
        #dayForm .btn-outline-primary:active,
        #packagePreviewModal .btn-outline-primary:hover,
        #packagePreviewModal .btn-outline-primary:focus,
        #packagePreviewModal .btn-outline-primary:active {
            color: #fff !important;
            border-color: transparent !important;
            background: var(--form-gradient-primary) !important;
            box-shadow: 0 4px 12px rgba(47, 85, 212, 0.35);
            transform: translateY(-1px);
        }
        #dayForm .btn-primary,
        #packagePreviewModal .btn-primary {
            color: #fff !important;
            border: none !important;
            background: var(--form-gradient-primary) !important;
            box-shadow: 0 4px 12px rgba(47, 85, 212, 0.35);
        }
        #dayForm .btn-primary:hover,
        #dayForm .btn-primary:focus,
        #dayForm .btn-primary:active,
        #packagePreviewModal .btn-primary:hover,
        #packagePreviewModal .btn-primary:focus,
        #packagePreviewModal .btn-primary:active {
            color: #fff !important;
            background: linear-gradient(135deg, #16306e 0%, #2545b8 100%) !important;
            box-shadow: 0 6px 16px rgba(30, 58, 138, 0.35);
            transform: translateY(-1px);
        }
        #dayForm .btn-outline-danger {
            color: var(--form-danger) !important;
            border-color: rgba(234, 84, 85, 0.45) !important;
            background-color: #fff5f5 !important;
        }
        #dayForm .btn-outline-danger:hover,
        #dayForm .btn-outline-danger:focus,
        #dayForm .btn-outline-danger:active {
            color: #fff !important;
            border-color: var(--form-danger-hover) !important;
            background-color: var(--form-danger-hover) !important;
            box-shadow: 0 0.45rem 1rem rgba(234, 84, 85, 0.25);
            transform: translateY(-1px);
        }
        #dayForm .btn-outline-secondary,
        #packagePreviewModal .btn-outline-secondary {
            color: #566a7f !important;
            border-color: rgba(86, 106, 127, 0.35) !important;
            background-color: #f8f9fb !important;
        }
        #dayForm .btn-outline-secondary:hover,
        #dayForm .btn-outline-secondary:focus,
        #dayForm .btn-outline-secondary:active,
        #packagePreviewModal .btn-outline-secondary:hover,
        #packagePreviewModal .btn-outline-secondary:focus,
        #packagePreviewModal .btn-outline-secondary:active {
            color: #fff !important;
            border-color: var(--form-secondary-hover) !important;
            background-color: var(--form-secondary-hover) !important;
            box-shadow: 0 0.45rem 1rem rgba(86, 106, 127, 0.22);
            transform: translateY(-1px);
        }
        #dayForm td .btn + .btn {
            margin-left: 0.45rem;
        }
        #dayForm td.action-cell {
            white-space: nowrap;
        }
        #dayForm .action-buttons {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            flex-wrap: nowrap;
        }
        #dayForm .action-buttons .btn + .btn {
            margin-left: 0;
        }
        #dayForm .btn-icon {
            width: 1.65rem;
            height: 1.65rem;
            min-height: 1.65rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        #dayForm .btn-icon svg {
            width: 1rem;
            height: 1rem;
            stroke: currentColor;
        }
        .day-card .day-card-header {
            color: #1f2d4d;
        }
        .day-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.65rem;
            height: 1.65rem;
            border-radius: 999px;
            background: #e8edfb;
            color: var(--form-brand);
            font-weight: 700;
            font-size: 0.8rem;
        }
        .day-card-header strong {
            font-size: 0.9rem;
        }
        .day-card-header .small {
            font-size: 0.7rem !important;
        }
        .detail-chip {
            display: inline-block;
            padding: 0.22rem 0.5rem;
            border-radius: 999px;
            background: #f0f4ff;
            color: var(--form-brand);
            border: 1px solid rgba(47, 85, 212, 0.2);
            font-weight: 600;
            margin: 0.1rem 0.15rem 0.1rem 0;
        }
        .day-card .fw-semibold.text-primary {
            color: var(--form-brand) !important;
            background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
            border: 1px solid rgba(47, 85, 212, 0.15);
            border-radius: var(--dl-radius);
            padding: 0.3rem 0.6rem;
            font-size: 0.78rem;
            margin-bottom: 0.15rem;
        }
        .section-label {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            font-size: 0.72rem;
            color: #566a7f;
            margin-bottom: 0.35rem;
        }
        .hotels-section .card-header,
        .multi-city-section .card-header,
        .dmc-form-section .card-header {
            padding: var(--dl-section-pad);
        }
        .hotels-section .card-header .section-subtitle,
        .multi-city-section .card-header .section-subtitle {
            display: block;
            font-size: 0.8rem;
            font-weight: 400;
            opacity: 0.9;
            margin-top: 0.2rem;
        }
        .hotels-form-body {
            padding: var(--dl-card-pad);
        }
        .hotels-form-panel {
            background: var(--form-panel-bg);
            border: 1px solid #b3d9ff;
            border-radius: var(--dl-radius);
            box-shadow: 0 1px 4px rgba(47, 85, 212, 0.06);
            padding: 0.65rem 0.75rem 0.75rem;
        }
        .hotels-form-panel + .hotels-form-panel {
            margin-top: 0.5rem;
        }
        .hotels-form-panel-title {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--form-brand);
            margin-bottom: 0.45rem;
        }
        .hotels-form-panel .form-label {
            font-size: var(--dl-label-fs);
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 0.2rem;
        }
        .hotels-form-panel .form-control,
        .hotels-form-panel .form-select {
            font-size: var(--dl-field-fs);
            border-color: #d9dee3;
            min-height: var(--dl-field-h);
            height: var(--dl-field-h);
        }
        .hotels-form-panel .form-check {
            min-height: var(--dl-field-h);
            display: flex;
            align-items: center;
            padding-left: 1.75rem;
            margin-bottom: 0;
        }
        .hotels-form-panel .form-check-input {
            margin-top: 0;
        }
        .hotels-form-panel .form-check-label {
            font-size: var(--dl-field-fs);
            font-weight: 500;
            color: #566a7f;
            padding-left: 2px;
        }
        .pricing-panel {
            background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: var(--dl-radius);
            padding: 0.65rem 0.75rem;
        }
        .pricing-panel-title {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #4338ca;
            margin-bottom: 0.45rem;
        }
        .pricing-panel .price-input-group .input-group-text {
            background: #fff;
            border-color: #c7d2fe;
            color: #4338ca;
            font-weight: 600;
            font-size: 0.72rem;
        }
        .pricing-panel .form-control {
            border-color: #c7d2fe;
            background: #fff;
        }
        .pricing-panel .price-input-group {
            height: var(--dl-field-h);
        }
        .pricing-panel .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
        }
        .pricing-total-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #4338ca;
            color: #fff;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .day-price-panel {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 0.55rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
        }
        .hotels-compact-table-wrap {
            margin-top: 0.5rem;
        }
        .hotels-compact-table th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .hotels-compact-table td {
            vertical-align: middle;
            font-size: 0.84rem;
        }
        .hotels-compact-table .hotel-cell-title {
            font-weight: 600;
            color: #334155;
            line-height: 1.35;
        }
        .hotels-compact-table .hotel-cell-meta {
            font-size: 0.76rem;
            color: #64748b;
            margin-top: 2px;
        }
        .hotels-compact-table .hotel-price-night {
            font-weight: 600;
            color: #4338ca;
            white-space: nowrap;
        }
        .hotels-compact-table .hotel-price-total {
            font-weight: 700;
            color: #0f766e;
            white-space: nowrap;
        }
        .hotels-compact-table .hotel-price-breakdown {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 2px;
        }
        #hotel_priority {
            max-width: 5.5rem;
        }
        .hotels-add-btn {
            min-height: var(--dl-field-h);
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }
        .hotels-section .modern-table-wrap {
            margin-top: 0.65rem;
        }
        #dayForm .modern-table-wrap.mt-3 {
            margin-top: 0.5rem !important;
        }
        #dayForm small.text-muted {
            font-size: 0.7rem;
        }
        #packagePreviewModal .modal-header {
            background: var(--form-gradient-primary);
            color: #fff !important;
            border-bottom: 0;
            padding: 1rem 1.25rem;
        }
        #dayForm .form-actions-bar {
            padding: 0.75rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        #packagePreviewModal .modal-header .modal-title {
            color: #fff !important;
        }
        #packagePreviewModal .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 1;
        }
        .preview-city-card {
            border: 1px solid rgba(47, 85, 212, 0.15);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(47, 85, 212, 0.08);
        }
        .preview-city-card .preview-city-head {
            background: var(--form-gradient-primary);
            color: #fff;
            padding: 0.75rem 1rem;
            font-weight: 600;
        }
        .preview-day-block {
            border-bottom: 1px solid rgba(67, 89, 113, 0.1);
            padding: 0.85rem 1rem;
        }
        .preview-day-block:last-child {
            border-bottom: 0;
        }
        .preview-day-title {
            font-weight: 700;
            color: var(--form-brand);
            margin-bottom: 0.45rem;
        }
        .preview-line {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            color: #566a7f;
        }
        .preview-line strong {
            color: #435971;
            min-width: 5.5rem;
            display: inline-block;
        }
        .preview-warnings .alert {
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        .preview-empty {
            color: #8592a3;
            font-size: 0.85rem;
            font-style: italic;
        }
        .preview-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .preview-summary-card {
            background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
            border: 1px solid rgba(47, 85, 212, 0.18);
            border-radius: 10px;
            padding: 0.85rem 1rem;
        }
        .preview-summary-card .label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #8592a3;
            font-weight: 600;
        }
        .preview-summary-card .value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #435971;
            margin-top: 0.15rem;
        }
        .preview-summary-card.grand-total .value {
            color: #198754;
        }
        .preview-service-table th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .preview-service-table td {
            font-size: 0.82rem;
            vertical-align: middle;
        }
        .preview-transfer-chip {
            display: inline-block;
            background: #eef2ff;
            border: 1px solid rgba(47, 85, 212, 0.2);
            border-radius: 999px;
            padding: 0.15rem 0.55rem;
            font-size: 0.78rem;
            color: #435971;
            margin-top: 0.2rem;
        }
        .preview-hotel-continuation {
            font-size: 0.78rem;
            color: #8592a3;
            font-style: italic;
        }
        .container-xxl.container-p-y:has(#dayForm) {
            padding-top: 0.65rem !important;
            padding-bottom: 0.85rem !important;
        }
        #dayForm .card-header.gap-2 {
            gap: 0.35rem !important;
        }
        #dayForm .sketch-card {
            border-radius: 8px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
        }
        #dayForm .data-table-sm td,
        #dayForm .data-table-sm th {
            padding: 0.35rem 0.5rem;
            font-size: 0.75rem;
        }
        #dayForm .data-table-sm thead th {
            font-size: 0.68rem;
        }
        #dayForm .form-actions-bar .btn {
            min-height: 34px;
            padding: 0.3rem 1rem;
            font-size: 0.8125rem;
        }
        #dayForm .btn.is-loading,
        #packagePreviewModal .btn.is-loading {
            opacity: 0.85;
            pointer-events: none;
            transform: none !important;
        }
        #dayForm .btn.is-loading .spinner-border,
        #packagePreviewModal .btn.is-loading .spinner-border {
            width: 0.85rem;
            height: 0.85rem;
            border-width: 0.14em;
            vertical-align: -0.1em;
        }
        #dayForm .alert {
            padding: 0.45rem 0.75rem;
            font-size: 0.8125rem;
            margin-bottom: 0.5rem;
        }
        #dayForm .section-subtitle {
            font-size: 0.72rem !important;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row mb-2 stp-page-banner">
                <div class="col-12">
                    <div class="card shadow-sm border-0 sketch-card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    @isset($dayLevel)
                                        Edit Day Level Package
                                    @else
                                        Create Day Level Package
                                    @endisset
                                </h4>
                                <div class="banner-subtitle mt-1">
                                    @if(!empty($editingPackageId))
                                        Editing one package only
                                    @elseif(isset($dayLevel))
                                        Update saved itinerary and services
                                    @else
                                        Design day-wise hotels, activities and transfers
                                    @endif
                                </div>
                            </div>
                            <button type="button" class="btn stp-back-btn" onclick="history.back()">Back</button>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form id="dayForm" method="POST" action="{{ isset($dayLevel) ? route('day-level.update', $dayLevel->id) : route('day-level.store') }}">
                @csrf
                @if(isset($dayLevel))
                    @method('PUT')
                @endif
                <input type="hidden" name="payload_json" id="payload_json">
                <input type="hidden" name="structured_mode" value="1">
                @if(!empty($editingPackageId))
                    <input type="hidden" name="edit_package_id" id="edit_package_id" value="{{ $editingPackageId }}">
                @endif
                <input type="hidden" name="hotels_json" id="hotels_json">
                <input type="hidden" name="activities_json" id="activities_json">
                <input type="hidden" name="inter_json" id="inter_json">

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card sketch-card dmc-form-section">
                            <div class="card-header modern-section-header text-white d-flex align-items-center gap-2">
                                <div class="section-header-icon section-header-icon--light">
                                    <i class="ri-settings-3-line text-white"></i>
                                </div>
                                <div>
                                    <strong class="text-white d-block">DMC Form</strong>
                                    <span class="section-subtitle">Package days and DMC assignment</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label section-label" for="days">No. of Days</label>
                                        <input type="number" class="form-control" id="days" name="days" min="1" max="365" value="{{ old('days', 1) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label section-label">Master DMC</label>
                                        <input type="text" class="form-control" value="{{ $masterDmcName ?? 'Master DMC' }}" readonly>
                                        <input type="hidden" id="master_dmc_id" value="{{ (int) $masterDmcId }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label section-label" for="dmc_id">DMC</label>
                                        <input type="text" class="form-control" value="{{ $dmcName ?? 'DMC' }}" readonly>
                                        <input type="hidden" id="dmc_id" value="{{ (int) $defaultDmcId }}" data-country="{{ $dmcCountry ?? '' }}">
                                    </div>
                                </div>

                                {{-- Day tabs removed: itinerary day mapping comes from Multi City --}}
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card sketch-card multi-city-section">
                            <div class="card-header modern-section-header text-white d-flex align-items-center gap-2">
                                <div class="section-header-icon section-header-icon--light">
                                    <i class="ri-map-pin-line text-white"></i>
                                </div>
                                <div>
                                    <strong class="text-white d-block">Multi City</strong>
                                    <span class="section-subtitle">Map cities to check-in and check-out days</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <input type="hidden" id="country" name="country" value="{{ old('country') }}">
                                    <div class="col-md-4">
                                        <label class="form-label" for="city_id">City</label>
                                        <select id="city_id" name="city_id" class="form-select searchable-select">
                                            <option value="">Select city</option>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->id }}"
                                                    data-name="{{ $city->name }}"
                                                    data-country="{{ $city->country }}"
                                                    {{ (string) old('city_id') === (string) $city->id ? 'selected' : '' }}>
                                                    {{ $city->name }}@if ($city->country), {{ $city->country }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="mc_day_in">Day Check In</label>
                                        <select id="mc_day_in" class="form-select searchable-select"></select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="mc_day_out">Day Check Out</label>
                                        <select id="mc_day_out" class="form-select searchable-select"></select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-primary w-100" id="multiCityAddBtn" onclick="addMultiCityPlan()">Add</button>
                                    </div>
                                </div>
                                <small class="text-muted">Data auto-loads when you change DMC/city.</small>

                                <div class="table-responsive modern-table-wrap mt-3">
                                    <table class="table table-sm data-table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>City</th>
                                                <th>Day Check In</th>
                                                <th>Day Check Out</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="multiCityRows">
                                            <tr><td colspan="4" class="text-muted">No city plan added</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card sketch-card hotels-section">
                            <div class="card-header modern-section-header d-flex align-items-center gap-2">
                                <div class="section-header-icon section-header-icon--muted">
                                    <i class="ri-hotel-line"></i>
                                </div>
                                <div>
                                    <strong class="d-block">Hotels</strong>
                                    <span class="section-subtitle">Add accommodation and meal plans with pricing</span>
                                </div>
                            </div>
                            <div class="card-body hotels-form-body">
                                <div class="hotels-form-panel">
                                    <div class="hotels-form-panel-title">Stay details</div>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label" for="hotel_city_select">City</label>
                                        <select id="hotel_city_select" class="form-select searchable-select">
                                            <option value="">Select city</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label" for="hotel_category">Hotel Category</label>
                                        <select id="hotel_category" class="form-select searchable-select">
                                            <option value="">Choose category</option>
                                            @foreach ($hotelStarRatings as $k => $v)
                                                <option value="{{ $k }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-md-8">
                                        <label class="form-label" for="hotel_select">Hotel</label>
                                        <select id="hotel_select" class="form-select searchable-select">
                                            <option value="">Select hotel</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-4">
                                        <label class="form-label" for="hotel_day">Nights</label>
                                        <select id="hotel_day" class="form-select searchable-select" title="Number of nights for this stay"></select>
                                    </div>
                                </div>
                                </div>

                                <div class="hotels-form-panel">
                                    <div class="hotels-form-panel-title">Room, bed &amp; meals</div>
                                    <div class="row g-3 align-items-end">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label" for="hotel_room_select">Room</label>
                                        <select id="hotel_room_select" class="form-select searchable-select">
                                            <option value="">Select hotel first</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label" for="hotel_bed_select">Bed</label>
                                        <select id="hotel_bed_select" class="form-select searchable-select">
                                            <option value="">Select room first</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label" for="hotel_meal_plan">Meal</label>
                                        <select id="hotel_meal_plan" class="form-select searchable-select">
                                            <option value="">Select meal plan</option>
                                        </select>
                                    </div>
                                    
                                    </div>
                                </div>

                                <div class="hotels-form-panel pricing-panel" id="hotel_pricing_panel">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="pricing-panel-title mb-0">Pricing (editable)</div>
                                        <span class="pricing-total-badge" id="hotel_price_total_badge">Total: SGD 0.00</span>
                                    </div>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label" for="hotel_room_price">Room Price <small class="text-muted">(double weekday)</small></label>
                                            <div class="input-group price-input-group">
                                                <span class="input-group-text">SGD</span>
                                                <input type="number" class="form-control" id="hotel_room_price" min="0" step="0.01" placeholder="0.00" oninput="updateHotelPriceTotal()">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label" for="hotel_breakfast_price">Breakfast</label>
                                            <div class="input-group price-input-group">
                                                <span class="input-group-text">SGD</span>
                                                <input type="number" class="form-control" id="hotel_breakfast_price" min="0" step="0.01" placeholder="0.00" oninput="updateHotelPriceTotal()">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label" for="hotel_lunch_price">Lunch</label>
                                            <div class="input-group price-input-group">
                                                <span class="input-group-text">SGD</span>
                                                <input type="number" class="form-control" id="hotel_lunch_price" min="0" step="0.01" placeholder="0.00" oninput="updateHotelPriceTotal()">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label" for="hotel_dinner_price">Dinner</label>
                                            <div class="input-group price-input-group">
                                                <span class="input-group-text">SGD</span>
                                                <input type="number" class="form-control" id="hotel_dinner_price" min="0" step="0.01" placeholder="0.00" oninput="updateHotelPriceTotal()">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="hotels-form-panel">
                                    <div class="row g-3 align-items-end">
                                    <div class="col-lg-2 col-md-3">
                                        <label class="form-label" for="hotel_priority">Priority</label>
                                        <input type="number" class="form-control" id="hotel_priority" min="1" value="1">
                                    </div>
                                    <div class="col-lg-3 col-md-4">
                                        <label class="form-label d-none d-md-block">&nbsp;</label>
                                        <button type="button" class="btn btn-outline-primary w-100 hotels-add-btn" id="hotelAddBtn" onclick="addHotel()">Add Hotel</button>
                                    </div>
                                    </div>
                                </div>

                                <div class="table-responsive modern-table-wrap hotels-compact-table-wrap">
                                    <table class="table table-sm data-table-sm hotels-compact-table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Day</th>
                                                <th>City</th>
                                                <th>Hotel</th>
                                                <th>Nights</th>
                                                <th>Room &amp; Meal</th>
                                                <th class="text-end">Price / Night</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end" style="width:88px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="hotelRows">
                                            <tr><td colspan="8" class="text-muted">No hotels added</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card sketch-card attraction-day-section border-0">
                            <div class="card-header modern-section-header text-white d-flex align-items-center gap-2">
                                <div class="section-header-icon section-header-icon--light">
                                    <i class="ri-calendar-event-line text-white"></i>
                                </div>
                                <div>
                                    <strong class="text-white d-block">Attraction / Restaurant by Day</strong>
                                    <span class="section-subtitle">Plan daily attractions, meals and transfers</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="dayWiseServiceBlocks"></div>
                            </div>
                        </div>
                    </div>


                    <div class="col-12">
                        <div class="d-flex justify-content-center gap-2 flex-wrap form-actions-bar">
                            <button type="button" class="btn btn-outline-primary btn-lg px-4" id="packagePreviewBtn" onclick="openPackagePreview()">Preview Package</button>
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="mainSubmitBtn" data-loading-text="{{ isset($dayLevel) ? 'Updating...' : 'Submitting...' }}">{{ isset($dayLevel) ? 'Update' : 'Submit' }}</button>
                            <button type="reset" class="btn btn-outline-secondary btn-lg px-4" onclick="resetAll()">Reset</button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="modal fade" id="packagePreviewModal" tabindex="-1" aria-labelledby="packagePreviewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="packagePreviewModalLabel">Package Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="packagePreviewBody"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="previewConfirmBtn" data-loading-text="{{ isset($dayLevel) ? 'Updating...' : 'Submitting...' }}" onclick="submitFromPreview()">Confirm &amp; {{ isset($dayLevel) ? 'Update' : 'Submit' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    @if(isset($dayLevel))
        <script id="edit-payload-data" type="application/json">{!! json_encode($editPayload ?? $dayLevel->structured_payload ?? ['Master_DMC' => []], JSON_UNESCAPED_SLASHES) !!}</script>
        <script id="edit-meta-data" type="application/json">{!! json_encode([
            'days' => (int)($editPackageDays ?? $dayLevel->days ?? 1),
            'row_days' => (int)($dayLevel->days ?? 1),
            'country' => (string)($dayLevel->country ?? ''),
            'city_id' => (int)($dayLevel->city_id ?? 0),
            'dmc_id' => (int)($dayLevel->dmc_id ?? 0),
            'master_dmc_id' => (int)($dayLevel->master_dmc_id ?? 0),
            'editing_package_id' => (string)($editingPackageId ?? ''),
        ], JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
    <script>
        const __editPayloadEl = document.getElementById('edit-payload-data');
        const __editMetaEl = document.getElementById('edit-meta-data');
        window.__EDIT_PAYLOAD__ = __editPayloadEl ? JSON.parse(__editPayloadEl.textContent || '{}') : null;
        window.__EDIT_DAY_LEVEL_META__ = __editMetaEl ? JSON.parse(__editMetaEl.textContent || '{}') : null;
        window.__EDITING_PACKAGE_ID__ = String(window.__EDIT_DAY_LEVEL_META__?.editing_package_id || '').trim();

        /** Set true to re-enable Master DMC / DMC / city alerts and blocking validation */
        const REQUIRE_MASTER_DMC_CITY = false;

        const DAY_LEVEL_ROUTES = {
            byCity: @json(route('day-level.by-city')),
            hotelsByRating: @json(route('day-level.hotels-by-rating')),
            roomsByHotel: @json(route('day-level.rooms-by-hotel')),
            bedsByRoom: @json(route('day-level.beds-by-room')),
            mealPlansByHotel: @json(route('day-level.meal-plans-by-hotel')),
            mealsByRestaurant: @json(route('day-level.meals-by-restaurant')),
            transferOptions: @json(route('day-level.transfer-options')),
            transferZonePrice: @json(route('day-level.transfer-zone-price')),
            ticketsByAttraction: @json(route('day-level.tickets-by-attraction')),
            citiesByCountry: @json(route('day-level.cities-by-country')),
        };

        function packageMatchesEditFilter(pkg) {
            if (!window.__EDITING_PACKAGE_ID__) {
                return true;
            }
            const pid = String(pkg?.package_id || pkg?.packageId || '').trim();
            return pid === window.__EDITING_PACKAGE_ID__;
        }

        let activeDay = 1;
        let daysCount = 1;
        let hotelsByRating = {};
        let hotelsFlat = [];
        let hotels = [];
        let dayItems = [];
        let inter = [];
        let allCities = [];
        let multiCityPlans = [];
        let isSyncingCitySelectors = false;
        let isPrefillingHotelForm = false;
        let editingMultiCityIndex = null;
        let editingHotelIndex = null;
        let editingActivityIndex = null;
        let isPrefillingActivityForm = false;
        let isSyncingTransferCity = false;
        let isApplyingTransferDefaults = false;
        let isHydratingDayServices = false;
        let isHydratingEditPayload = false;
        let hydrateDayServicesInFlight = null;
        let hydrateDayServicesQueued = false;
        let transferOptionsLoadTimer = null;
        const transferOptionsCache = {};
        const transferOptionsInflight = {};
        const activityCityByDay = {};
        let attractionsCache = [];
        let restaurantsCache = [];
        let transferLocationOptions = [];
        /** Arrival pickup: ports for Master DMC country. */
        let transferArrivalPickupOptions = [];
        /** Arrival drop: ports + hotels + attractions + restaurants. */
        let transferArrivalDropOptions = [];
        let zoneTransferOptions = [];
        let serviceTransferOptions = [];
        /** value (port:6, hotel:12, zone:3) → human-readable label from API / selects */
        let transferLocationLabelByValue = {};
        let transferDefaults = { defaultPort: '' };
        let hotelTransferState = { city: '', pickup: '', drop: '' };
        let hotelRoomsCache = [];
        let dayTransferExtras = {};

        function hotelsHaveArrivalDepartureTransferSaved() {
            return hotels.some(h => String(h.arrival_departure || 'No') === 'Yes');
        }

        function isArrivalDepartureTransfersActive() {
            return multiCityPlans.length > 0 || hotelsHaveArrivalDepartureTransferSaved();
        }

        function normalizeCityNameKey(name) {
            return String(name || '').split(',')[0].trim().toLowerCase();
        }

        function shouldShowArrivalForDay(dayVal) {
            const d = parseInt(String(dayVal || 0), 10) || 0;
            if (d < 1) return false;
            if (!multiCityPlans.length) {
                return d === 1;
            }
            return multiCityPlans.some(p => (parseInt(String(p?.day_in || 0), 10) || 0) === d);
        }

        function shouldShowDepartureForDay(dayVal) {
            const d = parseInt(String(dayVal || 0), 10) || 0;
            if (d < 1) return false;
            if (!multiCityPlans.length) {
                return d === daysCount;
            }
            return multiCityPlans.some(p => (parseInt(String(p?.day_out || 0), 10) || 0) === d);
        }

        function hotelRowToTransferLocation(row) {
            if (!row || !String(row.hotel_id || '').trim()) {
                return { value: '', label: '' };
            }
            const hotelUniqueId = resolveHotelUniqueIdForPayload(row.hotel_id);
            const name = String(row.hotel_name || '').replace(/^\s+|\s+$/g, '') || 'Hotel';
            return { value: `hotel:${hotelUniqueId}`, label: name };
        }

        function getArrivalHotelForDay(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const plans = multiCityPlans.filter(p => (parseInt(String(p?.day_in || 0), 10) || 0) === d);
            if (plans.length) {
                for (const plan of plans) {
                    const cityKey = normalizeCityNameKey(plan.city_name);
                    const match = hotels.find(h =>
                        normalizeCityNameKey(h.city_name) === cityKey
                        && (parseInt(String(h.day || 0), 10) || 0) === d
                    );
                    if (match) return hotelRowToTransferLocation(match);
                }
            }
            if (d === 1) {
                return getDayOneHotelDropValueAndLabel();
            }
            const anyOnDay = hotels.find(h => (parseInt(String(h.day || 0), 10) || 0) === d);
            return hotelRowToTransferLocation(anyOnDay);
        }

        function getDepartureHotelForDay(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const plans = multiCityPlans.filter(p => (parseInt(String(p?.day_out || 0), 10) || 0) === d);
            if (plans.length) {
                for (const plan of plans) {
                    const cityKey = normalizeCityNameKey(plan.city_name);
                    const match = hotels.find(h =>
                        normalizeCityNameKey(h.city_name) === cityKey
                        && hotelStayEndDay(h) === d
                    ) || hotels.find(h =>
                        normalizeCityNameKey(h.city_name) === cityKey
                        && (parseInt(String(h.day || 0), 10) || 0) <= d
                        && hotelStayEndDay(h) >= d
                    );
                    if (match) return hotelRowToTransferLocation(match);
                }
            }
            if (d === daysCount) {
                return getDeparturePickupHotelValueAndLabel();
            }
            const covering = hotels
                .filter(h => (parseInt(String(h.day || 0), 10) || 0) <= d && hotelStayEndDay(h) >= d)
                .sort((a, b) => (parseInt(String(b.day || 0), 10) || 0) - (parseInt(String(a.day || 0), 10) || 0))[0];
            return hotelRowToTransferLocation(covering);
        }

        function getMultiCityLegDays() {
            const days = new Set();
            multiCityPlans.forEach((plan) => {
                const din = parseInt(String(plan?.day_in || 0), 10) || 0;
                const dout = parseInt(String(plan?.day_out || 0), 10) || 0;
                if (din > 0) days.add(din);
                if (dout > 0) days.add(dout);
            });
            if (!days.size) {
                days.add(1);
                if (daysCount >= 2) days.add(daysCount);
            }
            return Array.from(days).sort((a, b) => a - b);
        }

        function loadTransferOptionsForLegDays() {
            getMultiCityLegDays().forEach((dayVal) => loadTransferOptionsForCity(dayVal));
        }

        function buildTransferOptionsCacheKey(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const cityName = getCityNameFromSelect(`activity_city_select_${d}`)
                || getCityNameFromSelect(`transfer_city_select_${d}`)
                || getCityNameFromSelect('hotel_city_select')
                || '';
            const dmcId = document.getElementById('dmc_id')?.value || '';
            const masterDmcId = document.getElementById('master_dmc_id')?.value || '';
            const country = document.getElementById('country')?.value || '';
            return `${dmcId}|${masterDmcId}|${country}|${cityName}`.toLowerCase();
        }

        /** One fetch per unique city/DMC — debounced to avoid request storms. */
        function scheduleTransferOptionsReload(force = false) {
            clearTimeout(transferOptionsLoadTimer);
            transferOptionsLoadTimer = setTimeout(() => {
                transferOptionsLoadTimer = null;
                loadTransferOptionsForUniqueCities(force);
            }, 250);
        }

        function loadTransferOptionsForUniqueCities(force = false) {
            const seen = new Set();
            for (let d = 1; d <= daysCount; d++) {
                const cityName = getCityNameFromSelect(`activity_city_select_${d}`);
                if (!cityName) continue;
                const key = buildTransferOptionsCacheKey(d);
                if (seen.has(key)) continue;
                seen.add(key);
                loadTransferOptionsForCity(d, { force });
            }
        }

        function loadTransferOptionsForAllDays() {
            scheduleTransferOptionsReload(false);
        }

        function resolveHotelUniqueIdForPayload(hotelId) {
            const id = String(hotelId || '').trim();
            if (!id) return '';
            const flatHit = (Array.isArray(hotelsFlat) ? hotelsFlat : []).find((h) => {
                return String(h.hotel_unique_id || '') === id || String(h.id || '') === id;
            });
            if (flatHit) {
                return String(flatHit.hotel_unique_id || flatHit.id || id).trim();
            }
            const gridHit = (Array.isArray(hotels) ? hotels : []).find((h) => {
                return String(h.hotel_id || '') === id;
            });
            return String(gridHit?.hotel_id || id).trim();
        }

        function getDayOneHotelDropValueAndLabel() {
            const sorted = [...hotels].sort((a, b) => (parseInt(String(a.day || 0), 10) || 0) - (parseInt(String(b.day || 0), 10) || 0));
            const row = sorted.find(h => (parseInt(String(h.day || 1), 10) || 1) === 1);
            if (!row || !String(row.hotel_id || '').trim()) {
                return { value: '', label: '' };
            }
            const hotelUniqueId = resolveHotelUniqueIdForPayload(row.hotel_id);
            const name = String(row.hotel_name || '').replace(/^\s+|\s+$/g, '') || 'Day 1 hotel';
            return { value: `hotel:${hotelUniqueId}`, label: name };
        }

        /**
         * Departure pickup: hotel that still covers checkout / final itinerary day when possible,
         * otherwise the hotel spanning the latest nights, finally Day‑1 hotel.
         */
        function getDeparturePickupHotelValueAndLabel() {
            const fallback = getDayOneHotelDropValueAndLabel();
            const dc = parseInt(String(daysCount || 1), 10) || 1;
            const rows = [];

            hotels.forEach((h) => {
                const hid = resolveHotelUniqueIdForPayload(h.hotel_id);
                if (!hid) return;
                const din = parseInt(String(h.day || 1), 10) || 1;
                const nghtRaw = parseInt(String(h.night ?? 1), 10);
                const nght = Number.isFinite(nghtRaw) && nghtRaw >= 1 ? nghtRaw : 1;
                const lastOccupied = din + nght - 1;
                const label = String(h.hotel_name || '').replace(/^\s+|\s+$/g, '') || 'Hotel';
                rows.push({
                    din,
                    nght,
                    lastOccupied,
                    value: `hotel:${hid}`,
                    label,
                });
            });

            if (!rows.length) return fallback;

            const coversCheckout = rows.filter((r) => r.din <= dc && r.lastOccupied >= dc);
            let chosen = coversCheckout.slice().sort((a, b) => b.din - a.din || b.lastOccupied - a.lastOccupied)[0];
            if (!chosen) {
                chosen = rows.slice().sort((a, b) => b.lastOccupied - a.lastOccupied || b.din - a.din)[0];
            }
            return chosen ? { value: chosen.value, label: chosen.label } : fallback;
        }

        function transferTypeFromValue(value) {
            const v = String(value || '').trim().toLowerCase();
            if (v.startsWith('port:')) return 'port';
            if (v.startsWith('hotel:')) return 'hotel';
            if (v.startsWith('attraction:')) return 'attraction';
            if (v.startsWith('restaurant:')) return 'restaurant';
            if (v.startsWith('zone:')) return 'zone';
            return '';
        }

        function transferTypeFromRow(row) {
            const type = String(row?.type || '').trim().toLowerCase();
            return type || transferTypeFromValue(row?.value);
        }

        /** e.g. "Changi Airport (port)" for clearer pickup/drop lists */
        function formatTransferLocationLabel(rowOrValue, fallbackLabel = '') {
            const row = (rowOrValue && typeof rowOrValue === 'object')
                ? rowOrValue
                : { value: rowOrValue, label: fallbackLabel || rowOrValue, type: transferTypeFromValue(rowOrValue) };
            const base = String(row?.label ?? '').trim();
            if (!base) return '';
            const suffixType = transferTypeFromRow(row);
            if (!suffixType) return base;
            const bracket = ` (${suffixType})`;
            if (base.toLowerCase().endsWith(bracket.toLowerCase())) {
                return base;
            }
            return base + bracket;
        }

        function mergeTransferLocationLabels(optionRows) {
            (Array.isArray(optionRows) ? optionRows : []).forEach((row) => {
                const value = String(row?.value ?? '').trim();
                const label = formatTransferLocationLabel(row);
                if (value && label) {
                    transferLocationLabelByValue[value] = label;
                }
            });
        }

        function labelFromAnyTransferSelect(value) {
            const v = String(value || '').trim();
            if (!v) return '';
            const selects = document.querySelectorAll(
                'select[id*="pickup_select"], select[id*="drop_select"], select[id*="transfer_pickup"], select[id*="transfer_drop"]'
            );
            for (const select of selects) {
                const option = Array.from(select.options || []).find(opt => String(opt.value) === v);
                if (option?.textContent) {
                    const text = option.textContent.trim();
                    if (text && text !== v) {
                        return text;
                    }
                }
            }
            return '';
        }

        function labelForStoredTransferLocation(value) {
            const v = String(value || '').trim();
            if (!v) return '';
            if (transferLocationLabelByValue[v]) {
                return transferLocationLabelByValue[v];
            }
            const fromDom = labelFromAnyTransferSelect(v);
            if (fromDom) return fromDom;
            const hit = [
                ...transferLocationOptions,
                ...transferArrivalPickupOptions,
                ...transferArrivalDropOptions,
                ...zoneTransferOptions,
            ].find((x) => String(x.value) === v);
            if (hit) return formatTransferLocationLabel(hit);
            if (v.startsWith('hotel:')) {
                const hotelId = v.replace(/^hotel:/, '');
                const hotelHit = (Array.isArray(hotels) ? hotels : []).find(h => String(h.hotel_id || h.id || '') === hotelId);
                if (hotelHit?.hotel_name) {
                    return formatTransferLocationLabel({ value: v, label: hotelHit.hotel_name, type: 'hotel' });
                }
                const flatHit = (Array.isArray(hotelsFlat) ? hotelsFlat : []).find(h => String(h.id || h.hotel_id || '') === hotelId);
                if (flatHit?.name || flatHit?.hotel_name) {
                    return formatTransferLocationLabel({
                        value: v,
                        label: flatHit.name || flatHit.hotel_name,
                        type: 'hotel',
                    });
                }
            }
            if (v.startsWith('attraction:')) {
                const attrId = v.replace(/^attraction:/, '');
                const attrHit = (Array.isArray(attractionsCache) ? attractionsCache : [])
                    .find(a => String(a.attraction_id || '') === attrId);
                if (attrHit?.name) {
                    return formatTransferLocationLabel({ value: v, label: attrHit.name, type: 'attraction' });
                }
            }
            if (v.startsWith('restaurant:')) {
                const restId = v.replace(/^restaurant:/, '');
                const restHit = (Array.isArray(restaurantsCache) ? restaurantsCache : [])
                    .find(r => String(r.restaurant_id || '') === restId);
                if (restHit?.name) {
                    return formatTransferLocationLabel({ value: v, label: restHit.name, type: 'restaurant' });
                }
            }
            if (v.startsWith('port:') || v.startsWith('zone:')) {
                return '';
            }
            return v;
        }

        function looksLikeTransferToken(value) {
            const v = String(value || '').trim().toLowerCase();
            return v.startsWith('port:')
                || v.startsWith('hotel:')
                || v.startsWith('attraction:')
                || v.startsWith('restaurant:')
                || v.startsWith('zone:');
        }

        function buildStoredTransferLocationFields(selectId, rawValue) {
            const value = String(rawValue || '').trim();
            if (!value) {
                return {
                    location: '',
                    location_value: '',
                    location_label: '',
                };
            }
            const label = getTransferLocationLabel(selectId, value)
                || labelForStoredTransferLocation(value)
                || value;
            return {
                location: label,
                location_value: value,
                location_label: label,
            };
        }

        function findTransferTokenByLabel(label) {
            const needle = String(label || '').trim().toLowerCase();
            if (!needle) return '';
            for (const [value, lbl] of Object.entries(transferLocationLabelByValue)) {
                const formatted = String(lbl || '').trim().toLowerCase();
                if (formatted === needle || formatted.includes(needle) || needle.includes(formatted)) {
                    return String(value);
                }
            }
            const pools = [
                transferLocationOptions,
                transferArrivalPickupOptions,
                transferArrivalDropOptions,
                zoneTransferOptions,
                serviceTransferOptions,
            ];
            for (const pool of pools) {
                const row = (Array.isArray(pool) ? pool : []).find((r) => {
                    const formatted = formatTransferLocationLabel(r).trim().toLowerCase();
                    return formatted === needle || formatted.includes(needle) || needle.includes(formatted);
                });
                if (row?.value) return String(row.value);
            }
            return '';
        }

        function getTransferLocationDisplay(transfer, field, dayNum, itemType = '') {
            if (!transfer || typeof transfer !== 'object') return '-';
            const transferType = String(transfer.transfer_type || '').trim();
            const selectId = resolveTransferSelectId(dayNum, field, transferType, itemType);
            const locKey = field === 'pickup' ? 'pickup_location' : 'drop_location';
            const labelKey = field === 'pickup' ? 'pickup_location_label' : 'drop_location_label';
            const valueKey = field === 'pickup' ? 'pickup_location_value' : 'drop_location_value';
            const idKey = field === 'pickup' ? 'pickup_location_id' : 'drop_location_id';
            const storedLabel = String(transfer[labelKey] || '').trim();
            const storedLoc = String(transfer[locKey] || '').trim();
            const storedValue = String(transfer[valueKey] || transfer[idKey] || '').trim();

            if (storedLabel && !looksLikeTransferToken(storedLabel)) return storedLabel;
            if (storedLoc && !looksLikeTransferToken(storedLoc)) return storedLoc;
            const token = storedValue || (looksLikeTransferToken(storedLoc) ? storedLoc : '');
            if (token) {
                return displayTransferLocation(token, selectId, storedLabel || storedLoc);
            }
            return '-';
        }

        function resolveTransferLocationForForm(transfer, field, selectId) {
            const valueKey = field === 'pickup' ? 'pickup_location_value' : 'drop_location_value';
            const idKey = field === 'pickup' ? 'pickup_location_id' : 'drop_location_id';
            const locKey = field === 'pickup' ? 'pickup_location' : 'drop_location';
            const labelKey = field === 'pickup' ? 'pickup_location_label' : 'drop_location_label';
            const storedValue = String(transfer?.[valueKey] || transfer?.[idKey] || '').trim();
            const storedLabel = String(transfer?.[labelKey] || '').trim();
            const storedLoc = String(transfer?.[locKey] || '').trim();

            if (storedValue && looksLikeTransferToken(storedValue)) {
                return {
                    value: storedValue,
                    label: storedLabel || labelForStoredTransferLocation(storedValue) || storedLoc || storedValue,
                };
            }
            if (storedLoc && looksLikeTransferToken(storedLoc)) {
                return {
                    value: storedLoc,
                    label: storedLabel || labelForStoredTransferLocation(storedLoc) || storedLoc,
                };
            }
            const label = storedLabel || (storedLoc && !looksLikeTransferToken(storedLoc) ? storedLoc : '');
            if (label) {
                const select = document.getElementById(selectId);
                const match = select
                    ? Array.from(select.options).find((opt) => {
                        const text = String(opt.textContent || '').trim();
                        return text === label || text.includes(label) || label.includes(text);
                    })
                    : null;
                if (match) {
                    return { value: String(match.value), label: String(match.textContent || '').trim() || label };
                }
                const token = findTransferTokenByLabel(label);
                if (token) {
                    return {
                        value: token,
                        label: labelForStoredTransferLocation(token) || label,
                    };
                }
                return { value: token, label };
            }
            return { value: '', label: '' };
        }

        function normalizeHydratedTransfer(rawT) {
            const t = rawT && typeof rawT === 'object' ? { ...rawT } : {};
            const pickupId = String(t.pickup_location_id || t.pickup_location_value || '').trim();
            const dropId = String(t.drop_location_id || t.drop_location_value || '').trim();
            const pickupLoc = String(t.pickup_location || '').trim();
            const dropLoc = String(t.drop_location || '').trim();

            if (pickupId && looksLikeTransferToken(pickupId)) {
                t.pickup_location_value = pickupId;
                t.pickup_location_label = String(t.pickup_location_label || '').trim()
                    || (looksLikeTransferToken(pickupLoc) ? '' : pickupLoc)
                    || labelForStoredTransferLocation(pickupId);
                t.pickup_location = t.pickup_location_label || pickupLoc;
            } else if (pickupLoc && looksLikeTransferToken(pickupLoc)) {
                t.pickup_location_value = pickupLoc;
                t.pickup_location_label = String(t.pickup_location_label || '').trim()
                    || labelForStoredTransferLocation(pickupLoc);
                t.pickup_location = t.pickup_location_label || pickupLoc;
            } else if (pickupLoc) {
                t.pickup_location_label = String(t.pickup_location_label || pickupLoc);
                t.pickup_location = t.pickup_location_label;
            }

            if (dropId && looksLikeTransferToken(dropId)) {
                t.drop_location_value = dropId;
                t.drop_location_label = String(t.drop_location_label || '').trim()
                    || (looksLikeTransferToken(dropLoc) ? '' : dropLoc)
                    || labelForStoredTransferLocation(dropId);
                t.drop_location = t.drop_location_label || dropLoc;
            } else if (dropLoc && looksLikeTransferToken(dropLoc)) {
                t.drop_location_value = dropLoc;
                t.drop_location_label = String(t.drop_location_label || '').trim()
                    || labelForStoredTransferLocation(dropLoc);
                t.drop_location = t.drop_location_label || dropLoc;
            } else if (dropLoc) {
                t.drop_location_label = String(t.drop_location_label || dropLoc);
                t.drop_location = t.drop_location_label;
            }
            return t;
        }

        function exportTransferForJson(transfer, dayNum, itemType = '') {
            if (!transfer || typeof transfer !== 'object') return {};
            const t = { ...transfer };
            const transferType = String(t.transfer_type || '').trim();
            const pickupSid = resolveTransferSelectId(dayNum, 'pickup', transferType, itemType);
            const dropSid = resolveTransferSelectId(dayNum, 'drop', transferType, itemType);

            const pickupVal = String(
                t.pickup_location_value
                || (looksLikeTransferToken(t.pickup_location) ? t.pickup_location : '')
                || t.pickup_location_id
                || ''
            ).trim();
            const dropVal = String(
                t.drop_location_value
                || (looksLikeTransferToken(t.drop_location) ? t.drop_location : '')
                || t.drop_location_id
                || ''
            ).trim();

            const pickupName = String(t.pickup_location_label || '').trim()
                || (pickupVal ? getTransferLocationLabel(pickupSid, pickupVal) : '')
                || (!looksLikeTransferToken(t.pickup_location) ? String(t.pickup_location || '').trim() : '')
                || pickupVal;
            const dropName = String(t.drop_location_label || '').trim()
                || (dropVal ? getTransferLocationLabel(dropSid, dropVal) : '')
                || (!looksLikeTransferToken(t.drop_location) ? String(t.drop_location || '').trim() : '')
                || dropVal;

            const out = { ...t };
            out.pickup_location = pickupName;
            out.drop_location = dropName;
            if (pickupVal) out.pickup_location_id = pickupVal;
            if (dropVal) out.drop_location_id = dropVal;
            delete out.pickup_location_value;
            delete out.drop_location_value;
            delete out.pickup_location_label;
            delete out.drop_location_label;

            if (Array.isArray(out.additional_transfers)) {
                out.additional_transfers = out.additional_transfers.map((row) => {
                    const pickupToken = String(
                        row?.pickup_location_value
                        || (looksLikeTransferToken(row?.pickup_location) ? row.pickup_location : '')
                        || row?.pickup_location_id
                        || ''
                    ).trim();
                    const dropToken = String(
                        row?.drop_location_value
                        || (looksLikeTransferToken(row?.drop_location) ? row.drop_location : '')
                        || row?.drop_location_id
                        || ''
                    ).trim();
                    return {
                        city: String(row?.city || ''),
                        pickup_location: String(row?.pickup_location_label || row?.pickup_location || '').trim()
                            || (pickupToken ? labelForStoredTransferLocation(pickupToken) : ''),
                        drop_location: String(row?.drop_location_label || row?.drop_location || '').trim()
                            || (dropToken ? labelForStoredTransferLocation(dropToken) : ''),
                        pickup_location_id: pickupToken,
                        drop_location_id: dropToken,
                    };
                });
            }
            return out;
        }

        function exportHotelLocationForJson(rawValue, fallbackLabel = '') {
            const value = String(rawValue || '').trim();
            if (!value) return '';
            if (!looksLikeTransferToken(value)) return value;
            return String(fallbackLabel || '').trim() || labelForStoredTransferLocation(value) || value;
        }

        function labelForSelectValue(selectId, value) {
            const v = String(value || '').trim();
            if (!v) return '';
            const select = document.getElementById(selectId);
            const option = select ? Array.from(select.options).find(opt => String(opt.value) === v) : null;
            return option?.textContent ? option.textContent.trim() : '';
        }

        function resolveTransferSelectId(dayVal, field, transferType, itemType = '') {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const type = String(transferType || '').trim().toLowerCase();
            const serviceType = String(itemType || '').trim().toLowerCase();
            const isPickup = field === 'pickup';
            if (type === 'arrival') {
                return isPickup ? `arrival_pickup_select_${d}` : `arrival_drop_select_${d}`;
            }
            if (type === 'departure') {
                return isPickup ? `departure_pickup_select_${d}` : `departure_drop_select_${d}`;
            }
            if (type === 'restaurant transfer' || serviceType === 'restaurant') {
                return isPickup ? `restaurant_transfer_pickup_select_${d}` : `restaurant_transfer_drop_select_${d}`;
            }
            return isPickup ? `attraction_transfer_pickup_select_${d}` : `attraction_transfer_drop_select_${d}`;
        }

        function getTransferLocationLabel(selectId, value) {
            const v = String(value || '').trim();
            if (!v) return '';
            return labelForSelectValue(selectId, v) || labelForStoredTransferLocation(v);
        }

        function displayTransferLocation(value, selectId = '', storedLabel = '') {
            const v = String(value || '').trim();
            if (!v) return '-';
            const saved = String(storedLabel || '').trim();
            if (saved && saved !== v && !looksLikeTransferToken(saved)) {
                return saved;
            }
            if (!looksLikeTransferToken(v)) {
                return saved || v;
            }
            return labelForSelectValue(selectId, v) || labelForStoredTransferLocation(v) || saved || v;
        }

        function actionIcon(type) {
            if (type === 'edit') {
                return '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>';
            }
            return '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>';
        }

        function getTransferOptionsForDay(dayVal) {
            return isMiddleTransferDay(dayVal) ? zoneTransferOptions : transferLocationOptions;
        }

        /** Departure drop + arrival pickup: Master DMC country ports only. */
        function getDepartureDropPortOptions() {
            return Array.isArray(transferArrivalPickupOptions) ? transferArrivalPickupOptions : [];
        }

        function resolveDefaultPortValue() {
            return String(transferDefaults.defaultPort || '').trim();
        }

        function enforceDepartureDropPort(dayVal, onlyIfEmpty = true) {
            const portVal = resolveDefaultPortValue();
            if (!portVal) return;
            const selectId = `departure_drop_select_${dayVal}`;
            const el = document.getElementById(selectId);
            if (onlyIfEmpty && el && String(el.value || '').trim()) {
                return;
            }
            const resolvedPortVal = ensureTransferLocationOption(selectId, portVal, labelForStoredTransferLocation(portVal));
            safeSetSelectValue(selectId, resolvedPortVal);
        }

        /** Middle itinerary days always show transfer (pickup/drop for attractions etc.), unrelated to hotel arrival checkbox. */
        function isMiddleTransferDay(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            return daysCount >= 3 && d > 1 && d < daysCount;
        }

        function itineraryUsesMiddleDayTransfers() {
            return daysCount >= 3;
        }

        function transferSectionActiveForDay(dayVal) {
            return true;
        }

        /** Clear pickup/drop for arrival / departure legs only — keep middle-day attraction transfers. */
        function clearEndDayTransferSelectionsOnly() {
            const targets = new Set([1]);
            if (daysCount >= 2) targets.add(daysCount);
            targets.forEach((d) => {
                safeSetSelectValue(`attraction_transfer_pickup_select_${d}`, '');
                safeSetSelectValue(`attraction_transfer_drop_select_${d}`, '');
                safeSetSelectValue(`restaurant_transfer_pickup_select_${d}`, '');
                safeSetSelectValue(`restaurant_transfer_drop_select_${d}`, '');
                safeSetSelectValueSilent(`transfer_city_select_${d}`, '');
            });
        }

        function initSearchableSelects(scope = document) {
            const $scope = $(scope);
            const $targets = $scope.hasClass('searchable-select') ? $scope : $scope.find('.searchable-select');
            $targets.each(function () {
                const $el = $(this);
                if ($el.data('select2')) return;
                $el.select2({ width: '100%', placeholder: 'Search and select', allowClear: true });
            });
        }

        /** Same hotel can arrive as hotel:<db id> (server lists) and hotel:<unique id> (booked rows). */
        function hotelUniqueIdFromAnyHotelId(anyId) {
            const id = String(anyId || '').trim();
            if (!id) return '';
            const hit = (Array.isArray(hotelsFlat) ? hotelsFlat : []).find(h =>
                String(h.hotel_unique_id || '').trim() === id || String(h.id || '').trim() === id);
            if (hit) return String(hit.hotel_unique_id || hit.id || id).trim();
            return id;
        }

        function canonicalTransferOptionValue(value) {
            const v = String(value || '').trim();
            if (!v.toLowerCase().startsWith('hotel:')) return v;
            return `hotel:${hotelUniqueIdFromAnyHotelId(v.slice(6))}`;
        }

        function normalizeTransferLabelKey(label) {
            return String(label || '').replace(/\s+/g, ' ').trim().toLowerCase();
        }

        function mapTransferSelectOptions(rows) {
            const out = [];
            const seenValues = new Set();
            const seenLabels = new Set();
            (Array.isArray(rows) ? rows : []).forEach((x) => {
                const value = String(x?.value ?? '').trim();
                if (!value) return;
                const canonical = canonicalTransferOptionValue(value);
                const label = formatTransferLocationLabel(x);
                const labelKey = normalizeTransferLabelKey(label);
                if (seenValues.has(canonical)) return;
                if (labelKey && seenLabels.has(labelKey)) return;
                seenValues.add(canonical);
                if (labelKey) seenLabels.add(labelKey);
                out.push({ value, label });
            });
            return out;
        }

        function getHotelsOnDayAsTransferOptions(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const seen = new Set();
            const rows = [];
            (Array.isArray(hotels) ? hotels : []).forEach((h) => {
                const dayIn = parseInt(String(h.day ?? 1), 10) || 1;
                const dayOut = hotelStayEndDay(h);
                if (d < dayIn || d > dayOut) return;
                const hotelId = String(h.hotel_id ?? h.id ?? '').trim();
                if (!hotelId || seen.has(`hotel:${hotelId}`)) return;
                seen.add(`hotel:${hotelId}`);
                rows.push({
                    value: `hotel:${hotelId}`,
                    label: String(h.hotel_name ?? h.label ?? `Hotel ${hotelId}`),
                    type: 'hotel',
                    id: hotelId,
                });
            });
            return rows;
        }

        function getServiceTransferOptionsForDay(dayVal) {
            const base = (serviceTransferOptions.length
                ? serviceTransferOptions
                : (transferArrivalDropOptions.length
                    ? transferArrivalDropOptions.filter((x) => String(x?.type || '').toLowerCase() !== 'port')
                    : transferLocationOptions));
            const merged = [];
            const seenValues = new Set();
            const seenLabels = new Set();
            [...base, ...getHotelsOnDayAsTransferOptions(dayVal)].forEach((row) => {
                const value = String(row?.value ?? '').trim();
                if (!value) return;
                const canonical = canonicalTransferOptionValue(value);
                const labelKey = normalizeTransferLabelKey(formatTransferLocationLabel(row));
                if (seenValues.has(canonical)) return;
                if (labelKey && seenLabels.has(labelKey)) return;
                seenValues.add(canonical);
                if (labelKey) seenLabels.add(labelKey);
                merged.push(row);
            });
            mergeTransferLocationLabels(merged);
            return merged;
        }

        function populateServiceTransferSelectsForDay(dayVal, silent = true) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const opts = mapTransferSelectOptions(getServiceTransferOptionsForDay(d));
            setSelectOptions(`attraction_transfer_pickup_select_${d}`, opts, silent);
            setSelectOptions(`attraction_transfer_drop_select_${d}`, opts, silent);
            setSelectOptions(`restaurant_transfer_pickup_select_${d}`, opts, silent);
            setSelectOptions(`restaurant_transfer_drop_select_${d}`, opts, silent);
        }

        function safeSetSelectValueSilent(selectId, value) {
            const el = document.getElementById(selectId);
            if (!el) return;
            const next = value == null ? '' : String(value);
            const $el = $('#' + selectId);
            $el.val(next);
            if ($el.data('select2')) {
                $el.trigger('change.select2');
            }
        }

        function syncTransferCityFromActivity(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const actVal = document.getElementById(`activity_city_select_${d}`)?.value || '';
            if (!actVal) return;
            isSyncingTransferCity = true;
            safeSetSelectValueSilent(`transfer_city_select_${d}`, actVal);
            isSyncingTransferCity = false;
        }

        function applyAttractionTransferDefaults(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const attractionOp = getSelectedOption(`attraction_select_${d}`);
            if (attractionOp?.value) {
                const dropVal = ensureTransferLocationOption(
                    `attraction_transfer_drop_select_${d}`,
                    `attraction:${attractionOp.value}`,
                    formatTransferLocationLabel({ value: `attraction:${attractionOp.value}`, label: attractionOp.textContent, type: 'attraction' })
                );
                safeSetSelectValue(`attraction_transfer_drop_select_${d}`, dropVal);
            }
            const hotel = getArrivalHotelForDay(d) || getDepartureHotelForDay(d);
            const pickupEl = document.getElementById(`attraction_transfer_pickup_select_${d}`);
            if (hotel?.value && pickupEl && !String(pickupEl.value || '').trim()) {
                const pickupVal = ensureTransferLocationOption(`attraction_transfer_pickup_select_${d}`, hotel.value, hotel.label || 'Hotel');
                safeSetSelectValue(`attraction_transfer_pickup_select_${d}`, pickupVal);
            }
        }

        function applyRestaurantTransferDefaults(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const restaurantOp = getSelectedOption(`restaurant_select_${d}`);
            if (restaurantOp?.value) {
                const dropVal = ensureTransferLocationOption(
                    `restaurant_transfer_drop_select_${d}`,
                    `restaurant:${restaurantOp.value}`,
                    formatTransferLocationLabel({ value: `restaurant:${restaurantOp.value}`, label: restaurantOp.textContent, type: 'restaurant' })
                );
                safeSetSelectValue(`restaurant_transfer_drop_select_${d}`, dropVal);
            }
            const hotel = getArrivalHotelForDay(d) || getDepartureHotelForDay(d);
            const pickupEl = document.getElementById(`restaurant_transfer_pickup_select_${d}`);
            if (hotel?.value && pickupEl && !String(pickupEl.value || '').trim()) {
                const pickupVal = ensureTransferLocationOption(`restaurant_transfer_pickup_select_${d}`, hotel.value, hotel.label || 'Hotel');
                safeSetSelectValue(`restaurant_transfer_pickup_select_${d}`, pickupVal);
            }
        }

        function setSelectOptions(selectId, options, silent = false) {
            const select = document.getElementById(selectId);
            if (!select) return;
            const $select = $(select);
            if ($select.data('select2')) {
                $select.select2('destroy');
            }
            select.innerHTML = '<option value=""></option>';
            options.forEach((opt) => {
                const op = document.createElement('option');
                op.value = String(opt.value ?? '');
                op.textContent = opt.label ?? '';
                if (opt.price !== undefined) op.dataset.price = String(opt.price);
                if (opt.adult_price !== undefined) op.dataset.adultPrice = String(opt.adult_price);
                if (opt.child_price !== undefined) op.dataset.childPrice = String(opt.child_price);
                if (opt.senior_price !== undefined) op.dataset.seniorPrice = String(opt.senior_price);
                if (opt.double_weekday_price !== undefined) op.dataset.doubleWeekdayPrice = String(opt.double_weekday_price);
                if (opt.breakfast_price !== undefined) op.dataset.breakfastPrice = String(opt.breakfast_price);
                if (opt.lunch_price !== undefined) op.dataset.lunchPrice = String(opt.lunch_price);
                if (opt.dinner_price !== undefined) op.dataset.dinnerPrice = String(opt.dinner_price);
                if (opt.rate !== undefined) op.dataset.rate = String(opt.rate);
                if (opt.data_name !== undefined) op.dataset.name = String(opt.data_name);
                if (opt.data_country !== undefined) op.dataset.country = String(opt.data_country);
                if (opt.data_day_in !== undefined) op.dataset.dayIn = String(opt.data_day_in);
                if (opt.data_day_out !== undefined) op.dataset.dayOut = String(opt.data_day_out);
                if (opt.data_meal_period_label !== undefined) op.dataset.mealPeriodLabel = String(opt.data_meal_period_label);
                if (opt.data_type_label !== undefined) op.dataset.typeLabel = String(opt.data_type_label);
                if (opt.data_meal_name !== undefined) op.dataset.mealName = String(opt.data_meal_name);
                if (opt.data_meal_period !== undefined) op.dataset.mealPeriod = String(opt.data_meal_period);
                select.appendChild(op);
            });
            initSearchableSelects(select);
            if (!silent) {
                $(select).trigger('change.select2');
            }
        }

        function safeSetSelectValue(selectId, value) {
            const el = document.getElementById(selectId);
            if (!el) return;
            const $el = $('#' + selectId);
            $el.val(value == null ? '' : String(value));
            // Trigger plain change; select2 listens to this once initialized.
            $el.trigger('change');
        }

        function safeSetSelectValues(selectId, values) {
            const el = document.getElementById(selectId);
            if (!el) return;
            const $el = $('#' + selectId);
            $el.val(Array.isArray(values) ? values.map(v => String(v)) : []);
            $el.trigger('change');
        }

        function getSelectedValues(selectId) {
            const el = document.getElementById(selectId);
            if (!el) return [];
            return Array.from(el.selectedOptions || []).map(o => String(o.value)).filter(Boolean);
        }

        function cacheAllCities() {
            const citySelect = document.getElementById('city_id');
            allCities = Array.from(citySelect.options)
                .filter(opt => opt.value)
                .map(opt => ({
                    value: opt.value,
                    name: opt.dataset.name || opt.textContent || '',
                    country: opt.dataset.country || '',
                }));
        }

        function applyCountryFilter() {
            const selectedCountry = String(document.getElementById('country').value || '').trim();
            const filteredByCountry = allCities.filter(c => cityMatchesCountry(c, selectedCountry));
            const filtered = filteredByCountry.length ? filteredByCountry : allCities;
            const prevCityId = $('#city_id').val();
            setSelectOptions('city_id', filtered.map(c => ({
                value: c.value,
                label: c.country ? `${c.name}, ${c.country}` : c.name,
                data_name: c.name,
                data_country: c.country,
            })));
            if (prevCityId && filtered.some(c => String(c.value) === String(prevCityId))) {
                safeSetSelectValue('city_id', prevCityId);
            } else {
                $('#city_id').val('').trigger('change');
            }
            setSectionCityOptions();
        }

        function syncCountryFromCityOrDefault(preferredCountry = '') {
            const hiddenCountry = document.getElementById('country');
            if (!hiddenCountry) return;

            const dmcOp = getSelectedOption('dmc_id');
            const dmcCountry = String(dmcOp?.dataset?.country || '').trim();
            const cityOp = getSelectedOption('city_id');
            const cityCountry = String(cityOp?.dataset?.country || '').trim();
            const fallbackCountry = String(allCities.find(c => !!c.country)?.country || '').trim();
            hiddenCountry.value = String(preferredCountry || dmcCountry || cityCountry || fallbackCountry || '').trim();
        }

        function getCityNameFromSelect(selectId) {
            const op = getSelectedOption(selectId);
            if (!op) return '';
            return String(op.dataset.name || op.textContent || '').split(',')[0].trim();
        }

        function cityMatchesCountry(city, country) {
            const wanted = String(country || '').trim().toLowerCase();
            if (!wanted) return true;
            return String(city?.country || '').trim().toLowerCase() === wanted;
        }

        function normalizeCityNameKey(cityName) {
            return String(cityName || '').split(',')[0].trim().toLowerCase();
        }

        function findCityRecordByName(cityName) {
            const key = normalizeCityNameKey(cityName);
            if (!key) return null;
            return allCities.find(c => normalizeCityNameKey(c.name) === key) || null;
        }

        function ensureCityOptionInSelect(selectId, cityId) {
            const cityIdStr = String(cityId || '').trim();
            if (!cityIdStr) return;
            const sel = document.getElementById(selectId);
            if (!sel) return;
            if (Array.from(sel.options).some(o => String(o.value) === cityIdStr)) return;
            const rec = allCities.find(c => String(c.value) === cityIdStr) || null;
            if (!rec) return;
            const op = document.createElement('option');
            op.value = String(rec.value);
            op.textContent = rec.country ? `${rec.name}, ${rec.country}` : rec.name;
            op.dataset.name = rec.name;
            op.dataset.country = rec.country || '';
            sel.appendChild(op);
            const $sel = $(sel);
            if ($sel.data('select2')) {
                $sel.trigger('change.select2');
            }
        }

        function resolveCityIdForDay(dayNum) {
            const d = parseInt(String(dayNum || 0), 10) || 0;
            if (d < 1) return '';
            const planCityId = resolveCityIdForPlan(getMultiCityPlanForDay(d));
            if (planCityId) return planCityId;

            const fromItems = (Array.isArray(dayItems) ? dayItems : []).find(
                x => (parseInt(String(x?.day || 0), 10) || 0) === d && String(x?.city_name || '').trim()
            );
            const fromHotels = (Array.isArray(hotels) ? hotels : []).find(
                h => hotelCoversDay(h, d) && String(h?.city_name || '').trim()
            );
            const cityName = String(
                fromItems?.city_name || fromHotels?.city_name || activityCityByDay[d] || ''
            ).trim();
            const hit = findCityRecordByName(cityName);
            return hit ? String(hit.value) : '';
        }

        function syncDayCitySelectorsFromStoredData(force = false) {
            isSyncingCitySelectors = true;
            for (let d = 1; d <= daysCount; d++) {
                const cityId = resolveCityIdForDay(d);
                if (!cityId) continue;
                const act = document.getElementById(`activity_city_select_${d}`);
                if (act && (force || !String(act.value || '').trim())) {
                    ensureCityOptionInSelect(`activity_city_select_${d}`, cityId);
                    safeSetSelectValueSilent(`activity_city_select_${d}`, cityId);
                    activityCityByDay[d] = getCityNameFromSelect(`activity_city_select_${d}`);
                }
            }
            isSyncingCitySelectors = false;
        }

        function getSectionCityOptions() {
            const multiCityFiltered = getDayCityOptionsFromMultiCity();
            const selectedCountry = document.getElementById('country').value || '';
            const source = (multiCityFiltered && multiCityFiltered.length)
                ? multiCityFiltered
                : allCities.filter(c => cityMatchesCountry(c, selectedCountry));
            return source.map(c => ({
                value: c.value,
                label: c.country ? `${c.name}, ${c.country}` : c.name,
                data_name: c.name,
                data_country: c.country,
            }));
        }

        function syncHotelCityFromMultiCity(options = null) {
            if (!multiCityPlans.length) return false;
            const opts = options || getSectionCityOptions();
            const current = String($('#hotel_city_select').val() || '').trim();
            if (current && opts.some(o => String(o.value) === current)) {
                return false;
            }
            const sorted = [...multiCityPlans].sort((a, b) =>
                (parseInt(String(a?.day_in || 0), 10) || 0) - (parseInt(String(b?.day_in || 0), 10) || 0)
            );
            for (const plan of sorted) {
                const cityId = resolveCityIdForPlan(plan);
                if (!cityId || !opts.some(o => String(o.value) === cityId)) continue;
                safeSetSelectValueSilent('hotel_city_select', cityId);
                syncHotelDayDropdownWithMultiCity();
                if (!isHydratingDayServices && !isPrefillingHotelForm) {
                    loadHotelCityServices();
                }
                return true;
            }
            return false;
        }

        function setSectionCityOptions() {
            const prevHotelCity = $('#hotel_city_select').val();
            const options = getSectionCityOptions();
            isSyncingCitySelectors = true;
            setSelectOptions('hotel_city_select', options, true);

            if (prevHotelCity && options.some(o => String(o.value) === String(prevHotelCity))) {
                safeSetSelectValueSilent('hotel_city_select', prevHotelCity);
            } else {
                syncHotelCityFromMultiCity(options);
            }
            isSyncingCitySelectors = false;
            hydrateDayServiceBlocksOptions();
        }

        function syncSectionCitySelectionsFromMain() {
            if (isSyncingCitySelectors) return;
            if (multiCityPlans.length) {
                setSectionCityOptions();
                syncDayCitySelectorsFromMultiCity();
                scheduleTransferOptionsReload(false);
                return;
            }
            isSyncingCitySelectors = true;
            const cityOp = getSelectedOption('city_id');
            if (!cityOp) {
                isSyncingCitySelectors = false;
                return;
            }
            safeSetSelectValueSilent('hotel_city_select', cityOp.value);
            for (let d = 1; d <= daysCount; d++) {
                safeSetSelectValueSilent(`activity_city_select_${d}`, cityOp.value);
                activityCityByDay[d] = getCityNameFromSelect(`activity_city_select_${d}`);
            }
            isSyncingCitySelectors = false;
            scheduleTransferOptionsReload(false);
        }

        function syncMainCityFromSection(sectionSelectId) {
            if (isSyncingCitySelectors) return;
            // Multi City picker (#city_id) is independent once plans exist.
            if (multiCityPlans.length > 0 && sectionSelectId === 'hotel_city_select') {
                return;
            }
            const op = getSelectedOption(sectionSelectId);
            if (!op) return;
            isSyncingCitySelectors = true;
            $('#city_id').val(String(op.value)).trigger('change');
            isSyncingCitySelectors = false;
        }

        function getNextMultiCityDaySpan() {
            if (!multiCityPlans.length) {
                return { day_in: 1, day_out: daysCount };
            }
            const lastDayOut = multiCityPlans.reduce((max, plan) => {
                const dout = parseInt(String(plan?.day_out || 0), 10) || 0;
                return Math.max(max, dout);
            }, 0);
            const nextIn = Math.min(daysCount, Math.max(1, lastDayOut));
            const nextOut = Math.min(daysCount, Math.max(nextIn, lastDayOut + 1));
            return { day_in: nextIn, day_out: nextOut };
        }

        function resetMultiCityPickerFields() {
            const $city = $('#city_id');
            if ($city.data('select2')) {
                $city.val(null).trigger('change.select2');
            } else {
                safeSetSelectValue('city_id', '');
            }
            const nextSpan = getNextMultiCityDaySpan();
            safeSetSelectValue('mc_day_in', String(nextSpan.day_in));
            safeSetSelectValue('mc_day_out', String(nextSpan.day_out));
        }

        function multiCityDayRangesOverlap(dayInA, dayOutA, dayInB, dayOutB) {
            const aIn = parseInt(String(dayInA || 0), 10);
            const aOut = parseInt(String(dayOutA || 0), 10);
            const bIn = parseInt(String(dayInB || 0), 10);
            const bOut = parseInt(String(dayOutB || 0), 10);
            if (!Number.isFinite(aIn) || !Number.isFinite(aOut) || !Number.isFinite(bIn) || !Number.isFinite(bOut)) {
                return false;
            }
            if (!(aIn <= bOut && aOut >= bIn)) return false;
            // Consecutive cities may share one boundary day (e.g. City A Day 1–3, City B Day 3–6).
            if (aOut === bIn || bOut === aIn) return false;
            return true;
        }

        function multiCityPlansMatchCity(planA, planB) {
            const idA = String(planA?.city_id || '').trim();
            const idB = String(planB?.city_id || '').trim();
            if (idA && idB && idA === idB) return true;
            return normalizeCityNameKey(planA?.city_name) === normalizeCityNameKey(planB?.city_name);
        }

        /** Returns { type: 'duplicate'|'overlap', existing } or null when the plan is allowed. */
        function findMultiCityPlanConflict(payload, excludeIndex = null) {
            const dayIn = parseInt(String(payload?.day_in || 0), 10);
            const dayOut = parseInt(String(payload?.day_out || 0), 10);
            for (let i = 0; i < multiCityPlans.length; i++) {
                if (excludeIndex !== null && i === excludeIndex) continue;
                const existing = multiCityPlans[i];
                const existingIn = parseInt(String(existing?.day_in || 0), 10);
                const existingOut = parseInt(String(existing?.day_out || 0), 10);
                if (multiCityPlansMatchCity(payload, existing)
                    && existingIn === dayIn
                    && existingOut === dayOut) {
                    return { type: 'duplicate', existing };
                }
                if (multiCityDayRangesOverlap(dayIn, dayOut, existingIn, existingOut)) {
                    return { type: 'overlap', existing };
                }
            }
            return null;
        }

        function addMultiCityPlan() {
            const cityOp = getSelectedOption('city_id');
            if (!cityOp) {
                alert('Select city first.');
                return;
            }
            const dayIn = parseInt(document.getElementById('mc_day_in').value || '1', 10);
            const dayOut = parseInt(document.getElementById('mc_day_out').value || String(dayIn), 10);
            if (!Number.isFinite(dayIn) || !Number.isFinite(dayOut) || dayIn < 1 || dayOut < dayIn) {
                alert('Please enter valid day check-in/check-out values.');
                return;
            }
            if (dayOut > daysCount) {
                alert(`Day check-out cannot exceed total package days (Day ${daysCount}).`);
                return;
            }
            const payload = {
                city_id: cityOp.value,
                city_name: cityOp.dataset.name || cityOp.textContent || '',
                day_in: dayIn,
                day_out: dayOut
            };
            const excludeIndex = editingMultiCityIndex !== null ? editingMultiCityIndex : null;
            const conflict = findMultiCityPlanConflict(payload, excludeIndex);
            if (conflict) {
                if (conflict.type === 'duplicate') {
                    alert(`This city is already added for Day ${dayIn} to Day ${dayOut}.`);
                } else {
                    const ex = conflict.existing;
                    alert(`Day ${dayIn} to Day ${dayOut} is already assigned to ${ex.city_name} (Day ${ex.day_in} to Day ${ex.day_out}). Please choose a different day range or city.`);
                }
                return;
            }
            if (editingMultiCityIndex !== null && multiCityPlans[editingMultiCityIndex]) {
                multiCityPlans[editingMultiCityIndex] = payload;
                editingMultiCityIndex = null;
                const btn = document.getElementById('multiCityAddBtn');
                if (btn) btn.textContent = 'Add';
            } else {
                multiCityPlans.push(payload);
            }
            renderMultiCityRows();
            setSectionCityOptions();
            resetMultiCityPickerFields();
            updateAllDayTransferVisibility();
            invalidateTransferOptionsCache();
            scheduleTransferOptionsReload(true);
            applyTransferDefaults();
            // Hotels (day + nights) depend solely on Multi City.
            syncHotelsWithMultiCity();
        }

        function removeMultiCityPlan(idx) {
            multiCityPlans.splice(idx, 1);
            if (editingMultiCityIndex === idx) {
                editingMultiCityIndex = null;
                const btn = document.getElementById('multiCityAddBtn');
                if (btn) btn.textContent = 'Add';
            }
            renderMultiCityRows();
            setSectionCityOptions();
            if (!multiCityPlans.length) {
                resetMultiCityPickerFields();
            }
            updateAllDayTransferVisibility();
            invalidateTransferOptionsCache();
            scheduleTransferOptionsReload(true);
            applyTransferDefaults();
            // Hotels (day + nights) depend solely on Multi City.
            syncHotelsWithMultiCity();
        }

        function editMultiCityPlan(idx) {
            const row = multiCityPlans[idx];
            if (!row) return;
            editingMultiCityIndex = idx;
            const citySelect = document.getElementById('city_id');
            const match = Array.from(citySelect.options).find(opt => {
                const nm = String(opt.dataset.name || opt.textContent || '').trim().toLowerCase();
                return nm === String(row.city_name || '').trim().toLowerCase();
            });
            if (match) {
                $('#city_id').val(String(match.value)).trigger('change');
            }
            document.getElementById('mc_day_in').value = String(row.day_in || 1);
            document.getElementById('mc_day_out').value = String(row.day_out || row.day_in || 1);
            const btn = document.getElementById('multiCityAddBtn');
            if (btn) btn.textContent = 'Update';
        }

        function renderMultiCityRows() {
            const body = document.getElementById('multiCityRows');
            if (!body) return;
            if (!multiCityPlans.length) {
                body.innerHTML = '<tr><td colspan="4" class="text-muted">No city plan added</td></tr>';
                return;
            }

            body.innerHTML = multiCityPlans.map((row, idx) => `
                <tr>
                    <td>${escapeHtml(row.city_name)}</td>
                    <td>Day ${row.day_in}</td>
                    <td>Day ${row.day_out}</td>
                    <td class="action-cell">
                        <span class="action-buttons">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeMultiCityPlan(${idx})" title="Remove" aria-label="Remove">${actionIcon('remove')}</button>
                        </span>
                    </td>
                </tr>
            `).join('');
        }

        function autoLoadBySelection() {
            syncCountryFromCityOrDefault();
            if (getSelectedOption('city_id')) {
                loadCityData();
            }
        }

        function initDays() {
            daysCount = Math.max(1, parseInt(document.getElementById('days').value || '1', 10));
            // No day-tabs in the UI anymore; itinerary day mapping is controlled by Multi City.
            activeDay = 1;

            const dayOptions = Array.from({ length: daysCount }, (_, i) => ({ value: i + 1, label: 'Day ' + (i + 1) }));
            setSelectOptions('mc_day_in', dayOptions);
            setSelectOptions('mc_day_out', dayOptions);
            safeSetSelectValue('mc_day_in', 1);
            safeSetSelectValue('mc_day_out', daysCount);
            renderDayServiceBlocks();
            // Hotels nights/day will be re-synced from multiCityPlans after Multi City changes.
            syncHotelNightsWithMultiCity();
        }

        function getMultiCityPlanForCity(cityName) {
            const key = String(cityName || '')
                .split(',')[0]
                .trim()
                .toLowerCase();
            if (!key) return null;
            return multiCityPlans.find(p => String(p?.city_name || '').split(',')[0].trim().toLowerCase() === key) || null;
        }

        function getMultiCityPlansForCity(cityName) {
            const key = String(cityName || '')
                .split(',')[0]
                .trim()
                .toLowerCase();
            if (!key) return [];
            return multiCityPlans
                .filter(p => String(p?.city_name || '').split(',')[0].trim().toLowerCase() === key)
                .sort((a, b) => (parseInt(String(a?.day_in || 0), 10) || 0) - (parseInt(String(b?.day_in || 0), 10) || 0));
        }

        function getMultiCityPlanForDay(dayVal) {
            const d = parseInt(String(dayVal || 0), 10) || 0;
            if (d < 1) return null;
            const candidates = multiCityPlans.filter(p => {
                const din = parseInt(String(p?.day_in || 0), 10) || 0;
                const dout = parseInt(String(p?.day_out || 0), 10) || 0;
                return din > 0 && dout > 0 && d >= din && d <= dout;
            });
            // On boundary days (prev checkout day == next check-in day), pick the later plan.
            candidates.sort((a, b) => (parseInt(String(b?.day_in || 0), 10) || 0) - (parseInt(String(a?.day_in || 0), 10) || 0));
            return candidates[0] || null;
        }

        function syncDayCitySelectorsFromMultiCity(force = false) {
            if (!multiCityPlans.length && !force) {
                return;
            }
            isSyncingCitySelectors = true;
            for (let d = 1; d <= daysCount; d++) {
                const cityId = resolveCityIdForDay(d);
                if (!cityId) continue;
                const act = document.getElementById(`activity_city_select_${d}`);
                const tr = document.getElementById(`transfer_city_select_${d}`);
                if (act && (force || !String(act.value || '').trim())) {
                    ensureCityOptionInSelect(`activity_city_select_${d}`, cityId);
                    safeSetSelectValueSilent(`activity_city_select_${d}`, cityId);
                    activityCityByDay[d] = getCityNameFromSelect(`activity_city_select_${d}`);
                }
                if (tr && (force || !String(tr.value || '').trim())) {
                    isSyncingTransferCity = true;
                    ensureCityOptionInSelect(`transfer_city_select_${d}`, cityId);
                    safeSetSelectValueSilent(`transfer_city_select_${d}`, cityId);
                    isSyncingTransferCity = false;
                }
            }
            isSyncingCitySelectors = false;
        }

        function getDayCityOptionsFromMultiCity() {
            if (!multiCityPlans.length) return null;
            const selectedCountry = document.getElementById('country').value || '';
            const filteredByCountry = allCities.filter(c => cityMatchesCountry(c, selectedCountry));
            const allowedIds = new Set(
                multiCityPlans
                    .map(p => String(resolveCityIdForPlan(p) || p?.city_id || '').trim())
                    .filter(Boolean)
            );
            const allowedNames = new Set(
                multiCityPlans
                    .map(p => normalizeCityNameKey(p?.city_name))
                    .filter(Boolean)
            );
            const matchPlanCity = (c) => {
                if (allowedIds.has(String(c.value))) return true;
                return allowedNames.has(normalizeCityNameKey(c.name));
            };
            let filtered = filteredByCountry.filter(matchPlanCity);
            if (!filtered.length) {
                filtered = allCities.filter(matchPlanCity);
            }
            return filtered.length ? filtered : [];
        }

        /**
         * Nights calculation single-source-of-truth:
         * Nights = Checkout Day - Checkin Day
         */
        function getNightsSpanForCity(cityName) {
            const plan = getMultiCityPlanForCity(cityName);
            if (!plan) return null;
            const din = parseInt(String(plan.day_in || 0), 10) || 0;
            const dout = parseInt(String(plan.day_out || 0), 10) || 0;
            if (!(Number.isFinite(din) && Number.isFinite(dout)) || din <= 0 || dout <= 0) return null;
            const nights = Math.max(1, dout - din); // clamp to at least 1 night
            return { day_in: din, day_out: dout, nights };
        }

        function getHotelStayPeriodValue(span) {
            if (!span) return '';
            return `${parseInt(String(span.day_in || 0), 10) || 0}|${parseInt(String(span.day_out || 0), 10) || 0}`;
        }

        function getSpanFromPlan(plan) {
            const din = parseInt(String(plan?.day_in || 0), 10) || 0;
            const dout = parseInt(String(plan?.day_out || 0), 10) || 0;
            if (!(Number.isFinite(din) && Number.isFinite(dout)) || din <= 0 || dout <= 0) return null;
            return { day_in: din, day_out: dout, nights: Math.max(1, dout - din) };
        }

        function hotelStayEndDay(row) {
            const start = parseInt(String(row?.day || 1), 10) || 1;
            const nights = Math.max(1, parseInt(String(row?.night || 1), 10) || 1);
            return start + nights;
        }

        function hotelStaysOverlap(a, b) {
            const aStart = parseInt(String(a?.day || 1), 10) || 1;
            const bStart = parseInt(String(b?.day || 1), 10) || 1;
            return aStart < hotelStayEndDay(b) && bStart < hotelStayEndDay(a);
        }

        function getAvailableHotelStaySpan(cityName, hotelId, ignoreIndex = null) {
            const spans = getMultiCityPlansForCity(cityName).map(getSpanFromPlan).filter(Boolean);
            if (!spans.length) return null;
            const sameHotelRows = (Array.isArray(hotels) ? hotels : []).filter((row, idx) => {
                if (ignoreIndex !== null && idx === ignoreIndex) return false;
                return String(row?.hotel_id || '') === String(hotelId || '');
            });
            return spans.find(span => {
                const candidate = { day: span.day_in, night: span.nights };
                return !sameHotelRows.some(row => hotelStaysOverlap(row, candidate));
            }) || spans[0];
        }

        function getHotelFormStaySpan(ignoreIndex = null) {
            const cityName = getCityNameFromSelect('hotel_city_select');
            const hotelId = getSelectedOption('hotel_select')?.value || '';
            return getAvailableHotelStaySpan(cityName, hotelId, ignoreIndex)
                || getNightsSpanForCity(cityName);
        }

        function getMaxNightsForHotelDropdown(span) {
            if (!span) return 0;
            const fromSpan = Math.max(1, parseInt(String(span.nights || 1), 10) || 1);
            const dayIn = Math.max(1, parseInt(String(span.day_in || 1), 10) || 1);
            const fromTrip = Math.max(1, daysCount - dayIn);
            return Math.min(fromSpan, fromTrip);
        }

        function buildHotelNightOptions(maxNights) {
            const max = Math.max(1, parseInt(String(maxNights || 1), 10) || 1);
            return Array.from({ length: max }, (_, i) => {
                const n = i + 1;
                return { value: String(n), label: `${n} Night${n === 1 ? '' : 's'}` };
            });
        }

        function syncHotelDayDropdownWithMultiCity() {
            const ignoreIndex = editingHotelIndex !== null ? editingHotelIndex : null;
            const span = getHotelFormStaySpan(ignoreIndex);
            const sel = document.getElementById('hotel_day');
            if (!sel) return;

            if (!span) {
                if (sel.options.length > 1 || String(sel.value || '') !== '') {
                    setSelectOptions('hotel_day', []);
                }
                return;
            }

            const maxNights = getMaxNightsForHotelDropdown(span);
            const options = buildHotelNightOptions(maxNights);
            const preferredNight = (editingHotelIndex !== null && hotels[editingHotelIndex]?.night)
                ? parseInt(String(hotels[editingHotelIndex].night), 10)
                : parseInt(String(sel.value || maxNights), 10) || maxNights;
            const preferred = String(Math.max(1, Math.min(maxNights, preferredNight)));
            const needsUpdate = sel.options.length !== options.length + 1
                || options.some((opt, idx) => {
                    const existing = sel.options[idx + 1];
                    return !existing || existing.value !== opt.value || existing.textContent !== opt.label;
                });
            if (needsUpdate) {
                setSelectOptions('hotel_day', options);
            }
            if (String(sel.value || '') !== preferred) {
                safeSetSelectValue('hotel_day', preferred);
            }
        }

        function syncHotelsWithMultiCity() {
            let changed = false;
            hotels = (Array.isArray(hotels) ? hotels : []).map((x) => {
                const cityName = String(x.city_name || '').trim();
                const matchingSpans = getMultiCityPlansForCity(cityName).map(getSpanFromPlan).filter(Boolean);
                const currentRange = {
                    day: parseInt(String(x.day || 1), 10) || 1,
                    night: Math.max(1, parseInt(String(x.night || 1), 10) || 1),
                };
                const currentStillValid = matchingSpans.some(span =>
                    currentRange.day === span.day_in && hotelStayEndDay(currentRange) === span.day_out
                );
                const span = currentStillValid
                    ? { day_in: currentRange.day, day_out: hotelStayEndDay(currentRange), nights: currentRange.night }
                    : (matchingSpans[0] || null);
                if (!span) return x;
                const nextDay = span.day_in;
                const nextNight = span.nights;
                if ((parseInt(String(x.day || 1), 10) || 1) !== nextDay || (parseInt(String(x.night || 1), 10) || 1) !== nextNight) {
                    changed = true;
                }
                return {
                    ...x,
                    day: nextDay,
                    night: nextNight,
                    city_name: cityName
                };
            });
            syncHotelDayDropdownWithMultiCity();
            if (changed) {
                renderHotelRows();
                // Transfer defaults depend on hotel day/night ranges.
                if (isArrivalDepartureTransfersActive() || itineraryUsesMiddleDayTransfers()) {
                    applyTransferDefaults();
                }
            }
        }

        function syncHotelNightsWithMultiCity() {
            syncHotelsWithMultiCity();
        }

        // Backward-compat wrapper (older code still calls syncHotelNightsWithDays())
        function syncHotelNightsWithDays(forceToTotal = false) {
            syncHotelNightsWithMultiCity();
        }

        function renderDayServiceBlocks() {
            const wrap = document.getElementById('dayWiseServiceBlocks');
            if (!wrap) return;
            let html = '';
            const dayHeaderClasses = [
                'day-header-primary',
                'day-header-success',
                'day-header-warning',
                'day-header-danger',
                'day-header-info',
                'day-header-purple'
            ];
            for (let d = 1; d <= daysCount; d++) {
                const dayHeaderClass = dayHeaderClasses[(d - 1) % dayHeaderClasses.length];
                html += `
                    <div class="card sketch-card day-card mb-2">
                        <div class="card-header day-card-header ${dayHeaderClass}">
                            <div class="d-flex align-items-center gap-2">
                                <span class="day-pill">${d}</span>
                                <div>
                                    <strong>Day ${d}</strong>
                                    <div class="small opacity-75">Plan attractions, meals and transfers</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 align-items-end mb-2">
                                <div class="col-md-4">
                                    <label class="form-label" for="activity_city_select_${d}">City</label>
                                    <select id="activity_city_select_${d}" class="form-select searchable-select">
                                        <option value="">Select city</option>
                                    </select>
                                </div>
                            </div>

                            <div class="day-service-group group-attraction" id="attraction_group_${d}">
                                <div class="day-service-group__header">
                                    <span class="day-service-group__badge" aria-hidden="true">🎟</span>
                                    <strong>Attraction</strong>
                                    <span class="day-service-group__hint">Attraction &amp; its transfer share this section</span>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label" for="attraction_select_${d}">Attraction</label>
                                        <select id="attraction_select_${d}" class="form-select searchable-select">
                                            <option value="">Select attraction</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="attraction_ticket_select_${d}">Select Ticket</label>
                                        <select id="attraction_ticket_select_${d}" class="form-select searchable-select">
                                            <option value="">Select ticket</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="attraction_price_${d}">Ticket Price</label>
                                        <div class="input-group price-input-group">
                                            <span class="input-group-text">SGD</span>
                                            <input type="number" class="form-control" id="attraction_price_${d}" min="0" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex">
                                        <button type="button" class="btn btn-outline-primary w-100 mt-4" id="attraction_add_btn_${d}" onclick="addAttractionItemForDay(${d})">Add Attraction</button>
                                    </div>
                                </div>
                                <div class="day-service-transfer-panel attraction-transfer-panel" id="attraction_transfer_panel_${d}">
                                    <div class="day-service-transfer-panel__header">
                                        <span class="day-service-transfer-panel__icon" aria-hidden="true">🚐</span>
                                        <div>
                                            <strong>Attraction Transfer</strong>
                                            <div class="small text-muted">Pickup from hotel, attraction or restaurant → drop at attraction</div>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label" for="attraction_transfer_pickup_select_${d}">Pickup Location</label>
                                            <select id="attraction_transfer_pickup_select_${d}" class="form-select searchable-select">
                                                <option value="">Select pickup</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="attraction_transfer_drop_select_${d}">Drop Location</label>
                                            <select id="attraction_transfer_drop_select_${d}" class="form-select searchable-select">
                                                <option value="">Select drop</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="attraction_transfer_price_${d}">Transfer Price</label>
                                            <div class="input-group price-input-group">
                                                <span class="input-group-text">SGD</span>
                                                <input type="number" class="form-control" id="attraction_transfer_price_${d}" min="0" step="0.01" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex">
                                            <button type="button" class="btn btn-outline-primary w-100 mt-4" id="attraction_transfer_add_btn_${d}" onclick="addAttractionTransferItemForDay(${d})">Add Transfer</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="day-group-msg" id="attraction_group_msg_${d}" role="alert"></div>
                            </div>

                            <div class="day-service-group group-restaurant" id="restaurant_group_${d}">
                                <div class="day-service-group__header">
                                    <span class="day-service-group__badge" aria-hidden="true">🍽</span>
                                    <strong>Restaurant</strong>
                                    <span class="day-service-group__hint">Restaurant &amp; its transfer share this section</span>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label" for="restaurant_select_${d}">Restaurant</label>
                                        <select id="restaurant_select_${d}" class="form-select searchable-select">
                                            <option value="">Select restaurant</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="restaurant_meal_period_${d}">Meal period</label>
                                        <select id="restaurant_meal_period_${d}" class="form-select searchable-select">
                                            <option value="">All periods</option>
                                            <option value="1">Breakfast</option>
                                            <option value="2">Lunch</option>
                                            <option value="3">Dinner</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="restaurant_meal_select_${d}">Meal</label>
                                        <select id="restaurant_meal_select_${d}" class="form-select searchable-select">
                                            <option value="">Select restaurant first</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="restaurant_price_${d}">Meal Price</label>
                                        <div class="input-group price-input-group">
                                            <span class="input-group-text">SGD</span>
                                            <input type="number" class="form-control" id="restaurant_price_${d}" min="0" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex">
                                        <button type="button" class="btn btn-outline-primary w-100 mt-4" id="restaurant_add_btn_${d}" onclick="addRestaurantItemForDay(${d})">Add Restaurant</button>
                                    </div>
                                </div>
                                <div class="day-service-transfer-panel restaurant-transfer-panel" id="restaurant_transfer_panel_${d}">
                                    <div class="day-service-transfer-panel__header">
                                        <span class="day-service-transfer-panel__icon" aria-hidden="true">🚐</span>
                                        <div>
                                            <strong>Restaurant Transfer</strong>
                                            <div class="small text-muted">Pickup from hotel, attraction or restaurant → drop at restaurant</div>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label" for="restaurant_transfer_pickup_select_${d}">Pickup Location</label>
                                            <select id="restaurant_transfer_pickup_select_${d}" class="form-select searchable-select">
                                                <option value="">Select pickup</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="restaurant_transfer_drop_select_${d}">Drop Location</label>
                                            <select id="restaurant_transfer_drop_select_${d}" class="form-select searchable-select">
                                                <option value="">Select drop</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="restaurant_transfer_price_${d}">Transfer Price</label>
                                            <div class="input-group price-input-group">
                                                <span class="input-group-text">SGD</span>
                                                <input type="number" class="form-control" id="restaurant_transfer_price_${d}" min="0" step="0.01" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex">
                                            <button type="button" class="btn btn-outline-primary w-100 mt-4" id="restaurant_transfer_add_btn_${d}" onclick="addRestaurantTransferItemForDay(${d})">Add Transfer</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="day-group-msg" id="restaurant_group_msg_${d}" role="alert"></div>
                            </div>

                            <select id="transfer_city_select_${d}" class="d-none" aria-hidden="true" tabindex="-1">
                                <option value="">Select city</option>
                            </select>

                            <div class="day-transfer-wrap day-airport-transfer-wrap" id="day_transfer_wrap_${d}">
                                <div class="day-service-group group-arrival day-arrival-wrap" id="day_arrival_wrap_${d}" style="display:none;">
                                    <div class="day-service-group__header">
                                        <span class="day-service-group__badge" aria-hidden="true">🛬</span>
                                        <strong>Arrival</strong>
                                        <span class="day-service-group__hint">Airport pickup on arrival day</span>
                                    </div>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label" for="arrival_pickup_select_${d}">Pickup Location</label>
                                            <select id="arrival_pickup_select_${d}" class="form-select searchable-select">
                                                <option value="">Select pickup</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="arrival_drop_select_${d}">Drop Location</label>
                                            <select id="arrival_drop_select_${d}" class="form-select searchable-select">
                                                <option value="">Select drop</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="arrival_price_${d}">Transfer Price <span class="small text-muted fw-normal">(zone auto / manual)</span></label>
                                            <div class="input-group price-input-group">
                                                <span class="input-group-text">SGD</span>
                                                <input type="number" class="form-control transfer-leg-price-input" id="arrival_price_${d}" data-transfer-prefix="arrival" min="0" step="0.01" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex">
                                            <button type="button" class="btn btn-outline-primary w-100 mt-4" id="arrival_add_btn_${d}" onclick="addArrivalItemForDay(${d})">Add Arrival</button>
                                        </div>
                                    </div>
                                    <div class="day-group-msg" id="arrival_group_msg_${d}" role="alert"></div>
                                </div>

                                <div class="day-service-group group-departure day-departure-wrap" id="day_departure_wrap_${d}" style="display:none;">
                                    <div class="day-service-group__header">
                                        <span class="day-service-group__badge" aria-hidden="true">🛫</span>
                                        <strong>Departure</strong>
                                        <span class="day-service-group__hint">Airport drop on departure day</span>
                                    </div>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label" for="departure_pickup_select_${d}">Pickup Location</label>
                                            <select id="departure_pickup_select_${d}" class="form-select searchable-select">
                                                <option value="">Select pickup</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="departure_drop_select_${d}">Drop Location</label>
                                            <select id="departure_drop_select_${d}" class="form-select searchable-select">
                                                <option value="">Select drop</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="departure_price_${d}">Transfer Price <span class="small text-muted fw-normal">(zone auto / manual)</span></label>
                                            <div class="input-group price-input-group">
                                                <span class="input-group-text">SGD</span>
                                                <input type="number" class="form-control transfer-leg-price-input" id="departure_price_${d}" data-transfer-prefix="departure" min="0" step="0.01" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex">
                                            <button type="button" class="btn btn-outline-primary w-100 mt-4" id="departure_add_btn_${d}" onclick="addDepartureItemForDay(${d})">Add Departure</button>
                                        </div>
                                    </div>
                                    <div class="day-group-msg" id="departure_group_msg_${d}" role="alert"></div>
                                </div>

                                <div class="mt-2 extra-transfer-wrap" id="extra_transfer_wrap_${d}" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Additional transfer options (for transfer days)</small>
                                    </div>
                                    <div id="extra_transfer_rows_${d}"></div>
                                </div>
                            </div>

                            <div class="mt-2" id="day_items_${d}"></div>
                        </div>
                    </div>
                `;
            }
            wrap.innerHTML = html;
            initSearchableSelects(wrap);
            hydrateDayServiceBlocksOptions();
            updateAllDayTransferVisibility();
            hydrateAllDayTransferCityOptions();
            renderAllExtraTransferRows();
        }

        async function hydrateDayServiceBlocksOptions() {
            if (hydrateDayServicesInFlight) {
                hydrateDayServicesQueued = true;
                return hydrateDayServicesInFlight;
            }
            const runHydrate = async () => {
                do {
                    hydrateDayServicesQueued = false;
                    isHydratingDayServices = true;
                    const selectedCountry = document.getElementById('country').value || '';
                    const filteredCities = allCities.filter(c => cityMatchesCountry(c, selectedCountry));
                    const multiCityFiltered = getDayCityOptionsFromMultiCity();
                    const source = (multiCityFiltered && multiCityFiltered.length) ? multiCityFiltered : filteredCities;
                    const cityOptions = source.map(c => ({
                        value: c.value,
                        label: c.country ? `${c.name}, ${c.country}` : c.name,
                        data_name: c.name,
                        data_country: c.country,
                    }));
                    const forceCitySync = isHydratingEditPayload || multiCityPlans.length > 0;
                    for (let d = 1; d <= daysCount; d++) {
                        setSelectOptions(`activity_city_select_${d}`, cityOptions, true);
                        setSelectOptions(`attraction_select_${d}`, [{ value: '', label: 'Select city first' }], true);
                        setSelectOptions(`restaurant_select_${d}`, [{ value: '', label: 'Select city first' }], true);
                        setSelectOptions(`attraction_ticket_select_${d}`, [], true);
                    }
                    if (multiCityPlans.length || isHydratingEditPayload) {
                        syncDayCitySelectorsFromMultiCity(forceCitySync);
                        syncDayCitySelectorsFromStoredData(forceCitySync);
                    } else {
                        syncSectionCitySelectionsFromMain();
                    }
                    for (let d = 1; d <= daysCount; d++) {
                        if (!activityCityByDay[d]) {
                            activityCityByDay[d] = getCityNameFromSelect(`activity_city_select_${d}`);
                        }
                    }
                    // Populate every day block; fetchCityServicesData caches per city so
                    // repeated cities (Day 2, Day 3, ...) reuse one request but still get options.
                    await Promise.all(
                        Array.from({ length: daysCount }, (_, i) => i + 1).map((d) => {
                            const cityName = getCityNameFromSelect(`activity_city_select_${d}`);
                            activityCityByDay[d] = cityName;
                            if (!cityName) return Promise.resolve();
                            return populateDayServiceOptionsByCity(d, cityName, { loadTransfers: false });
                        })
                    );
                    hydrateAllDayTransferCityOptions();
                    isHydratingDayServices = false;
                    scheduleTransferOptionsReload(false);
                } while (hydrateDayServicesQueued);
            };
            hydrateDayServicesInFlight = runHydrate().finally(() => {
                hydrateDayServicesInFlight = null;
            });
            return hydrateDayServicesInFlight;
        }

        function getSelectedOption(selectId) {
            const sel = document.getElementById(selectId);
            if (!sel || !sel.value) return null;
            if (sel.tagName === 'INPUT') {
                return { value: sel.value, dataset: sel.dataset || {} };
            }
            return sel.options[sel.selectedIndex] || null;
        }

        function resolveCityName() {
            const cityOp = getSelectedOption('city_id');
            const fromDataset = String(cityOp?.dataset?.name || '').trim();
            if (fromDataset) return fromDataset;
            const cityId = String(cityOp?.value || document.getElementById('city_id')?.value || '').trim();
            if (cityId) {
                const fromList = (Array.isArray(allCities) ? allCities : []).find(c => String(c.value) === cityId);
                const fromListName = String(fromList?.name || '').trim();
                if (fromListName) return fromListName;
            }
            const label = String(cityOp?.textContent || '').trim();
            if (label && label.toLowerCase() !== 'select city') {
                return label.split(',')[0].trim();
            }
            return '';
        }

        function unwrapDestinationNode(raw) {
            if (!raw || typeof raw !== 'object') return {};
            // API / accessor shape: destinations[i] === { DMC: [ { DMC_id, country, packages, ... } ] }
            if (Array.isArray(raw.DMC) && raw.DMC.length && typeof raw.DMC[0] === 'object') {
                return raw.DMC[0];
            }
            // Storage / form shape: { DMC_id, country, cities, ... }
            return raw;
        }

        function inferMultiCityPlansFromPackageDays(packages) {
            const plans = [];
            const pkg0 = Array.isArray(packages) ? packages[0] : null;
            const daysObj = pkg0 && typeof pkg0.days === 'object' && pkg0.days ? pkg0.days : null;
            if (!daysObj) return plans;
            const firstDay = Object.values(daysObj).find(d => d && typeof d === 'object');
            const cmap = firstDay && typeof firstDay.cities === 'object' ? firstDay.cities : null;
            if (!cmap) return plans;
            Object.values(cmap).forEach((c) => {
                if (!c || typeof c !== 'object') return;
                const name = String(c.city || '').trim();
                const din = parseInt(String(c.city_checkin ?? c.checkin_day ?? ''), 10);
                const dout = parseInt(String(c.city_checkout ?? c.checkout_day ?? ''), 10);
                if (name && Number.isFinite(din) && Number.isFinite(dout) && din > 0 && dout >= din) {
                    plans.push({ city_id: '', city_name: name, day_in: din, day_out: dout });
                }
            });
            return plans;
        }

        function resolveCityIdForPlan(plan) {
            if (!plan) return '';
            const direct = String(plan.city_id || '').trim();
            if (direct) return direct;
            const key = normalizeCityNameKey(plan.city_name);
            if (!key) return '';
            const fromAll = allCities.find(c => normalizeCityNameKey(c.name) === key);
            if (fromAll) {
                plan.city_id = String(fromAll.value);
                return plan.city_id;
            }
            const citySelect = document.getElementById('city_id');
            const match = citySelect
                ? Array.from(citySelect.options).find(opt => {
                    const nm = String(opt.dataset.name || opt.textContent || '').trim().toLowerCase();
                    return nm === key;
                })
                : null;
            if (match) {
                plan.city_id = String(match.value);
                return plan.city_id;
            }
            return '';
        }

        function resolveCityIdsForMultiPlans() {
            multiCityPlans.forEach((plan) => resolveCityIdForPlan(plan));
        }

        function normalizeRestaurantRowId(row) {
            return String(row?.restaurant_id || row?.id || '').trim();
        }

        function normalizeRestaurantRowName(row) {
            return String(row?.restaurant_name || row?.name || row?.label || '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, ' ');
        }

        function isHydratedRestaurantServiceRow(row) {
            if (!row || typeof row !== 'object') return false;
            if (String(row.service_type || row.type || '').toLowerCase() === 'restaurant') return true;
            return normalizeRestaurantRowId(row) !== '';
        }

        function trackHydratedRestaurantRef(hydratedRefs, dayNum, row) {
            const rid = normalizeRestaurantRowId(row);
            const name = normalizeRestaurantRowName(row);
            if (rid) hydratedRefs.add(`${dayNum}|id|${rid}`);
            if (name) hydratedRefs.add(`${dayNum}|name|${name}`);
        }

        function legacyRestaurantAlreadyHydrated(hydratedRefs, dayNum, row) {
            const rid = normalizeRestaurantRowId(row);
            const name = normalizeRestaurantRowName(row);
            if (rid && hydratedRefs.has(`${dayNum}|id|${rid}`)) return true;
            if (name && hydratedRefs.has(`${dayNum}|name|${name}`)) return true;
            return false;
        }

        function restaurantDayItemHasTransfer(item) {
            const t = item?.transfer && typeof item.transfer === 'object' ? item.transfer : {};
            return parseFloat(String(t.cost ?? t.transfer_price ?? 0)) > 0
                || !!(t.pickup_location || t.drop_location || t.pickup_location_label || t.drop_location_label);
        }

        function dedupeRestaurantDayItems(items) {
            const list = Array.isArray(items) ? items : [];
            const dropIndices = new Set();
            const restaurantGroups = new Map();

            list.forEach((item, idx) => {
                if (item?.type !== 'restaurant' || !String(item.id || '').trim()) return;
                const dayNum = parseInt(String(item.day || 1), 10) || 1;
                const key = `${dayNum}|${String(item.id || '').trim()}`;
                if (!restaurantGroups.has(key)) {
                    restaurantGroups.set(key, []);
                }
                restaurantGroups.get(key).push({ item, idx });
            });

            restaurantGroups.forEach((group) => {
                if (group.length <= 1) return;
                const best = group.reduce((winner, current) => {
                    const wXfer = restaurantDayItemHasTransfer(winner.item);
                    const cXfer = restaurantDayItemHasTransfer(current.item);
                    if (cXfer && !wXfer) return current;
                    if (wXfer && !cXfer) return winner;
                    const wMeal = String(winner.item?.meal?.dish || winner.item?.meal?.meal_type || '').trim();
                    const cMeal = String(current.item?.meal?.dish || current.item?.meal?.meal_type || '').trim();
                    if (cMeal && !wMeal) return current;
                    return winner;
                });
                group.forEach((entry) => {
                    if (entry.idx !== best.idx) dropIndices.add(entry.idx);
                });
            });

            return list.filter((_, idx) => !dropIndices.has(idx));
        }

        async function hydrateFromEditPayload() {
            const payload = window.__EDIT_PAYLOAD__;
            if (!payload || !Array.isArray(payload.Master_DMC) || !payload.Master_DMC.length) {
                return;
            }
            isHydratingEditPayload = true;
            try {

            const meta = window.__EDIT_DAY_LEVEL_META__ || {};
            if (meta.master_dmc_id) {
                document.getElementById('master_dmc_id').value = String(meta.master_dmc_id);
            }
            if (meta.dmc_id) {
                const dmcEl = document.getElementById('dmc_id');
                if (dmcEl) {
                    dmcEl.value = String(meta.dmc_id);
                    dmcEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            const firstMaster = payload.Master_DMC[0] || {};
            const rawDestination = Array.isArray(firstMaster.destinations) ? (firstMaster.destinations[0] || {}) : {};
            const destination = unwrapDestinationNode(rawDestination);

            const countryVal = String(destination.country || meta.country || '');
            if (countryVal) {
                document.getElementById('country').value = countryVal;
            }
            applyCountryFilter();

            const cities = Array.isArray(destination.cities) ? destination.cities : [];
            const packagesFallback = Array.isArray(destination.packages) ? destination.packages : [];
            const cityNodesForHydration = cities.length
                ? cities
                : (packagesFallback.length ? [{ city: '', packages: packagesFallback }] : []);

            const firstCity = cities[0] || {};
            const inferredPlansPreview = inferMultiCityPlansFromPackageDays(packagesFallback);
            const firstCityName = String(firstCity.city || inferredPlansPreview[0]?.city_name || '');

            if (firstCityName) {
                const citySelect = document.getElementById('city_id');
                const matchOpt = Array.from(citySelect.options).find(opt => {
                    const nm = String(opt.dataset.name || opt.textContent || '').trim().toLowerCase();
                    return nm === firstCityName.trim().toLowerCase();
                });
                if (matchOpt) {
                    $('#city_id').val(String(matchOpt.value)).trigger('change');
                } else if (meta.city_id) {
                    $('#city_id').val(String(meta.city_id)).trigger('change');
                }
            } else if (meta.city_id) {
                $('#city_id').val(String(meta.city_id)).trigger('change');
            }

            const maxDayFromPayload = cityNodesForHydration.reduce((maxD, cityNode) => {
                const packages = Array.isArray(cityNode?.packages) ? cityNode.packages : [];
                let localMax = maxD;
                packages.forEach(pkg => {
                    const daysObj = (pkg && typeof pkg.days === 'object' && pkg.days) ? pkg.days : {};
                    Object.values(daysObj).forEach(d => {
                        if (d && typeof d === 'object') {
                            localMax = Math.max(localMax, parseInt(String(d.day || 0), 10) || 0);
                        }
                    });
                });
                return localMax;
            }, 0);

            const maxCheckoutFromCities = cities.reduce((maxV, c) => {
                const out = parseInt(String(c?.checkout_day || 0), 10) || 0;
                return Math.max(maxV, out);
            }, 0);
            const maxCheckoutFromInferred = inferredPlansPreview.reduce((maxV, p) => {
                const out = parseInt(String(p?.day_out || 0), 10) || 0;
                return Math.max(maxV, out);
            }, 0);
            const maxCheckoutDay = Math.max(maxCheckoutFromCities, maxCheckoutFromInferred);
            const relevantPackages = [];
            cityNodesForHydration.forEach((cityNode) => {
                const packages = Array.isArray(cityNode?.packages) ? cityNode.packages : [];
                packages.forEach((pkg) => {
                    if (packageMatchesEditFilter(pkg)) {
                        relevantPackages.push(pkg);
                    }
                });
            });
            if (!relevantPackages.length && packagesFallback.length) {
                packagesFallback.forEach((pkg) => {
                    if (packageMatchesEditFilter(pkg)) {
                        relevantPackages.push(pkg);
                    }
                });
            }
            const packageTotalDays = relevantPackages.reduce((max, pkg) => {
                const td = parseInt(String(pkg?.total_days ?? pkg?.totalDays ?? 0), 10) || 0;
                let fromDayNodes = 0;
                const daysObj = pkg?.days && typeof pkg.days === 'object' ? pkg.days : {};
                Object.values(daysObj).forEach((d) => {
                    if (d && typeof d === 'object') {
                        fromDayNodes = Math.max(fromDayNodes, parseInt(String(d.day || 0), 10) || 0);
                    }
                });
                return Math.max(max, td, fromDayNodes);
            }, 0);
            const metaDays = parseInt(String(meta.days || 0), 10) || 0;
            const resolvedDays = window.__EDITING_PACKAGE_ID__
                ? Math.max(1, packageTotalDays || maxDayFromPayload || maxCheckoutDay)
                : Math.max(1, metaDays, maxDayFromPayload, maxCheckoutDay);
            document.getElementById('days').value = String(resolvedDays);
            daysCount = resolvedDays;
            initDays();

            hotels = [];
            dayItems = [];
            multiCityPlans = [];
            dayTransferExtras = {};
            const seenDayActivityKeys = new Set();

            cityNodesForHydration.forEach((cityNode) => {
                const packages = Array.isArray(cityNode?.packages) ? cityNode.packages : [];
                const relevantPackages = packages.filter(pkg => packageMatchesEditFilter(pkg));
                if (window.__EDITING_PACKAGE_ID__ && relevantPackages.length === 0) {
                    return;
                }

                const cityName = String(cityNode?.city || '');
                const checkin = parseInt(String(cityNode?.checkin_day || ''), 10);
                const checkout = parseInt(String(cityNode?.checkout_day || ''), 10);
                if (Number.isFinite(checkin) && Number.isFinite(checkout) && checkin > 0 && checkout >= checkin) {
                    multiCityPlans.push({
                        city_id: '',
                        city_name: cityName,
                        day_in: checkin,
                        day_out: checkout
                    });
                }

                const packagesToHydrate = relevantPackages.length ? relevantPackages : packages;
                packagesToHydrate.forEach(pkg => {
                    const daysObj = (pkg && typeof pkg.days === 'object' && pkg.days) ? pkg.days : {};
                    Object.values(daysObj).forEach(dayNode => {
                        if (!dayNode || typeof dayNode !== 'object') return;
                        const dayNum = parseInt(String(dayNode.day || 1), 10) || 1;

                        const hotelSource = dayNode.hotels;
                        const hotelVals = Array.isArray(hotelSource)
                            ? hotelSource
                            : Object.values(hotelSource && typeof hotelSource === 'object' ? hotelSource : {});
                        hotelVals.forEach(h => {
                            if (!h || typeof h !== 'object') return;
                            const nights = Math.max(1, parseInt(String(h.night || 1), 10) || 1);
                            const checkinDay = Math.max(1, parseInt(String(h.checkin_day || h.booked_day || dayNum), 10) || dayNum);
                            const totalPrice = parseFloat(String(h.total_price ?? h.price ?? 0)) || 0;
                            const perNightStored = parseFloat(String(h.price_per_night ?? 0)) || 0;
                            const perNight = perNightStored > 0
                                ? perNightStored
                                : (totalPrice > 0 && nights > 0 ? totalPrice / nights : 0);
                            hotels.push({
                                day: checkinDay,
                                cat: '',
                                cat_label: '',
                                hotel_id: String(h.hotel_id || ''),
                                hotel_name: String(h.hotel_name || ''),
                                city_name: String(h.city || cityName || ''),
                                night: nights,
                                room_id: String(h.room_id || ''),
                                room_type: String(h.room_type || ''),
                                bed_id: String(h.bed_id || ''),
                                bed_type: String(h.bed_type || ''),
                                meal_plan: String(h.meal_plan || ''),
                                meal_type: String(h.meal_type || ''),
                                guide_required: String(h.guide_required || 'No'),
                                arrival_departure: String(h.arrival_departure || 'No'),
                                arrival_departure_type: String(h.arrival_departure_type || ''),
                                transfer_city: String(h.transfer_city || ''),
                                transfer_pickup: String(h.transfer_pickup || ''),
                                transfer_drop: String(h.transfer_drop || ''),
                                room_price: parseFloat(String(h.room_price ?? perNight)) || perNight,
                                breakfast_price: parseFloat(String(h.breakfast_price ?? 0)) || 0,
                                lunch_price: parseFloat(String(h.lunch_price ?? 0)) || 0,
                                dinner_price: parseFloat(String(h.dinner_price ?? 0)) || 0,
                                price: perNight,
                                priority: parseInt(String(h.priority || 1), 10) || 1
                            });
                        });

                        Object.values(dayNode.arrivals && typeof dayNode.arrivals === 'object' ? dayNode.arrivals : {}).forEach((row) => {
                            hydrateTransferLegDayItem(dayNum, row, cityName, seenDayActivityKeys, 'arrival');
                        });
                        Object.values(dayNode.departures && typeof dayNode.departures === 'object' ? dayNode.departures : {}).forEach((row) => {
                            hydrateTransferLegDayItem(dayNum, row, cityName, seenDayActivityKeys, 'departure');
                        });
                        Object.values(dayNode.transfers && typeof dayNode.transfers === 'object' ? dayNode.transfers : {}).forEach((row) => {
                            hydrateTransferLegDayItem(dayNum, row, cityName, seenDayActivityKeys, 'attraction transfer');
                        });

                        const attrVals = Object.values(dayNode.attractions && typeof dayNode.attractions === 'object' ? dayNode.attractions : {});
                        attrVals.forEach(a => {
                            if (!a || typeof a !== 'object') return;
                            const aid = String(a.attraction_id || '');
                            const tid = String(a.ticket_id || '');
                            const rawT = a.transfer && typeof a.transfer === 'object' ? a.transfer : {};
                            const normalizedTransfer = normalizeHydratedTransfer(rawT);
                            const transferType = String(normalizedTransfer.transfer_type || '').trim().toLowerCase();
                            const isTransferOnly = !aid && (
                                transferType === 'arrival'
                                || transferType === 'departure'
                                || transferType === 'attraction transfer'
                                || String(a.name || '').toLowerCase().includes('arrival')
                                || String(a.name || '').toLowerCase().includes('departure')
                                || String(a.name || '').toLowerCase().includes('transfer')
                            );
                            const dedupeKey = isTransferOnly
                                ? `${dayNum}|transfer|${transferType}|${normalizedTransfer.pickup_location}|${normalizedTransfer.drop_location}`
                                : `${dayNum}|attraction|${aid}|${tid}`;
                            if (seenDayActivityKeys.has(dedupeKey)) return;
                            seenDayActivityKeys.add(dedupeKey);
                            const resolvedAttractionCity = String(
                                a.city || normalizedTransfer.city || cityName || ''
                            );
                            const hasXfer = String(normalizedTransfer.required || '').toLowerCase() === 'yes'
                                || !!(normalizedTransfer.pickup_location || normalizedTransfer.drop_location);
                            const transferOnlyLabel = transferType === 'arrival'
                                ? 'Day Arrival'
                                : transferType === 'departure'
                                    ? 'Day Departure'
                                    : (String(a.name || '').trim() || 'Attraction Transfer');
                            dayItems.push({
                                day: dayNum,
                                type: 'attraction',
                                id: aid,
                                label: isTransferOnly ? transferOnlyLabel : String(a.name || ''),
                                city_name: resolvedAttractionCity,
                                ticket_id: tid,
                                ticket_name: String(a.ticket_name || ''),
                                ticket_price: parseFloat(String(a.ticket_price ?? a.price ?? 0)) || 0,
                                price: parseFloat(String(a.ticket_price ?? a.price ?? 0)) || 0,
                                transfer: buildHydratedItemTransferFields(normalizedTransfer, hasXfer),
                            });
                            const addlXfer = normalizedTransfer.additional_transfers;
                            if (Array.isArray(addlXfer) && addlXfer.length) {
                                ensureDayTransferExtras(dayNum);
                                const mapped = addlXfer.map(item => {
                                    const row = normalizeHydratedTransfer(item);
                                    return {
                                        city: String(row?.city ?? ''),
                                        pickup_location: String(row?.pickup_location_value || row?.pickup_location_id || row?.pickup_location || ''),
                                        drop_location: String(row?.drop_location_value || row?.drop_location_id || row?.drop_location || ''),
                                        pickup_location_label: String(row?.pickup_location_label || row?.pickup_location || ''),
                                        drop_location_label: String(row?.drop_location_label || row?.drop_location || ''),
                                    };
                                }).filter(item => item.pickup_location || item.drop_location || item.city);
                                dayTransferExtras[dayNum] = [...(dayTransferExtras[dayNum] || []), ...mapped];
                            }
                        });

                        const serviceVals = Object.values(dayNode.services && typeof dayNode.services === 'object' ? dayNode.services : {});
                        const hydratedRestaurantRefs = new Set();
                        serviceVals.forEach(s => {
                            if (!isHydratedRestaurantServiceRow(s)) return;
                            const rid = normalizeRestaurantRowId(s);
                            const normalizedTransfer = normalizeHydratedTransfer(s.transfer || {});
                            const transferType = String(normalizedTransfer.transfer_type || '').trim().toLowerCase();
                            const serviceType = String(s.service_type || '').toLowerCase();
                            if (!rid) {
                                const isRestaurantTransfer = serviceType === 'restaurant_transfer'
                                    || transferType === 'restaurant transfer'
                                    || String(s.restaurant_name || s.name || '').toLowerCase().includes('transfer');
                                if (!isRestaurantTransfer) return;
                                const dedupeKey = `${dayNum}|restaurant_transfer|${normalizedTransfer.pickup_location}|${normalizedTransfer.drop_location}`;
                                if (seenDayActivityKeys.has(dedupeKey)) return;
                                seenDayActivityKeys.add(dedupeKey);
                                dayItems.push({
                                    day: dayNum,
                                    type: 'restaurant',
                                    id: '',
                                    label: 'Restaurant Transfer',
                                    city_name: String(s.city || cityName || ''),
                                    meal_price: 0,
                                    price: 0,
                                    meal: {
                                        meal_type: '',
                                        dish: '',
                                        time_slot: '',
                                    },
                                    transfer: {
                                        required: String(normalizedTransfer.required || 'Yes'),
                                        city: String(normalizedTransfer.city || ''),
                                        transfer_type: 'Restaurant Transfer',
                                        pickup_location: String(normalizedTransfer.pickup_location || ''),
                                        drop_location: String(normalizedTransfer.drop_location || ''),
                                        pickup_location_value: String(normalizedTransfer.pickup_location_value || normalizedTransfer.pickup_location_id || ''),
                                        drop_location_value: String(normalizedTransfer.drop_location_value || normalizedTransfer.drop_location_id || ''),
                                        pickup_location_label: String(normalizedTransfer.pickup_location_label || ''),
                                        drop_location_label: String(normalizedTransfer.drop_location_label || ''),
                                        cost: parseFloat(String(normalizedTransfer.cost ?? normalizedTransfer.transfer_price ?? 0)) || 0,
                                        transfer_price: parseFloat(String(normalizedTransfer.transfer_price ?? normalizedTransfer.cost ?? 0)) || 0,
                                        additional_transfers: [],
                                    },
                                });
                                return;
                            }
                            const mt = String(s.meal_configuration?.meal_type || '');
                            const dish = String(s.meal_configuration?.dish || '');
                            const dedupeKey = `${dayNum}|restaurant|${rid}|${mt}|${dish}`;
                            if (seenDayActivityKeys.has(dedupeKey)) return;
                            seenDayActivityKeys.add(dedupeKey);
                            trackHydratedRestaurantRef(hydratedRestaurantRefs, dayNum, s);
                            dayItems.push({
                                day: dayNum,
                                type: 'restaurant',
                                id: rid,
                                label: String(s.restaurant_name || ''),
                                city_name: String(s.city || cityName || ''),
                                meal_price: parseFloat(String(s.meal_price ?? s.price ?? s.meal_configuration?.meal_price ?? 0)) || 0,
                                price: parseFloat(String(s.meal_price ?? s.price ?? s.meal_configuration?.meal_price ?? 0)) || 0,
                                meal: {
                                    meal_id: String(s.meal_configuration?.meal_id || ''),
                                    meal_type: mt,
                                    dish: dish,
                                    meal_name: String(s.meal_configuration?.meal_name || ''),
                                    meal_period: String(s.meal_configuration?.meal_period || ''),
                                    meal_price: parseFloat(String(s.meal_price ?? s.price ?? s.meal_configuration?.meal_price ?? 0)) || 0,
                                    time_slot: String(s.meal_configuration?.time_slot || '')
                                },
                                transfer: (() => {
                                    const normalizedTransfer = normalizeHydratedTransfer(s.transfer || {});
                                    return {
                                        required: String(normalizedTransfer.required || 'No'),
                                        city: String(normalizedTransfer.city || ''),
                                        type: String(normalizedTransfer.type || ''),
                                        way: String(normalizedTransfer.way || ''),
                                        vehicle_id: String(normalizedTransfer.vehicle_id || ''),
                                        vehicle_name: String(normalizedTransfer.vehicle_name || ''),
                                        pickup_location_id: String(normalizedTransfer.pickup_location_id || normalizedTransfer.pickup_location_value || ''),
                                        drop_location_id: String(normalizedTransfer.drop_location_id || normalizedTransfer.drop_location_value || ''),
                                        pickup_location: String(normalizedTransfer.pickup_location || ''),
                                        drop_location: String(normalizedTransfer.drop_location || ''),
                                        pickup_location_value: String(normalizedTransfer.pickup_location_value || normalizedTransfer.pickup_location_id || ''),
                                        drop_location_value: String(normalizedTransfer.drop_location_value || normalizedTransfer.drop_location_id || ''),
                                        pickup_location_label: String(normalizedTransfer.pickup_location_label || ''),
                                        drop_location_label: String(normalizedTransfer.drop_location_label || ''),
                                        transfer_type: String(normalizedTransfer.transfer_type || ''),
                                        cost: parseFloat(String(normalizedTransfer.cost ?? normalizedTransfer.transfer_price ?? 0)) || 0,
                                        transfer_price: parseFloat(String(normalizedTransfer.transfer_price ?? normalizedTransfer.cost ?? 0)) || 0,
                                        pickup_time: String(normalizedTransfer.pickup_time || ''),
                                        additional_transfers: [],
                                    };
                                })()
                            });
                        });

                        const legacyRestVals = Object.values(dayNode.restaurants && typeof dayNode.restaurants === 'object' ? dayNode.restaurants : {});
                        legacyRestVals.forEach(r => {
                            if (!r || typeof r !== 'object') return;
                            if (legacyRestaurantAlreadyHydrated(hydratedRestaurantRefs, dayNum, r)) {
                                return;
                            }
                            const rid = normalizeRestaurantRowId(r);
                            const dedupeKey = `${dayNum}|restaurant_raw|${rid}|${normalizeRestaurantRowName(r)}`;
                            if (seenDayActivityKeys.has(dedupeKey)) return;
                            seenDayActivityKeys.add(dedupeKey);
                            dayItems.push({
                                day: dayNum,
                                type: 'restaurant',
                                id: rid,
                                label: String(r.name || ''),
                                city_name: String(r.city || cityName || ''),
                                meal: {
                                    meal_type: String(r.meal_type || ''),
                                    dish: String(r.dish || ''),
                                    time_slot: String(r.time_slot || '')
                                },
                                transfer: {
                                    required: 'No',
                                    city: '',
                                    type: '',
                                    way: '',
                                    vehicle_id: '',
                                    vehicle_name: '',
                                    pickup_location_id: '',
                                    pickup_location: '',
                                    drop_location: '',
                                    cost: 0,
                                    pickup_time: ''
                                }
                            });
                        });
                    });
                });
            });

            if (!multiCityPlans.length && inferredPlansPreview.length) {
                multiCityPlans.push(...inferredPlansPreview.map(p => ({ ...p })));
            }
            resolveCityIdsForMultiPlans();

            const hotelMergeMap = new Map();
            hotels.forEach((h) => {
                const hotelId = String(h.hotel_id || '').trim();
                if (!hotelId) return;
                const cityKey = String(h.city_name || '').trim().toLowerCase();
                const key = `${hotelId}|${cityKey}`;
                const dayNum = parseInt(String(h.day || 1), 10) || 1;
                const nights = Math.max(1, parseInt(String(h.night || 1), 10) || 1);
                const existing = hotelMergeMap.get(key);
                if (!existing) {
                    hotelMergeMap.set(key, { ...h, day: dayNum, night: nights });
                    return;
                }
                existing.day = Math.min(existing.day, dayNum);
                existing.night = Math.max(existing.night, nights);
            });
            hotels = Array.from(hotelMergeMap.values());

            dayItems = dedupeRestaurantDayItems(dayItems);

            dayItems = (Array.isArray(dayItems) ? dayItems : []).map((item) => {
                if (!item?.transfer || typeof item.transfer !== 'object') return item;
                return { ...item, transfer: normalizeHydratedTransfer(item.transfer) };
            });

            renderMultiCityRows();
            resolveCityIdsForMultiPlans();
            setSectionCityOptions();
            // City sync + per-day option population both happen inside
            // hydrateDayServiceBlocksOptions (sync runs before populate).
            await hydrateDayServiceBlocksOptions();
            syncHotelNightsWithDays();
            renderHotelRows();
            renderActivityRows();
            updateAllDayTransferVisibility();
            renderAllExtraTransferRows();
            scheduleTransferOptionsReload(false);
            } finally {
                isHydratingEditPayload = false;
            }
        }

        function toggleHotelTransferFields() {
            const locked = hotelsHaveArrivalDepartureTransferSaved();
            updateAllDayTransferVisibility();
            if (!locked) {
                hotelTransferState = { city: '', pickup: '', drop: '' };
                clearEndDayTransferSelectionsOnly();
            }
        }

        function updateAllDayTransferVisibility() {
            for (let d = 1; d <= daysCount; d++) {
                const wrap = document.getElementById(`day_transfer_wrap_${d}`);
                if (wrap) {
                    wrap.style.display = '';
                }
                const arrivalWrap = document.getElementById(`day_arrival_wrap_${d}`);
                if (arrivalWrap) {
                    arrivalWrap.style.display = shouldShowArrivalForDay(d) ? '' : 'none';
                }
                const departureWrap = document.getElementById(`day_departure_wrap_${d}`);
                if (departureWrap) {
                    departureWrap.style.display = shouldShowDepartureForDay(d) ? '' : 'none';
                }
                const extraWrap = document.getElementById(`extra_transfer_wrap_${d}`);
                if (extraWrap) {
                    const isLegDay = shouldShowArrivalForDay(d) || shouldShowDepartureForDay(d);
                    const showExtra = daysCount >= 3 && d > 1 && d < daysCount && !isLegDay;
                    extraWrap.style.display = showExtra ? '' : 'none';
                }
            }

            if (serviceTransferOptions.length > 0 || transferLocationOptions.length > 0) {
                for (let d = 1; d <= daysCount; d++) {
                    populateServiceTransferSelectsForDay(d);
                }
            }
        }

        function hydrateAllDayTransferCityOptions() {
            const selectedCountry = document.getElementById('country').value || '';
            const filtered = allCities.filter(c => !selectedCountry || c.country === selectedCountry);
            const multiCityFiltered = getDayCityOptionsFromMultiCity();
            const source = (multiCityFiltered && multiCityFiltered.length) ? multiCityFiltered : filtered;
            const options = source.map(c => ({
                value: c.value,
                label: c.country ? `${c.name}, ${c.country}` : c.name,
                data_name: c.name,
                data_country: c.country,
            }));
            isSyncingTransferCity = true;
            const fallbackCity = hotelTransferState.city || document.getElementById('hotel_city_select')?.value || '';
            for (let d = 1; d <= daysCount; d++) {
                setSelectOptions(`transfer_city_select_${d}`, options, true);
                if (fallbackCity) {
                    safeSetSelectValueSilent(`transfer_city_select_${d}`, fallbackCity);
                }
            }
            isSyncingTransferCity = false;
            renderAllExtraTransferRows();
        }

        function getTransferTypeLabelForDay(dayVal) {
            return 'Transfer';
        }

        function ensureDayTransferExtras(dayVal) {
            if (!Array.isArray(dayTransferExtras[dayVal])) {
                dayTransferExtras[dayVal] = [];
            }
        }

        function removeExtraTransferRow(dayVal, idx) {
            ensureDayTransferExtras(dayVal);
            dayTransferExtras[dayVal].splice(idx, 1);
            renderExtraTransferRows(dayVal);
        }

        function updateExtraTransferValue(dayVal, idx, key, value) {
            ensureDayTransferExtras(dayVal);
            if (!dayTransferExtras[dayVal][idx]) return;
            const v = String(value || '').trim();
            dayTransferExtras[dayVal][idx][key] = v;
            if (key === 'pickup_location') {
                dayTransferExtras[dayVal][idx].pickup_location_label = getTransferLocationLabel(
                    `attraction_transfer_pickup_select_${dayVal}`,
                    v
                );
            } else if (key === 'drop_location') {
                dayTransferExtras[dayVal][idx].drop_location_label = getTransferLocationLabel(
                    `attraction_transfer_drop_select_${dayVal}`,
                    v
                );
            }
        }

        function backfillTransferLabelsOnDayItems() {
            (Array.isArray(dayItems) ? dayItems : []).forEach((item) => {
                const transfer = item?.transfer;
                if (!transfer || typeof transfer !== 'object') return;
                const day = parseInt(String(item.day || 1), 10) || 1;
                const type = transfer.transfer_type || '';
                const itemType = String(item?.type || '').trim();
                if (transfer.pickup_location && !transfer.pickup_location_label) {
                    transfer.pickup_location_label = getTransferLocationLabel(
                        resolveTransferSelectId(day, 'pickup', type, itemType),
                        transfer.pickup_location
                    );
                }
                if (transfer.drop_location && !transfer.drop_location_label) {
                    transfer.drop_location_label = getTransferLocationLabel(
                        resolveTransferSelectId(day, 'drop', type, itemType),
                        transfer.drop_location
                    );
                }
            });
            renderActivityRows();
        }

        function renderAllExtraTransferRows() {
            for (let d = 1; d <= daysCount; d++) {
                renderExtraTransferRows(d);
            }
        }

        function renderExtraTransferRows(dayVal) {
            const wrap = document.getElementById(`extra_transfer_rows_${dayVal}`);
            if (!wrap) return;
            ensureDayTransferExtras(dayVal);
            const rows = dayTransferExtras[dayVal];
            if (!(dayVal > 1 && dayVal < daysCount)) {
                wrap.innerHTML = '';
                return;
            }
            if (!rows.length) {
                wrap.innerHTML = '<small class="text-muted">No extra transfer added.</small>';
                return;
            }
            const selectedCountry = document.getElementById('country').value || '';
            const filteredCities = allCities.filter(c => !selectedCountry || c.country === selectedCountry);
            const cityOptionMarkup = filteredCities.map(c => {
                const cityLabel = c.country ? `${c.name}, ${c.country}` : c.name;
                return `<option value="${escapeHtml(c.name)}">${escapeHtml(cityLabel)}</option>`;
            }).join('');
            const dayOptions = getServiceTransferOptionsForDay(dayVal);
            const optionMarkup = dayOptions.map((x) => {
                const label = formatTransferLocationLabel(x);
                return `<option value="${escapeHtml(x.value)}">${escapeHtml(label)}</option>`;
            }).join('');
            wrap.innerHTML = rows.map((row, idx) => `
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <select class="form-select searchable-select extra-transfer-input" data-extra-day="${dayVal}" data-extra-idx="${idx}" data-extra-key="city" onchange="updateExtraTransferValue(${dayVal}, ${idx}, 'city', this.value)">
                            <option value="">Select city</option>
                            ${cityOptionMarkup}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pickup Location</label>
                        <select class="form-select searchable-select extra-transfer-input" data-extra-day="${dayVal}" data-extra-idx="${idx}" data-extra-key="pickup_location" onchange="updateExtraTransferValue(${dayVal}, ${idx}, 'pickup_location', this.value)">
                            <option value="">Select pickup</option>
                            ${optionMarkup}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Drop Location</label>
                        <select class="form-select searchable-select extra-transfer-input" data-extra-day="${dayVal}" data-extra-idx="${idx}" data-extra-key="drop_location" onchange="updateExtraTransferValue(${dayVal}, ${idx}, 'drop_location', this.value)">
                            <option value="">Select drop</option>
                            ${optionMarkup}
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeExtraTransferRow(${dayVal}, ${idx})">X</button>
                    </div>
                </div>
            `).join('');
            initSearchableSelects(wrap);
            rows.forEach((row, idx) => {
                const selects = wrap.querySelectorAll('.row.mb-2');
                const rowEl = selects[idx];
                if (!rowEl) return;
                const citySel = rowEl.querySelectorAll('select')[0];
                const pickupSel = rowEl.querySelectorAll('select')[1];
                const dropSel = rowEl.querySelectorAll('select')[2];
                const fallbackCityRaw = row.city || getCityNameFromSelect(`transfer_city_select_${dayVal}`) || '';
                const fallbackCity = String(fallbackCityRaw).split(',')[0].trim();
                const pickupValue = String(row.pickup_location || '').trim();
                const dropValue = String(row.drop_location || '').trim();

                if (citySel && fallbackCity) {
                    const hasCity = Array.from(citySel.options).some(opt => String(opt.value).toLowerCase() === fallbackCity.toLowerCase());
                    if (!hasCity) {
                        const option = document.createElement('option');
                        option.value = fallbackCity;
                        option.textContent = fallbackCity;
                        citySel.appendChild(option);
                    }
                    $(citySel).val(fallbackCity).trigger('change.select2');
                } else if (citySel) {
                    $(citySel).val('').trigger('change.select2');
                }

                if (pickupSel && pickupValue) {
                    const pickupLabel = displayTransferLocation(pickupValue, '', row.pickup_location_label);
                    const hasPickup = Array.from(pickupSel.options).some(opt => String(opt.value) === pickupValue);
                    if (!hasPickup) {
                        const option = document.createElement('option');
                        option.value = pickupValue;
                        option.textContent = pickupLabel !== '-' ? pickupLabel : pickupValue;
                        pickupSel.appendChild(option);
                    }
                    $(pickupSel).val(pickupValue).trigger('change.select2');
                } else if (pickupSel) {
                    $(pickupSel).val('').trigger('change.select2');
                }

                if (dropSel && dropValue) {
                    const dropLabel = displayTransferLocation(dropValue, '', row.drop_location_label);
                    const hasDrop = Array.from(dropSel.options).some(opt => String(opt.value) === dropValue);
                    if (!hasDrop) {
                        const option = document.createElement('option');
                        option.value = dropValue;
                        option.textContent = dropLabel !== '-' ? dropLabel : dropValue;
                        dropSel.appendChild(option);
                    }
                    $(dropSel).val(dropValue).trigger('change.select2');
                } else if (dropSel) {
                    $(dropSel).val('').trigger('change.select2');
                }
            });
        }

        /**
         * Ensures the location is selectable and returns the value to select.
         * Reuses an existing option when it refers to the same place (exact value,
         * canonical hotel value, or identical label) so dropdowns never list a
         * hotel/attraction/restaurant twice.
         */
        function ensureTransferLocationOption(selectId, value, fallbackLabel = '') {
            const requested = String(value || '').trim();
            if (!requested) return '';
            const select = document.getElementById(selectId);
            if (!select) return requested;
            const canonical = canonicalTransferOptionValue(requested);
            const options = Array.from(select.options);
            const byValue = options.find(opt => {
                const v = String(opt.value || '').trim();
                return v && (v === requested || canonicalTransferOptionValue(v) === canonical);
            });
            if (byValue) return String(byValue.value);
            const formattedLabel = formatTransferLocationLabel(
                { value: requested, label: fallbackLabel || requested, type: transferTypeFromValue(requested) }
            );
            const labelKey = normalizeTransferLabelKey(formattedLabel);
            const byLabel = labelKey
                ? options.find(opt => String(opt.value || '').trim() && normalizeTransferLabelKey(opt.textContent) === labelKey)
                : null;
            if (byLabel) return String(byLabel.value);
            const option = document.createElement('option');
            option.value = requested;
            option.textContent = formattedLabel;
            select.appendChild(option);
            return requested;
        }

        function ensureSelectOptionByValue(selectId, value, label = '') {
            if (!value) return;
            const select = document.getElementById(selectId);
            if (!select) return;
            const exists = Array.from(select.options).some(opt => String(opt.value) === String(value));
            if (!exists) {
                const option = document.createElement('option');
                option.value = String(value);
                option.textContent = label || String(value);
                select.appendChild(option);
            }
        }

        function applyTransferOptionsPayload(data) {
            transferLocationOptions = Array.isArray(data?.locations) ? data.locations : [];
            serviceTransferOptions = Array.isArray(data?.service_transfer_locations)
                ? data.service_transfer_locations
                : (Array.isArray(data?.arrival_drop_locations)
                    ? data.arrival_drop_locations.filter((x) => String(x?.type || '').toLowerCase() !== 'port')
                    : []);
            transferArrivalPickupOptions = Array.isArray(data?.arrival_pickup_ports)
                ? data.arrival_pickup_ports
                : transferLocationOptions.filter(x => String(x?.type || '').toLowerCase() === 'port'
                    || String(x?.value || '').startsWith('port:'));
            transferArrivalDropOptions = Array.isArray(data?.arrival_drop_locations)
                ? data.arrival_drop_locations
                : transferLocationOptions;
            zoneTransferOptions = Array.isArray(data?.zones) ? data.zones : [];
            mergeTransferLocationLabels(transferLocationOptions);
            mergeTransferLocationLabels(serviceTransferOptions);
            mergeTransferLocationLabels(transferArrivalPickupOptions);
            mergeTransferLocationLabels(transferArrivalDropOptions);
            mergeTransferLocationLabels(zoneTransferOptions);
            const portCanon = String(
                data?.default_port_value ?? data?.default_pickup ?? ''
            ).trim();
            transferDefaults = { defaultPort: portCanon };
        }

        function applyTransferOptionsToDayUI(dayVal, silent = true) {
            const day = parseInt(String(dayVal || 1), 10) || 1;
            const dayOptions = getTransferOptionsForDay(day);
            populateServiceTransferSelectsForDay(day, silent);
            setSelectOptions(`arrival_pickup_select_${day}`, mapTransferSelectOptions(transferArrivalPickupOptions), silent);
            setSelectOptions(`arrival_drop_select_${day}`, mapTransferSelectOptions(transferArrivalDropOptions), silent);
            setSelectOptions(`departure_pickup_select_${day}`, mapTransferSelectOptions(transferArrivalDropOptions.length ? transferArrivalDropOptions : dayOptions), silent);
            setSelectOptions(`departure_drop_select_${day}`, mapTransferSelectOptions(getDepartureDropPortOptions()), silent);
            renderExtraTransferRows(day);
        }

        function applyTransferOptionsToAllDaysWithSameCity(cacheKey, dayVal) {
            const matchedDays = [];
            for (let d = 1; d <= daysCount; d++) {
                if (buildTransferOptionsCacheKey(d) === cacheKey) {
                    matchedDays.push(d);
                }
            }
            if (!matchedDays.length) {
                matchedDays.push(parseInt(String(dayVal || 1), 10) || 1);
            }
            matchedDays.forEach((d) => applyTransferOptionsToDayUI(d, true));
            const needsLegDefaults = matchedDays.some((d) => shouldShowArrivalForDay(d) || shouldShowDepartureForDay(d));
            if (needsLegDefaults) {
                isApplyingTransferDefaults = true;
                applyTransferDefaults();
                isApplyingTransferDefaults = false;
            }
            backfillTransferLabelsOnDayItems();
        }

        async function loadTransferOptionsForCity(dayVal = activeDay, options = {}) {
            const force = options.force === true;
            syncTransferCityFromActivity(dayVal);
            const cacheKey = buildTransferOptionsCacheKey(dayVal);
            if (!force && transferOptionsCache[cacheKey]) {
                applyTransferOptionsPayload(transferOptionsCache[cacheKey]);
                applyTransferOptionsToAllDaysWithSameCity(cacheKey, dayVal);
                return;
            }
            if (!force && transferOptionsInflight[cacheKey]) {
                return transferOptionsInflight[cacheKey];
            }

            const cityName = getCityNameFromSelect(`activity_city_select_${dayVal}`)
                || getCityNameFromSelect(`transfer_city_select_${dayVal}`)
                || getCityNameFromSelect('hotel_city_select');
            if (!String(cityName || '').trim()) {
                return;
            }
            const dmcId = document.getElementById('dmc_id').value || '';
            const masterDmcId = document.getElementById('master_dmc_id')?.value || '';
            const country = document.getElementById('country')?.value || '';
            let url = `${DAY_LEVEL_ROUTES.transferOptions}?dmc_id=${encodeURIComponent(dmcId)}&city_name=${encodeURIComponent(cityName || '')}`;
            if (masterDmcId) {
                url += `&master_dmc_id=${encodeURIComponent(masterDmcId)}`;
            }
            if (country) {
                url += `&country=${encodeURIComponent(country)}`;
            }

            const request = (async () => {
                try {
                    const res = await fetch(url);
                    if (!res.ok) throw new Error('Failed');
                    const data = await res.json();
                    transferOptionsCache[cacheKey] = data;
                    applyTransferOptionsPayload(data);
                } catch (e) {
                    transferOptionsCache[cacheKey] = {
                        locations: [],
                        service_transfer_locations: [],
                        arrival_pickup_ports: [],
                        arrival_drop_locations: [],
                        zones: [],
                    };
                    applyTransferOptionsPayload(transferOptionsCache[cacheKey]);
                } finally {
                    delete transferOptionsInflight[cacheKey];
                }
                applyTransferOptionsToAllDaysWithSameCity(cacheKey, dayVal);
            })();

            transferOptionsInflight[cacheKey] = request;
            return request;
        }

        function invalidateTransferOptionsCacheForDay(dayVal) {
            const key = buildTransferOptionsCacheKey(dayVal);
            delete transferOptionsCache[key];
            delete transferOptionsInflight[key];
        }

        function invalidateTransferOptionsCache() {
            Object.keys(transferOptionsCache).forEach((k) => delete transferOptionsCache[k]);
            Object.keys(transferOptionsInflight).forEach((k) => delete transferOptionsInflight[k]);
        }

        function applyTransferDefaults() {
            if (!isArrivalDepartureTransfersActive() && !itineraryUsesMiddleDayTransfers()) return;
            if (!isArrivalDepartureTransfersActive() && itineraryUsesMiddleDayTransfers()) {
                return;
            }

            const portVal = String(transferDefaults.defaultPort || '').trim();

            for (let d = 1; d <= daysCount; d++) {
                if (shouldShowArrivalForDay(d)) {
                    const arrivalHotel = getArrivalHotelForDay(d);
                    if (portVal) {
                        const arrivalPickupVal = ensureTransferLocationOption(`arrival_pickup_select_${d}`, portVal, labelForStoredTransferLocation(portVal));
                        safeSetSelectValue(`arrival_pickup_select_${d}`, arrivalPickupVal);
                    }
                    const arrivalDropEl = document.getElementById(`arrival_drop_select_${d}`);
                    if (arrivalDropEl && arrivalHotel.value && !String(arrivalDropEl.value || '').trim()) {
                        const arrivalDropVal = ensureTransferLocationOption(`arrival_drop_select_${d}`, arrivalHotel.value, arrivalHotel.label || 'Hotel');
                        safeSetSelectValue(`arrival_drop_select_${d}`, arrivalDropVal);
                    }
                }

                if (shouldShowDepartureForDay(d) && portVal) {
                    const departureHotel = getDepartureHotelForDay(d);
                    const depHotelVal = departureHotel.value;
                    enforceDepartureDropPort(d, true);
                    const depPickupEl = document.getElementById(`departure_pickup_select_${d}`);
                    if (depPickupEl && depHotelVal && !String(depPickupEl.value || '').trim()) {
                        const depPickupVal = ensureTransferLocationOption(
                            `departure_pickup_select_${d}`,
                            depHotelVal,
                            departureHotel.label || labelForStoredTransferLocation(depHotelVal)
                        );
                        safeSetSelectValue(`departure_pickup_select_${d}`, depPickupVal);
                    }
                    const depDropEl = document.getElementById(`departure_drop_select_${d}`);
                    if (depDropEl && !String(depDropEl.value || '').trim()) {
                        const depDropVal = ensureTransferLocationOption(`departure_drop_select_${d}`, portVal, labelForStoredTransferLocation(portVal));
                        safeSetSelectValue(`departure_drop_select_${d}`, depDropVal);
                    }
                }
            }
            if (!isPrefillingActivityForm) {
                fetchTransferZonePricesForVisibleLegs();
            }
        }

        function getLegTransferPickupForDay(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            if (shouldShowArrivalForDay(d)) {
                return String(document.getElementById(`arrival_pickup_select_${d}`)?.value || '').trim();
            }
            if (shouldShowDepartureForDay(d)) {
                return String(document.getElementById(`departure_pickup_select_${d}`)?.value || '').trim();
            }
            return '';
        }

        function getLegTransferDropForDay(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            if (shouldShowArrivalForDay(d)) {
                return String(document.getElementById(`arrival_drop_select_${d}`)?.value || '').trim();
            }
            if (shouldShowDepartureForDay(d)) {
                return String(document.getElementById(`departure_drop_select_${d}`)?.value || '').trim();
            }
            return '';
        }

        function loadCityData() {
            const cityOp = getSelectedOption('city_id');
            if (!cityOp) {
                alert('Please select city.');
                return;
            }

            const cityName = cityOp.dataset.name || cityOp.textContent || '';
            const dmcId = document.getElementById('dmc_id').value;

            fetch(`${DAY_LEVEL_ROUTES.byCity}?city_name=${encodeURIComponent(cityName)}&type=all&dmc_id=${encodeURIComponent(dmcId)}`)
                .then(r => r.json())
                .then(data => {
                    hotelsByRating = data.hotels || {};
                    hotelsFlat = data.hotels_flat || [];
                    attractionsCache = data.attractions || [];
                    restaurantsCache = data.restaurants || [];

                    setSelectOptions('attraction_select', (data.attractions || []).map(x => ({
                        value: x.attraction_id,
                        label: x.name + (x.location ? ` - ${x.location}` : ''),
                        price: x.adult_price || 0
                    })));

                    setSelectOptions('restaurant_select', (data.restaurants || []).map(x => ({
                        value: x.restaurant_id,
                        label: x.name + (x.city ? ` - ${x.city}` : '')
                    })));

                    setSelectOptions('attraction_ticket_select', []);

                    filterHotelOptions();
                    hydrateDayServiceBlocksOptions();
                })
                .catch(() => alert('Failed to load city data.'));
        }

        async function loadTicketsForAttraction() {
            const attractionOp = getSelectedOption('attraction_select');
            if (!attractionOp) {
                setSelectOptions('attraction_ticket_select', []);
                return;
            }

            const dmcId = document.getElementById('dmc_id').value || '';
            try {
                const url = `${DAY_LEVEL_ROUTES.ticketsByAttraction}?attraction_id=${encodeURIComponent(attractionOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error('Failed to fetch tickets');
                const data = await res.json();
                const tickets = Array.isArray(data?.tickets) ? data.tickets : [];
                setSelectOptions('attraction_ticket_select', tickets.map(t => ({
                    value: t.ticket_id,
                    label: t.name || `Ticket ${t.ticket_id}`,
                })));
            } catch (e) {
                setSelectOptions('attraction_ticket_select', []);
            }
        }

        function getDayFromElementId(idValue) {
            const m = String(idValue || '').match(/_(\d+)$/);
            return m ? parseInt(m[1], 10) : 1;
        }

        function getFilteredAdditionalTransfers(dayVal) {
            if (!(dayVal > 1 && dayVal < daysCount)) return [];
            return (dayTransferExtras[dayVal] || []).filter(x => x && (x.pickup_location || x.drop_location));
        }

        function parseDayTransferPriceInput(prefix, dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const el = document.getElementById(`${prefix}_price_${d}`);
            return parseFloat(el?.value || '0') || 0;
        }

        function setDayTransferPriceInput(prefix, dayVal, amount) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const el = document.getElementById(`${prefix}_price_${d}`);
            if (el) {
                el.value = parseFloat(amount ?? 0).toFixed(2);
            }
        }

        function clearTransferLegPriceManualFlag(prefix, dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const el = document.getElementById(`${prefix}_price_${d}`);
            if (el) {
                delete el.dataset.manualPrice;
            }
        }

        async function fetchTransferZonePrice(prefix, dayVal) {
            if (isPrefillingActivityForm) return;
            const legPrefix = String(prefix || '').trim().toLowerCase();
            if (legPrefix !== 'arrival' && legPrefix !== 'departure') return;

            const d = parseInt(String(dayVal || 1), 10) || 1;
            const pickup = String(document.getElementById(`${legPrefix}_pickup_select_${d}`)?.value || '').trim();
            const drop = String(document.getElementById(`${legPrefix}_drop_select_${d}`)?.value || '').trim();
            if (!pickup || !drop) return;

            const priceEl = document.getElementById(`${legPrefix}_price_${d}`);
            if (!priceEl || priceEl.dataset.manualPrice === '1') return;

            const dmcId = document.getElementById('dmc_id')?.value || '';
            const params = new URLSearchParams({
                pickup_value: pickup,
                drop_value: drop,
                transfer_type: 'private',
                dmc_id: String(dmcId),
            });

            try {
                const res = await fetch(`${DAY_LEVEL_ROUTES.transferZonePrice}?${params.toString()}`);
                if (!res.ok) return;
                const data = await res.json();
                if (priceEl.dataset.manualPrice === '1') return;

                if (data?.zone_mapped && parseFloat(data.price) > 0) {
                    setDayTransferPriceInput(legPrefix, d, data.price);
                    priceEl.dataset.zoneAuto = '1';
                } else if (priceEl.dataset.zoneAuto === '1') {
                    setDayTransferPriceInput(legPrefix, d, 0);
                    delete priceEl.dataset.zoneAuto;
                }
            } catch (e) {
                // Zone lookup failed — user can enter price manually.
            }
        }

        function fetchTransferZonePricesForVisibleLegs() {
            for (let d = 1; d <= daysCount; d++) {
                if (shouldShowArrivalForDay(d)) {
                    fetchTransferZonePrice('arrival', d);
                }
                if (shouldShowDepartureForDay(d)) {
                    fetchTransferZonePrice('departure', d);
                }
            }
        }

        function resolveTransferPriceFieldPrefix(transferType, itemType = '') {
            const type = String(transferType || '').trim().toLowerCase();
            const serviceType = String(itemType || '').trim().toLowerCase();
            if (type === 'arrival') return 'arrival';
            if (type === 'departure') return 'departure';
            if (type === 'restaurant transfer') return 'restaurant_transfer';
            if (serviceType === 'restaurant' && type !== 'attraction transfer') return 'restaurant_transfer';
            return 'attraction_transfer';
        }

        function inferTransferTypeFromItem(item) {
            const transfer = item?.transfer && typeof item.transfer === 'object' ? item.transfer : {};
            const savedType = String(transfer.transfer_type || '').trim();
            if (savedType) return savedType;
            const label = String(item?.label || '').trim().toLowerCase();
            if (label === 'day arrival' || label === 'arrival') return 'Arrival';
            if (label === 'day departure' || label === 'departure') return 'Departure';
            if (label === 'restaurant transfer') return 'Restaurant Transfer';
            if (label === 'attraction transfer' || label === 'day transfer') return 'Attraction Transfer';
            if (item?.type === 'restaurant') return 'Restaurant Transfer';
            if (item?.type === 'attraction' && String(item?.id || '').trim()) return 'Attraction Transfer';
            return 'Attraction Transfer';
        }

        function isTransferOnlyActivityItem(item) {
            if (!item) return false;
            const label = String(item.label || '').trim().toLowerCase();
            if (item.type === 'restaurant' && !String(item.id || '').trim()) {
                return label === 'restaurant transfer';
            }
            if (item.type !== 'attraction' || String(item.id || '').trim()) return false;
            return label === 'day transfer'
                || label === 'attraction transfer'
                || label === 'day arrival'
                || label === 'day departure'
                || label === 'arrival'
                || label === 'departure';
        }

        function setDayButtonText(buttonId, text) {
            const el = document.getElementById(buttonId);
            if (el) el.textContent = text;
        }

        function resetDayActivityEditButtons() {
            for (let d = 1; d <= daysCount; d++) {
                setDayButtonText(`attraction_add_btn_${d}`, 'Add Attraction');
                setDayButtonText(`attraction_transfer_add_btn_${d}`, 'Add Transfer');
                setDayButtonText(`restaurant_add_btn_${d}`, 'Add Restaurant');
                setDayButtonText(`restaurant_transfer_add_btn_${d}`, 'Add Transfer');
                setDayButtonText(`arrival_add_btn_${d}`, 'Add Arrival');
                setDayButtonText(`departure_add_btn_${d}`, 'Add Departure');
            }
        }

        function updateDayActivityEditButtons(rowDay, item) {
            resetDayActivityEditButtons();
            const d = parseInt(String(rowDay || 1), 10) || 1;
            if (!item) return;
            const transferType = String(inferTransferTypeFromItem(item) || '').toLowerCase();
            const transferOnly = isTransferOnlyActivityItem(item);
            if (item.type === 'attraction' && String(item.id || '').trim()) {
                setDayButtonText(`attraction_add_btn_${d}`, 'Update Attraction');
                if (item.transfer && (item.transfer.pickup_location || item.transfer.drop_location || parseFloat(item.transfer.cost ?? 0) > 0)) {
                    setDayButtonText(`attraction_transfer_add_btn_${d}`, 'Update Transfer');
                }
            } else if (item.type === 'restaurant' && String(item.id || '').trim()) {
                setDayButtonText(`restaurant_add_btn_${d}`, 'Update Restaurant');
                if (item.transfer && (item.transfer.pickup_location || item.transfer.drop_location || parseFloat(item.transfer.cost ?? 0) > 0)) {
                    setDayButtonText(`restaurant_transfer_add_btn_${d}`, 'Update Transfer');
                }
            } else if (transferOnly) {
                if (transferType === 'arrival') {
                    setDayButtonText(`arrival_add_btn_${d}`, 'Update Arrival');
                } else if (transferType === 'departure') {
                    setDayButtonText(`departure_add_btn_${d}`, 'Update Departure');
                } else if (transferType === 'restaurant transfer') {
                    setDayButtonText(`restaurant_transfer_add_btn_${d}`, 'Update Transfer');
                } else {
                    setDayButtonText(`attraction_transfer_add_btn_${d}`, 'Update Transfer');
                }
            }
        }

        function scrollToDayCard(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const anchor = document.getElementById(`day_items_${d}`)
                || document.getElementById(`activity_city_select_${d}`);
            const card = anchor?.closest('.day-card');
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        async function applyTransferFieldsToDayForm(rowDay, item) {
            const transfer = item?.transfer && typeof item.transfer === 'object' ? item.transfer : {};
            const transferType = inferTransferTypeFromItem(item);
            const itemType = String(item?.type || '').trim();
            const pickupSelectId = resolveTransferSelectId(rowDay, 'pickup', transferType, itemType);
            const dropSelectId = resolveTransferSelectId(rowDay, 'drop', transferType, itemType);
            const transferCost = parseFloat(transfer.cost ?? transfer.transfer_price ?? 0) || 0;
            const hasTransferData = String(transfer.required || '').toLowerCase() === 'yes'
                || !!(transfer.pickup_location || transfer.drop_location || transfer.city)
                || transferCost > 0
                || (Array.isArray(transfer.additional_transfers) && transfer.additional_transfers.length > 0);
            if (!hasTransferData) return;

            const extraRows = Array.isArray(transfer.additional_transfers)
                ? transfer.additional_transfers
                    .map((row) => ({
                        city: String(row?.city || ''),
                        pickup_location: String(row?.pickup_location || ''),
                        drop_location: String(row?.drop_location || ''),
                        pickup_location_label: String(row?.pickup_location_label || ''),
                        drop_location_label: String(row?.drop_location_label || ''),
                    }))
                    .filter((row) => row.pickup_location || row.drop_location || row.city)
                : [];
            dayTransferExtras[rowDay] = extraRows;
            renderExtraTransferRows(rowDay);

            if (transfer.city) {
                const transferCitySelect = document.getElementById(`transfer_city_select_${rowDay}`);
                const transferCityMatch = transferCitySelect
                    ? Array.from(transferCitySelect.options).find(opt => {
                        const nm = String(opt.dataset.name || opt.textContent || '').split(',')[0].trim().toLowerCase();
                        const target = String(transfer.city || '').split(',')[0].trim().toLowerCase();
                        return nm === target;
                    })
                    : null;
                if (transferCityMatch) {
                    isSyncingTransferCity = true;
                    safeSetSelectValueSilent(`transfer_city_select_${rowDay}`, transferCityMatch.value);
                    isSyncingTransferCity = false;
                }
            }

            await loadTransferOptionsForCity(rowDay);

            const tType = String(transferType || '').trim().toLowerCase();
            const pickupResolved = resolveTransferLocationForForm(transfer, 'pickup', pickupSelectId);
            const dropResolved = resolveTransferLocationForForm(transfer, 'drop', dropSelectId);

            if (pickupResolved.value || pickupResolved.label) {
                const pickupVal = ensureTransferLocationOption(pickupSelectId, pickupResolved.value, pickupResolved.label);
                safeSetSelectValue(pickupSelectId, pickupVal);
                if (tType === 'arrival') {
                    const legVal = ensureTransferLocationOption(`arrival_pickup_select_${rowDay}`, pickupResolved.value, pickupResolved.label);
                    safeSetSelectValue(`arrival_pickup_select_${rowDay}`, legVal);
                } else if (tType === 'departure') {
                    const legVal = ensureTransferLocationOption(`departure_pickup_select_${rowDay}`, pickupResolved.value, pickupResolved.label);
                    safeSetSelectValue(`departure_pickup_select_${rowDay}`, legVal);
                }
            }
            if (dropResolved.value || dropResolved.label) {
                const dropVal = ensureTransferLocationOption(dropSelectId, dropResolved.value, dropResolved.label);
                safeSetSelectValue(dropSelectId, dropVal);
                if (tType === 'arrival') {
                    const legVal = ensureTransferLocationOption(`arrival_drop_select_${rowDay}`, dropResolved.value, dropResolved.label);
                    safeSetSelectValue(`arrival_drop_select_${rowDay}`, legVal);
                } else if (tType === 'departure') {
                    const legVal = ensureTransferLocationOption(`departure_drop_select_${rowDay}`, dropResolved.value, dropResolved.label);
                    safeSetSelectValue(`departure_drop_select_${rowDay}`, legVal);
                }
            }

            const pricePrefix = resolveTransferPriceFieldPrefix(transferType, itemType);
            setDayTransferPriceInput(pricePrefix, rowDay, transferCost);
            if (transferCost > 0 && (pricePrefix === 'arrival' || pricePrefix === 'departure')) {
                const savedPriceEl = document.getElementById(`${pricePrefix}_price_${rowDay}`);
                if (savedPriceEl) {
                    savedPriceEl.dataset.manualPrice = '1';
                }
            }
            renderExtraTransferRows(rowDay);
        }

        function getAttractionTransferPayload(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const pickupSelectId = `attraction_transfer_pickup_select_${d}`;
            const dropSelectId = `attraction_transfer_drop_select_${d}`;
            const pickupVal = String(document.getElementById(pickupSelectId)?.value || '').trim();
            const dropVal = String(document.getElementById(dropSelectId)?.value || '').trim();
            const pickupFields = buildStoredTransferLocationFields(pickupSelectId, pickupVal);
            const dropFields = buildStoredTransferLocationFields(dropSelectId, dropVal);
            const extras = getFilteredAdditionalTransfers(d).map((row) => {
                const pickupToken = String(row.pickup_location_value || row.pickup_location || '').trim();
                const dropToken = String(row.drop_location_value || row.drop_location || '').trim();
                const pickupStored = buildStoredTransferLocationFields(pickupSelectId, pickupToken);
                const dropStored = buildStoredTransferLocationFields(dropSelectId, dropToken);
                return {
                    city: String(row.city || ''),
                    pickup_location: pickupStored.location,
                    pickup_location_value: pickupStored.location_value,
                    pickup_location_label: pickupStored.location_label,
                    drop_location: dropStored.location,
                    drop_location_value: dropStored.location_value,
                    drop_location_label: dropStored.location_label,
                };
            });
            const transferPrice = parseDayTransferPriceInput('attraction_transfer', d);
            const hasPrimaryTransfer = !!pickupVal || !!dropVal || transferPrice > 0;
            ensureDayTransferExtras(d);
            return {
                required: (hasPrimaryTransfer || extras.length) ? 'Yes' : 'No',
                transfer_type: 'Attraction Transfer',
                city: getCityNameFromSelect(`activity_city_select_${d}`) || getCityNameFromSelect(`transfer_city_select_${d}`) || '',
                pickup_location: pickupFields.location,
                pickup_location_value: pickupFields.location_value,
                pickup_location_label: pickupFields.location_label,
                drop_location: dropFields.location,
                drop_location_value: dropFields.location_value,
                drop_location_label: dropFields.location_label,
                cost: transferPrice,
                transfer_price: transferPrice,
                additional_transfers: extras,
            };
        }

        function getRestaurantTransferPayload(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const pickupSelectId = `restaurant_transfer_pickup_select_${d}`;
            const dropSelectId = `restaurant_transfer_drop_select_${d}`;
            const pickupVal = String(document.getElementById(pickupSelectId)?.value || '').trim();
            const dropVal = String(document.getElementById(dropSelectId)?.value || '').trim();
            const pickupFields = buildStoredTransferLocationFields(pickupSelectId, pickupVal);
            const dropFields = buildStoredTransferLocationFields(dropSelectId, dropVal);
            const transferPrice = parseDayTransferPriceInput('restaurant_transfer', d);
            return {
                required: (pickupVal || dropVal || transferPrice > 0) ? 'Yes' : 'No',
                transfer_type: 'Restaurant Transfer',
                city: getCityNameFromSelect(`activity_city_select_${d}`) || getCityNameFromSelect(`transfer_city_select_${d}`) || '',
                type: '',
                way: '',
                vehicle_id: '',
                vehicle_name: '',
                pickup_location_id: pickupFields.location_value,
                pickup_location: pickupFields.location,
                pickup_location_value: pickupFields.location_value,
                pickup_location_label: pickupFields.location_label,
                drop_location: dropFields.location,
                drop_location_value: dropFields.location_value,
                drop_location_label: dropFields.location_label,
                drop_location_id: dropFields.location_value,
                cost: transferPrice,
                transfer_price: transferPrice,
                pickup_time: '',
                additional_transfers: [],
            };
        }

        /** @deprecated use getAttractionTransferPayload or getRestaurantTransferPayload */
        function getDayTransferPayload(dayVal) {
            return getAttractionTransferPayload(dayVal);
        }

        function getArrivalTransferPayload(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const pickupSelectId = `arrival_pickup_select_${d}`;
            const dropSelectId = `arrival_drop_select_${d}`;
            const pickupVal = String(document.getElementById(pickupSelectId)?.value || '').trim();
            const dropVal = String(document.getElementById(dropSelectId)?.value || '').trim();
            const pickupFields = buildStoredTransferLocationFields(pickupSelectId, pickupVal);
            const dropFields = buildStoredTransferLocationFields(dropSelectId, dropVal);
            const transferPrice = parseDayTransferPriceInput('arrival', d);
            return {
                required: 'Yes',
                transfer_type: 'Arrival',
                city: getCityNameFromSelect(`transfer_city_select_${d}`) || getCityNameFromSelect(`activity_city_select_${d}`) || '',
                pickup_location: pickupFields.location,
                pickup_location_value: pickupFields.location_value,
                pickup_location_label: pickupFields.location_label,
                pickup_location_id: pickupFields.location_value,
                drop_location: dropFields.location,
                drop_location_value: dropFields.location_value,
                drop_location_label: dropFields.location_label,
                drop_location_id: dropFields.location_value,
                cost: transferPrice,
                transfer_price: transferPrice,
                additional_transfers: []
            };
        }

        function getDepartureTransferPayload(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const pickupSelectId = `departure_pickup_select_${d}`;
            const dropSelectId = `departure_drop_select_${d}`;
            const pickupVal = String(document.getElementById(pickupSelectId)?.value || '').trim();
            const dropFromSelect = String(document.getElementById(dropSelectId)?.value || '').trim();
            const dropVal = transferTypeFromValue(dropFromSelect) === 'port'
                ? dropFromSelect
                : (resolveDefaultPortValue() || dropFromSelect);
            const pickupFields = buildStoredTransferLocationFields(pickupSelectId, pickupVal);
            const dropFields = buildStoredTransferLocationFields(dropSelectId, dropVal);
            const transferPrice = parseDayTransferPriceInput('departure', d);
            return {
                required: 'Yes',
                transfer_type: 'Departure',
                city: getCityNameFromSelect(`transfer_city_select_${d}`) || getCityNameFromSelect(`activity_city_select_${d}`) || '',
                pickup_location: pickupFields.location,
                pickup_location_value: pickupFields.location_value,
                pickup_location_label: pickupFields.location_label,
                pickup_location_id: pickupFields.location_value,
                drop_location: dropFields.location,
                drop_location_value: dropFields.location_value,
                drop_location_label: dropFields.location_label,
                drop_location_id: dropFields.location_value,
                cost: transferPrice,
                transfer_price: transferPrice,
                additional_transfers: []
            };
        }

        function resetDayEntryFields(dayVal, resetServiceSelects = true) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            if (resetServiceSelects) {
                safeSetSelectValue(`attraction_select_${d}`, '');
                setSelectOptions(`attraction_ticket_select_${d}`, []);
                safeSetSelectValue(`restaurant_select_${d}`, '');
                safeSetSelectValue(`restaurant_meal_period_${d}`, '');
                setSelectOptions(`restaurant_meal_select_${d}`, [{ value: '', label: 'Select restaurant first' }]);
            }
            safeSetSelectValue(`attraction_transfer_pickup_select_${d}`, '');
            safeSetSelectValue(`attraction_transfer_drop_select_${d}`, '');
            safeSetSelectValue(`restaurant_transfer_pickup_select_${d}`, '');
            safeSetSelectValue(`restaurant_transfer_drop_select_${d}`, '');
            safeSetSelectValue(`arrival_pickup_select_${d}`, '');
            safeSetSelectValue(`arrival_drop_select_${d}`, '');
            safeSetSelectValue(`departure_pickup_select_${d}`, '');
            safeSetSelectValue(`departure_drop_select_${d}`, '');
            ['attraction_transfer', 'restaurant_transfer', 'arrival', 'departure'].forEach((prefix) => {
                setDayTransferPriceInput(prefix, d, 0);
            });
            const attractionPriceEl = document.getElementById(`attraction_price_${d}`);
            if (attractionPriceEl) attractionPriceEl.value = '0.00';
            const restaurantPriceEl = document.getElementById(`restaurant_price_${d}`);
            if (restaurantPriceEl) restaurantPriceEl.value = '0.00';
            dayTransferExtras[d] = [];
            renderExtraTransferRows(d);
        }

        function mealPeriodValueFromLabel(label) {
            const key = String(label || '').trim().toLowerCase();
            if (key === 'breakfast') return '1';
            if (key === 'lunch') return '2';
            if (key === 'dinner') return '3';
            return '';
        }

        async function loadMealsForRestaurantForDay(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const restaurantOp = getSelectedOption(`restaurant_select_${d}`);
            const mealPeriod = document.getElementById(`restaurant_meal_period_${d}`)?.value || '';

            if (!restaurantOp || !restaurantOp.value) {
                setSelectOptions(`restaurant_meal_select_${d}`, [{ value: '', label: 'Select restaurant first' }]);
                return;
            }

            const dmcId = document.getElementById('dmc_id').value || '';
            let url = `${DAY_LEVEL_ROUTES.mealsByRestaurant}?restaurant_id=${encodeURIComponent(restaurantOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
            if (mealPeriod) {
                url += `&meal_period=${encodeURIComponent(mealPeriod)}`;
            }

            setSelectOptions(`restaurant_meal_select_${d}`, [{ value: '', label: 'Loading meals...' }]);

            try {
                const res = await fetch(url);
                if (!res.ok) {
                    throw new Error('Failed to fetch meals');
                }
                const data = await res.json();
                const meals = Array.isArray(data?.meals) ? data.meals : [];
                if (!meals.length) {
                    setSelectOptions(`restaurant_meal_select_${d}`, [{ value: '', label: 'No meals for this restaurant' }]);
                    return;
                }
                setSelectOptions(`restaurant_meal_select_${d}`, meals.map(meal => {
                    const periodLabel = String(meal.meal_period_label || '').trim();
                    const typeLabel = String(meal.type_label || '').trim();
                    const name = String(meal.name || '').trim();
                    const mealPrice = parseFloat(meal.type) === 1
                        ? (parseFloat(meal.adult_price) || 0)
                        : (parseFloat(meal.price) || parseFloat(meal.adult_price) || 0);
                    const labelParts = [periodLabel, typeLabel, name].filter(Boolean);
                    return {
                        value: String(meal.meal_id ?? ''),
                        label: `${labelParts.join(' · ') || `Meal ${meal.meal_id}`}${mealPrice > 0 ? ` — SGD ${mealPrice.toFixed(2)}` : ''}`,
                        price: mealPrice,
                        adult_price: parseFloat(meal.adult_price) || 0,
                        child_price: parseFloat(meal.child_price) || 0,
                        data_meal_period_label: periodLabel,
                        data_type_label: typeLabel,
                        data_meal_name: name,
                        data_meal_period: String(meal.meal_period ?? ''),
                    };
                }));
                applyRestaurantMealPrice(d);
            } catch (e) {
                setSelectOptions(`restaurant_meal_select_${d}`, [{ value: '', label: 'Error loading meals' }]);
            }
        }

        async function loadTicketsForAttractionForDay(dayVal) {
            const attractionOp = getSelectedOption(`attraction_select_${dayVal}`);
            if (!attractionOp) {
                setSelectOptions(`attraction_ticket_select_${dayVal}`, []);
                return;
            }

            const dmcId = document.getElementById('dmc_id').value || '';
            try {
                const url = `${DAY_LEVEL_ROUTES.ticketsByAttraction}?attraction_id=${encodeURIComponent(attractionOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error('Failed to fetch tickets');
                const data = await res.json();
                const tickets = Array.isArray(data?.tickets) ? data.tickets : [];
                setSelectOptions(`attraction_ticket_select_${dayVal}`, tickets.map(t => {
                    const adult = parseFloat(t.adult_price) || 0;
                    return {
                        value: t.ticket_id,
                        label: `${t.name || `Ticket ${t.ticket_id}`}${adult > 0 ? ` — SGD ${adult.toFixed(2)}` : ''}`,
                        adult_price: adult,
                        child_price: parseFloat(t.child_price) || 0,
                        senior_price: parseFloat(t.senior_adult_price) || 0,
                        price: adult,
                    };
                }));
                applyAttractionTicketPrice(dayVal);
            } catch (e) {
                setSelectOptions(`attraction_ticket_select_${dayVal}`, []);
            }
        }

        const cityServicesDataCache = {};

        /** One fetch per city+DMC; every day block reuses the cached payload. */
        async function fetchCityServicesData(cityName) {
            const dmcId = document.getElementById('dmc_id').value || '';
            const key = `${dmcId}|${String(cityName || '').trim().toLowerCase()}`;
            if (!cityServicesDataCache[key]) {
                cityServicesDataCache[key] = fetch(`${DAY_LEVEL_ROUTES.byCity}?city_name=${encodeURIComponent(cityName)}&type=all&dmc_id=${encodeURIComponent(dmcId)}`)
                    .then(r => r.json())
                    .catch((e) => {
                        delete cityServicesDataCache[key];
                        throw e;
                    });
            }
            return cityServicesDataCache[key];
        }

        async function populateDayServiceOptionsByCity(dayVal, cityName, options = {}) {
            const loadTransfers = options.loadTransfers !== false;
            const silent = options.silent !== false;
            const normalizedCity = String(cityName || '').split(',')[0].trim();
            if (!normalizedCity) {
                setSelectOptions(`attraction_select_${dayVal}`, [], silent);
                setSelectOptions(`restaurant_select_${dayVal}`, [], silent);
                setSelectOptions(`attraction_ticket_select_${dayVal}`, [], silent);
                return;
            }
            try {
                const data = await fetchCityServicesData(normalizedCity);
                setSelectOptions(`attraction_select_${dayVal}`, (data.attractions || []).map(x => {
                    const adult = parseFloat(x.adult_price) || 0;
                    return {
                        value: x.attraction_id,
                        label: `${x.name}${x.location ? ` - ${x.location}` : ''}${adult > 0 ? ` — from SGD ${adult.toFixed(2)}` : ''}`,
                        price: adult,
                        adult_price: adult,
                    };
                }), silent);
                setSelectOptions(`restaurant_select_${dayVal}`, (data.restaurants || []).map(x => ({
                    value: x.restaurant_id,
                    label: x.name + (x.city ? ` - ${x.city}` : '')
                })), silent);
                setSelectOptions(`restaurant_meal_select_${dayVal}`, [{ value: '', label: 'Select restaurant first' }], silent);
                setSelectOptions(`attraction_ticket_select_${dayVal}`, [], silent);
            } catch (e) {
                setSelectOptions(`attraction_select_${dayVal}`, [], silent);
                setSelectOptions(`restaurant_select_${dayVal}`, [], silent);
                setSelectOptions(`attraction_ticket_select_${dayVal}`, [], silent);
            }
            syncTransferCityFromActivity(dayVal);
            if (loadTransfers) {
                await loadTransferOptionsForCity(dayVal);
            }
        }

        async function filterHotelOptions() {
            const category = document.getElementById('hotel_category').value;
            if (!category) {
                setSelectOptions('hotel_select', []);
                return;
            }

            let list = [];
            const dmcId = document.getElementById('dmc_id').value || '';

            // Primary source: backend on-change fetch by rating + hotel-city context.
            // In edit mode, hotel city can differ from currently selected city_id.
            const hotelCityDisplay = getCityNameFromSelect('hotel_city_select');
            const cityOp = getSelectedOption('city_id');
            const selectedCityNameRaw = cityOp?.dataset?.name || cityOp?.textContent || '';
            const selectedCityName = String(selectedCityNameRaw).split(',')[0].trim();
            const cityName = hotelCityDisplay || selectedCityName;
            try {
                const ratingUrl = cityName
                    ? `${DAY_LEVEL_ROUTES.hotelsByRating}?rating=${encodeURIComponent(category)}&city_name=${encodeURIComponent(cityName)}&dmc_id=${encodeURIComponent(dmcId)}`
                    : `${DAY_LEVEL_ROUTES.hotelsByRating}?rating=${encodeURIComponent(category)}&dmc_id=${encodeURIComponent(dmcId)}`;
                const res = await fetch(ratingUrl);
                if (res.ok) {
                    list = await res.json();
                }
            } catch (e) {
                // fallback below
            }

            // Secondary source: by-city hotels payload, then filter by category
            if (!list.length && cityName) {
                try {
                    const resByCity = await fetch(`${DAY_LEVEL_ROUTES.byCity}?city_name=${encodeURIComponent(cityName)}&type=hotels&dmc_id=${encodeURIComponent(dmcId)}`);
                    if (resByCity.ok) {
                        const data = await resByCity.json();
                        const byCityFlat = data.hotels_flat || [];
                        if (byCityFlat.length) {
                            list = byCityFlat.filter(x => String(x.hotel_star_rating ?? '').trim() === String(category).trim());
                        } else {
                            list = (data.hotels && data.hotels[category]) ? data.hotels[category] : [];
                        }
                    }
                } catch (e) {
                    // fallback below
                }
            }

            setSelectOptions('hotel_select', list.map(x => {
                const uniqueId = String(x.hotel_unique_id ?? '').trim();
                const listId = String(x.id ?? '').trim();
                return {
                    value: uniqueId || listId,
                    label: x.name + (x.city ? ` - ${x.city}` : ''),
                    price: x.price || x.base_price || 0,
                    data_hotel_list_id: listId,
                };
            }));

            setSelectOptions('hotel_room_select', [{ value: '', label: 'Select hotel first' }]);
            setSelectOptions('hotel_bed_select', [{ value: '', label: 'Select room first' }]);
            setSelectOptions('hotel_meal_plan', []);
            hotelRoomsCache = [];
            resetHotelPriceFields();
        }

        async function loadRoomsForSelectedHotel() {
            const hotelOp = getSelectedOption('hotel_select');
            if (!hotelOp) {
                hotelRoomsCache = [];
                setSelectOptions('hotel_room_select', [{ value: '', label: 'Select hotel first' }]);
                setSelectOptions('hotel_bed_select', [{ value: '', label: 'Select room first' }]);
                setSelectOptions('hotel_meal_plan', []);
                resetHotelPriceFields();
                toggleHotelMealTypeVisibility();
                return;
            }

            const dmcId = document.getElementById('dmc_id').value || '';
            const url = `${DAY_LEVEL_ROUTES.roomsByHotel}?hotel_unique_id=${encodeURIComponent(hotelOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
            setSelectOptions('hotel_room_select', [{ value: '', label: 'Loading rooms...' }]);
            setSelectOptions('hotel_bed_select', [{ value: '', label: 'Select room first' }]);
            setSelectOptions('hotel_meal_plan', []);

            try {
                const res = await fetch(url);
                if (!res.ok) {
                    throw new Error('Failed to fetch rooms');
                }
                const data = await res.json();
                const rooms = Array.isArray(data) ? data : [];
                hotelRoomsCache = rooms;
                if (!rooms.length) {
                    setSelectOptions('hotel_room_select', [{ value: '', label: 'No rooms available for this hotel' }]);
                    resetHotelPriceFields();
                    return;
                }
                setSelectOptions('hotel_room_select', rooms.map(room => {
                    const roomPrice = parseFloat(room.double_weekday_price) || parseFloat(room.weekday_price) || 0;
                    return {
                        value: String(room.room_id ?? ''),
                        label: `${room.room_type || `Room ${room.room_id}`} — SGD ${roomPrice.toFixed(2)}`,
                        double_weekday_price: room.double_weekday_price,
                        breakfast_price: room.breakfast_price,
                        lunch_price: room.lunch_price,
                        dinner_price: room.dinner_price,
                    };
                }));
                applyHotelRoomBasePrice();
            } catch (e) {
                hotelRoomsCache = [];
                setSelectOptions('hotel_room_select', [{ value: '', label: 'Error loading rooms' }]);
                resetHotelPriceFields();
            }
        }

        async function loadBedsForSelectedRoom() {
            const roomOp = getSelectedOption('hotel_room_select');
            if (!roomOp || !roomOp.value) {
                setSelectOptions('hotel_bed_select', [{ value: '', label: 'Select room first' }]);
                return;
            }

            const dmcId = document.getElementById('dmc_id').value || '';
            const url = `${DAY_LEVEL_ROUTES.bedsByRoom}?room_id=${encodeURIComponent(roomOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
            setSelectOptions('hotel_bed_select', [{ value: '', label: 'Loading beds...' }]);

            try {
                const res = await fetch(url);
                if (!res.ok) {
                    throw new Error('Failed to fetch beds');
                }
                const data = await res.json();
                const beds = Array.isArray(data) ? data : [];
                if (!beds.length) {
                    setSelectOptions('hotel_bed_select', [{ value: '', label: 'No beds available for this room' }]);
                    return;
                }
                setSelectOptions('hotel_bed_select', beds.map(bed => ({
                    value: String(bed.bed_id ?? ''),
                    label: String(bed.bed_type || bed.room_type || `Bed ${bed.bed_id}`),
                })));
            } catch (e) {
                setSelectOptions('hotel_bed_select', [{ value: '', label: 'Error loading beds' }]);
            }
        }

        async function loadMealPlansForSelectedHotel() {
            const hotelOp = getSelectedOption('hotel_select');
            const roomOp = getSelectedOption('hotel_room_select');
            if (!hotelOp) {
                setSelectOptions('hotel_meal_plan', []);
                toggleHotelMealTypeVisibility();
                return;
            }
            if (!roomOp || !roomOp.value) {
                setSelectOptions('hotel_meal_plan', [{ value: '', label: 'Select room first' }]);
                toggleHotelMealTypeVisibility();
                return;
            }

            const dmcId = document.getElementById('dmc_id').value || '';
            const url = `${DAY_LEVEL_ROUTES.mealPlansByHotel}?hotel_unique_id=${encodeURIComponent(hotelOp.value)}&room_id=${encodeURIComponent(roomOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
            try {
                const res = await fetch(url);
                if (!res.ok) {
                    throw new Error('Failed to fetch meal plans');
                }
                const plans = await res.json();
                if (Array.isArray(plans)) {
                    setSelectOptions('hotel_meal_plan', plans);
                } else {
                    setSelectOptions('hotel_meal_plan', []);
                }
                toggleHotelMealTypeVisibility();
            } catch (e) {
                setSelectOptions('hotel_meal_plan', []);
                toggleHotelMealTypeVisibility();
            }
        }

        function toggleHotelMealTypeVisibility() {
            const wrap = document.getElementById('hotel_meal_type_wrap');
            const mealTypeSel = document.getElementById('hotel_meal_type');
            const selectedOp = getSelectedOption('hotel_meal_plan');
            const selectedText = String(selectedOp?.textContent || '').trim().toLowerCase();
            const selectedValue = String(selectedOp?.value || '').trim().toLowerCase();
            const isRoomOnly = selectedText.includes('room only') || selectedValue.includes('room only');

            if (wrap) {
                wrap.style.display = isRoomOnly ? 'none' : '';
            }
            if (isRoomOnly && mealTypeSel) {
                safeSetSelectValue('hotel_meal_type', '');
            }
            applyHotelMealPlanPrices();
        }

        function parsePriceInput(id) {
            const val = parseFloat(document.getElementById(id)?.value || '0');
            return Number.isFinite(val) ? val : 0;
        }

        function setPriceInput(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            const num = parseFloat(value);
            el.value = Number.isFinite(num) ? num.toFixed(2) : '0.00';
        }

        function getSelectedRoomPricing() {
            const roomOp = getSelectedOption('hotel_room_select');
            if (!roomOp || !roomOp.value) return null;
            const room = (hotelRoomsCache || []).find(r => String(r.room_id) === String(roomOp.value));
            return room || null;
        }

        function applyHotelRoomBasePrice() {
            const room = getSelectedRoomPricing();
            if (!room) {
                setPriceInput('hotel_room_price', 0);
                return;
            }
            const roomPrice = parseFloat(room.double_weekday_price) || parseFloat(room.weekday_price) || 0;
            setPriceInput('hotel_room_price', roomPrice);
            applyHotelMealPlanPrices();
            updateHotelPriceTotal();
        }

        function applyHotelMealPlanPrices() {
            const room = getSelectedRoomPricing();
            const mealOp = getSelectedOption('hotel_meal_plan');
            const plan = String(mealOp?.value || mealOp?.textContent || '').toLowerCase();
            const breakfast = parseFloat(room?.breakfast_price) || 0;
            const lunch = parseFloat(room?.lunch_price) || 0;
            const dinner = parseFloat(room?.dinner_price) || 0;

            setPriceInput('hotel_breakfast_price', plan.includes('breakfast') ? breakfast : 0);
            setPriceInput('hotel_lunch_price', plan.includes('lunch') ? lunch : 0);
            setPriceInput('hotel_dinner_price', plan.includes('dinner') ? dinner : 0);
            updateHotelPriceTotal();
        }

        function updateHotelPriceTotal() {
            const total = parsePriceInput('hotel_room_price')
                + parsePriceInput('hotel_breakfast_price')
                + parsePriceInput('hotel_lunch_price')
                + parsePriceInput('hotel_dinner_price');
            const badge = document.getElementById('hotel_price_total_badge');
            if (badge) {
                badge.textContent = `Total: SGD ${total.toFixed(2)}`;
            }
        }

        function resetHotelPriceFields() {
            ['hotel_room_price', 'hotel_breakfast_price', 'hotel_lunch_price', 'hotel_dinner_price'].forEach(id => setPriceInput(id, 0));
            updateHotelPriceTotal();
        }

        function getHotelPerNightPrice(row) {
            const room = parseFloat(row?.room_price ?? 0) || 0;
            const breakfast = parseFloat(row?.breakfast_price ?? 0) || 0;
            const lunch = parseFloat(row?.lunch_price ?? 0) || 0;
            const dinner = parseFloat(row?.dinner_price ?? 0) || 0;
            const total = room + breakfast + lunch + dinner;
            if (total > 0) return total;
            return parseFloat(row?.price ?? 0) || 0;
        }

        function getHotelStayTotalPrice(row) {
            const perNight = getHotelPerNightPrice(row);
            const nights = Math.max(1, parseInt(String(row?.night || 1), 10) || 1);
            return perNight * nights;
        }

        function getActivityItemServicePrice(item) {
            if (!item) return 0;
            if (item.type === 'restaurant' && String(item.id || '').trim()) {
                return parseFloat(item.meal_price ?? item.price ?? item.meal?.meal_price ?? 0) || 0;
            }
            if (item.type === 'attraction' && String(item.id || '').trim()) {
                return parseFloat(item.ticket_price ?? item.price ?? 0) || 0;
            }
            return 0;
        }

        function getActivityItemTransferPrice(item) {
            const transfer = item?.transfer && typeof item.transfer === 'object' ? item.transfer : {};
            return parseFloat(transfer.cost ?? transfer.transfer_price ?? 0) || 0;
        }

        function getActivityItemTotalPrice(item) {
            return getActivityItemServicePrice(item) + getActivityItemTransferPrice(item);
        }

        function getHotelCheckoutDay(row) {
            return hotelStayEndDay(row);
        }

        function hotelCoversDay(row, dayNum) {
            const d = parseInt(String(dayNum || 0), 10) || 0;
            if (d < 1) return false;
            const start = parseInt(String(row?.day || 1), 10) || 1;
            return d >= start && d <= getHotelCheckoutDay(row);
        }

        function getHotelStayDayNumbers(row) {
            const start = parseInt(String(row?.day || 1), 10) || 1;
            const checkout = getHotelCheckoutDay(row);
            const days = [];
            for (let d = start; d <= checkout; d++) {
                days.push(d);
            }
            return days;
        }

        function formatHotelStayDayLabel(row) {
            const days = getHotelStayDayNumbers(row);
            if (!days.length) return '-';
            return days.map((d) => `Day${d}`).join(', ');
        }

        function formatHotelStayDayBadgesHtml(row) {
            const days = getHotelStayDayNumbers(row);
            if (!days.length) {
                return '<span class="badge bg-light text-dark border">-</span>';
            }
            if (days.length === 1) {
                return `<span class="badge bg-light text-dark border">${escapeHtml(`Day${days[0]}`)}</span>`;
            }
            return days.map((d) =>
                `<span class="badge bg-light text-dark border me-1 mb-1">${escapeHtml(`Day${d}`)}</span>`
            ).join('');
        }

        function formatHotelPriceBreakdown(row) {
            const parts = [];
            const room = parseFloat(row?.room_price ?? 0) || 0;
            const breakfast = parseFloat(row?.breakfast_price ?? 0) || 0;
            const lunch = parseFloat(row?.lunch_price ?? 0) || 0;
            const dinner = parseFloat(row?.dinner_price ?? 0) || 0;
            if (room > 0) parts.push(`Room ${room.toFixed(2)}`);
            if (breakfast > 0) parts.push(`B ${breakfast.toFixed(2)}`);
            if (lunch > 0) parts.push(`L ${lunch.toFixed(2)}`);
            if (dinner > 0) parts.push(`D ${dinner.toFixed(2)}`);
            return parts.length ? parts.join(' + ') : '—';
        }

        function formatHotelRoomMealSummary(row) {
            const room = String(row?.room_type || row?.room_id || '-').replace(/\s*—\s*SGD.*$/i, '').trim();
            const meal = [row?.meal_plan, row?.meal_type].filter(Boolean).join(' · ');
            return meal ? `${room} · ${meal}` : room;
        }

        async function loadHotelCityServices() {
            const cityName = getCityNameFromSelect('hotel_city_select');
            if (!cityName) {
                setSelectOptions('hotel_select', []);
                setSelectOptions('hotel_category', document.getElementById('hotel_category') ? Array.from(document.getElementById('hotel_category').options).slice(1).map(o => ({ value: o.value, label: o.textContent })) : []);
                return;
            }
            const dmcId = document.getElementById('dmc_id').value || '';
            try {
                const res = await fetch(`${DAY_LEVEL_ROUTES.byCity}?city_name=${encodeURIComponent(cityName)}&type=hotels&dmc_id=${encodeURIComponent(dmcId)}`);
                if (res.ok) {
                    const data = await res.json();
                    hotelsFlat = data.hotels_flat || [];
                    hotelsByRating = data.hotels || {};
                }
            } catch (e) {
                // keep existing cache
            }
            await filterHotelOptions();
        }

        function applyAttractionTicketPrice(dayVal) {
            const ticketOp = getSelectedOption(`attraction_ticket_select_${dayVal}`);
            const priceEl = document.getElementById(`attraction_price_${dayVal}`);
            if (!priceEl || !ticketOp) return;
            const adult = parseFloat(ticketOp.dataset?.adultPrice || ticketOp.dataset?.price || '0');
            if (Number.isFinite(adult) && adult > 0) {
                priceEl.value = adult.toFixed(2);
            }
        }

        function applyRestaurantMealPrice(dayVal) {
            const mealOp = getSelectedOption(`restaurant_meal_select_${dayVal}`);
            const priceEl = document.getElementById(`restaurant_price_${dayVal}`);
            if (!priceEl || !mealOp) return;
            const mealPrice = parseFloat(mealOp.dataset?.price || mealOp.dataset?.adultPrice || '0');
            if (Number.isFinite(mealPrice) && mealPrice > 0) {
                priceEl.value = mealPrice.toFixed(2);
            }
        }

        async function resolveHotelCategoryForEdit(hotelId, cityName) {
            if (!hotelId) return '';

            // First try current cache
            const hotelKey = String(hotelId);
            const cached = (hotelsFlat || []).find(h =>
                String(h.hotel_unique_id || '') === hotelKey || String(h.id || '') === hotelKey
            );
            if (cached && cached.hotel_star_rating !== undefined && cached.hotel_star_rating !== null) {
                return String(cached.hotel_star_rating);
            }

            // Fallback: fetch city hotels and resolve star rating from record
            if (!cityName) return '';
            const dmcId = document.getElementById('dmc_id').value || '';
            try {
                const res = await fetch(`${DAY_LEVEL_ROUTES.byCity}?city_name=${encodeURIComponent(cityName)}&type=hotels&dmc_id=${encodeURIComponent(dmcId)}`);
                if (!res.ok) return '';
                const data = await res.json();
                const flat = Array.isArray(data?.hotels_flat) ? data.hotels_flat : [];
                const match = flat.find(h =>
                    String(h.hotel_unique_id || '') === hotelKey || String(h.id || '') === hotelKey
                );
                if (!match) return '';
                return String(match.hotel_star_rating ?? '');
            } catch (e) {
                return '';
            }
        }

        function resetHotelFields() {
            // Reset only the add/edit controls; keep already-added hotel rows intact.
            $('#hotel_city_select').val('').trigger('change.select2');
            $('#hotel_category').val('').trigger('change.select2');
            setSelectOptions('hotel_select', []);
            setSelectOptions('hotel_day', []);
            setSelectOptions('hotel_room_select', [{ value: '', label: 'Select hotel first' }]);
            setSelectOptions('hotel_bed_select', [{ value: '', label: 'Select room first' }]);
            setSelectOptions('hotel_meal_plan', []);
            hotelRoomsCache = [];
            safeSetSelectValue('hotel_meal_type', '');
            document.getElementById('hotel_priority').value = '1';
            document.getElementById('hotelAddBtn').textContent = 'Add Hotel';
            editingHotelIndex = null;
            toggleHotelMealTypeVisibility();
            toggleHotelTransferFields();
        }

        function addHotel() {
            const hotelOp = getSelectedOption('hotel_select');
            const categoryOp = getSelectedOption('hotel_category');
            const roomOp = getSelectedOption('hotel_room_select');
            const bedOp = getSelectedOption('hotel_bed_select');
            const mealPlanOp = getSelectedOption('hotel_meal_plan');
            if (!hotelOp || !categoryOp) {
                alert('Select hotel category and hotel.');
                return;
            }
            if (!roomOp || !roomOp.value) {
                alert('Select a room for this hotel.');
                return;
            }

            const cityName = getCityNameFromSelect('hotel_city_select') || '';
            const span = getAvailableHotelStaySpan(cityName, hotelOp.value);
            if (!span) {
                alert('Please configure Multi City for the selected hotel city first.');
                return;
            }

            const maxNights = getMaxNightsForHotelDropdown(span);
            const nightFromForm = parseInt(document.getElementById('hotel_day')?.value || String(maxNights), 10) || maxNights;
            const selectedNight = Math.max(1, Math.min(maxNights, nightFromForm));
            const derivedDay = span.day_in;
            const selectedTransferCity = getCityNameFromSelect(`transfer_city_select_${derivedDay}`) || cityName || '';
            const payload = {
                cat: categoryOp.value,
                cat_label: categoryOp.textContent,
                hotel_id: hotelOp.value,
                hotel_name: hotelOp.textContent,
                city_name: cityName,
                day: derivedDay,
                night: selectedNight,
                room_id: roomOp.value,
                room_type: roomOp.textContent || '',
                bed_id: bedOp?.value || '',
                bed_type: bedOp?.textContent || '',
                meal_plan: mealPlanOp?.value || '',
                meal_type: document.getElementById('hotel_meal_type')?.value || '',
                guide_required: 'No',
                arrival_departure: 'No',
                arrival_departure_type: '',
                transfer_city: selectedTransferCity,
                transfer_pickup: isArrivalDepartureTransfersActive() ? getLegTransferPickupForDay(derivedDay) : '',
                transfer_drop: isArrivalDepartureTransfersActive() ? getLegTransferDropForDay(derivedDay) : '',
                room_price: parsePriceInput('hotel_room_price'),
                breakfast_price: parsePriceInput('hotel_breakfast_price'),
                lunch_price: parsePriceInput('hotel_lunch_price'),
                dinner_price: parsePriceInput('hotel_dinner_price'),
                price: parsePriceInput('hotel_room_price') + parsePriceInput('hotel_breakfast_price') + parsePriceInput('hotel_lunch_price') + parsePriceInput('hotel_dinner_price'),
                priority: parseInt(document.getElementById('hotel_priority').value || '1', 10)
            };

            if (editingHotelIndex !== null && hotels[editingHotelIndex]) {
                const overlapIndex = hotels.findIndex((x, idx) =>
                    idx !== editingHotelIndex &&
                    String(x.hotel_id) === String(payload.hotel_id) &&
                    hotelStaysOverlap(x, payload)
                );
                if (overlapIndex !== -1) {
                    alert('This hotel already has an overlapping stay for the selected date range.');
                    return;
                }
                hotels[editingHotelIndex] = { ...payload };
                editingHotelIndex = null;
                document.getElementById('hotelAddBtn').textContent = 'Add Hotel';
            } else {
                const overlapIndex = hotels.findIndex((x) =>
                    String(x.hotel_id) === String(payload.hotel_id) &&
                    hotelStaysOverlap(x, payload)
                );
                if (overlapIndex !== -1) {
                    alert('This hotel already has an overlapping stay for the selected date range.');
                    return;
                } else {
                    hotels.push({ ...payload });
                }
            }
            renderHotelRows();
            updateAllDayTransferVisibility();
            applyTransferDefaults();
            resetHotelFields();
        }

        function removeHotel(idx) {
            hotels.splice(idx, 1);
            if (editingHotelIndex === idx) {
                editingHotelIndex = null;
                document.getElementById('hotelAddBtn').textContent = 'Add Hotel';
            }
            renderHotelRows();
            toggleHotelTransferFields();
        }

        async function editHotel(idx) {
            const x = hotels[idx];
            if (!x) return;
            editingHotelIndex = idx;
            isPrefillingHotelForm = true;
            initDays();
            // initDays() resets any "day" UI state; re-apply Multi City as the source of truth.
            if (multiCityPlans.length) syncDayCitySelectorsFromMultiCity();
            const derivedDay = x.day ?? getNightsSpanForCity(String(x.city_name || '').trim())?.day_in ?? 1;
            activeDay = Math.max(1, Math.min(daysCount, parseInt(String(derivedDay || 1), 10) || 1));
            renderHotelRows();
            renderActivityRows();
            const citySelect = document.getElementById('hotel_city_select');
            const cityMatch = Array.from(citySelect.options).find(opt => {
                const nm = String(opt.dataset.name || opt.textContent || '').split(',')[0].trim().toLowerCase();
                const target = String(x.city_name || '').split(',')[0].trim().toLowerCase();
                return nm === target;
            });
            if (cityMatch) {
                safeSetSelectValue('hotel_city_select', cityMatch.value);
            }
            // Nights dropdown depends on hotel city + Multi City span.
            syncHotelDayDropdownWithMultiCity();
            safeSetSelectValue('hotel_day', String(x.night || 1));
            const resolvedCategory = (x.cat && String(x.cat).trim() !== '')
                ? String(x.cat)
                : await resolveHotelCategoryForEdit(x.hotel_id, x.city_name || getCityNameFromSelect('hotel_city_select'));
            safeSetSelectValue('hotel_category', resolvedCategory);
            await filterHotelOptions();
            safeSetSelectValue('hotel_select', resolveHotelUniqueIdForPayload(x.hotel_id) || '');
            await loadRoomsForSelectedHotel();
            safeSetSelectValue('hotel_room_select', x.room_id || '');
            await loadBedsForSelectedRoom();
            safeSetSelectValue('hotel_bed_select', x.bed_id || '');
            await loadMealPlansForSelectedHotel();
            safeSetSelectValue('hotel_meal_plan', x.meal_plan || '');
            toggleHotelMealTypeVisibility();

            safeSetSelectValue('hotel_meal_type', x.meal_type || '');
            setPriceInput('hotel_room_price', x.room_price ?? x.price ?? 0);
            setPriceInput('hotel_breakfast_price', x.breakfast_price ?? 0);
            setPriceInput('hotel_lunch_price', x.lunch_price ?? 0);
            setPriceInput('hotel_dinner_price', x.dinner_price ?? 0);
            updateHotelPriceTotal();
            document.getElementById('hotel_priority').value = String(x.priority || 1);
            toggleHotelTransferFields();
            const shouldLoadXferOpts = hotelsHaveArrivalDepartureTransferSaved();
            if (shouldLoadXferOpts) {
                // When Multi City is configured, transfer city selectors are locked by day mapping.
                if (!multiCityPlans.length && x.transfer_city) {
                    const transferCitySelect = document.getElementById(`transfer_city_select_${activeDay}`);
                    const transferCityMatch = Array.from(transferCitySelect.options).find(opt => {
                        const nm = String(opt.dataset.name || opt.textContent || '').split(',')[0].trim().toLowerCase();
                        const target = String(x.transfer_city || '').split(',')[0].trim().toLowerCase();
                        return nm === target;
                    });
                    if (transferCityMatch) {
                        isSyncingTransferCity = true;
                        safeSetSelectValueSilent(`transfer_city_select_${activeDay}`, transferCityMatch.value);
                        isSyncingTransferCity = false;
                    }
                }
                await loadTransferOptionsForCity(activeDay);
                const pickupSelectId = shouldShowArrivalForDay(activeDay)
                    ? `arrival_pickup_select_${activeDay}`
                    : (shouldShowDepartureForDay(activeDay) ? `departure_pickup_select_${activeDay}` : '');
                const dropSelectId = shouldShowArrivalForDay(activeDay)
                    ? `arrival_drop_select_${activeDay}`
                    : (shouldShowDepartureForDay(activeDay) ? `departure_drop_select_${activeDay}` : '');
                if (x.transfer_pickup && pickupSelectId) {
                    const hotelPickupLabel = labelForStoredTransferLocation(x.transfer_pickup) || x.transfer_pickup;
                    const pickupVal = ensureTransferLocationOption(pickupSelectId, x.transfer_pickup, hotelPickupLabel);
                    safeSetSelectValue(pickupSelectId, pickupVal);
                }
                if (x.transfer_drop && dropSelectId) {
                    const dropVal = ensureTransferLocationOption(dropSelectId, x.transfer_drop, x.transfer_drop);
                    safeSetSelectValue(dropSelectId, dropVal);
                }
            }
            document.getElementById('hotelAddBtn').textContent = 'Update Hotel';
            isPrefillingHotelForm = false;
        }

        function renderHotelRows() {
            const body = document.getElementById('hotelRows');
            const current = [...hotels].sort((a, b) => (a.day || 0) - (b.day || 0));
            if (!current.length) {
                body.innerHTML = '<tr><td colspan="8" class="text-muted">No hotels added</td></tr>';
            } else {
                body.innerHTML = current.map((x) => {
                    const idx = hotels.indexOf(x);
                    const perNight = getHotelPerNightPrice(x);
                    const stayTotal = getHotelStayTotalPrice(x);
                    const hotelLabel = String(x.hotel_name || '-').replace(/\s*-\s*[^-]+$/i, '').trim() || x.hotel_name || '-';
                    return `
                        <tr>
                            <td><div class="d-flex flex-wrap gap-1">${formatHotelStayDayBadgesHtml(x)}</div></td>
                            <td>${escapeHtml(x.city_name || '-')}</td>
                            <td>
                                <div class="hotel-cell-title">${escapeHtml(hotelLabel)}</div>
                                <div class="hotel-cell-meta">${escapeHtml(x.cat_label || '')}</div>
                            </td>
                            <td>${escapeHtml(String(x.night || 1))}</td>
                            <td>
                                <div class="hotel-cell-title">${escapeHtml(formatHotelRoomMealSummary(x))}</div>
                                ${x.bed_type ? `<div class="hotel-cell-meta">Bed: ${escapeHtml(x.bed_type)}</div>` : ''}
                            </td>
                            <td class="text-end">
                                <div class="hotel-price-night">SGD ${perNight.toFixed(2)}</div>
                                <div class="hotel-price-breakdown">${escapeHtml(formatHotelPriceBreakdown(x))}</div>
                            </td>
                            <td class="text-end">
                                <div class="hotel-price-total">SGD ${stayTotal.toFixed(2)}</div>
                                <div class="hotel-price-breakdown">${Math.max(1, parseInt(String(x.night || 1), 10) || 1)} night(s)</div>
                            </td>
                            <td class="action-cell text-end">
                                <span class="action-buttons">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-icon" onclick="editHotel(${idx})" title="Edit" aria-label="Edit">${actionIcon('edit')}</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeHotel(${idx})" title="Remove" aria-label="Remove">${actionIcon('remove')}</button>
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
            document.getElementById('hotels_json').value = JSON.stringify(hotels);
            updateAllDayTransferVisibility();
            for (let d = 1; d <= daysCount; d++) {
                populateServiceTransferSelectsForDay(d);
            }
        }

        function isTransferOnlyPlaceholderItem(item) {
            if (!item || String(item.id || '').trim()) return false;
            const label = String(item.label || '').toLowerCase();
            if (item.type === 'restaurant') {
                return label === 'restaurant transfer';
            }
            return label === 'day transfer' || label === 'attraction transfer';
        }

        function removeTransferOnlyPlaceholderForDay(dayVal, preserveIndex = null) {
            const targetDay = parseInt(String(dayVal || 0), 10) || 0;
            if (!targetDay) return;
            dayItems = (Array.isArray(dayItems) ? dayItems : []).filter((x, idx) => {
                if (preserveIndex !== null && idx === preserveIndex) return true;
                if (parseInt(String(x.day || 0), 10) !== targetDay) return true;
                return !isTransferOnlyPlaceholderItem(x);
            });
        }

        function syncTransferOnlyPlaceholderForDay(dayVal, shouldRender = true) {
            const d = parseInt(String(dayVal || 0), 10) || 0;
            if (!d) return;
            if (isPrefillingActivityForm) return;

            const hasRealItems = (Array.isArray(dayItems) ? dayItems : []).some(x => {
                if (parseInt(String(x.day || 0), 10) !== d) return false;
                if (x.type === 'restaurant') return true;
                if (x.type === 'attraction' && String(x.id || '').trim()) return true;
                return false;
            });

            const placeholderIdx = (Array.isArray(dayItems) ? dayItems : []).findIndex(x => {
                return parseInt(String(x.day || 0), 10) === d && isTransferOnlyPlaceholderItem(x);
            });

            const transferPayload = getAttractionTransferPayload(d);
            const hasTransferData = transferPayload.required === 'Yes'
                || String(transferPayload.city || '').trim()
                || String(transferPayload.pickup_location || '').trim()
                || String(transferPayload.pickup_location_value || '').trim()
                || String(transferPayload.drop_location || '').trim()
                || String(transferPayload.drop_location_value || '').trim()
                || parseFloat(transferPayload.cost ?? 0) > 0
                || (Array.isArray(transferPayload.additional_transfers) && transferPayload.additional_transfers.length > 0);

            if (hasRealItems || !hasTransferData) {
                if (placeholderIdx !== -1) {
                    dayItems.splice(placeholderIdx, 1);
                }
                if (shouldRender) renderActivityRows();
                return;
            }

            const payload = {
                day: d,
                type: 'attraction',
                id: '',
                label: 'Day Transfer',
                city_name: getCityNameFromSelect(`activity_city_select_${d}`) || transferPayload.city || '',
                ticket_id: '',
                ticket_name: '',
                transfer: transferPayload
            };

            if (placeholderIdx !== -1) {
                dayItems[placeholderIdx] = payload;
            } else {
                dayItems.push(payload);
            }
            if (shouldRender) renderActivityRows();
        }

        function addAttractionItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const selOp = getSelectedOption(`attraction_select_${dayVal}`);
            const ticketOp = getSelectedOption(`attraction_ticket_select_${dayVal}`);
            if (!selOp) {
                alert('Select attraction first.');
                return;
            }

            const ticketPrice = parseFloat(document.getElementById(`attraction_price_${dayVal}`)?.value || '0') || 0;
            const payload = {
                day: normalizedDay,
                type: 'attraction',
                id: selOp.value,
                label: selOp.textContent,
                city_name: getCityNameFromSelect(`activity_city_select_${dayVal}`) || '',
                ticket_id: ticketOp?.value || '',
                ticket_name: ticketOp?.textContent || '',
                ticket_price: ticketPrice,
                price: ticketPrice,
                transfer: getAttractionTransferPayload(normalizedDay)
            };
            if (editingActivityIndex !== null && dayItems[editingActivityIndex]) {
                dayItems[editingActivityIndex] = payload;
                editingActivityIndex = null;
            } else {
                dayItems.push(payload);
            }
            renderActivityRows();
            resetDayEntryFields(normalizedDay);
            resetDayActivityEditButtons();
        }

        function addDayEntryForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const attractionOp = getSelectedOption(`attraction_select_${normalizedDay}`);
            const restaurantOp = getSelectedOption(`restaurant_select_${normalizedDay}`);
            const attractionTransferPayload = getAttractionTransferPayload(normalizedDay);
            const restaurantTransferPayload = getRestaurantTransferPayload(normalizedDay);
            const hasAttractionTransfer = attractionTransferPayload.required === 'Yes'
                || !!attractionTransferPayload.pickup_location
                || !!attractionTransferPayload.drop_location
                || parseFloat(attractionTransferPayload.cost ?? 0) > 0
                || (Array.isArray(attractionTransferPayload.additional_transfers) && attractionTransferPayload.additional_transfers.length > 0);
            const hasRestaurantTransfer = restaurantTransferPayload.required === 'Yes'
                || !!restaurantTransferPayload.pickup_location
                || !!restaurantTransferPayload.drop_location
                || parseFloat(restaurantTransferPayload.cost ?? 0) > 0;

            let addedCount = 0;
            if (attractionOp) {
                const before = dayItems.length;
                addAttractionItemForDay(normalizedDay);
                if (dayItems.length !== before) {
                    addedCount++;
                }
            }

            if (restaurantOp) {
                const before = dayItems.length;
                addRestaurantItemForDay(normalizedDay);
                if (dayItems.length !== before) {
                    addedCount++;
                }
            }

            if (!attractionOp && hasAttractionTransfer) {
                if (addAttractionTransferItemForDay(normalizedDay)) addedCount++;
            }
            if (!restaurantOp && hasRestaurantTransfer) {
                if (addRestaurantTransferItemForDay(normalizedDay)) addedCount++;
            }

            if (!addedCount) {
                alert('Select at least one entry (Attraction or Restaurant), or fill transfer details before adding.');
            }
        }

        function addRestaurantItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const selOp = getSelectedOption(`restaurant_select_${dayVal}`);
            if (!selOp) {
                alert('Select restaurant first.');
                return;
            }
            const mealOp = getSelectedOption(`restaurant_meal_select_${dayVal}`);
            if (!mealOp || !mealOp.value) {
                alert('Select a meal for this restaurant.');
                return;
            }

            const periodOp = getSelectedOption(`restaurant_meal_period_${dayVal}`);
            const mealPrice = parseFloat(document.getElementById(`restaurant_price_${dayVal}`)?.value || '0') || 0;
            const payload = {
                day: normalizedDay,
                type: 'restaurant',
                id: selOp.value,
                label: selOp.textContent,
                city_name: getCityNameFromSelect(`activity_city_select_${dayVal}`) || '',
                meal_price: mealPrice,
                price: mealPrice,
                meal: {
                    meal_id: mealOp.value,
                    meal_type: (periodOp?.textContent || mealOp.dataset?.mealPeriodLabel || '').trim(),
                    dish: (mealOp.dataset?.typeLabel || '').trim(),
                    meal_name: (mealOp.dataset?.mealName || mealOp.textContent || '').trim(),
                    meal_period: periodOp?.value || mealOp.dataset?.mealPeriod || '',
                    meal_price: mealPrice,
                    time_slot: ''
                },
                transfer: getRestaurantTransferPayload(normalizedDay)
            };
            if (editingActivityIndex !== null && dayItems[editingActivityIndex]) {
                dayItems[editingActivityIndex] = payload;
                editingActivityIndex = null;
            } else {
                dayItems.push(payload);
            }
            renderActivityRows();
            resetDayEntryFields(normalizedDay);
            resetDayActivityEditButtons();
        }

        const dayGroupMsgTimers = {};

        function showDayGroupMessage(dayVal, group, text) {
            const el = document.getElementById(`${group}_group_msg_${dayVal}`);
            if (!el) {
                alert(text);
                return;
            }
            el.textContent = text;
            el.classList.add('show');
            const key = `${group}_${dayVal}`;
            if (dayGroupMsgTimers[key]) clearTimeout(dayGroupMsgTimers[key]);
            dayGroupMsgTimers[key] = setTimeout(() => {
                el.classList.remove('show');
            }, 4000);
        }

        function addAttractionTransferItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const hasAttraction = !!getSelectedOption(`attraction_select_${normalizedDay}`);
            const editingItem = editingActivityIndex !== null ? dayItems[editingActivityIndex] : null;
            const editingTransferOnly = !!(editingItem && !String(editingItem.id || '').trim());
            if (!hasAttraction && !editingTransferOnly) {
                showDayGroupMessage(normalizedDay, 'attraction', 'Please choose an attraction first, then add its transfer.');
                return false;
            }
            if (hasAttraction) {
                // Attraction + transfer are stored as one combined entry.
                addAttractionItemForDay(normalizedDay);
                return true;
            }
            // Legacy transfer-only row being edited.
            const transferPayload = getAttractionTransferPayload(normalizedDay);
            return addTransferLikeItemForDay(normalizedDay, 'Attraction Transfer', transferPayload, 'attraction');
        }

        function addRestaurantTransferItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const hasRestaurant = !!getSelectedOption(`restaurant_select_${normalizedDay}`);
            const editingItem = editingActivityIndex !== null ? dayItems[editingActivityIndex] : null;
            const editingTransferOnly = !!(editingItem && !String(editingItem.id || '').trim());
            if (!hasRestaurant && !editingTransferOnly) {
                showDayGroupMessage(normalizedDay, 'restaurant', 'Please choose a restaurant first, then add its transfer.');
                return false;
            }
            if (hasRestaurant) {
                // Restaurant + transfer are stored as one combined entry.
                addRestaurantItemForDay(normalizedDay);
                return true;
            }
            // Legacy transfer-only row being edited.
            const transferPayload = getRestaurantTransferPayload(normalizedDay);
            return addTransferLikeItemForDay(normalizedDay, 'Restaurant Transfer', transferPayload, 'restaurant');
        }

        function addArrivalItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const pickupOp = getSelectedOption(`arrival_pickup_select_${normalizedDay}`);
            const dropOp = getSelectedOption(`arrival_drop_select_${normalizedDay}`);
            if (!pickupOp || !dropOp) {
                showDayGroupMessage(normalizedDay, 'arrival', 'Please select pickup and drop location first, then add the arrival.');
                return false;
            }
            const transferPayload = getArrivalTransferPayload(normalizedDay);
            return addTransferLikeItemForDay(normalizedDay, 'Day Arrival', transferPayload);
        }

        function addDepartureItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const pickupOp = getSelectedOption(`departure_pickup_select_${normalizedDay}`);
            const dropOp = getSelectedOption(`departure_drop_select_${normalizedDay}`);
            if (!pickupOp || !dropOp) {
                showDayGroupMessage(normalizedDay, 'departure', 'Please select pickup and drop location first, then add the departure.');
                return false;
            }
            const transferPayload = getDepartureTransferPayload(normalizedDay);
            return addTransferLikeItemForDay(normalizedDay, 'Day Departure', transferPayload);
        }

        function addTransferLikeItemForDay(normalizedDay, label, transferPayload, itemType = 'attraction') {
            const transferCost = parseFloat(transferPayload.cost ?? transferPayload.transfer_price ?? 0) || 0;
            const hasTransfer = transferPayload.required === 'Yes'
                || !!transferPayload.city
                || !!transferPayload.pickup_location
                || !!transferPayload.drop_location
                || transferCost > 0
                || (Array.isArray(transferPayload.additional_transfers) && transferPayload.additional_transfers.length > 0);
            if (!hasTransfer) {
                alert(`Select ${String(label || 'transfer').replace(/^Day\s+/i, '').toLowerCase()} details first.`);
                return false;
            }
            const isRestaurant = String(itemType || '').toLowerCase() === 'restaurant';
            const payload = {
                day: normalizedDay,
                type: isRestaurant ? 'restaurant' : 'attraction',
                id: '',
                label: label || (isRestaurant ? 'Restaurant Transfer' : 'Attraction Transfer'),
                city_name: getCityNameFromSelect(`activity_city_select_${normalizedDay}`) || transferPayload.city || '',
                ticket_id: '',
                ticket_name: '',
                transfer: transferPayload
            };
            if (isRestaurant) {
                payload.meal_price = 0;
                payload.price = 0;
                payload.meal = {
                    meal_id: '',
                    meal_type: '',
                    dish: '',
                    meal_name: '',
                    meal_period: '',
                    meal_price: 0,
                    time_slot: '',
                };
            }
            if (editingActivityIndex !== null && dayItems[editingActivityIndex]) {
                dayItems[editingActivityIndex] = payload;
                editingActivityIndex = null;
            } else {
                dayItems.push(payload);
            }
            renderActivityRows();
            resetDayEntryFields(normalizedDay);
            resetDayActivityEditButtons();
            return true;
        }

        function removeActivity(idx) {
            const wasEditing = editingActivityIndex === idx;
            const editedDay = wasEditing ? parseInt(String(dayItems[idx]?.day || 1), 10) || 1 : null;
            dayItems.splice(idx, 1);
            if (wasEditing) {
                editingActivityIndex = null;
                if (editedDay) resetDayEntryFields(editedDay);
                resetDayActivityEditButtons();
            } else if (editingActivityIndex !== null && editingActivityIndex > idx) {
                editingActivityIndex -= 1;
            }
            renderActivityRows();
        }

        async function editActivity(idx) {
            const x = dayItems[idx];
            if (!x) return;

            editingActivityIndex = idx;
            isPrefillingActivityForm = true;
            const rowDay = parseInt(String(x.day || 1), 10) || 1;
            activeDay = Math.max(1, Math.min(daysCount, rowDay));

            if (!document.getElementById(`activity_city_select_${rowDay}`)) {
                initDays();
            } else {
                updateAllDayTransferVisibility();
            }
            renderHotelRows();
            renderActivityRows();

            resetDayEntryFields(rowDay, true);
            updateDayActivityEditButtons(rowDay, x);

            const citySelectId = `activity_city_select_${rowDay}`;
            const resolvedCityName =
                String(x.city_name || '').split(',')[0].trim()
                || String(x.transfer?.city || '').split(',')[0].trim()
                || getCityNameFromSelect(citySelectId)
                || getCityNameFromSelect(`transfer_city_select_${rowDay}`)
                || getCityNameFromSelect('hotel_city_select')
                || getCityNameFromSelect('city_id')
                || '';

            if (resolvedCityName) {
                const citySelect = document.getElementById(citySelectId);
                if (citySelect) {
                    const cityMatch = Array.from(citySelect.options).find(opt => {
                        const nm = String(opt.dataset.name || opt.textContent || '').split(',')[0].trim().toLowerCase();
                        return nm === resolvedCityName.trim().toLowerCase();
                    });
                    if (cityMatch) {
                        isSyncingCitySelectors = true;
                        safeSetSelectValueSilent(citySelectId, cityMatch.value);
                        activityCityByDay[rowDay] = resolvedCityName;
                        syncTransferCityFromActivity(rowDay);
                        isSyncingCitySelectors = false;
                    }
                }
            }

            await populateDayServiceOptionsByCity(rowDay, resolvedCityName, { loadTransfers: false, silent: true });

            const hasAttraction = x.type === 'attraction' && String(x.id || '').trim();
            const hasRestaurant = x.type === 'restaurant' && String(x.id || '').trim();

            try {
            if (hasAttraction) {
                ensureSelectOptionByValue(`attraction_select_${rowDay}`, x.id || '', x.label || '');
                safeSetSelectValue(`attraction_select_${rowDay}`, x.id || '');
                await loadTicketsForAttractionForDay(rowDay);
                if (x.ticket_id) {
                    ensureSelectOptionByValue(`attraction_ticket_select_${rowDay}`, x.ticket_id || '', x.ticket_name || '');
                    safeSetSelectValue(`attraction_ticket_select_${rowDay}`, x.ticket_id || '');
                }
                const priceEl = document.getElementById(`attraction_price_${rowDay}`);
                if (priceEl) {
                    priceEl.value = parseFloat(x.ticket_price ?? x.price ?? 0).toFixed(2);
                }
            } else if (hasRestaurant) {
                ensureSelectOptionByValue(`restaurant_select_${rowDay}`, x.id || '', x.label || '');
                safeSetSelectValue(`restaurant_select_${rowDay}`, x.id || '');
                const periodVal = String(x.meal?.meal_period || mealPeriodValueFromLabel(x.meal?.meal_type) || '');
                safeSetSelectValue(`restaurant_meal_period_${rowDay}`, periodVal);
                await loadMealsForRestaurantForDay(rowDay);
                if (x.meal?.meal_id) {
                    ensureSelectOptionByValue(`restaurant_meal_select_${rowDay}`, x.meal.meal_id, x.meal.meal_name || x.meal.dish || '');
                    safeSetSelectValue(`restaurant_meal_select_${rowDay}`, x.meal.meal_id);
                } else if (x.meal?.dish) {
                    const mealSelect = document.getElementById(`restaurant_meal_select_${rowDay}`);
                    const legacyMatch = mealSelect
                        ? Array.from(mealSelect.options).find(opt =>
                            String(opt.dataset.typeLabel || opt.textContent || '').includes(String(x.meal.dish))
                        )
                        : null;
                    if (legacyMatch) {
                        safeSetSelectValue(`restaurant_meal_select_${rowDay}`, legacyMatch.value);
                    }
                }
                const restPriceEl = document.getElementById(`restaurant_price_${rowDay}`);
                if (restPriceEl) {
                    restPriceEl.value = parseFloat(x.meal_price ?? x.price ?? x.meal?.meal_price ?? 0).toFixed(2);
                }
            }

            await applyTransferFieldsToDayForm(rowDay, x);
            scrollToDayCard(rowDay);
            } finally {
                isPrefillingActivityForm = false;
            }
        }

        function renderActivityRows() {
            for (let d = 1; d <= daysCount; d++) {
                const container = document.getElementById(`day_items_${d}`);
                if (!container) continue;

                const itemsForDay = (Array.isArray(dayItems) ? dayItems : [])
                    .filter(x => parseInt(String(x.day || 0), 10) === d)
                    .sort((a, b) => {
                        const aT = String(a.type || '');
                        const bT = String(b.type || '');
                        if (aT === bT) return 0;
                        return aT.localeCompare(bT);
                    });

                if (!itemsForDay.length) {
                    container.innerHTML = `
                        <div class="table-responsive modern-table-wrap mt-2">
                            <table class="table table-sm data-table-sm day-items-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:11%">Type</th>
                                        <th style="width:36%">Service</th>
                                        <th>Transfer</th>
                                        <th style="width:100px" class="text-end">Total</th>
                                        <th style="width:90px" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="5" class="text-muted">No services added for Day ${d}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                    continue;
                }

                const rowsHtml = itemsForDay.map((x) => {
                    const idx = dayItems.indexOf(x);
                    const transfer = x.transfer && typeof x.transfer === 'object' ? x.transfer : {};
                    const normalizedLabel = String(x.label || '').trim().toLowerCase();
                    const isTransferLikeOnly = x.type === 'attraction'
                        && (!String(x.id || '').trim())
                        && (normalizedLabel === 'day transfer'
                            || normalizedLabel === 'attraction transfer'
                            || normalizedLabel === 'day arrival'
                            || normalizedLabel === 'day departure')
                        || (x.type === 'restaurant'
                            && (!String(x.id || '').trim())
                            && normalizedLabel === 'restaurant transfer');
                    const transferTypeLabel = String(transfer.transfer_type || '').trim() || (
                        normalizedLabel === 'day arrival' ? 'Arrival'
                            : normalizedLabel === 'day departure' ? 'Departure'
                                : 'Transfer'
                    );

                    const itemType = String(x.type || '').trim();
                    const pickupDisplay = getTransferLocationDisplay(transfer, 'pickup', d, itemType);
                    const dropDisplay = getTransferLocationDisplay(transfer, 'drop', d, itemType);
                    const lineTotal = getActivityItemTotalPrice(x);
                    const cityDisplay = transfer.city || x.city_name || '-';
                    const transferCost = parseFloat(transfer.cost ?? transfer.transfer_price ?? 0) || 0;
                    const hasTransfer = transfer.required === 'Yes' || transfer.pickup_location || transfer.drop_location || transferCost > 0;

                    let badgeClass = 'item-type-badge--attraction';
                    let badgeText = 'Attraction';
                    if (x.type === 'restaurant' && !isTransferLikeOnly) {
                        badgeClass = 'item-type-badge--restaurant';
                        badgeText = 'Restaurant';
                    } else if (isTransferLikeOnly) {
                        const tKey = transferTypeLabel.toLowerCase();
                        badgeClass = tKey === 'arrival' ? 'item-type-badge--arrival'
                            : tKey === 'departure' ? 'item-type-badge--departure'
                                : 'item-type-badge--transfer';
                        badgeText = transferTypeLabel;
                    }

                    let serviceCellHtml = '';
                    if (x.type === 'restaurant' && !isTransferLikeOnly) {
                        const mealPrice = parseFloat(x.meal_price ?? x.price ?? x.meal?.meal_price ?? 0);
                        const metaParts = [];
                        if (x.meal?.dish) metaParts.push(`Dish: ${escapeHtml(x.meal.dish)}`);
                        if (x.meal?.meal_type) metaParts.push(escapeHtml(x.meal.meal_type));
                        if (x.city_name) metaParts.push(escapeHtml(x.city_name));
                        serviceCellHtml = `
                            <div class="item-title">${escapeHtml(x.label || 'Restaurant')}</div>
                            ${metaParts.length ? `<div class="item-meta">${metaParts.join(' · ')}</div>` : ''}
                            ${mealPrice > 0 ? `<span class="item-price-tag item-price-tag--meal">Meal SGD ${mealPrice.toFixed(2)}</span>` : ''}
                        `;
                    } else if (isTransferLikeOnly) {
                        serviceCellHtml = `
                            <div class="item-title">${escapeHtml(transferTypeLabel)} transfer</div>
                            <div class="item-meta">${escapeHtml(cityDisplay)}</div>
                        `;
                    } else {
                        const ticketPrice = parseFloat(x.ticket_price ?? x.price ?? 0);
                        const metaParts = [];
                        if (x.ticket_name) metaParts.push(`Ticket: ${escapeHtml(x.ticket_name)}`);
                        if (x.city_name) metaParts.push(escapeHtml(x.city_name));
                        serviceCellHtml = `
                            <div class="item-title">${escapeHtml(x.label || 'Attraction')}</div>
                            ${metaParts.length ? `<div class="item-meta">${metaParts.join(' · ')}</div>` : ''}
                            ${ticketPrice > 0 ? `<span class="item-price-tag item-price-tag--meal">Ticket SGD ${ticketPrice.toFixed(2)}</span>` : ''}
                        `;
                    }

                    const transferCellHtml = hasTransfer
                        ? `
                            <div class="item-route">
                                <span class="item-route-stop">${escapeHtml(pickupDisplay)}</span>
                                <span class="item-route-arrow" aria-hidden="true">→</span>
                                <span class="item-route-stop">${escapeHtml(dropDisplay)}</span>
                            </div>
                            ${transferCost > 0 ? `<span class="item-price-tag item-price-tag--transfer">Transfer SGD ${transferCost.toFixed(2)}</span>` : ''}
                        `
                        : '<span class="item-meta">No transfer</span>';

                    return `
                        <tr>
                            <td class="align-middle"><span class="item-type-badge ${badgeClass}">${escapeHtml(badgeText)}</span></td>
                            <td class="align-middle">${serviceCellHtml}</td>
                            <td class="align-middle">${transferCellHtml}</td>
                            <td class="text-end align-middle">
                                ${lineTotal > 0
                                    ? `<div class="hotel-price-total">SGD ${lineTotal.toFixed(2)}</div>`
                                    : '<span class="text-muted">-</span>'}
                            </td>
                            <td class="text-end align-middle action-cell">
                                <span class="action-buttons">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-icon" onclick="editActivity(${idx})" title="Edit" aria-label="Edit">${actionIcon('edit')}</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeActivity(${idx})" title="Remove" aria-label="Remove">${actionIcon('remove')}</button>
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('');

                container.innerHTML = `
                    <div class="table-responsive modern-table-wrap mt-2">
                        <table class="table table-sm data-table-sm day-items-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:11%">Type</th>
                                    <th style="width:36%">Service</th>
                                    <th>Transfer</th>
                                    <th style="width:100px" class="text-end">Total</th>
                                    <th style="width:90px" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>${rowsHtml}</tbody>
                        </table>
                    </div>
                `;
            }

            const outEl = document.getElementById('activities_json');
            if (outEl) outEl.value = JSON.stringify(dayItems);
        }

        function toggleRestaurantTransferConfig() {}

        function addInter() {
            const cityOp = getSelectedOption('ic_city');
            if (!cityOp) {
                alert('Select inter city destination.');
                return;
            }
            const rawPri = parseInt(String(document.getElementById('ic_priority').value || '1').trim(), 10);
            const pri = Number.isFinite(rawPri) ? Math.min(10, Math.max(1, rawPri)) : 1;
            inter.push({
                city_id: cityOp.value,
                city: cityOp.dataset.name || cityOp.textContent,
                tr: document.getElementById('ic_trans').value || '',
                veh: document.getElementById('ic_vehicle').value || '',
                pri: pri,
                cost: parseFloat(document.getElementById('ic_cost').value || '0')
            });
            renderInterRows();
        }

        function removeInter(idx) {
            inter.splice(idx, 1);
            renderInterRows();
        }

        function renderInterRows() {
            const body = document.getElementById('interRows');
            if (!body) {
                document.getElementById('inter_json').value = JSON.stringify(inter);
                return;
            }
            if (!inter.length) {
                body.innerHTML = '<tr><td colspan="6" class="text-muted">No inter-city rows</td></tr>';
            } else {
                body.innerHTML = inter.map((x, idx) => {
                    const p = (x.pri != null && x.pri !== '') ? x.pri : (x.priority != null ? x.priority : 1);
                    return `
                    <tr>
                        <td>${escapeHtml(x.city)}</td>
                        <td>${escapeHtml(x.tr)}</td>
                        <td>${escapeHtml(x.veh)}</td>
                        <td>${escapeHtml(String(p))}</td>
                        <td>${x.cost}</td>
                        <td class="action-cell"><span class="action-buttons"><button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeInter(${idx})" title="Remove" aria-label="Remove">${actionIcon('remove')}</button></span></td>
                    </tr>
                `;
                }).join('');
            }
            document.getElementById('inter_json').value = JSON.stringify(inter);
        }

        function normalizeFormStateBeforeBuild() {
            const fallbackMainCity = getCityNameFromSelect('city_id') || '';
            for (let d = 1; d <= daysCount; d++) {
                syncDayExtraTransfersFromDom(d);
            }

            hotels = (Array.isArray(hotels) ? hotels : []).map((x) => {
                const dayNum = parseInt(String(x?.day || 1), 10) || 1;
                const fallbackCity = String(x?.city_name || '').trim()
                    || getCityNameFromSelect('hotel_city_select')
                    || fallbackMainCity
                    || '';
                return {
                    ...x,
                    day: dayNum,
                    night: Math.max(1, parseInt(String(x?.night || 1), 10) || 1),
                    city_name: fallbackCity,
                    priority: parseInt(String(x?.priority || 1), 10) || 1,
                };
            });

            dayItems = (Array.isArray(dayItems) ? dayItems : []).map((x) => {
                const dayNum = parseInt(String(x?.day || 1), 10) || 1;
                const fallbackCity = String(x?.city_name || '').trim()
                    || String(x?.transfer?.city || '').trim()
                    || getCityNameFromSelect(`activity_city_select_${dayNum}`)
                    || getCityNameFromSelect(`transfer_city_select_${dayNum}`)
                    || fallbackMainCity
                    || '';
                const transfer = (x?.transfer && typeof x.transfer === 'object') ? { ...x.transfer } : {};
                if (!transfer.city && fallbackCity) {
                    transfer.city = fallbackCity;
                }
                return {
                    ...x,
                    day: dayNum,
                    city_name: fallbackCity,
                    transfer,
                };
            });

            const normalizedExtras = {};
            for (let d = 1; d <= daysCount; d++) {
                const rows = Array.isArray(dayTransferExtras[d]) ? dayTransferExtras[d] : [];
                normalizedExtras[d] = rows
                    .map((row) => ({
                        city: String(row?.city || '').trim(),
                        pickup_location: String(row?.pickup_location || '').trim(),
                        drop_location: String(row?.drop_location || '').trim(),
                    }))
                    .filter((row) => row.city || row.pickup_location || row.drop_location);
            }
            dayTransferExtras = normalizedExtras;
        }

        function syncDayExtraTransfersFromDom(dayVal) {
            const wrap = document.getElementById(`extra_transfer_rows_${dayVal}`);
            if (!wrap) return;
            const rowEls = Array.from(wrap.querySelectorAll('.row.mb-2'));
            if (!rowEls.length) return;
            dayTransferExtras[dayVal] = rowEls.map((rowEl) => {
                const selects = rowEl.querySelectorAll('select');
                const citySel = selects[0];
                const pickupSel = selects[1];
                const dropSel = selects[2];
                const pickupVal = String(pickupSel?.value || '').trim();
                const dropVal = String(dropSel?.value || '').trim();
                const pickupStored = buildStoredTransferLocationFields(pickupSel?.id || '', pickupVal);
                const dropStored = buildStoredTransferLocationFields(dropSel?.id || '', dropVal);
                return {
                    city: String(citySel?.value || '').trim(),
                    pickup_location: pickupStored.location,
                    pickup_location_value: pickupStored.location_value,
                    pickup_location_label: pickupStored.location_label,
                    drop_location: dropStored.location,
                    drop_location_value: dropStored.location_value,
                    drop_location_label: dropStored.location_label,
                };
            });
        }

        /** Professional day-scoped keys: "Day 1 Hotel", "Day 2 Attraction 1", "Day 1 Arrival", etc. */
        function buildDayBucketKey(dayNum, label, index = 1, omitIndexWhenOne = false) {
            const d = Math.max(1, parseInt(String(dayNum || 1), 10) || 1);
            const idx = Math.max(1, parseInt(String(index || 1), 10) || 1);
            if (omitIndexWhenOne && idx === 1) {
                return `Day ${d} ${label}`;
            }
            return `Day ${d} ${label} ${idx}`;
        }

        function classifyAttractionDayItemForPayload(item) {
            if (String(item?.id || '').trim()) return 'attraction';
            if (!isTransferOnlyActivityItem(item)) return 'attraction';
            const transferType = String(inferTransferTypeFromItem(item) || '').toLowerCase();
            if (transferType === 'arrival') return 'arrival';
            if (transferType === 'departure') return 'departure';
            return 'attraction_transfer';
        }

        function emptyDayPayloadBuckets(dayNum) {
            return {
                day: dayNum,
                hotels: {},
                attractions: {},
                arrivals: {},
                departures: {},
                transfers: {},
                restaurants: {},
                services: {},
            };
        }

        function buildTransferLegPayloadRow(dayNum, transferOut, cityName, kind) {
            const transferPrice = parseFloat(transferOut.cost ?? transferOut.transfer_price ?? 0) || 0;
            const isArrival = kind === 'arrival';
            const isDeparture = kind === 'departure';
            const name = isArrival ? 'Arrival' : isDeparture ? 'Departure' : 'Attraction Transfer';
            const serviceType = isArrival ? 'arrival' : isDeparture ? 'departure' : 'attraction_transfer';
            return {
                booked_day: dayNum,
                day: dayNum,
                service_type: serviceType,
                name,
                label: isArrival ? 'Day Arrival' : isDeparture ? 'Day Departure' : 'Attraction Transfer',
                city: cityName,
                total_price: transferPrice,
                transfer: transferOut,
            };
        }

        function buildHydratedItemTransferFields(normalizedTransfer, hasXfer = null) {
            const xfer = normalizedTransfer && typeof normalizedTransfer === 'object' ? normalizedTransfer : {};
            const hasTransfer = hasXfer !== null
                ? hasXfer
                : (String(xfer.required || '').toLowerCase() === 'yes'
                    || !!(xfer.pickup_location || xfer.drop_location));
            return {
                required: hasTransfer ? 'Yes' : String(xfer.required || 'No'),
                city: String(xfer.city || ''),
                pickup_location: String(xfer.pickup_location || ''),
                drop_location: String(xfer.drop_location || ''),
                pickup_location_value: String(xfer.pickup_location_value || xfer.pickup_location_id || ''),
                drop_location_value: String(xfer.drop_location_value || xfer.drop_location_id || ''),
                pickup_location_label: String(xfer.pickup_location_label || ''),
                drop_location_label: String(xfer.drop_location_label || ''),
                transfer_type: String(xfer.transfer_type || ''),
                additional_transfers: Array.isArray(xfer.additional_transfers)
                    ? xfer.additional_transfers.map((row) => normalizeHydratedTransfer(row))
                    : [],
                type: String(xfer.type || ''),
                way: String(xfer.way || ''),
                vehicle_id: String(xfer.vehicle_id || ''),
                vehicle_name: String(xfer.vehicle_name || ''),
                pickup_location_id: String(xfer.pickup_location_id || xfer.pickup_location_value || ''),
                drop_location_id: String(xfer.drop_location_id || xfer.drop_location_value || ''),
                cost: parseFloat(String(xfer.cost ?? xfer.transfer_price ?? 0)) || 0,
                transfer_price: parseFloat(String(xfer.transfer_price ?? xfer.cost ?? 0)) || 0,
                pickup_time: String(xfer.pickup_time || ''),
            };
        }

        function hydrateTransferLegDayItem(dayNum, row, cityName, seenDayActivityKeys, defaultTransferType) {
            if (!row || typeof row !== 'object') return;
            const normalizedTransfer = normalizeHydratedTransfer(row.transfer || {});
            const transferType = String(
                normalizedTransfer.transfer_type || row.service_type || defaultTransferType || ''
            ).trim().toLowerCase();
            const dedupeKey = `${dayNum}|transfer|${transferType}|${normalizedTransfer.pickup_location}|${normalizedTransfer.drop_location}`;
            if (seenDayActivityKeys.has(dedupeKey)) return;
            seenDayActivityKeys.add(dedupeKey);
            const isArrival = transferType === 'arrival';
            const isDeparture = transferType === 'departure';
            const label = isArrival
                ? 'Day Arrival'
                : isDeparture
                    ? 'Day Departure'
                    : (String(row.label || row.name || '').trim() || 'Attraction Transfer');
            const hasXfer = String(normalizedTransfer.required || '').toLowerCase() === 'yes'
                || !!(normalizedTransfer.pickup_location || normalizedTransfer.drop_location);
            dayItems.push({
                day: dayNum,
                type: 'attraction',
                id: '',
                label,
                city_name: String(row.city || normalizedTransfer.city || cityName || ''),
                ticket_id: '',
                ticket_name: '',
                ticket_price: 0,
                price: parseFloat(String(row.total_price ?? normalizedTransfer.cost ?? 0)) || 0,
                transfer: buildHydratedItemTransferFields(normalizedTransfer, hasXfer),
            });
        }

        function buildStructuredPayload() {
            normalizeFormStateBeforeBuild();
            const masterDmcId = parseInt(document.getElementById('master_dmc_id').value || '0', 10);
            const dmcId = parseInt(document.getElementById('dmc_id').value || '0', 10);
            const cityOp = getSelectedOption('city_id');
            const countryVal = document.getElementById('country').value || cityOp?.dataset?.country || '';
            const cityName = resolveCityName();
            const totalDays = Math.max(
                1,
                parseInt(String(daysCount || document.getElementById('days')?.value || '1'), 10) || 1
            );

            if (REQUIRE_MASTER_DMC_CITY && (!masterDmcId || !dmcId || !cityName)) {
                return null;
            }
            const effectiveCityName = cityName || 'Unknown';

            const normalizedPlans = [...multiCityPlans]
                .filter(p => p && Number.isFinite(p.day_in) && Number.isFinite(p.day_out))
                .map(p => ({
                    city_name: String(p.city_name || '').trim(),
                    day_in: Math.max(1, parseInt(String(p.day_in), 10) || 1),
                    day_out: Math.min(daysCount, parseInt(String(p.day_out), 10) || daysCount),
                }))
                .filter(p => p.city_name && p.day_out >= p.day_in)
                .sort((a, b) => a.day_in - b.day_in);

            const days = {};
            for (let d = 1; d <= daysCount; d++) {
                const isSameDay = (value, fallback = 0) => (parseInt(String(value ?? fallback), 10) || fallback) === d;
                let h = [];
                if (normalizedPlans.length) {
                    // Multi City is the single source of truth for which city is “active” for day d.
                    // Hotels are stored once on check-in day only.
                    const planForDay = normalizedPlans
                        .filter(p => d >= p.day_in && d <= p.day_out)
                        .sort((a, b) => b.day_in - a.day_in)[0] || null;

                    if (planForDay) {
                        const cityKey = String(planForDay.city_name || '').trim().toLowerCase();
                        const start = parseInt(String(planForDay.day_in || 1), 10) || 1;
                        const end = start + Math.max(1, parseInt(String(planForDay.day_out || start), 10) - start) - 1;

                        if (d >= start && d <= end) {
                            h = hotels.filter(x => {
                                const hotelStart = parseInt(String(x.day || 1), 10) || 1;
                                return String(x.city_name || '').trim().toLowerCase() === cityKey
                                    && d === hotelStart;
                            });
                        }
                    }
                } else {
                    // Legacy fallback when Multi City is not configured — one hotel row on check-in day only.
                    h = hotels.filter((x) => isSameDay(x.day, d));
                }
                const a = dayItems.filter(x => isSameDay(x.day, 0) && x.type === 'attraction');
                const r = dayItems.filter(x => isSameDay(x.day, 0) && x.type === 'restaurant');

                const hotelMap = {};
                const attrMap = {};
                const arrivalMap = {};
                const departureMap = {};
                const transferMap = {};
                const serviceMap = {};
                let attractionSeq = 0;
                let arrivalSeq = 0;
                let departureSeq = 0;
                let attractionTransferSeq = 0;
                let restaurantSeq = 0;
                let restaurantTransferSeq = 0;

                h.forEach((x, i) => {
                    const nights = Math.max(1, parseInt(String(x.night || '1'), 10) || 1);
                    const perNight = getHotelPerNightPrice(x);
                    const totalPrice = perNight * nights;
                    hotelMap[buildDayBucketKey(d, 'Hotel', i + 1)] = {
                        booked_day: d,
                        hotel_id: resolveHotelUniqueIdForPayload(x.hotel_id),
                        hotel_name: x.hotel_name,
                        city: x.city_name || '',
                        room_id: String(x.room_id || ''),
                        room_type: String(x.room_type || ''),
                        bed_id: String(x.bed_id || ''),
                        bed_type: String(x.bed_type || ''),
                        meal_plan: x.meal_plan || '',
                        room_price: parseFloat(x.room_price ?? 0),
                        breakfast_price: parseFloat(x.breakfast_price ?? 0),
                        lunch_price: parseFloat(x.lunch_price ?? 0),
                        dinner_price: parseFloat(x.dinner_price ?? 0),
                        price_per_night: perNight,
                        price: totalPrice,
                        total_price: totalPrice,
                        night: nights,
                        checkin_day: parseInt(String(x.day || d), 10) || d,
                        checkout_day: getHotelCheckoutDay(x),
                        stay_days: getHotelStayDayNumbers(x),
                        meal_type: x.meal_type || '',
                        guide_required: x.guide_required || 'No',
                        arrival_departure: x.arrival_departure || 'No',
                        arrival_departure_type: x.arrival_departure_type || '',
                        transfer_city: x.transfer_city || '',
                        transfer_pickup: exportHotelLocationForJson(x.transfer_pickup),
                        transfer_drop: exportHotelLocationForJson(x.transfer_drop),
                        priority: parseInt(String(x.priority || '1'), 10) || 1
                    };
                });
                a.forEach((x) => {
                    const transferOut = exportTransferForJson(
                        x.transfer && typeof x.transfer === 'object' ? x.transfer : {},
                        d,
                        'attraction'
                    );
                    const fallbackCityName =
                        String(x.city_name || '').trim()
                        || String(transferOut.city || '').trim()
                        || getCityNameFromSelect(`activity_city_select_${d}`)
                        || getCityNameFromSelect(`transfer_city_select_${d}`)
                        || effectiveCityName
                        || '';
                    if (!transferOut.city && fallbackCityName) {
                        transferOut.city = fallbackCityName;
                    }
                    const itemKind = classifyAttractionDayItemForPayload(x);
                    if (itemKind === 'arrival') {
                        arrivalSeq += 1;
                        arrivalMap[buildDayBucketKey(d, 'Arrival', arrivalSeq, true)] = buildTransferLegPayloadRow(
                            d, transferOut, fallbackCityName, 'arrival'
                        );
                        return;
                    }
                    if (itemKind === 'departure') {
                        departureSeq += 1;
                        departureMap[buildDayBucketKey(d, 'Departure', departureSeq, true)] = buildTransferLegPayloadRow(
                            d, transferOut, fallbackCityName, 'departure'
                        );
                        return;
                    }
                    if (itemKind === 'attraction_transfer') {
                        attractionTransferSeq += 1;
                        transferMap[buildDayBucketKey(d, 'Attraction Transfer', attractionTransferSeq)] = buildTransferLegPayloadRow(
                            d, transferOut, fallbackCityName, 'attraction_transfer'
                        );
                        return;
                    }
                    attractionSeq += 1;
                    const attractionTicketPrice = parseFloat(x.ticket_price ?? x.price ?? 0) || 0;
                    const attractionTransferPrice = parseFloat(transferOut.cost ?? transferOut.transfer_price ?? 0) || 0;
                    attrMap[buildDayBucketKey(d, 'Attraction', attractionSeq)] = {
                        booked_day: d,
                        attraction_id: String(x.id),
                        name: x.label,
                        city: fallbackCityName,
                        ticket_id: x.ticket_id ? String(x.ticket_id) : '',
                        ticket_name: x.ticket_name || '',
                        ticket_price: attractionTicketPrice,
                        price: attractionTicketPrice,
                        total_price: attractionTicketPrice + attractionTransferPrice,
                        transfer: transferOut
                    };
                });
                r.filter((x) => String(x.id || '').trim() || isTransferOnlyActivityItem(x)).forEach((x) => {
                    const fallbackCityName =
                        String(x.city_name || '').trim()
                        || String(x.transfer?.city || '').trim()
                        || getCityNameFromSelect(`activity_city_select_${d}`)
                        || getCityNameFromSelect(`transfer_city_select_${d}`)
                        || effectiveCityName
                        || '';
                    const transferOut = exportTransferForJson(
                        x.transfer && typeof x.transfer === 'object' ? x.transfer : {},
                        d,
                        'restaurant'
                    );
                    if (!transferOut.city && fallbackCityName) {
                        transferOut.city = fallbackCityName;
                    }
                    const restaurantMealPrice = parseFloat(x.meal_price ?? x.price ?? x.meal?.meal_price ?? 0) || 0;
                    const restaurantTransferPrice = parseFloat(transferOut.cost ?? transferOut.transfer_price ?? 0) || 0;
                    const hasRestaurantId = String(x.id || '').trim();
                    let serviceKey;
                    let serviceType;
                    if (hasRestaurantId) {
                        restaurantSeq += 1;
                        serviceKey = buildDayBucketKey(d, 'Restaurant', restaurantSeq);
                        serviceType = 'restaurant';
                    } else {
                        restaurantTransferSeq += 1;
                        serviceKey = buildDayBucketKey(d, 'Restaurant Transfer', restaurantTransferSeq);
                        serviceType = 'restaurant_transfer';
                    }
                    serviceMap[serviceKey] = {
                        booked_day: d,
                        day: d,
                        service_type: serviceType,
                        restaurant_id: String(x.id || ''),
                        restaurant_name: hasRestaurantId ? x.label : 'Restaurant Transfer',
                        name: hasRestaurantId ? x.label : 'Restaurant Transfer',
                        city: fallbackCityName,
                        meal_price: restaurantMealPrice,
                        price: restaurantMealPrice,
                        total_price: restaurantMealPrice + restaurantTransferPrice,
                        meal_configuration: x.meal || {},
                        transfer: transferOut
                    };
                });

                days[String(d - 1)] = {
                    day: d,
                    hotels: hotelMap,
                    attractions: attrMap,
                    arrivals: arrivalMap,
                    departures: departureMap,
                    transfers: transferMap,
                    restaurants: {},
                    services: serviceMap
                };
            }

            const citiesForPayload = [];
            // One submit action = one logical package. Reuse package_id when editing a specific package.
            const packageId = window.__EDITING_PACKAGE_ID__
                ? window.__EDITING_PACKAGE_ID__
                : `pkg_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;

            if (normalizedPlans.length) {
                normalizedPlans.forEach((plan) => {
                    const cityDays = {};
                    let dayPos = 0;
                    for (let d = plan.day_in; d <= plan.day_out; d++, dayPos++) {
                        const source = days[String(d - 1)] || emptyDayPayloadBuckets(d);
                        cityDays[String(dayPos)] = source;
                    }
                    citiesForPayload.push({
                        city: plan.city_name,
                        checkin_day: plan.day_in,
                        checkout_day: plan.day_out,
                        packages: [{
                            package_id: packageId,
                            total_days: totalDays,
                            days: cityDays
                        }]
                    });
                });
            } else {
                citiesForPayload.push({
                    city: effectiveCityName,
                    packages: [{
                        package_id: packageId,
                        total_days: totalDays,
                        days: days
                    }]
                });
            }

            const serviceMeta = {
                airport_transfer: {
                    type: '',
                    vehicle_id: '',
                    vehicle_service_type: '',
                    vehicle_passengers: 0,
                    cost: 0
                },
                departure_transfer: {
                    type: '',
                    vehicle_id: '',
                    vehicle_service_type: '',
                    vehicle_passengers: 0,
                    cost: 0
                },
                guide: {
                    guide_id: '',
                    guide_name: '',
                    guide_cost: 0
                },
                inter_city: (Array.isArray(inter) ? inter : []).map((row) => ({
                    city_id: String(row.city_id || ''),
                    city: String(row.city || ''),
                    transfer_type: String(row.tr || row.transfer_type || ''),
                    vehicle: String(row.veh || row.vehicle || ''),
                    priority: parseInt(String(row.pri ?? row.priority ?? 1), 10) || 1,
                    cost: parseFloat(String(row.cost ?? 0)) || 0,
                }))
            };

            return {
                Master_DMC: [{
                    Master_DMC_id: masterDmcId,
                    destinations: [{
                        DMC_id: dmcId,
                        country: countryVal,
                        service_meta: serviceMeta,
                        cities: citiesForPayload
                    }]
                }]
            };
        }

        function syncHiddenJsonFieldsBeforeSubmit() {
            const hotelsEl = document.getElementById('hotels_json');
            const activitiesEl = document.getElementById('activities_json');
            const interEl = document.getElementById('inter_json');
            if (hotelsEl) hotelsEl.value = JSON.stringify(Array.isArray(hotels) ? hotels : []);
            if (activitiesEl) activitiesEl.value = JSON.stringify(Array.isArray(dayItems) ? dayItems : []);
            if (interEl) interEl.value = JSON.stringify(Array.isArray(inter) ? inter : []);
        }

        function applyPayloadJsonToForm(payload) {
            syncHiddenJsonFieldsBeforeSubmit();
            document.getElementById('payload_json').value = JSON.stringify(payload);
        }

        function preparePayloadForSubmit() {
            const payload = buildStructuredPayload();
            if (payload && Array.isArray(payload.Master_DMC) && payload.Master_DMC.length) {
                return payload;
            }
            return REQUIRE_MASTER_DMC_CITY ? null : payload;
        }

        function getPreviewWarnings(payload) {
            const warnings = [];
            if (!multiCityPlans.length) {
                warnings.push('No Multi City plan added — package will use the primary city only.');
            }
            if (!hotels.length) {
                warnings.push('No hotels added to this package.');
            }
            const hasActivities = (Array.isArray(dayItems) ? dayItems : []).some(x =>
                (x.type === 'attraction' && String(x.id || '').trim())
                || x.type === 'restaurant'
            );
            if (!hasActivities) {
                warnings.push('No attractions or restaurants added for any day.');
            }
            if (Array.isArray(inter) && inter.length) {
                warnings.push('Inter-city rows are stored separately and may not appear in the structured payload preview.');
            }
            return warnings;
        }

        function getPackagePreviewGrandTotal() {
            const hotelTotal = (Array.isArray(hotels) ? hotels : []).reduce((sum, h) => sum + getHotelStayTotalPrice(h), 0);
            const activityTotal = (Array.isArray(dayItems) ? dayItems : []).reduce((sum, x) => sum + getActivityItemTotalPrice(x), 0);
            return hotelTotal + activityTotal;
        }

        function renderPreviewTransferLine(transfer, dayNum, itemType = '') {
            if (!transfer || typeof transfer !== 'object') return '';
            const required = String(transfer.required || 'No');
            const pickup = getTransferLocationDisplay(transfer, 'pickup', dayNum, itemType);
            const drop = getTransferLocationDisplay(transfer, 'drop', dayNum, itemType);
            const previewCost = parseFloat(transfer.cost ?? transfer.transfer_price ?? 0) || 0;
            const hasTransfer = required === 'Yes' || pickup !== '-' || drop !== '-' || previewCost > 0;
            if (!hasTransfer) return '';

            const typeLabel = String(transfer.transfer_type || 'Transfer').trim();
            const route = (pickup !== '-' && drop !== '-')
                ? `${pickup} → ${drop}`
                : (pickup !== '-' ? pickup : drop);
            return `
                <div class="preview-transfer-chip">
                    <strong>${escapeHtml(typeLabel)}:</strong>
                    ${escapeHtml(route)}
                    ${previewCost > 0 ? ` · <span class="text-success fw-semibold">SGD ${previewCost.toFixed(2)}</span>` : ''}
                </div>
            `;
        }

        function renderPreviewActivityTableRows(dayNum) {
            const itemsForDay = (Array.isArray(dayItems) ? dayItems : [])
                .filter((x) => parseInt(String(x.day || 0), 10) === dayNum);

            if (!itemsForDay.length) return '';

            const rows = itemsForDay.map((x) => {
                const transfer = x.transfer && typeof x.transfer === 'object' ? x.transfer : {};
                const normalizedLabel = String(x.label || '').trim().toLowerCase();
                const isTransferLikeOnly = (x.type === 'attraction' && !String(x.id || '').trim()
                    && (normalizedLabel.includes('transfer') || normalizedLabel.includes('arrival') || normalizedLabel.includes('departure')))
                    || (x.type === 'restaurant' && !String(x.id || '').trim() && normalizedLabel === 'restaurant transfer');
                const typeLabel = x.type === 'restaurant'
                    ? 'Restaurant'
                    : (isTransferLikeOnly ? (transfer.transfer_type || x.label || 'Transfer') : 'Attraction');
                const itemType = String(x.type || '').trim();
                const pickup = getTransferLocationDisplay(transfer, 'pickup', dayNum, itemType);
                const drop = getTransferLocationDisplay(transfer, 'drop', dayNum, itemType);
                const lineTotal = getActivityItemTotalPrice(x);
                const servicePrice = getActivityItemServicePrice(x);
                const transferPrice = getActivityItemTransferPrice(x);

                let details = '';
                if (x.type === 'restaurant' && String(x.id || '').trim()) {
                    details = `${x.label || 'Restaurant'}${x.meal?.dish ? ` · ${x.meal.dish}` : ''}`;
                } else if (isTransferLikeOnly) {
                    details = String(transfer.transfer_type || x.label || 'Transfer');
                } else {
                    details = `${x.label || 'Attraction'}${x.ticket_name ? ` · ${x.ticket_name}` : ''}`;
                }
                const priceParts = [];
                if (servicePrice > 0) {
                    priceParts.push(x.type === 'restaurant' ? `Meal SGD ${servicePrice.toFixed(2)}` : `Ticket SGD ${servicePrice.toFixed(2)}`);
                }
                if (transferPrice > 0) priceParts.push(`Transfer SGD ${transferPrice.toFixed(2)}`);

                return `
                    <tr>
                        <td><span class="badge bg-primary-subtle text-primary">${escapeHtml(typeLabel)}</span></td>
                        <td class="small">${pickup !== '-' ? escapeHtml(pickup) : '<span class="text-muted">-</span>'}</td>
                        <td class="small">${drop !== '-' ? escapeHtml(drop) : '<span class="text-muted">-</span>'}</td>
                        <td class="small">
                            <div>${escapeHtml(details)}</div>
                            ${priceParts.length ? `<div class="text-muted" style="font-size:0.76rem;">${escapeHtml(priceParts.join(' + '))}</div>` : ''}
                            ${renderPreviewTransferLine(transfer, dayNum, itemType)}
                        </td>
                        <td class="text-end fw-semibold ${lineTotal > 0 ? 'text-success' : 'text-muted'}">
                            ${lineTotal > 0 ? `SGD ${lineTotal.toFixed(2)}` : '-'}
                        </td>
                    </tr>
                `;
            }).join('');

            return `
                <div class="table-responsive modern-table-wrap mt-2 mb-2">
                    <table class="table table-sm data-table-sm mb-0 preview-service-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:12%">Type</th>
                                <th style="width:18%">Pickup</th>
                                <th style="width:18%">Drop</th>
                                <th>Details</th>
                                <th style="width:90px" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        function renderPreviewDayBlockLive(dayNum, cityName = '') {
            const d = parseInt(String(dayNum || 0), 10) || 0;
            if (d < 1) return '';

            const hotelsForDay = (Array.isArray(hotels) ? hotels : []).filter((h) => hotelCoversDay(h, d));
            const itemsForDay = (Array.isArray(dayItems) ? dayItems : [])
                .filter((x) => parseInt(String(x.day || 0), 10) === d);
            const resolvedCity = cityName
                || getMultiCityPlanForDay(d)?.city_name
                || hotelsForDay[0]?.city_name
                || itemsForDay[0]?.city_name
                || '';

            let inner = `
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div class="preview-day-title mb-0">Day ${escapeHtml(String(d))}</div>
                    ${resolvedCity ? `<span class="badge bg-light text-dark border">${escapeHtml(resolvedCity)}</span>` : ''}
                </div>
            `;

            if (!hotelsForDay.length && !itemsForDay.length) {
                inner += '<div class="preview-empty">No services scheduled</div>';
                return `<div class="preview-day-block">${inner}</div>`;
            }

            hotelsForDay.forEach((h) => {
                const checkin = parseInt(String(h.day || 1), 10) || 1;
                const isCheckinDay = d === checkin;
                const perNight = getHotelPerNightPrice(h);
                const stayTotal = getHotelStayTotalPrice(h);
                const hotelLabel = String(h.hotel_name || '-').replace(/\s*-\s*[^-]+$/i, '').trim() || h.hotel_name || '-';
                const stayLabel = formatHotelStayDayLabel(h);

                inner += `
                    <div class="preview-line mb-2">
                        <strong>Hotel</strong>
                        ${!isCheckinDay ? `<span class="preview-hotel-continuation">(continued stay · booked ${escapeHtml(stayLabel)})</span>` : ''}
                        <div class="mt-1">
                            <span class="fw-semibold">${escapeHtml(hotelLabel)}</span>
                            <span class="text-muted"> · ${escapeHtml(String(h.night || 1))} night(s) · ${escapeHtml(formatHotelRoomMealSummary(h))}</span>
                            ${isCheckinDay
                                ? `<span class="text-primary fw-semibold"> · SGD ${perNight.toFixed(2)}/night · Total SGD ${stayTotal.toFixed(2)}</span>`
                                : `<span class="text-muted"> · Part of ${escapeHtml(stayLabel)} stay</span>`}
                        </div>
                    </div>
                `;
            });

            inner += renderPreviewActivityTableRows(d);

            return `<div class="preview-day-block">${inner}</div>`;
        }

        function renderPackagePreviewHtml(payload) {
            let html = '';
            const grandTotal = getPackagePreviewGrandTotal();
            const activityCount = (Array.isArray(dayItems) ? dayItems : []).length;

            const warnings = getPreviewWarnings(payload);
            if (warnings.length) {
                html += `<div class="preview-warnings mb-3">${warnings.map(w => `<div class="alert alert-warning mb-2 py-2">${escapeHtml(w)}</div>`).join('')}</div>`;
            }

            html += `
                <div class="preview-summary-grid">
                    <div class="preview-summary-card">
                        <div class="label">Total Days</div>
                        <div class="value">${escapeHtml(String(daysCount || 1))}</div>
                    </div>
                    <div class="preview-summary-card">
                        <div class="label">Cities</div>
                        <div class="value">${escapeHtml(String(multiCityPlans.length || 1))}</div>
                    </div>
                    <div class="preview-summary-card">
                        <div class="label">Hotels</div>
                        <div class="value">${escapeHtml(String((Array.isArray(hotels) ? hotels : []).length))}</div>
                    </div>
                    <div class="preview-summary-card">
                        <div class="label">Day Services</div>
                        <div class="value">${escapeHtml(String(activityCount))}</div>
                    </div>
                    <div class="preview-summary-card grand-total">
                        <div class="label">Package Total</div>
                        <div class="value">SGD ${grandTotal.toFixed(2)}</div>
                    </div>
                </div>
            `;

            if (multiCityPlans.length) {
                html += `
                    <h6 class="fw-semibold mb-2">Multi City itinerary</h6>
                    <div class="table-responsive modern-table-wrap mb-3">
                        <table class="table table-sm data-table-sm mb-0">
                            <thead><tr><th>City</th><th>Check-in</th><th>Check-out</th><th>Nights</th><th>Covered Days</th></tr></thead>
                            <tbody>
                                ${multiCityPlans.map(p => {
                                    const nights = Math.max(1, (parseInt(String(p.day_out || 0), 10) || 0) - (parseInt(String(p.day_in || 0), 10) || 0));
                                    const covered = [];
                                    for (let d = parseInt(String(p.day_in || 1), 10) || 1; d <= (parseInt(String(p.day_out || p.day_in), 10) || 1); d++) {
                                        covered.push(`Day${d}`);
                                    }
                                    return `
                                    <tr>
                                        <td>${escapeHtml(p.city_name || '-')}</td>
                                        <td>Day ${escapeHtml(String(p.day_in || '-'))}</td>
                                        <td>Day ${escapeHtml(String(p.day_out || '-'))}</td>
                                        <td>${escapeHtml(String(nights))}</td>
                                        <td>${escapeHtml(covered.join(', '))}</td>
                                    </tr>
                                `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            if (hotels.length) {
                html += `
                    <h6 class="fw-semibold mb-2">Hotels summary</h6>
                    <div class="table-responsive modern-table-wrap mb-3">
                        <table class="table table-sm data-table-sm mb-0">
                            <thead><tr><th>Stay Days</th><th>City</th><th>Hotel</th><th>Nights</th><th>Room &amp; Meal</th><th class="text-end">Price / Night</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                ${[...hotels].sort((a, b) => (a.day || 0) - (b.day || 0)).map(h => `
                                    <tr>
                                        <td>${formatHotelStayDayBadgesHtml(h)}</td>
                                        <td>${escapeHtml(h.city_name || '-')}</td>
                                        <td>
                                            <div class="hotel-cell-title">${escapeHtml(String(h.hotel_name || '-').replace(/\s*-\s*[^-]+$/i, '').trim() || h.hotel_name || '-')}</div>
                                            ${h.cat_label ? `<div class="hotel-cell-meta">${escapeHtml(h.cat_label)}</div>` : ''}
                                        </td>
                                        <td>${escapeHtml(String(h.night || 1))}</td>
                                        <td>${escapeHtml(formatHotelRoomMealSummary(h))}</td>
                                        <td class="text-end">SGD ${getHotelPerNightPrice(h).toFixed(2)}</td>
                                        <td class="text-end fw-semibold text-success">SGD ${getHotelStayTotalPrice(h).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            html += '<h6 class="fw-semibold mb-2">Day-by-day itinerary</h6>';
            html += '<p class="text-muted small mb-3">Hotels appear on each covered day (check-in through check-out). Service totals include ticket/meal plus transfer.</p>';

            if (multiCityPlans.length) {
                multiCityPlans.forEach((plan) => {
                    const cityName = plan.city_name || 'Unknown city';
                    const dayIn = parseInt(String(plan.day_in || 1), 10) || 1;
                    const dayOut = parseInt(String(plan.day_out || dayIn), 10) || dayIn;
                    html += `
                        <div class="preview-city-card">
                            <div class="preview-city-head">
                                ${escapeHtml(cityName)}
                                · Check-in Day ${dayIn}
                                · Check-out Day ${dayOut}
                                · ${Math.max(1, dayOut - dayIn)} night(s)
                            </div>
                            <div class="preview-city-body">
                    `;
                    for (let d = dayIn; d <= dayOut; d++) {
                        html += renderPreviewDayBlockLive(d, cityName);
                    }
                    html += '</div></div>';
                });
            } else {
                html += '<div class="preview-city-card"><div class="preview-city-body">';
                for (let d = 1; d <= daysCount; d++) {
                    html += renderPreviewDayBlockLive(d);
                }
                html += '</div></div>';
            }

            return html;
        }

        function openPackagePreview() {
            let payload = null;
            try {
                payload = buildStructuredPayload();
            } catch (err) {
                console.error('Preview build failed', err);
                alert('Could not build preview. Please check your entries and try again.');
                return;
            }
            if (REQUIRE_MASTER_DMC_CITY && (!payload || !Array.isArray(payload.Master_DMC) || !payload.Master_DMC.length)) {
                alert('Master DMC, DMC and city are required before preview.');
                return;
            }

            const body = document.getElementById('packagePreviewBody');
            if (!body) return;
            body.innerHTML = renderPackagePreviewHtml(payload);

            const modalEl = document.getElementById('packagePreviewModal');
            if (!modalEl || typeof bootstrap === 'undefined') {
                alert('Preview is ready but the modal could not be opened. Please refresh the page.');
                return;
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        function setButtonLoading(btn) {
            if (!btn || btn.dataset.loading === '1') return;
            btn.dataset.loading = '1';
            btn.dataset.originalHtml = btn.innerHTML;
            const label = btn.dataset.loadingText || 'Please wait...';
            btn.disabled = true;
            btn.classList.add('is-loading');
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${label}`;
        }

        function clearButtonLoading(btn) {
            if (!btn || btn.dataset.loading !== '1') return;
            btn.dataset.loading = '';
            btn.disabled = false;
            btn.classList.remove('is-loading');
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }

        function submitFromPreview() {
            const confirmBtn = document.getElementById('previewConfirmBtn');
            if (confirmBtn?.dataset.loading === '1') return;
            const payload = preparePayloadForSubmit();
            if (REQUIRE_MASTER_DMC_CITY && !payload) {
                alert('Master DMC, DMC and city are required.');
                return;
            }
            applyPayloadJsonToForm(payload);
            setButtonLoading(confirmBtn);
            const modalEl = document.getElementById('packagePreviewModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) instance.hide();
            }
            document.getElementById('dayForm').requestSubmit();
        }

        function resetAll() {
            hotels = [];
            dayItems = [];
            inter = [];
            multiCityPlans = [];
            editingMultiCityIndex = null;
            editingHotelIndex = null;
            editingActivityIndex = null;
            activeDay = 1;
            dayTransferExtras = {};
            initDays();
            renderHotelRows();
            renderActivityRows();
            renderInterRows();
            renderMultiCityRows();
            setSectionCityOptions();
            syncSectionCitySelectionsFromMain();
            const hotelBtn = document.getElementById('hotelAddBtn');
            const attrBtn = document.getElementById('attractionAddBtn');
            const restBtn = document.getElementById('restaurantAddBtn');
            const multiBtn = document.getElementById('multiCityAddBtn');
            if (hotelBtn) hotelBtn.textContent = 'Add Hotel';
            if (attrBtn) attrBtn.textContent = 'Add Attraction';
            if (restBtn) restBtn.textContent = 'Add Restaurant';
            if (multiBtn) multiBtn.textContent = 'Add';
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        document.addEventListener('DOMContentLoaded', function () {
            initSearchableSelects(document);
            cacheAllCities();
            syncCountryFromCityOrDefault(document.getElementById('country')?.value || '');
            applyCountryFilter();
            setSectionCityOptions();
            initDays();
            renderHotelRows();
            renderMultiCityRows();
            renderActivityRows();
            renderInterRows();
            toggleRestaurantTransferConfig();
            syncSectionCitySelectionsFromMain();
            hydrateFromEditPayload().catch(() => {});

            document.getElementById('days').addEventListener('input', function () {
                const newDays = Math.max(1, parseInt(this.value || '1', 10) || 1);
                const oldDays = daysCount;
                if (multiCityPlans.length && newDays !== oldDays) {
                    let plansChanged = false;
                    multiCityPlans = multiCityPlans.map((p, idx, arr) => {
                        let din = parseInt(String(p.day_in || 0), 10) || 0;
                        let dout = parseInt(String(p.day_out || 0), 10) || 0;
                        const next = { ...p };
                        if (dout > newDays) {
                            next.day_out = newDays;
                            plansChanged = true;
                        }
                        if (newDays > oldDays && dout === oldDays && (arr.length === 1 && din === 1 || idx === arr.length - 1)) {
                            next.day_out = newDays;
                            plansChanged = true;
                        }
                        return next;
                    });
                    if (plansChanged) {
                        renderMultiCityRows();
                    }
                }
                initDays();
                renderHotelRows();
                renderActivityRows();
                if (multiCityPlans.length) {
                    setSectionCityOptions();
                    syncHotelDayDropdownWithMultiCity();
                }
            });

            // Select2 emits jQuery events; bind through jQuery to ensure onchange always fires.
            $('#hotel_category').on('change select2:select select2:clear', function () {
                if (isPrefillingHotelForm) return;
                filterHotelOptions();
            });

            $('#hotel_select').on('change select2:select select2:clear', function () {
                if (isPrefillingHotelForm) return;
                syncHotelDayDropdownWithMultiCity();
                loadRoomsForSelectedHotel();
                applyTransferDefaults();
            });
            $('#hotel_room_select').on('change select2:select select2:clear', function () {
                if (isPrefillingHotelForm) return;
                applyHotelRoomBasePrice();
                loadBedsForSelectedRoom();
                loadMealPlansForSelectedHotel();
            });
            $('#hotel_bed_select').on('change select2:select select2:clear', function () {
                if (isPrefillingHotelForm) return;
            });
            $('#hotel_meal_plan').on('change select2:select select2:clear', function () {
                toggleHotelMealTypeVisibility();
            });
            $(document).on('change select2:select select2:clear', '[id^="transfer_city_select_"]', function () {
                if (isSyncingTransferCity || isApplyingTransferDefaults || isHydratingDayServices) return;
                const dayVal = getDayFromElementId(this.id);
                if (!transferSectionActiveForDay(dayVal)) return;
                hotelTransferState.city = this.value || '';
                invalidateTransferOptionsCacheForDay(dayVal);
                loadTransferOptionsForCity(dayVal, { force: true });
            });
            $(document).on('change select2:select select2:clear', '[id^="arrival_pickup_select_"], [id^="departure_pickup_select_"]', function () {
                hotelTransferState.pickup = this.value || '';
                const dayVal = getDayFromElementId(this.id);
                const prefix = String(this.id || '').startsWith('arrival_') ? 'arrival' : 'departure';
                clearTransferLegPriceManualFlag(prefix, dayVal);
                fetchTransferZonePrice(prefix, dayVal);
            });
            $(document).on('change select2:select select2:clear', '[id^="arrival_drop_select_"], [id^="departure_drop_select_"]', function () {
                hotelTransferState.drop = this.value || '';
                const dayVal = getDayFromElementId(this.id);
                const prefix = String(this.id || '').startsWith('arrival_') ? 'arrival' : 'departure';
                clearTransferLegPriceManualFlag(prefix, dayVal);
                fetchTransferZonePrice(prefix, dayVal);
            });
            $(document).on('input', '.transfer-leg-price-input', function () {
                this.dataset.manualPrice = '1';
                delete this.dataset.zoneAuto;
            });

            $('#city_id').on('change', function () {
                if (isSyncingCitySelectors) return;
                syncCountryFromCityOrDefault();
                autoLoadBySelection();
                syncSectionCitySelectionsFromMain();
                invalidateTransferOptionsCache();
                hydrateAllDayTransferCityOptions();
                scheduleTransferOptionsReload(true);
            });

            $('#hotel_city_select').on('change select2:select select2:clear', function () {
                if (isPrefillingHotelForm || isSyncingCitySelectors) return;
                syncMainCityFromSection('hotel_city_select');
                loadHotelCityServices();
                // Existing hotel rows keep their own ranges; only refresh the new row helper.
                syncHotelDayDropdownWithMultiCity();
                if (!hotelTransferState.city) {
                    hotelTransferState.city = this.value || '';
                    isSyncingTransferCity = true;
                    for (let d = 1; d <= daysCount; d++) {
                        safeSetSelectValueSilent(`transfer_city_select_${d}`, this.value || '');
                    }
                    isSyncingTransferCity = false;
                }
                if (isArrivalDepartureTransfersActive() || itineraryUsesMiddleDayTransfers()) {
                    invalidateTransferOptionsCache();
                    scheduleTransferOptionsReload(true);
                }
            });

            $(document).on('change select2:select select2:clear', '[id^="attraction_select_"]', function () {
                if (isPrefillingActivityForm) return;
                const d = getDayFromElementId(this.id);
                loadTicketsForAttractionForDay(d);
            });
            $(document).on('change select2:select select2:clear', '[id^="attraction_ticket_select_"]', function () {
                if (isPrefillingActivityForm) return;
                applyAttractionTicketPrice(getDayFromElementId(this.id));
            });

            $(document).on('change select2:select select2:clear', '[id^="restaurant_select_"]', function () {
                if (isPrefillingActivityForm) return;
                const d = getDayFromElementId(this.id);
                loadMealsForRestaurantForDay(d);
            });

            $(document).on('change select2:select select2:clear', '[id^="restaurant_meal_period_"]', function () {
                if (isPrefillingActivityForm) return;
                const d = getDayFromElementId(this.id);
                loadMealsForRestaurantForDay(d);
            });
            $(document).on('change select2:select select2:clear', '[id^="restaurant_meal_select_"]', function () {
                if (isPrefillingActivityForm) return;
                applyRestaurantMealPrice(getDayFromElementId(this.id));
            });

            $(document).on('change select2:select select2:clear', '[id^="activity_city_select_"]', function () {
                if (isPrefillingActivityForm || isHydratingDayServices || isSyncingCitySelectors) return;
                const dayVal = getDayFromElementId(this.id);
                const cityName = getCityNameFromSelect(this.id);
                const prevCity = String(activityCityByDay[dayVal] || '');
                if (prevCity === cityName) return;
                activityCityByDay[dayVal] = cityName;
                syncTransferCityFromActivity(dayVal);
                invalidateTransferOptionsCacheForDay(dayVal);
                populateDayServiceOptionsByCity(dayVal, cityName, { loadTransfers: false, silent: true });
                scheduleTransferOptionsReload(true);
            });

            $(document).on('change select2:select select2:clear', '[id^="attraction_select_"]', function () {
                if (isPrefillingActivityForm) return;
                applyAttractionTransferDefaults(getDayFromElementId(this.id));
            });

            $(document).on('change select2:select select2:clear', '[id^="restaurant_select_"]', function () {
                if (isPrefillingActivityForm) return;
                applyRestaurantTransferDefaults(getDayFromElementId(this.id));
            });

            $(document).on('change select2:select select2:clear', '.extra-transfer-input', function () {
                const dayVal = parseInt(String(this.dataset.extraDay || ''), 10);
                const idx = parseInt(String(this.dataset.extraIdx || ''), 10);
                const key = String(this.dataset.extraKey || '');
                if (!Number.isFinite(dayVal) || !Number.isFinite(idx) || !key) return;
                updateExtraTransferValue(dayVal, idx, key, this.value || '');
            });

            toggleHotelMealTypeVisibility();
            toggleHotelTransferFields();
            hydrateAllDayTransferCityOptions();

            document.getElementById('dayForm').addEventListener('submit', function (e) {
                const submitBtn = document.getElementById('mainSubmitBtn');
                let payload = null;
                try {
                    payload = preparePayloadForSubmit();
                } catch (err) {
                    e.preventDefault();
                    clearButtonLoading(submitBtn);
                    clearButtonLoading(document.getElementById('previewConfirmBtn'));
                    console.error('Failed to build payload_json', err);
                    alert('Payload generation failed. Please check required fields and try again.');
                    return;
                }
                if (REQUIRE_MASTER_DMC_CITY && !payload) {
                    e.preventDefault();
                    clearButtonLoading(submitBtn);
                    clearButtonLoading(document.getElementById('previewConfirmBtn'));
                    alert('Master DMC, DMC and city are required.');
                    return;
                }
                applyPayloadJsonToForm(payload);
                setButtonLoading(submitBtn);
            });
        });
    </script>
@endpush
