@extends('layouts.layout')

@section('title', 'Day Level')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --form-brand: #667eea;
            --form-brand-end: #764ba2;
            --form-brand-hover: #5a67d8;
            --form-gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --form-gradient-page: linear-gradient(135deg, #4facfe 0%, #00c9ff 100%);
            --form-gradient-teal: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%);
            --form-gradient-info: linear-gradient(135deg, #0dcaf0 0%, #0d6efd 100%);
            --form-gradient-success: linear-gradient(135deg, #198754 0%, #20c997 100%);
            --form-header-bg: #667eea;
            --form-danger: #dc3545;
            --form-danger-hover: #bb2d3b;
            --form-secondary: #8592a3;
            --form-secondary-hover: #6c757d;
            --form-label-color: #495057;
            --form-border: #dee2e6;
            --form-panel-bg: linear-gradient(135deg, #f8f9ff 0%, #e7f3ff 100%);
        }
        #dayForm .form-label {
            color: var(--form-label-color);
            font-size: 0.875rem;
            font-weight: 600;
        }
        #dayForm .form-control,
        #dayForm .form-select,
        .select2-container .select2-selection--single {
            min-height: 40px;
            border: 1px solid var(--form-border);
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .select2-container .select2-selection--single {
            height: 40px;
            padding-top: 5px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 16px;
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
            border: 1px solid rgba(102, 126, 234, 0.15);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.08);
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
            padding: 1.75rem;
            background: #fff;
        }
        .stp-page-banner .card-header {
            background: var(--form-gradient-page);
            border: none;
            padding: 1.25rem 1.75rem;
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
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
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
            padding: 1.15rem 1.5rem;
            color: #fff;
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
        #dayForm .day-card .day-header-primary { background: var(--form-gradient-primary); }
        #dayForm .day-card .day-header-success { background: var(--form-gradient-success); }
        #dayForm .day-card .day-header-warning { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
        #dayForm .day-card .day-header-danger { background: linear-gradient(135deg, #dc3545 0%, #e35d6a 100%); }
        #dayForm .day-card .day-header-info { background: var(--form-gradient-info); }
        #dayForm .day-card .day-header-purple { background: linear-gradient(135deg, #8b5cf6 0%, #6f42c1 100%); }
        #dayForm .day-card-header {
            color: #fff;
            border-bottom: 0;
        }
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
            box-shadow: 0 0.45rem 1.25rem rgba(67, 89, 113, 0.10);
            overflow: hidden;
            margin-bottom: 1.75rem !important;
        }
        .day-card > .card-body {
            padding-top: 1.75rem !important;
        }
        #dayWiseServiceBlocks {
            padding-top: 1.25rem;
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
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
            transform: translateY(-1px);
        }
        #dayForm .btn-primary,
        #packagePreviewModal .btn-primary {
            color: #fff !important;
            border: none !important;
            background: var(--form-gradient-primary) !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
        }
        #dayForm .btn-primary:hover,
        #dayForm .btn-primary:focus,
        #dayForm .btn-primary:active,
        #packagePreviewModal .btn-primary:hover,
        #packagePreviewModal .btn-primary:focus,
        #packagePreviewModal .btn-primary:active {
            color: #fff !important;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.45);
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
            width: 2rem;
            height: 2rem;
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
            color: #fff;
        }
        .day-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
            height: 2.25rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            font-weight: 700;
        }
        .detail-chip {
            display: inline-block;
            padding: 0.22rem 0.5rem;
            border-radius: 999px;
            background: #f0f4ff;
            color: var(--form-brand);
            border: 1px solid rgba(102, 126, 234, 0.2);
            font-weight: 600;
            margin: 0.1rem 0.15rem 0.1rem 0;
        }
        .day-card .fw-semibold.text-primary {
            color: var(--form-brand) !important;
            background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
            border: 1px solid rgba(102, 126, 234, 0.15);
            border-radius: 8px;
            padding: 0.5rem 0.85rem;
            font-size: 0.9rem;
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
            padding: 1rem 1.25rem;
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
            padding: 1.25rem 1.5rem 1.5rem;
        }
        .hotels-form-panel {
            background: var(--form-panel-bg);
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.08);
            padding: 1.15rem 1.25rem 1.25rem;
        }
        .hotels-form-panel + .hotels-form-panel {
            margin-top: 1rem;
        }
        .hotels-form-panel-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--form-brand);
            margin-bottom: 0.85rem;
        }
        .hotels-form-panel .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 0.35rem;
        }
        .hotels-form-panel .form-control,
        .hotels-form-panel .form-select {
            font-size: 0.875rem;
            border-color: #d9dee3;
            min-height: 38px;
        }
        .hotels-form-panel .form-check {
            min-height: 38px;
            display: flex;
            align-items: center;
            padding-left: 1.75rem;
            margin-bottom: 0;
        }
        .hotels-form-panel .form-check-input {
            margin-top: 0;
        }
        .hotels-form-panel .form-check-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #566a7f;
            padding-left: 2px;
        }
        #hotel_priority {
            max-width: 5.5rem;
        }
        .hotels-add-btn {
            min-height: 38px;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }
        .hotels-section .modern-table-wrap {
            margin-top: 1.25rem;
        }
        #packagePreviewModal .modal-header {
            background: var(--form-gradient-primary);
            color: #fff !important;
            border-bottom: 0;
            padding: 1rem 1.25rem;
        }
        #dayForm .form-actions-bar {
            padding: 1.25rem;
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
            border: 1px solid rgba(102, 126, 234, 0.15);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.08);
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
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row mb-4 stp-page-banner">
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
                                <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
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
                                <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
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
                                <div style="width:36px;height:36px;background:rgba(73,80,87,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="ri-hotel-line" style="color:#495057;"></i>
                                </div>
                                <div>
                                    <strong class="d-block">Hotels</strong>
                                    <span class="section-subtitle">Add accommodation, meals, and optional arrival or departure transfers</span>
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
                                    <div class="hotels-form-panel-title">Meals &amp; options</div>
                                    <div class="row g-3 align-items-end">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label" for="hotel_meal_plan">Meal</label>
                                        <select id="hotel_meal_plan" class="form-select searchable-select">
                                            <option value="">Select meal plan</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6" id="hotel_meal_type_wrap">
                                        <label class="form-label" for="hotel_meal_type">Select Dish</label>
                                        <select id="hotel_meal_type" class="form-select searchable-select">
                                            <option value="">Select dish</option>
                                            <option value="Buffet">Buffet</option>
                                            <option value="Set Menu">Set Menu</option>
                                            <option value="A La Carte">A La Carte</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="hotel_guide_required">
                                            <label class="form-check-label" for="hotel_guide_required">Guide</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="hotel_arrival_departure">
                                            <label class="form-check-label" for="hotel_arrival_departure">Arrival / Departure</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label" for="hotel_arrival_departure_type">If Yes, Type</label>
                                        <select id="hotel_arrival_departure_type" class="form-select searchable-select">
                                            <option value="">Select</option>
                                            <option value="private">Private</option>
                                            <option value="shared">Shared</option>
                                        </select>
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

                                <div class="table-responsive modern-table-wrap">
                                    <table class="table table-sm data-table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Day</th>
                                                <th>City</th>
                                                <th>Category</th>
                                                <th>Hotel</th>
                                                <th>Night</th>
                                                <th>Meal</th>
                                                <th>Select Dish</th>
                                                <th>Guide</th>
                                                <th>Arrival/Departure</th>
                                                <th>Type</th>
                                                <th>Priority</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="hotelRows">
                                            <tr><td colspan="12" class="text-muted">No hotels added</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card sketch-card attraction-day-section border-0">
                            <div class="card-header modern-section-header text-white d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
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
                            <button type="submit" class="btn btn-primary btn-lg px-5">{{ isset($dayLevel) ? 'Update' : 'Submit' }}</button>
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
                            <button type="button" class="btn btn-primary" onclick="submitFromPreview()">Confirm &amp; {{ isset($dayLevel) ? 'Update' : 'Submit' }}</button>
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
        let attractionsCache = [];
        let restaurantsCache = [];
        let transferLocationOptions = [];
        let zoneTransferOptions = [];
        /** value (port:6, hotel:12, zone:3) → human-readable label from API / selects */
        let transferLocationLabelByValue = {};
        let transferDefaults = { defaultPort: '' };
        let hotelTransferState = { city: '', pickup: '', drop: '' };
        let dayTransferExtras = {};

        function hotelsHaveArrivalDepartureTransferSaved() {
            return hotels.some(h => String(h.arrival_departure || 'No') === 'Yes');
        }

        function isArrivalDepartureTransfersActive() {
            return !!document.getElementById('hotel_arrival_departure')?.checked || hotelsHaveArrivalDepartureTransferSaved();
        }

        function getDayOneHotelDropValueAndLabel() {
            const sorted = [...hotels].sort((a, b) => (parseInt(String(a.day || 0), 10) || 0) - (parseInt(String(b.day || 0), 10) || 0));
            const row = sorted.find(h => (parseInt(String(h.day || 1), 10) || 1) === 1);
            if (!row || !String(row.hotel_id || '').trim()) {
                return { value: '', label: '' };
            }
            const name = String(row.hotel_name || '').replace(/^\s+|\s+$/g, '') || 'Day 1 hotel';
            return { value: `hotel:${String(row.hotel_id)}`, label: name };
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
                const hid = String(h.hotel_id || '').trim();
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

        function mergeTransferLocationLabels(optionRows) {
            (Array.isArray(optionRows) ? optionRows : []).forEach((row) => {
                const value = String(row?.value ?? '').trim();
                const label = String(row?.label ?? '').trim();
                if (value && label) {
                    transferLocationLabelByValue[value] = label;
                }
            });
        }

        function labelFromAnyTransferSelect(value) {
            const v = String(value || '').trim();
            if (!v) return '';
            const selects = document.querySelectorAll(
                'select[id*="pickup_select"], select[id*="drop_select"], select[id^="transfer_pickup"], select[id^="transfer_drop"]'
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
            const hit = [...transferLocationOptions, ...zoneTransferOptions].find((x) => String(x.value) === v);
            if (hit?.label) return String(hit.label);
            if (v.startsWith('hotel:')) {
                const hotelId = v.replace(/^hotel:/, '');
                const hotelHit = (Array.isArray(hotels) ? hotels : []).find(h => String(h.hotel_id || h.id || '') === hotelId);
                if (hotelHit?.hotel_name) return String(hotelHit.hotel_name);
                const flatHit = (Array.isArray(hotelsFlat) ? hotelsFlat : []).find(h => String(h.id || h.hotel_id || '') === hotelId);
                if (flatHit?.name || flatHit?.hotel_name) return String(flatHit.name || flatHit.hotel_name);
            }
            if (v.startsWith('port:') || v.startsWith('zone:')) {
                return '';
            }
            return v;
        }

        function labelForSelectValue(selectId, value) {
            const v = String(value || '').trim();
            if (!v) return '';
            const select = document.getElementById(selectId);
            const option = select ? Array.from(select.options).find(opt => String(opt.value) === v) : null;
            return option?.textContent ? option.textContent.trim() : '';
        }

        function resolveTransferSelectId(dayVal, field, transferType) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const type = String(transferType || '').trim().toLowerCase();
            const isPickup = field === 'pickup';
            if (type === 'arrival') {
                return isPickup ? `arrival_pickup_select_${d}` : `arrival_drop_select_${d}`;
            }
            if (type === 'departure') {
                return isPickup ? `departure_pickup_select_${d}` : `departure_drop_select_${d}`;
            }
            return isPickup ? `transfer_pickup_select_${d}` : `transfer_drop_select_${d}`;
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
            if (saved && saved !== v && !saved.startsWith('port:') && !saved.startsWith('zone:') && !saved.startsWith('hotel:')) {
                return saved;
            }
            return labelForSelectValue(selectId, v) || labelForStoredTransferLocation(v) || v;
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
                setSelectOptions(`transfer_pickup_select_${d}`, []);
                setSelectOptions(`transfer_drop_select_${d}`, []);
                safeSetSelectValue(`transfer_city_select_${d}`, '');
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

        function setSelectOptions(selectId, options) {
            const select = document.getElementById(selectId);
            if (!select) return;
            select.innerHTML = '<option value=""></option>';
            options.forEach((opt) => {
                const op = document.createElement('option');
                op.value = String(opt.value ?? '');
                op.textContent = opt.label ?? '';
                if (opt.price !== undefined) op.dataset.price = String(opt.price);
                if (opt.rate !== undefined) op.dataset.rate = String(opt.rate);
                if (opt.data_name !== undefined) op.dataset.name = String(opt.data_name);
                if (opt.data_country !== undefined) op.dataset.country = String(opt.data_country);
                if (opt.data_day_in !== undefined) op.dataset.dayIn = String(opt.data_day_in);
                if (opt.data_day_out !== undefined) op.dataset.dayOut = String(opt.data_day_out);
                select.appendChild(op);
            });
            initSearchableSelects(select);
            $(select).trigger('change.select2');
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
            const selectedCountry = String(document.getElementById('country').value || '').trim().toLowerCase();
            const filteredByCountry = allCities.filter(c => {
                if (!selectedCountry) return true;
                return String(c.country || '').trim().toLowerCase() === selectedCountry;
            });
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

        function setSectionCityOptions() {
            const selectedCountry = document.getElementById('country').value || '';
            const filtered = allCities.filter(c => !selectedCountry || c.country === selectedCountry);
            const prevHotelCity = $('#hotel_city_select').val();
            const options = filtered.map(c => ({
                value: c.value,
                label: c.country ? `${c.name}, ${c.country}` : c.name,
                data_name: c.name,
                data_country: c.country,
            }));
            setSelectOptions('hotel_city_select', options);

            if (prevHotelCity && options.some(o => String(o.value) === String(prevHotelCity))) {
                safeSetSelectValue('hotel_city_select', prevHotelCity);
            }
            hydrateDayServiceBlocksOptions();
        }

        function syncSectionCitySelectionsFromMain() {
            if (isSyncingCitySelectors) return;
            if (multiCityPlans.length) {
                // Multi City is the single source of truth for day-wise cities.
                syncDayCitySelectorsFromMultiCity();
                return;
            }
            isSyncingCitySelectors = true;
            const cityOp = getSelectedOption('city_id');
            if (!cityOp) {
                isSyncingCitySelectors = false;
                return;
            }
            safeSetSelectValue('hotel_city_select', cityOp.value);
            for (let d = 1; d <= daysCount; d++) {
                safeSetSelectValue(`activity_city_select_${d}`, cityOp.value);
            }
            isSyncingCitySelectors = false;
        }

        function syncMainCityFromSection(sectionSelectId) {
            if (isSyncingCitySelectors) return;
            const op = getSelectedOption(sectionSelectId);
            if (!op) return;
            isSyncingCitySelectors = true;
            $('#city_id').val(String(op.value)).trigger('change');
            isSyncingCitySelectors = false;
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
            const payload = {
                city_id: cityOp.value,
                city_name: cityOp.dataset.name || cityOp.textContent || '',
                day_in: dayIn,
                day_out: dayOut
            };
            if (editingMultiCityIndex !== null && multiCityPlans[editingMultiCityIndex]) {
                multiCityPlans[editingMultiCityIndex] = payload;
                editingMultiCityIndex = null;
                const btn = document.getElementById('multiCityAddBtn');
                if (btn) btn.textContent = 'Add';
            } else {
                multiCityPlans.push(payload);
            }
            renderMultiCityRows();
            safeSetSelectValue('city_id', '');
            safeSetSelectValue('mc_day_in', '');
            safeSetSelectValue('mc_day_out', '');
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

        function syncDayCitySelectorsFromMultiCity() {
            // Multi City drives which cities are available; user can still choose.
            if (!multiCityPlans.length) {
                return;
            }
            for (let d = 1; d <= daysCount; d++) {
                const planForDay = getMultiCityPlanForDay(d);
                const cityId = planForDay?.city_id;
                // Default to Multi City mapped city only when empty.
                const act = document.getElementById(`activity_city_select_${d}`);
                const tr = document.getElementById(`transfer_city_select_${d}`);
                if (cityId && act && !String(act.value || '').trim()) {
                    safeSetSelectValue(`activity_city_select_${d}`, String(cityId));
                }
                if (cityId && tr && !String(tr.value || '').trim()) {
                    safeSetSelectValue(`transfer_city_select_${d}`, String(cityId));
                }
            }
        }

        function getDayCityOptionsFromMultiCity() {
            if (!multiCityPlans.length) return null;
            const selectedCountry = document.getElementById('country').value || '';
            const filteredByCountry = allCities.filter(c => !selectedCountry || c.country === selectedCountry);
            const allowedIds = new Set(
                multiCityPlans
                    .map(p => String(p?.city_id || '').trim())
                    .filter(Boolean)
            );
            const allowedNames = new Set(
                multiCityPlans
                    .map(p => String(p?.city_name || '').split(',')[0].trim().toLowerCase())
                    .filter(Boolean)
            );
            const filtered = filteredByCountry.filter(c => {
                if (allowedIds.has(String(c.value))) return true;
                return allowedNames.has(String(c.name || '').trim().toLowerCase());
            });
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
                    <div class="card sketch-card day-card mb-3">
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
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" for="activity_city_select_${d}">City</label>
                                <select id="activity_city_select_${d}" class="form-select searchable-select">
                                    <option value="">Select city</option>
                                </select>
                            </div>

                            <div class="col-12"><div class="fw-semibold text-primary">Attraction</div></div>
                            <div class="col-md-4">
                                <label class="form-label" for="attraction_select_${d}">Attraction</label>
                                <select id="attraction_select_${d}" class="form-select searchable-select">
                                    <option value="">Select attraction</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="attraction_ticket_select_${d}">Select Ticket</label>
                                <select id="attraction_ticket_select_${d}" class="form-select searchable-select">
                                    <option value="">Select ticket</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex">
                                <button type="button" class="btn btn-outline-primary w-100 mt-4" onclick="addAttractionItemForDay(${d})">Add Attraction</button>
                            </div>

                            <div class="col-12"><div class="fw-semibold text-primary">Restaurant</div></div>
                            <div class="col-md-4">
                                <label class="form-label" for="restaurant_select_${d}">Restaurant</label>
                                <select id="restaurant_select_${d}" class="form-select searchable-select">
                                    <option value="">Select restaurant</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="restaurant_dish_type_${d}">Select Dish</label>
                                <select id="restaurant_dish_type_${d}" class="form-select searchable-select">
                                    <option value="">Select dish</option>
                                    <option value="Buffet">Buffet</option>
                                    <option value="Set Menu">Set Menu</option>
                                    <option value="A La Carte">A La Carte</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex">
                                <button type="button" class="btn btn-outline-primary w-100 mt-4" onclick="addRestaurantItemForDay(${d})">Add Restaurant</button>
                            </div>

                            <div class="col-12"><div class="fw-semibold text-primary">Transfer</div></div>
                            <div class="col-12 day-transfer-wrap" id="day_transfer_wrap_${d}">
                                <div class="row g-3 align-items-end mt-1">
                                    <div class="col-md-3">
                                        <label class="form-label" for="transfer_city_select_${d}">City</label>
                                        <select id="transfer_city_select_${d}" class="form-select searchable-select">
                                            <option value="">Select city</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="transfer_pickup_select_${d}">Pickup Location</label>
                                        <select id="transfer_pickup_select_${d}" class="form-select searchable-select">
                                            <option value="">Select pickup</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="transfer_drop_select_${d}">Drop Location</label>
                                        <select id="transfer_drop_select_${d}" class="form-select searchable-select">
                                            <option value="">Select drop</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex">
                                        <button type="button" class="btn btn-outline-primary w-100 mt-4" onclick="addTransferItemForDay(${d})">Add Transfer</button>
                                    </div>
                                </div>

                                <div class="row g-3 align-items-end mt-2">
                                    <div class="col-12"><div class="fw-semibold text-primary">Arrival</div></div>
                                    <div class="col-md-5">
                                        <label class="form-label" for="arrival_pickup_select_${d}">Pickup Location</label>
                                        <select id="arrival_pickup_select_${d}" class="form-select searchable-select">
                                            <option value="">Select pickup</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label" for="arrival_drop_select_${d}">Drop Location</label>
                                        <select id="arrival_drop_select_${d}" class="form-select searchable-select">
                                            <option value="">Select drop</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex">
                                        <button type="button" class="btn btn-outline-primary w-100 mt-4" onclick="addArrivalItemForDay(${d})">Add Arrival</button>
                                    </div>
                                </div>

                                <div class="row g-3 align-items-end mt-2">
                                    <div class="col-12"><div class="fw-semibold text-primary">Departure</div></div>
                                    <div class="col-md-5">
                                        <label class="form-label" for="departure_pickup_select_${d}">Pickup Location</label>
                                        <select id="departure_pickup_select_${d}" class="form-select searchable-select">
                                            <option value="">Select pickup</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label" for="departure_drop_select_${d}">Drop Location</label>
                                        <select id="departure_drop_select_${d}" class="form-select searchable-select">
                                            <option value="">Select drop</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex">
                                        <button type="button" class="btn btn-outline-primary w-100 mt-4" onclick="addDepartureItemForDay(${d})">Add Departure</button>
                                    </div>
                                </div>

                                <div class="mt-2 extra-transfer-wrap" id="extra_transfer_wrap_${d}" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Additional transfer options (for transfer days)</small>
                                    </div>
                                    <div id="extra_transfer_rows_${d}"></div>
                                </div>
                            </div>

                            <div class="mt-3" id="day_items_${d}"></div>
                        </div>
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

        function hydrateDayServiceBlocksOptions() {
            const selectedCountry = document.getElementById('country').value || '';
            const filteredCities = allCities.filter(c => !selectedCountry || c.country === selectedCountry);
            const multiCityFiltered = getDayCityOptionsFromMultiCity();
            const source = (multiCityFiltered && multiCityFiltered.length) ? multiCityFiltered : filteredCities;
            const cityOptions = source.map(c => ({
                value: c.value,
                label: c.country ? `${c.name}, ${c.country}` : c.name,
                data_name: c.name,
                data_country: c.country,
            }));
            for (let d = 1; d <= daysCount; d++) {
                setSelectOptions(`activity_city_select_${d}`, cityOptions);
                setSelectOptions(`attraction_select_${d}`, attractionsCache.map(x => ({
                    value: x.attraction_id,
                    label: x.name + (x.location ? ` - ${x.location}` : ''),
                    price: x.adult_price || 0
                })));
                setSelectOptions(`restaurant_select_${d}`, restaurantsCache.map(x => ({
                    value: x.restaurant_id,
                    label: x.name + (x.city ? ` - ${x.city}` : '')
                })));
                setSelectOptions(`attraction_ticket_select_${d}`, []);
            }
            // If Multi City exists, default day city to mapped city (without locking).
            if (multiCityPlans.length) {
                syncDayCitySelectorsFromMultiCity();
            }
            hydrateAllDayTransferCityOptions();
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

        function resolveCityIdsForMultiPlans() {
            const citySelect = document.getElementById('city_id');
            if (!citySelect) return;
            multiCityPlans.forEach((plan) => {
                if (!plan || plan.city_id) return;
                const key = String(plan.city_name || '').trim().toLowerCase();
                if (!key) return;
                const match = Array.from(citySelect.options).find(opt => {
                    const nm = String(opt.dataset.name || opt.textContent || '').trim().toLowerCase();
                    return nm === key;
                });
                if (match) plan.city_id = String(match.value);
            });
        }

        function hydrateFromEditPayload() {
            const payload = window.__EDIT_PAYLOAD__;
            if (!payload || !Array.isArray(payload.Master_DMC) || !payload.Master_DMC.length) {
                return;
            }

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
                            hotels.push({
                                day: dayNum,
                                cat: '',
                                cat_label: '',
                                hotel_id: String(h.hotel_id || ''),
                                hotel_name: String(h.hotel_name || ''),
                                city_name: String(h.city || cityName || ''),
                                night: Math.max(0, parseInt(String(h.night || (daysCount - 1)), 10) || Math.max(0, daysCount - 1)),
                                meal_plan: String(h.meal_plan || ''),
                                meal_type: String(h.meal_type || ''),
                                guide_required: String(h.guide_required || 'No'),
                                arrival_departure: String(h.arrival_departure || 'No'),
                                arrival_departure_type: String(h.arrival_departure_type || ''),
                                transfer_city: String(h.transfer_city || ''),
                                transfer_pickup: String(h.transfer_pickup || ''),
                                transfer_drop: String(h.transfer_drop || ''),
                                price: parseFloat(String(h.price || 0)) || 0,
                                priority: parseInt(String(h.priority || 1), 10) || 1
                            });
                        });

                        const attrVals = Object.values(dayNode.attractions && typeof dayNode.attractions === 'object' ? dayNode.attractions : {});
                        attrVals.forEach(a => {
                            if (!a || typeof a !== 'object') return;
                            const aid = String(a.attraction_id || '');
                            const tid = String(a.ticket_id || '');
                            const dedupeKey = `${dayNum}|attraction|${aid}|${tid}`;
                            if (seenDayActivityKeys.has(dedupeKey)) return;
                            seenDayActivityKeys.add(dedupeKey);
                            const rawT = a.transfer && typeof a.transfer === 'object' ? a.transfer : {};
                            const resolvedAttractionCity = String(
                                a.city || rawT.city || cityName || ''
                            );
                            const hasXfer = String(rawT.required || '').toLowerCase() === 'yes'
                                || !!(rawT.pickup_location || rawT.drop_location);
                            dayItems.push({
                                day: dayNum,
                                type: 'attraction',
                                id: aid,
                                label: String(a.name || ''),
                                city_name: resolvedAttractionCity,
                                ticket_id: tid,
                                ticket_name: String(a.ticket_name || ''),
                                transfer: {
                                    required: hasXfer ? 'Yes' : String(rawT.required || 'No'),
                                    city: String(rawT.city || ''),
                                    pickup_location: String(rawT.pickup_location || ''),
                                    drop_location: String(rawT.drop_location || ''),
                                    transfer_type: String(rawT.transfer_type || ''),
                                    additional_transfers: Array.isArray(rawT.additional_transfers) ? rawT.additional_transfers : [],
                                    type: String(rawT.type || ''),
                                    way: String(rawT.way || ''),
                                    vehicle_id: String(rawT.vehicle_id || ''),
                                    vehicle_name: String(rawT.vehicle_name || ''),
                                    pickup_location_id: String(rawT.pickup_location_id || ''),
                                    cost: parseFloat(String(rawT.cost || 0)) || 0,
                                    pickup_time: String(rawT.pickup_time || ''),
                                }
                            });
                            const addlXfer = rawT.additional_transfers;
                            if (Array.isArray(addlXfer) && addlXfer.length) {
                                ensureDayTransferExtras(dayNum);
                                const mapped = addlXfer.map(item => ({
                                    city: String(item?.city ?? ''),
                                    pickup_location: String(item?.pickup_location ?? ''),
                                    drop_location: String(item?.drop_location ?? ''),
                                })).filter(item => item.pickup_location || item.drop_location);
                                dayTransferExtras[dayNum] = [...(dayTransferExtras[dayNum] || []), ...mapped];
                            }
                        });

                        const legacyRestVals = Object.values(dayNode.restaurants && typeof dayNode.restaurants === 'object' ? dayNode.restaurants : {});
                        legacyRestVals.forEach(r => {
                            if (!r || typeof r !== 'object') return;
                            const rid = String(r.restaurant_id || '');
                            const dedupeKey = `${dayNum}|restaurant_raw|${rid}`;
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

                        const serviceVals = Object.values(dayNode.services && typeof dayNode.services === 'object' ? dayNode.services : {});
                        serviceVals.forEach(s => {
                            if (!s || typeof s !== 'object') return;
                            if (String(s.service_type || '').toLowerCase() !== 'restaurant') return;
                            const rid = String(s.restaurant_id || '');
                            const mt = String(s.meal_configuration?.meal_type || '');
                            const dish = String(s.meal_configuration?.dish || '');
                            const dedupeKey = `${dayNum}|restaurant|${rid}|${mt}|${dish}`;
                            if (seenDayActivityKeys.has(dedupeKey)) return;
                            seenDayActivityKeys.add(dedupeKey);
                            dayItems.push({
                                day: dayNum,
                                type: 'restaurant',
                                id: rid,
                                label: String(s.restaurant_name || ''),
                                city_name: String(s.city || cityName || ''),
                                meal: {
                                    meal_type: mt,
                                    dish: dish,
                                    time_slot: String(s.meal_configuration?.time_slot || '')
                                },
                                transfer: {
                                    required: String(s.transfer?.required || 'No'),
                                    city: String(s.transfer?.city || ''),
                                    type: String(s.transfer?.type || ''),
                                    way: String(s.transfer?.way || ''),
                                    vehicle_id: String(s.transfer?.vehicle_id || ''),
                                    vehicle_name: String(s.transfer?.vehicle_name || ''),
                                    pickup_location_id: String(s.transfer?.pickup_location_id || ''),
                                    pickup_location: String(s.transfer?.pickup_location || ''),
                                    drop_location: String(s.transfer?.drop_location || ''),
                                    cost: parseFloat(String(s.transfer?.cost || 0)) || 0,
                                    pickup_time: String(s.transfer?.pickup_time || '')
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

            const dedupedHotels = [];
            const seenHotelDayKeys = new Set();
            hotels.forEach((h) => {
                const hotelId = String(h.hotel_id || '').trim();
                const dayNum = parseInt(String(h.day || 1), 10) || 1;
                const key = `${dayNum}|${hotelId}`;
                if (!hotelId || seenHotelDayKeys.has(key)) return;
                seenHotelDayKeys.add(key);
                dedupedHotels.push({
                    ...h,
                    day: dayNum,
                    night: parseInt(String(h.night || Math.max(1, daysCount - 1)), 10) || Math.max(1, daysCount - 1),
                });
            });
            hotels = dedupedHotels;

            renderMultiCityRows();
            syncHotelNightsWithDays();
            renderHotelRows();
            renderActivityRows();
            hydrateAllDayTransferCityOptions();
            // Multi City drives day-wise city mapping for attractions/restaurants/transfers.
            syncDayCitySelectorsFromMultiCity();
            updateAllDayTransferVisibility();
            renderAllExtraTransferRows();
        }

        function toggleHotelTransferFields() {
            const isChecked = document.getElementById('hotel_arrival_departure')?.checked;
            const locked = hotelsHaveArrivalDepartureTransferSaved();
            updateAllDayTransferVisibility();
            if (!isChecked && !locked) {
                hotelTransferState = { city: '', pickup: '', drop: '' };
                clearEndDayTransferSelectionsOnly();
            }
        }

        function updateAllDayTransferVisibility() {
            for (let d = 1; d <= daysCount; d++) {
                const wrap = document.getElementById(`day_transfer_wrap_${d}`);
                if (wrap) {
                    // Transfer inputs are available for every day (Arrival/Departure/Transfer).
                    wrap.style.display = '';
                }
                const extraWrap = document.getElementById(`extra_transfer_wrap_${d}`);
                if (extraWrap) {
                    const showExtra = daysCount >= 3 && d > 1 && d < daysCount;
                    extraWrap.style.display = showExtra ? '' : 'none';
                }
            }

            const dmcOk = !!(document.getElementById('dmc_id')?.value);
            if (transferLocationOptions.length === 0 && dmcOk) {
                loadTransferOptionsForCity(1);
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
            for (let d = 1; d <= daysCount; d++) {
                setSelectOptions(`transfer_city_select_${d}`, options);
                if (hotelTransferState.city) {
                    safeSetSelectValue(`transfer_city_select_${d}`, hotelTransferState.city);
                } else if (document.getElementById('hotel_city_select')?.value) {
                    safeSetSelectValue(`transfer_city_select_${d}`, document.getElementById('hotel_city_select').value);
                }
            }
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
                    `transfer_pickup_select_${dayVal}`,
                    v
                );
            } else if (key === 'drop_location') {
                dayTransferExtras[dayVal][idx].drop_location_label = getTransferLocationLabel(
                    `transfer_drop_select_${dayVal}`,
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
                if (transfer.pickup_location && !transfer.pickup_location_label) {
                    transfer.pickup_location_label = getTransferLocationLabel(
                        resolveTransferSelectId(day, 'pickup', type),
                        transfer.pickup_location
                    );
                }
                if (transfer.drop_location && !transfer.drop_location_label) {
                    transfer.drop_location_label = getTransferLocationLabel(
                        resolveTransferSelectId(day, 'drop', type),
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
            const dayOptions = getTransferOptionsForDay(dayVal);
            const optionMarkup = dayOptions.map(x => `<option value="${escapeHtml(x.value)}">${escapeHtml(x.label)}</option>`).join('');
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

        function ensureTransferLocationOption(selectId, value, fallbackLabel = '') {
            if (!value) return;
            const select = document.getElementById(selectId);
            if (!select) return;
            const exists = Array.from(select.options).some(opt => String(opt.value) === String(value));
            if (!exists) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = fallbackLabel || value;
                select.appendChild(option);
            }
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

        async function loadTransferOptionsForCity(dayVal = activeDay) {
            const cityName = getCityNameFromSelect(`transfer_city_select_${dayVal}`) || getCityNameFromSelect('hotel_city_select');
            const dmcId = document.getElementById('dmc_id').value || '';
            const url = `/day-level/transfer-options?dmc_id=${encodeURIComponent(dmcId)}&city_name=${encodeURIComponent(cityName || '')}`;
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                transferLocationOptions = Array.isArray(data?.locations) ? data.locations : [];
                zoneTransferOptions = Array.isArray(data?.zones) ? data.zones : [];
                mergeTransferLocationLabels(transferLocationOptions);
                mergeTransferLocationLabels(zoneTransferOptions);
                const portCanon = String(
                    data?.default_port_value ?? data?.default_pickup ?? ''
                ).trim();
                transferDefaults = { defaultPort: portCanon };
            } catch (e) {
                transferLocationOptions = [];
                zoneTransferOptions = [];
                transferDefaults = { defaultPort: '' };
            }
            const day = parseInt(String(dayVal || 1), 10) || 1;
            const dayOptions = getTransferOptionsForDay(day);
            setSelectOptions(
                `transfer_pickup_select_${day}`,
                dayOptions.map(x => ({ value: x.value, label: x.label }))
            );
            setSelectOptions(
                `transfer_drop_select_${day}`,
                dayOptions.map(x => ({ value: x.value, label: x.label }))
            );
            setSelectOptions(
                `arrival_pickup_select_${day}`,
                dayOptions.map(x => ({ value: x.value, label: x.label }))
            );
            setSelectOptions(
                `arrival_drop_select_${day}`,
                dayOptions.map(x => ({ value: x.value, label: x.label }))
            );
            setSelectOptions(
                `departure_pickup_select_${day}`,
                dayOptions.map(x => ({ value: x.value, label: x.label }))
            );
            setSelectOptions(
                `departure_drop_select_${day}`,
                dayOptions.map(x => ({ value: x.value, label: x.label }))
            );
            // Arrival/Departure defaults are only meant for Day 1 + Last Day.
            // Avoid recalculating them when loading middle-day transfer options.
            if (day === 1 || day === daysCount) {
                applyTransferDefaults();
            }
            renderExtraTransferRows(day);
            backfillTransferLabelsOnDayItems();
        }

        function applyTransferDefaults() {
            if (!isArrivalDepartureTransfersActive() && !itineraryUsesMiddleDayTransfers()) return;

            /** Middle itinerary days populate their own attraction transfers — no arrival/hotel defaults. */
            if (!isArrivalDepartureTransfersActive() && itineraryUsesMiddleDayTransfers()) {
                return;
            }

            /** Arrival / Departure scripted defaults only (middle-day attraction transfers unchanged elsewhere). */
            const portVal = String(transferDefaults.defaultPort || '').trim();
            const dayOneHotel = getDayOneHotelDropValueAndLabel();
            const checkoutHotel = getDeparturePickupHotelValueAndLabel();

            const pickup1El = document.getElementById('transfer_pickup_select_1');
            const drop1El = document.getElementById('transfer_drop_select_1');

            /** Day 1 — Arrival: pickup = DMC default port; drop = Day 1 hotel from hotels grid */
            if (pickup1El && portVal && !String(pickup1El.value || '').trim()) {
                ensureTransferLocationOption(
                    'transfer_pickup_select_1',
                    portVal,
                    labelForStoredTransferLocation(portVal)
                );
                safeSetSelectValue('transfer_pickup_select_1', portVal);
            }

            if (drop1El && dayOneHotel.value && !String(drop1El.value || '').trim()) {
                ensureTransferLocationOption(`transfer_drop_select_1`, dayOneHotel.value, dayOneHotel.label || 'Day 1 hotel');
                safeSetSelectValue(`transfer_drop_select_1`, dayOneHotel.value);
            }

            /** Last day — Departure: pickup = hotel covering checkout; drop = same default port */
            if (daysCount >= 2 && portVal) {
                const lastD = daysCount;
                const pickLastEl = document.getElementById(`transfer_pickup_select_${lastD}`);
                const dropLastEl = document.getElementById(`transfer_drop_select_${lastD}`);
                const depHotelVal = checkoutHotel.value;

                if (pickLastEl && depHotelVal && !String(pickLastEl.value || '').trim()) {
                    ensureTransferLocationOption(
                        `transfer_pickup_select_${lastD}`,
                        depHotelVal,
                        checkoutHotel.label || labelForStoredTransferLocation(depHotelVal)
                    );
                    safeSetSelectValue(`transfer_pickup_select_${lastD}`, depHotelVal);
                }
                if (dropLastEl && !String(dropLastEl.value || '').trim()) {
                    ensureTransferLocationOption(
                        `transfer_drop_select_${lastD}`,
                        portVal,
                        labelForStoredTransferLocation(portVal)
                    );
                    safeSetSelectValue(`transfer_drop_select_${lastD}`, portVal);
                }
            }
        }

        function loadCityData() {
            const cityOp = getSelectedOption('city_id');
            if (!cityOp) {
                alert('Please select city.');
                return;
            }

            const cityName = cityOp.dataset.name || cityOp.textContent || '';
            const dmcId = document.getElementById('dmc_id').value;

            fetch(`/day-level/by-city?city_name=${encodeURIComponent(cityName)}&type=all&dmc_id=${encodeURIComponent(dmcId)}`)
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
                const url = `/day-level/tickets-by-attraction?attraction_id=${encodeURIComponent(attractionOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
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

        function getDayTransferPayload(dayVal) {
            const pickupSelectId = `transfer_pickup_select_${dayVal}`;
            const dropSelectId = `transfer_drop_select_${dayVal}`;
            const pickupVal = String(document.getElementById(pickupSelectId)?.value || '').trim();
            const dropVal = String(document.getElementById(dropSelectId)?.value || '').trim();
            const extras = getFilteredAdditionalTransfers(dayVal).map(row => {
                const pickup = String(row.pickup_location || '').trim();
                const drop = String(row.drop_location || '').trim();
                return {
                    city: String(row.city || ''),
                    pickup_location: pickup,
                    pickup_location_label: String(row.pickup_location_label || '').trim()
                        || getTransferLocationLabel(pickupSelectId, pickup)
                        || labelForStoredTransferLocation(pickup),
                    drop_location: drop,
                    drop_location_label: String(row.drop_location_label || '').trim()
                        || getTransferLocationLabel(dropSelectId, drop)
                        || labelForStoredTransferLocation(drop),
                };
            });
            const hasPrimaryTransfer = !!pickupVal || !!dropVal;
            ensureDayTransferExtras(dayVal);
            return {
                required: (hasPrimaryTransfer || extras.length) ? 'Yes' : 'No',
                transfer_type: getTransferTypeLabelForDay(dayVal),
                city: getCityNameFromSelect(`transfer_city_select_${dayVal}`) || '',
                pickup_location: pickupVal,
                pickup_location_label: getTransferLocationLabel(pickupSelectId, pickupVal),
                drop_location: dropVal,
                drop_location_label: getTransferLocationLabel(dropSelectId, dropVal),
                additional_transfers: extras
            };
        }

        function getArrivalTransferPayload(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const pickupSelectId = `arrival_pickup_select_${d}`;
            const dropSelectId = `arrival_drop_select_${d}`;
            const pickupVal = String(document.getElementById(pickupSelectId)?.value || '').trim();
            const dropVal = String(document.getElementById(dropSelectId)?.value || '').trim();
            return {
                required: 'Yes',
                transfer_type: 'Arrival',
                city: getCityNameFromSelect(`transfer_city_select_${d}`) || getCityNameFromSelect(`activity_city_select_${d}`) || '',
                pickup_location: pickupVal,
                pickup_location_label: getTransferLocationLabel(pickupSelectId, pickupVal),
                drop_location: dropVal,
                drop_location_label: getTransferLocationLabel(dropSelectId, dropVal),
                additional_transfers: []
            };
        }

        function getDepartureTransferPayload(dayVal) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            const pickupSelectId = `departure_pickup_select_${d}`;
            const dropSelectId = `departure_drop_select_${d}`;
            const pickupVal = String(document.getElementById(pickupSelectId)?.value || '').trim();
            const dropVal = String(document.getElementById(dropSelectId)?.value || '').trim();
            return {
                required: 'Yes',
                transfer_type: 'Departure',
                city: getCityNameFromSelect(`transfer_city_select_${d}`) || getCityNameFromSelect(`activity_city_select_${d}`) || '',
                pickup_location: pickupVal,
                pickup_location_label: getTransferLocationLabel(pickupSelectId, pickupVal),
                drop_location: dropVal,
                drop_location_label: getTransferLocationLabel(dropSelectId, dropVal),
                additional_transfers: []
            };
        }

        function resetDayEntryFields(dayVal, resetServiceSelects = true) {
            const d = parseInt(String(dayVal || 1), 10) || 1;
            if (resetServiceSelects) {
                safeSetSelectValue(`attraction_select_${d}`, '');
                setSelectOptions(`attraction_ticket_select_${d}`, []);
                safeSetSelectValue(`restaurant_select_${d}`, '');
                safeSetSelectValue(`restaurant_dish_type_${d}`, '');
            }
            safeSetSelectValue(`transfer_pickup_select_${d}`, '');
            safeSetSelectValue(`transfer_drop_select_${d}`, '');
            safeSetSelectValue(`arrival_pickup_select_${d}`, '');
            safeSetSelectValue(`arrival_drop_select_${d}`, '');
            safeSetSelectValue(`departure_pickup_select_${d}`, '');
            safeSetSelectValue(`departure_drop_select_${d}`, '');
            dayTransferExtras[d] = [];
            renderExtraTransferRows(d);
        }

        async function loadTicketsForAttractionForDay(dayVal) {
            const attractionOp = getSelectedOption(`attraction_select_${dayVal}`);
            if (!attractionOp) {
                setSelectOptions(`attraction_ticket_select_${dayVal}`, []);
                return;
            }

            const dmcId = document.getElementById('dmc_id').value || '';
            try {
                const url = `/day-level/tickets-by-attraction?attraction_id=${encodeURIComponent(attractionOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error('Failed to fetch tickets');
                const data = await res.json();
                const tickets = Array.isArray(data?.tickets) ? data.tickets : [];
                setSelectOptions(`attraction_ticket_select_${dayVal}`, tickets.map(t => ({
                    value: t.ticket_id,
                    label: t.name || `Ticket ${t.ticket_id}`,
                })));
            } catch (e) {
                setSelectOptions(`attraction_ticket_select_${dayVal}`, []);
            }
        }

        async function populateDayServiceOptionsByCity(dayVal, cityName) {
            const normalizedCity = String(cityName || '').split(',')[0].trim();
            if (!normalizedCity) {
                setSelectOptions(`attraction_select_${dayVal}`, []);
                setSelectOptions(`restaurant_select_${dayVal}`, []);
                setSelectOptions(`attraction_ticket_select_${dayVal}`, []);
                return;
            }
            const dmcId = document.getElementById('dmc_id').value || '';
            try {
                const res = await fetch(`/day-level/by-city?city_name=${encodeURIComponent(normalizedCity)}&type=all&dmc_id=${encodeURIComponent(dmcId)}`);
                const data = await res.json();
                setSelectOptions(`attraction_select_${dayVal}`, (data.attractions || []).map(x => ({
                    value: x.attraction_id,
                    label: x.name + (x.location ? ` - ${x.location}` : ''),
                    price: x.adult_price || 0
                })));
                setSelectOptions(`restaurant_select_${dayVal}`, (data.restaurants || []).map(x => ({
                    value: x.restaurant_id,
                    label: x.name + (x.city ? ` - ${x.city}` : '')
                })));
                setSelectOptions(`attraction_ticket_select_${dayVal}`, []);
            } catch (e) {
                setSelectOptions(`attraction_select_${dayVal}`, []);
                setSelectOptions(`restaurant_select_${dayVal}`, []);
                setSelectOptions(`attraction_ticket_select_${dayVal}`, []);
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
                    ? `/day-level/hotels-by-rating?rating=${encodeURIComponent(category)}&city_name=${encodeURIComponent(cityName)}&dmc_id=${encodeURIComponent(dmcId)}`
                    : `/day-level/hotels-by-rating?rating=${encodeURIComponent(category)}&dmc_id=${encodeURIComponent(dmcId)}`;
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
                    const resByCity = await fetch(`/day-level/by-city?city_name=${encodeURIComponent(cityName)}&type=hotels&dmc_id=${encodeURIComponent(dmcId)}`);
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

            // Fallback to cached city payload if endpoint has no rows
            if (!list.length && hotelsFlat.length) {
                list = hotelsFlat.filter(x => String(x.hotel_star_rating ?? '').trim() === String(category).trim());
            }
            if (!list.length) {
                list = hotelsByRating[category] || [];
            }

            setSelectOptions('hotel_select', list.map(x => ({
                value: x.id,
                label: x.name + (x.city ? ` - ${x.city}` : ''),
                price: x.price || x.base_price || 0
            })));

            setSelectOptions('hotel_meal_plan', []);
        }

        async function loadMealPlansForSelectedHotel() {
            const hotelOp = getSelectedOption('hotel_select');
            if (!hotelOp) {
                setSelectOptions('hotel_meal_plan', []);
                toggleHotelMealTypeVisibility();
                return;
            }

            const dmcId = document.getElementById('dmc_id').value || '';
            const url = `/day-level/meal-plans-by-hotel?hotel_id=${encodeURIComponent(hotelOp.value)}&dmc_id=${encodeURIComponent(dmcId)}`;
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
        }

        async function resolveHotelCategoryForEdit(hotelId, cityName) {
            if (!hotelId) return '';

            // First try current cache
            const cached = (hotelsFlat || []).find(h => String(h.id) === String(hotelId));
            if (cached && cached.hotel_star_rating !== undefined && cached.hotel_star_rating !== null) {
                return String(cached.hotel_star_rating);
            }

            // Fallback: fetch city hotels and resolve star rating from record
            if (!cityName) return '';
            const dmcId = document.getElementById('dmc_id').value || '';
            try {
                const res = await fetch(`/day-level/by-city?city_name=${encodeURIComponent(cityName)}&type=hotels&dmc_id=${encodeURIComponent(dmcId)}`);
                if (!res.ok) return '';
                const data = await res.json();
                const flat = Array.isArray(data?.hotels_flat) ? data.hotels_flat : [];
                const match = flat.find(h => String(h.id) === String(hotelId));
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
            setSelectOptions('hotel_meal_plan', []);
            safeSetSelectValue('hotel_meal_type', '');
            document.getElementById('hotel_priority').value = '1';
            document.getElementById('hotel_guide_required').checked = false;
            document.getElementById('hotel_arrival_departure').checked = false;
            safeSetSelectValue('hotel_arrival_departure_type', '');
            document.getElementById('hotelAddBtn').textContent = 'Add Hotel';
            editingHotelIndex = null;
            toggleHotelMealTypeVisibility();
            toggleHotelTransferFields();
        }

        function addHotel() {
            const hotelOp = getSelectedOption('hotel_select');
            const categoryOp = getSelectedOption('hotel_category');
            const mealPlanOp = getSelectedOption('hotel_meal_plan');
            if (!hotelOp || !categoryOp) {
                alert('Select hotel category and hotel.');
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
                meal_plan: mealPlanOp?.value || '',
                meal_type: document.getElementById('hotel_meal_type')?.value || '',
                guide_required: document.getElementById('hotel_guide_required')?.checked ? 'Yes' : 'No',
                arrival_departure: document.getElementById('hotel_arrival_departure')?.checked ? 'Yes' : 'No',
                arrival_departure_type: document.getElementById('hotel_arrival_departure_type')?.value || '',
                transfer_city: selectedTransferCity,
                transfer_pickup: isArrivalDepartureTransfersActive() ? (document.getElementById(`transfer_pickup_select_${derivedDay}`)?.value || '') : '',
                transfer_drop: isArrivalDepartureTransfersActive() ? (document.getElementById(`transfer_drop_select_${derivedDay}`)?.value || '') : '',
                price: parseFloat(hotelOp.dataset.price || '0'),
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
            safeSetSelectValue('hotel_select', x.hotel_id || '');
            await loadMealPlansForSelectedHotel();
            safeSetSelectValue('hotel_meal_plan', x.meal_plan || '');
            toggleHotelMealTypeVisibility();

            safeSetSelectValue('hotel_meal_type', x.meal_type || '');
            document.getElementById('hotel_priority').value = String(x.priority || 1);
            document.getElementById('hotel_guide_required').checked = (x.guide_required || 'No') === 'Yes';
            document.getElementById('hotel_arrival_departure').checked = (x.arrival_departure || 'No') === 'Yes';
            safeSetSelectValue('hotel_arrival_departure_type', x.arrival_departure_type || '');
            toggleHotelTransferFields();
            const shouldLoadXferOpts = ((x.arrival_departure || 'No') === 'Yes') || hotelsHaveArrivalDepartureTransferSaved();
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
                        safeSetSelectValue(`transfer_city_select_${activeDay}`, transferCityMatch.value);
                    }
                }
                await loadTransferOptionsForCity();
                if (x.transfer_pickup) {
                    const hotelPickupLabel = labelForStoredTransferLocation(x.transfer_pickup) || x.transfer_pickup;
                    ensureTransferLocationOption(`transfer_pickup_select_${activeDay}`, x.transfer_pickup, hotelPickupLabel);
                    safeSetSelectValue(`transfer_pickup_select_${activeDay}`, x.transfer_pickup);
                }
                if (x.transfer_drop) {
                    ensureTransferLocationOption(`transfer_drop_select_${activeDay}`, x.transfer_drop, x.transfer_drop);
                    safeSetSelectValue(`transfer_drop_select_${activeDay}`, x.transfer_drop);
                }
            }
            document.getElementById('hotelAddBtn').textContent = 'Update Hotel';
            isPrefillingHotelForm = false;
        }

        function renderHotelRows() {
            const body = document.getElementById('hotelRows');
            const current = [...hotels].sort((a, b) => (a.day || 0) - (b.day || 0));
            if (!current.length) {
                body.innerHTML = '<tr><td colspan="12" class="text-muted">No hotels added</td></tr>';
            } else {
                body.innerHTML = current.map((x) => {
                    const idx = hotels.indexOf(x);
                    return `
                        <tr>
                            <td>${escapeHtml(String(x.day != null ? x.day : '-'))}</td>
                            <td>${escapeHtml(x.city_name || '-')}</td>
                            <td>${escapeHtml(x.cat_label)}</td>
                            <td>${escapeHtml(x.hotel_name)}</td>
                            <td>${escapeHtml(String(x.night || 1))}</td>
                            <td>${escapeHtml(x.meal_plan || '-')}</td>
                            <td>${escapeHtml(x.meal_type || '-')}</td>
                            <td>${escapeHtml(x.guide_required || 'No')}</td>
                            <td>${escapeHtml(x.arrival_departure || 'No')}</td>
                            <td>${escapeHtml(x.arrival_departure_type || '-')}</td>
                            <td>${x.priority}</td>
                            <td class="action-cell">
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
        }

        function isTransferOnlyPlaceholderItem(item) {
            return item
                && item.type === 'attraction'
                && !String(item.id || '').trim()
                && String(item.label || '').toLowerCase() === 'day transfer';
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

            const transferPayload = getDayTransferPayload(d);
            const hasTransferData = transferPayload.required === 'Yes'
                || String(transferPayload.city || '').trim()
                || String(transferPayload.pickup_location || '').trim()
                || String(transferPayload.drop_location || '').trim()
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

            const payload = {
                day: normalizedDay,
                type: 'attraction',
                id: selOp.value,
                label: selOp.textContent,
                city_name: getCityNameFromSelect(`activity_city_select_${dayVal}`) || '',
                ticket_id: ticketOp?.value || '',
                ticket_name: ticketOp?.textContent || '',
                transfer: getDayTransferPayload(normalizedDay)
            };
            if (editingActivityIndex !== null && dayItems[editingActivityIndex]) {
                dayItems[editingActivityIndex] = payload;
                editingActivityIndex = null;
            } else {
                dayItems.push(payload);
            }
            renderActivityRows();
            resetDayEntryFields(normalizedDay);
        }

        function addDayEntryForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const attractionOp = getSelectedOption(`attraction_select_${normalizedDay}`);
            const restaurantOp = getSelectedOption(`restaurant_select_${normalizedDay}`);
            const transferPayload = getDayTransferPayload(normalizedDay);
            const hasTransfer = transferPayload.required === 'Yes'
                || !!transferPayload.city
                || !!transferPayload.pickup_location
                || !!transferPayload.drop_location
                || (Array.isArray(transferPayload.additional_transfers) && transferPayload.additional_transfers.length > 0);

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

            // Allow saving day transfer-only entries without forcing attraction selection.
            if (!attractionOp && !restaurantOp && hasTransfer) {
                const payload = {
                    day: normalizedDay,
                    type: 'attraction',
                    id: '',
                    label: 'Day Transfer',
                    city_name: getCityNameFromSelect(`activity_city_select_${normalizedDay}`) || transferPayload.city || '',
                    ticket_id: '',
                    ticket_name: '',
                    transfer: transferPayload
                };
                if (editingActivityIndex !== null && dayItems[editingActivityIndex]) {
                    dayItems[editingActivityIndex] = payload;
                    editingActivityIndex = null;
                } else {
                    dayItems.push(payload);
                }
                addedCount++;
                renderActivityRows();
                resetDayEntryFields(normalizedDay);
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
            const dishType = document.getElementById(`restaurant_dish_type_${dayVal}`)?.value || '';
            if (!dishType) {
                alert('Select Dish before adding restaurant.');
                return;
            }

            const payload = {
                day: normalizedDay,
                type: 'restaurant',
                id: selOp.value,
                label: selOp.textContent,
                city_name: getCityNameFromSelect(`activity_city_select_${dayVal}`) || '',
                meal: {
                    meal_type: '',
                    dish: dishType,
                    time_slot: ''
                },
                transfer: {
                    required: getDayTransferPayload(normalizedDay).required,
                    city: getDayTransferPayload(normalizedDay).city,
                    type: '',
                    way: '',
                    vehicle_id: '',
                    vehicle_name: '',
                    pickup_location_id: '',
                    pickup_location: getDayTransferPayload(normalizedDay).pickup_location,
                    drop_location: getDayTransferPayload(normalizedDay).drop_location,
                    cost: 0,
                    pickup_time: ''
                }
            };
            if (editingActivityIndex !== null && dayItems[editingActivityIndex]) {
                dayItems[editingActivityIndex] = payload;
                editingActivityIndex = null;
            } else {
                dayItems.push(payload);
            }
            renderActivityRows();
            resetDayEntryFields(normalizedDay);
        }

        function addTransferItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const transferPayload = getDayTransferPayload(normalizedDay);
            return addTransferLikeItemForDay(normalizedDay, 'Day Transfer', transferPayload);
        }

        function addArrivalItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const transferPayload = getArrivalTransferPayload(normalizedDay);
            return addTransferLikeItemForDay(normalizedDay, 'Day Arrival', transferPayload);
        }

        function addDepartureItemForDay(dayVal) {
            const normalizedDay = parseInt(String(dayVal || 1), 10) || 1;
            const transferPayload = getDepartureTransferPayload(normalizedDay);
            return addTransferLikeItemForDay(normalizedDay, 'Day Departure', transferPayload);
        }

        function addTransferLikeItemForDay(normalizedDay, label, transferPayload) {
            const hasTransfer = transferPayload.required === 'Yes'
                || !!transferPayload.city
                || !!transferPayload.pickup_location
                || !!transferPayload.drop_location
                || (Array.isArray(transferPayload.additional_transfers) && transferPayload.additional_transfers.length > 0);
            if (!hasTransfer) {
                alert(`Select ${String(label || 'transfer').replace(/^Day\s+/i, '').toLowerCase()} details first.`);
                return false;
            }
            const payload = {
                day: normalizedDay,
                type: 'attraction',
                id: '',
                label: label || 'Day Transfer',
                city_name: getCityNameFromSelect(`activity_city_select_${normalizedDay}`) || transferPayload.city || '',
                ticket_id: '',
                ticket_name: '',
                transfer: transferPayload
            };
            if (editingActivityIndex !== null && dayItems[editingActivityIndex]) {
                dayItems[editingActivityIndex] = payload;
                editingActivityIndex = null;
            } else {
                dayItems.push(payload);
            }
            renderActivityRows();
            resetDayEntryFields(normalizedDay);
            return true;
        }

        function removeActivity(idx) {
            dayItems.splice(idx, 1);
            if (editingActivityIndex === idx) {
                editingActivityIndex = null;
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
            initDays();
            renderHotelRows();
            renderActivityRows();

            const citySelectId = `activity_city_select_${rowDay}`;
            const citySelect = document.getElementById(citySelectId);
            if (citySelect && x.city_name) {
                const cityMatch = Array.from(citySelect.options).find(opt => {
                    const nm = String(opt.dataset.name || opt.textContent || '').split(',')[0].trim().toLowerCase();
                    const target = String(x.city_name || '').split(',')[0].trim().toLowerCase();
                    return nm === target;
                });
                if (cityMatch) {
                    safeSetSelectValue(citySelectId, cityMatch.value);
                }
            }

            const resolvedCityName =
                getCityNameFromSelect(citySelectId)
                || String(x.city_name || '').split(',')[0].trim()
                || String(x.transfer?.city || '').split(',')[0].trim()
                || getCityNameFromSelect(`transfer_city_select_${rowDay}`)
                || getCityNameFromSelect('hotel_city_select')
                || getCityNameFromSelect('city_id')
                || '';
            await populateDayServiceOptionsByCity(rowDay, resolvedCityName);

            if (x.type === 'attraction' && String(x.id || '').trim()) {
                ensureSelectOptionByValue(`attraction_select_${rowDay}`, x.id || '', x.label || '');
                safeSetSelectValue(`attraction_select_${rowDay}`, x.id || '');
                loadTicketsForAttractionForDay(rowDay).then(() => {
                    ensureSelectOptionByValue(`attraction_ticket_select_${rowDay}`, x.ticket_id || '', x.ticket_name || '');
                    safeSetSelectValue(`attraction_ticket_select_${rowDay}`, x.ticket_id || '');
                });
            } else if (x.type === 'restaurant') {
                ensureSelectOptionByValue(`restaurant_select_${rowDay}`, x.id || '', x.label || '');
                safeSetSelectValue(`restaurant_select_${rowDay}`, x.id || '');
                safeSetSelectValue(`restaurant_dish_type_${rowDay}`, x.meal?.dish || '');
            }

            const transfer = x.transfer && typeof x.transfer === 'object' ? x.transfer : {};
            const requiresTransfer = String(transfer.required || '').toLowerCase() === 'yes'
                || !!(transfer.pickup_location || transfer.drop_location || transfer.city);
            if (requiresTransfer) {
                const extraRows = Array.isArray(transfer.additional_transfers)
                    ? transfer.additional_transfers
                        .map((row) => ({
                            city: String(row?.city || ''),
                            pickup_location: String(row?.pickup_location || ''),
                            drop_location: String(row?.drop_location || ''),
                        }))
                        .filter((row) => row.pickup_location || row.drop_location || row.city)
                    : [];
                dayTransferExtras[rowDay] = extraRows;
                renderExtraTransferRows(rowDay);
                const transferCitySelectId = `transfer_city_select_${rowDay}`;
                const transferCitySelect = document.getElementById(transferCitySelectId);
                if (transferCitySelect && transfer.city) {
                    const transferCityMatch = Array.from(transferCitySelect.options).find(opt => {
                        const nm = String(opt.dataset.name || opt.textContent || '').split(',')[0].trim().toLowerCase();
                        const target = String(transfer.city || '').split(',')[0].trim().toLowerCase();
                        return nm === target;
                    });
                    if (transferCityMatch) {
                        safeSetSelectValue(transferCitySelectId, transferCityMatch.value);
                    }
                }
                loadTransferOptionsForCity(rowDay).then(() => {
                    const tType = String(transfer.transfer_type || '').trim().toLowerCase();
                    const pickupSelectId = resolveTransferSelectId(rowDay, 'pickup', transfer.transfer_type);
                    const dropSelectId = resolveTransferSelectId(rowDay, 'drop', transfer.transfer_type);
                    const pickupLabel = displayTransferLocation(
                        transfer.pickup_location,
                        pickupSelectId,
                        transfer.pickup_location_label
                    );
                    const dropLabel = displayTransferLocation(
                        transfer.drop_location,
                        dropSelectId,
                        transfer.drop_location_label
                    );
                    if (transfer.pickup_location) {
                        ensureTransferLocationOption(`transfer_pickup_select_${rowDay}`, transfer.pickup_location, pickupLabel);
                        if (tType === 'arrival') {
                            ensureTransferLocationOption(`arrival_pickup_select_${rowDay}`, transfer.pickup_location, pickupLabel);
                            safeSetSelectValue(`arrival_pickup_select_${rowDay}`, transfer.pickup_location);
                        } else if (tType === 'departure') {
                            ensureTransferLocationOption(`departure_pickup_select_${rowDay}`, transfer.pickup_location, pickupLabel);
                            safeSetSelectValue(`departure_pickup_select_${rowDay}`, transfer.pickup_location);
                        } else {
                            safeSetSelectValue(`transfer_pickup_select_${rowDay}`, transfer.pickup_location);
                        }
                    }
                    if (transfer.drop_location) {
                        ensureTransferLocationOption(`transfer_drop_select_${rowDay}`, transfer.drop_location, dropLabel);
                        if (tType === 'arrival') {
                            ensureTransferLocationOption(`arrival_drop_select_${rowDay}`, transfer.drop_location, dropLabel);
                            safeSetSelectValue(`arrival_drop_select_${rowDay}`, transfer.drop_location);
                        } else if (tType === 'departure') {
                            ensureTransferLocationOption(`departure_drop_select_${rowDay}`, transfer.drop_location, dropLabel);
                            safeSetSelectValue(`departure_drop_select_${rowDay}`, transfer.drop_location);
                        } else {
                            safeSetSelectValue(`transfer_drop_select_${rowDay}`, transfer.drop_location);
                        }
                    }
                    renderExtraTransferRows(rowDay);
                });
            }
            setTimeout(() => { isPrefillingActivityForm = false; }, 250);
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
                            <table class="table table-sm data-table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:14%">Type</th>
                                        <th style="width:22%">Pickup</th>
                                        <th style="width:22%">Drop</th>
                                        <th>Details</th>
                                        <th style="width:140px">Actions</th>
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
                        && (normalizedLabel === 'day transfer' || normalizedLabel === 'day arrival' || normalizedLabel === 'day departure');
                    const transferTypeLabel = String(transfer.transfer_type || '').trim() || (
                        normalizedLabel === 'day arrival' ? 'Arrival'
                            : normalizedLabel === 'day departure' ? 'Departure'
                                : 'Transfer'
                    );

                    const transferTypeForSelect = transfer.transfer_type || transferTypeLabel;
                    const pickupSelectId = resolveTransferSelectId(d, 'pickup', transferTypeForSelect);
                    const dropSelectId = resolveTransferSelectId(d, 'drop', transferTypeForSelect);
                    const pickupDisplay = displayTransferLocation(
                        transfer.pickup_location,
                        pickupSelectId,
                        transfer.pickup_location_label
                    );
                    const dropDisplay = displayTransferLocation(
                        transfer.drop_location,
                        dropSelectId,
                        transfer.drop_location_label
                    );
                    const cityDisplay = transfer.city || x.city_name || '-';
                    const transferSummary = (transfer.required === 'Yes' || transfer.pickup_location || transfer.drop_location)
                        ? ` | ${transfer.transfer_type || 'Transfer'}: ${pickupDisplay} -> ${dropDisplay}`
                        : '';
                    const pickupCell = pickupDisplay;
                    const dropCell = dropDisplay;

                    let details = '';
                    let detailsHtml = '';
                    if (x.type === 'restaurant') {
                        details = `${x.label || 'Restaurant'} | Dish: ${x.meal?.dish || '-'} | City: ${x.city_name || '-'}`;
                        if (transferSummary) details += transferSummary;
                        detailsHtml = `
                            <span class="detail-chip">${escapeHtml(x.label || 'Restaurant')}</span>
                            <span class="detail-chip">Dish: ${escapeHtml(x.meal?.dish || '-')}</span>
                            <span class="detail-chip">City: ${escapeHtml(x.city_name || '-')}</span>
                            ${transferSummary ? `<span class="detail-chip">${escapeHtml((transfer.transfer_type || 'Transfer') + ': ' + pickupDisplay + ' -> ' + dropDisplay)}</span>` : ''}
                        `;
                    } else if (isTransferLikeOnly) {
                        details = `${transferTypeLabel} | City: ${cityDisplay} | ${pickupDisplay} -> ${dropDisplay}`;
                        detailsHtml = `
                            <span class="detail-chip">${escapeHtml(transferTypeLabel)}</span>
                            <span class="detail-chip">City: ${escapeHtml(cityDisplay)}</span>
                            <span class="detail-chip">${escapeHtml(pickupDisplay)} -> ${escapeHtml(dropDisplay)}</span>
                        `;
                    } else {
                        details = `${x.label || 'Attraction'} | City: ${x.city_name || '-'}`;
                        if (x.ticket_name) details += ` | Ticket: ${x.ticket_name}`;
                        if (transferSummary) details += transferSummary;
                        detailsHtml = `
                            <span class="detail-chip">${escapeHtml(x.label || 'Attraction')}</span>
                            <span class="detail-chip">City: ${escapeHtml(x.city_name || '-')}</span>
                            ${x.ticket_name ? `<span class="detail-chip">Ticket: ${escapeHtml(x.ticket_name)}</span>` : ''}
                            ${transferSummary ? `<span class="detail-chip">${escapeHtml((transfer.transfer_type || 'Transfer') + ': ' + pickupDisplay + ' -> ' + dropDisplay)}</span>` : ''}
                        `;
                    }

                    return `
                        <tr>
                            <td class="fw-semibold align-middle"><span class="badge bg-primary-subtle text-primary">${escapeHtml(x.type === 'restaurant' ? 'Restaurant' : (isTransferLikeOnly ? transferTypeLabel : 'Attraction'))}</span></td>
                            <td class="small text-muted align-middle">${escapeHtml(pickupCell)}</td>
                            <td class="small text-muted align-middle">${escapeHtml(dropCell)}</td>
                            <td class="small text-muted align-middle">${detailsHtml || escapeHtml(details)}</td>
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
                        <table class="table table-sm data-table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:14%">Type</th>
                                    <th style="width:22%">Pickup</th>
                                    <th style="width:22%">Drop</th>
                                    <th>Details</th>
                                    <th style="width:140px">Actions</th>
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
                return {
                    city: String(citySel?.value || '').trim(),
                    pickup_location: String(pickupSel?.value || '').trim(),
                    drop_location: String(dropSel?.value || '').trim(),
                };
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
                let derivedNight = null;
                if (normalizedPlans.length) {
                    // Multi City is the single source of truth for which city is “active” for day d.
                    // Hotels occupy nights range: [checkin_day, checkout_day - 1]
                    const planForDay = normalizedPlans
                        .filter(p => d >= p.day_in && d <= p.day_out)
                        .sort((a, b) => b.day_in - a.day_in)[0] || null;

                    if (planForDay) {
                        const cityKey = String(planForDay.city_name || '').trim().toLowerCase();
                        const start = parseInt(String(planForDay.day_in || 1), 10) || 1;
                        const nights = Math.max(1, parseInt(String(planForDay.day_out || start), 10) - start);
                        derivedNight = nights;
                        const end = start + nights - 1;

                        if (d >= start && d <= end) {
                            h = hotels.filter(x => {
                                const hotelStart = parseInt(String(x.day || 1), 10) || 1;
                                const hotelEndExclusive = hotelStayEndDay(x);
                                return String(x.city_name || '').trim().toLowerCase() === cityKey
                                    && d >= hotelStart
                                    && d < hotelEndExclusive;
                            });
                        }
                    }
                } else {
                    // Legacy fallback when Multi City is not configured.
                    h = hotels.filter(x => isSameDay(x.day, 1));
                    if (!h.length) {
                        h = hotels.filter((x) => {
                            const start = parseInt(String(x.day || 1), 10) || 1;
                            const nights = Math.max(1, parseInt(String(x.night || 1), 10) || 1);
                            const end = Math.min(daysCount, start + nights - 1);
                            return d >= start && d <= end;
                        });
                    }
                }
                const a = dayItems.filter(x => isSameDay(x.day, 0) && x.type === 'attraction');
                const r = dayItems.filter(x => isSameDay(x.day, 0) && x.type === 'restaurant');

                const hotelMap = {};
                const attrMap = {};
                const restMap = {};
                const serviceMap = {};

                h.forEach((x, i) => {
                    hotelMap[`Hotel ${i + 1}`] = {
                        hotel_id: String(x.hotel_id),
                        hotel_name: x.hotel_name,
                        city: x.city_name || '',
                        meal_plan: x.meal_plan || '',
                        price: x.price || 0,
                        night: derivedNight != null ? derivedNight : (parseInt(String(x.night || '1'), 10) || 1),
                        meal_type: x.meal_type || '',
                        guide_required: x.guide_required || 'No',
                        arrival_departure: x.arrival_departure || 'No',
                        arrival_departure_type: x.arrival_departure_type || '',
                        transfer_city: x.transfer_city || '',
                        transfer_pickup: x.transfer_pickup || '',
                        transfer_drop: x.transfer_drop || '',
                        priority: parseInt(String(x.priority || '1'), 10) || 1
                    };
                });
                a.forEach((x, i) => {
                    const transferOut = { ...(x.transfer && typeof x.transfer === 'object' ? x.transfer : {}) };
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
                    const uiExtras = getFilteredAdditionalTransfers(d).map(row => ({
                        city: String(row.city || ''),
                        pickup_location: String(row.pickup_location || ''),
                        drop_location: String(row.drop_location || ''),
                    }));
                    if (uiExtras.length) {
                        transferOut.additional_transfers = uiExtras;
                    }
                    attrMap[`Attraction ${i + 1}`] = {
                        attraction_id: String(x.id),
                        name: x.label,
                        city: fallbackCityName,
                        ticket_id: x.ticket_id ? String(x.ticket_id) : '',
                        ticket_name: x.ticket_name || '',
                        transfer: transferOut
                    };
                });
                r.forEach((x, i) => {
                    const fallbackCityName =
                        String(x.city_name || '').trim()
                        || String(x.transfer?.city || '').trim()
                        || getCityNameFromSelect(`activity_city_select_${d}`)
                        || getCityNameFromSelect(`transfer_city_select_${d}`)
                        || effectiveCityName
                        || '';
                    restMap[`Restaurant ${i + 1}`] = {
                        restaurant_id: String(x.id),
                        name: x.label,
                        city: fallbackCityName,
                    };
                });
                r.forEach((x, i) => {
                    const fallbackCityName =
                        String(x.city_name || '').trim()
                        || String(x.transfer?.city || '').trim()
                        || getCityNameFromSelect(`activity_city_select_${d}`)
                        || getCityNameFromSelect(`transfer_city_select_${d}`)
                        || effectiveCityName
                        || '';
                    const transferOut = { ...(x.transfer && typeof x.transfer === 'object' ? x.transfer : {}) };
                    if (!transferOut.city && fallbackCityName) {
                        transferOut.city = fallbackCityName;
                    }
                    serviceMap[`Service ${i + 1}`] = {
                        service_type: 'restaurant',
                        restaurant_id: String(x.id),
                        restaurant_name: x.label,
                        city: fallbackCityName,
                        meal_configuration: x.meal || {},
                        transfer: transferOut
                    };
                });

                days[String(d - 1)] = {
                    day: d,
                    hotels: hotelMap,
                    attractions: attrMap,
                    restaurants: restMap,
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
                        const source = days[String(d - 1)] || {
                            day: d,
                            hotels: {},
                            attractions: {},
                            restaurants: {},
                            services: {}
                        };
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

        function renderPreviewTransfer(transfer) {
            if (!transfer || typeof transfer !== 'object') return '';
            const required = String(transfer.required || 'No');
            if (required !== 'Yes') {
                return '<span class="text-muted">Transfer: No</span>';
            }
            const parts = [
                `Type: ${escapeHtml(transfer.type || '-')}`,
                `Way: ${escapeHtml(transfer.way || '-')}`,
                `Vehicle: ${escapeHtml(transfer.vehicle_name || '-')}`,
                `Pickup: ${escapeHtml(displayTransferLocation(transfer.pickup_location, '', transfer.pickup_location_label))}`,
            ];
            if (transfer.pickup_time) {
                parts.push(`Time: ${escapeHtml(transfer.pickup_time)}`);
            }
            if (transfer.cost != null && transfer.cost !== '') {
                parts.push(`Cost: ${escapeHtml(String(transfer.cost))}`);
            }
            const extras = Array.isArray(transfer.additional_transfers) ? transfer.additional_transfers : [];
            let html = `<div class="preview-line ms-2"><span class="text-muted">Transfer:</span> ${parts.join(' · ')}</div>`;
            if (extras.length) {
                html += extras.map((row, i) => `
                    <div class="preview-line ms-3">
                        <span class="text-muted">Extra ${i + 1}:</span>
                        ${escapeHtml(displayTransferLocation(row.pickup_location, '', row.pickup_location_label))} → ${escapeHtml(displayTransferLocation(row.drop_location, '', row.drop_location_label))}
                    </div>
                `).join('');
            }
            return html;
        }

        function renderPreviewDayBlock(day) {
            if (!day || typeof day !== 'object') return '';
            const dayNum = day.day ?? '?';
            const hotelList = Object.values(day.hotels || {});
            const attractionList = Object.values(day.attractions || {});
            const restaurantList = Object.values(day.restaurants || {});
            const serviceList = Object.values(day.services || {});

            let inner = `<div class="preview-day-title">Day ${escapeHtml(String(dayNum))}</div>`;

            if (!hotelList.length && !attractionList.length && !restaurantList.length && !serviceList.length) {
                inner += '<div class="preview-empty">No services scheduled</div>';
            } else {
                hotelList.forEach((h) => {
                    inner += `
                        <div class="preview-line"><strong>Hotel:</strong>
                            ${escapeHtml(h.hotel_name || '-')}
                            <span class="text-muted"> · ${escapeHtml(h.city || '')} · ${escapeHtml(String(h.night || 1))} night(s) · ${escapeHtml(h.meal_plan || 'No meal')}</span>
                        </div>`;
                    if ((h.guide_required || 'No') === 'Yes') {
                        inner += '<div class="preview-line ms-3"><span class="text-muted">Guide required</span></div>';
                    }
                    if ((h.arrival_departure || 'No') === 'Yes') {
                        inner += `<div class="preview-line ms-3"><span class="text-muted">Arrival/Departure:</span> ${escapeHtml(h.arrival_departure_type || '-')}</div>`;
                    }
                });
                attractionList.forEach((a) => {
                    inner += `
                        <div class="preview-line"><strong>Attraction:</strong>
                            ${escapeHtml(a.name || '-')}
                            ${a.ticket_name ? `<span class="text-muted"> · Ticket: ${escapeHtml(a.ticket_name)}</span>` : ''}
                        </div>`;
                    inner += renderPreviewTransfer(a.transfer);
                });
                restaurantList.forEach((r) => {
                    inner += `<div class="preview-line"><strong>Restaurant:</strong> ${escapeHtml(r.name || '-')}</div>`;
                });
                serviceList.forEach((s) => {
                    const meal = s.meal_configuration && typeof s.meal_configuration === 'object' ? s.meal_configuration : null;
                    inner += `
                        <div class="preview-line"><strong>Restaurant service:</strong>
                            ${escapeHtml(s.restaurant_name || s.name || '-')}
                            ${meal ? `<span class="text-muted"> · ${escapeHtml(meal.meal_type || '-')} / ${escapeHtml(meal.dish || '-')} @ ${escapeHtml(meal.time_slot || '-')}</span>` : ''}
                        </div>`;
                    inner += renderPreviewTransfer(s.transfer);
                });
            }

            return `<div class="preview-day-block">${inner}</div>`;
        }

        function renderPackagePreviewHtml(payload) {
            const master = payload.Master_DMC[0] || {};
            const dest = (master.destinations || [])[0] || {};

            let html = '';

            const warnings = getPreviewWarnings(payload);
            if (warnings.length) {
                html += `<div class="preview-warnings mb-3">${warnings.map(w => `<div class="alert alert-warning mb-0 py-2">${escapeHtml(w)}</div>`).join('')}</div>`;
            }

            if (multiCityPlans.length) {
                html += `
                    <h6 class="fw-semibold mb-2">Multi City itinerary</h6>
                    <div class="table-responsive modern-table-wrap mb-3">
                        <table class="table table-sm data-table-sm mb-0">
                            <thead><tr><th>City</th><th>Check-in</th><th>Check-out</th></tr></thead>
                            <tbody>
                                ${multiCityPlans.map(p => `
                                    <tr>
                                        <td>${escapeHtml(p.city_name || '-')}</td>
                                        <td>Day ${escapeHtml(String(p.day_in || '-'))}</td>
                                        <td>Day ${escapeHtml(String(p.day_out || '-'))}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            if (hotels.length) {
                html += `
                    <h6 class="fw-semibold mb-2">Hotels (${hotels.length})</h6>
                    <div class="table-responsive modern-table-wrap mb-3">
                        <table class="table table-sm data-table-sm mb-0">
                            <thead><tr><th>Day</th><th>City</th><th>Hotel</th><th>Nights</th><th>Meal</th><th>Guide</th></tr></thead>
                            <tbody>
                                ${[...hotels].sort((a, b) => (a.day || 0) - (b.day || 0)).map(h => `
                                    <tr>
                                        <td>${escapeHtml(String(h.day || 1))}</td>
                                        <td>${escapeHtml(h.city_name || '-')}</td>
                                        <td>${escapeHtml(h.hotel_name || '-')}</td>
                                        <td>${escapeHtml(String(h.night || 1))}</td>
                                        <td>${escapeHtml(h.meal_plan || '-')}</td>
                                        <td>${escapeHtml(h.guide_required || 'No')}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            html += '<h6 class="fw-semibold mb-2">Day-by-day package</h6>';

            const cities = Array.isArray(dest.cities) ? dest.cities : [];
            if (!cities.length) {
                html += '<p class="preview-empty">No city data in package.</p>';
            } else {
                cities.forEach((cityNode) => {
                    const cityName = cityNode.city || 'Unknown city';
                    const checkIn = cityNode.checkin_day ? ` · Check-in Day ${cityNode.checkin_day}` : '';
                    const checkOut = cityNode.checkout_day ? ` · Check-out Day ${cityNode.checkout_day}` : '';
                    const packages = Array.isArray(cityNode.packages) ? cityNode.packages : [];
                    const pkg = packages[0] || {};
                    const dayMap = pkg.days || {};
                    const dayKeys = Object.keys(dayMap).sort((a, b) => parseInt(a, 10) - parseInt(b, 10));

                    html += `
                        <div class="preview-city-card">
                            <div class="preview-city-head">${escapeHtml(cityName)}${escapeHtml(checkIn)}${escapeHtml(checkOut)}</div>
                            <div class="preview-city-body">
                    `;

                    if (!dayKeys.length) {
                        html += '<div class="p-3 preview-empty">No days in this city slice.</div>';
                    } else {
                        dayKeys.forEach((key) => {
                            html += renderPreviewDayBlock(dayMap[key]);
                        });
                    }

                    html += '</div></div></div>';
                });
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

        function submitFromPreview() {
            const payload = preparePayloadForSubmit();
            if (REQUIRE_MASTER_DMC_CITY && !payload) {
                alert('Master DMC, DMC and city are required.');
                return;
            }
            applyPayloadJsonToForm(payload);
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
            hydrateFromEditPayload();

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
                    syncDayCitySelectorsFromMultiCity();
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
                loadMealPlansForSelectedHotel();
                applyTransferDefaults();
            });
            $('#hotel_meal_plan').on('change select2:select select2:clear', function () {
                toggleHotelMealTypeVisibility();
            });
            $('#hotel_arrival_departure').on('change', function () {
                toggleHotelTransferFields();
                if (this.checked || hotelsHaveArrivalDepartureTransferSaved() || itineraryUsesMiddleDayTransfers()) {
                    // Ensure end-days dropdowns are populated when Arrival/Departure is enabled.
                    loadTransferOptionsForCity(1);
                    if (daysCount >= 2) loadTransferOptionsForCity(daysCount);
                }
            });
            $(document).on('change select2:select select2:clear', '[id^="transfer_city_select_"]', function () {
                const dayVal = getDayFromElementId(this.id);
                if (!transferSectionActiveForDay(dayVal)) return;
                hotelTransferState.city = this.value || '';
                loadTransferOptionsForCity(dayVal);
            });
            $(document).on('change select2:select select2:clear', '[id^="transfer_pickup_select_"]', function () {
                const pickedValue = this.value || '';
                hotelTransferState.pickup = pickedValue;
            });
            $(document).on('change select2:select select2:clear', '[id^="transfer_drop_select_"]', function () {
                const pickedValue = this.value || '';
                hotelTransferState.drop = pickedValue;
            });

            $('#city_id').on('change', function () {
                syncCountryFromCityOrDefault();
                autoLoadBySelection();
                syncSectionCitySelectionsFromMain();
                hydrateAllDayTransferCityOptions();
            });

            $('#hotel_city_select').on('change select2:select select2:clear', function () {
                if (isPrefillingHotelForm) return;
                syncMainCityFromSection('hotel_city_select');
                filterHotelOptions();
                // Existing hotel rows keep their own ranges; only refresh the new row helper.
                syncHotelDayDropdownWithMultiCity();
                if (!hotelTransferState.city) {
                    hotelTransferState.city = this.value || '';
                    for (let d = 1; d <= daysCount; d++) {
                        safeSetSelectValue(`transfer_city_select_${d}`, this.value || '');
                    }
                }
                if (isArrivalDepartureTransfersActive() || itineraryUsesMiddleDayTransfers()) {
                    loadTransferOptionsForCity(1);
                    if (daysCount >= 2) loadTransferOptionsForCity(daysCount);
                }
            });

            $(document).on('change select2:select select2:clear', '[id^="attraction_select_"]', function () {
                if (isPrefillingActivityForm) return;
                const d = getDayFromElementId(this.id);
                loadTicketsForAttractionForDay(d);
            });


            $(document).on('change select2:select select2:clear', '[id^="activity_city_select_"]', function () {
                if (isPrefillingActivityForm) return;
                const dayVal = getDayFromElementId(this.id);
                const cityName = getCityNameFromSelect(this.id);
                populateDayServiceOptionsByCity(dayVal, cityName);
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
                let payload = null;
                try {
                    payload = preparePayloadForSubmit();
                } catch (err) {
                    e.preventDefault();
                    console.error('Failed to build payload_json', err);
                    alert('Payload generation failed. Please check required fields and try again.');
                    return;
                }
                if (REQUIRE_MASTER_DMC_CITY && !payload) {
                    e.preventDefault();
                    alert('Master DMC, DMC and city are required.');
                    return;
                }
                applyPayloadJsonToForm(payload);
            });
        });
    </script>
@endpush
