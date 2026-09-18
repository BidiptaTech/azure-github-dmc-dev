@extends('layouts.layout')
@section('title', 'Facility')
{{-- @section('css')
    <link rel="stylesheet" href="{{ URL::asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet"
        href="{{ URL::asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet"
        href="{{ URL::asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/ekko-lightbox/dist/ekko-lightbox.css" rel="stylesheet">
    <link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endsection --}}
@section('css')
<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    /* ===== Outer Container Card - Premium Wrapper ===== */
    .container-p-y > .card {
        border: 1px solid #d0d7e2;
        border-radius: 0.75rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06), 0 0 1px rgba(0, 0, 0, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        overflow: hidden;
    }

    /* ===== Page Header ===== */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
        border-bottom: 1px solid #d0d7e2;
        margin-bottom: 0;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.01em;
    }

    /* ===== Toolbar: Search + Category Tabs ===== */
    .facility-toolbar {
        padding: 1rem 1.25rem 0.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e9edf3;
    }

    .facility-search-box {
        position: relative;
        max-width: 320px;
        margin-bottom: 0.75rem;
    }

    .facility-search-box input {
        width: 100%;
        padding: 0.45rem 0.75rem 0.45rem 2.25rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
        color: #334155;
        transition: all 0.2s ease;
    }

    .facility-search-box input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .facility-search-box input::placeholder {
        color: #94a3b8;
    }

    .facility-search-box .search-icon {
        position: absolute;
        left: 0.7rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }

    /* Category Filter Tabs */
    .category-tabs-wrapper {
        position: relative;
        padding-bottom: 0.75rem;
    }

    .category-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .category-tabs.collapsed {
        max-height: 72px; /* ~2 rows of tabs */
    }

    .category-tabs.expanded {
        max-height: 1000px;
    }

    .category-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        color: #475569;
        transition: all 0.25s ease;
        user-select: none;
    }

    .category-tab:hover {
        border-color: #6366f1;
        color: #4338ca;
        background: #eef2ff;
    }

    .category-tab.active {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
    }

    .category-tab .tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        background: rgba(0, 0, 0, 0.08);
        color: inherit;
    }

    .category-tab.active .tab-count {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    /* More/Less Toggle Button */
    .tabs-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        margin-top: 0.35rem;
        float: right;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px dashed #a5b4fc;
        background: #eef2ff;
        color: #4f46e5;
        transition: all 0.25s ease;
        user-select: none;
    }

    .tabs-toggle-btn:hover {
        background: #e0e7ff;
        border-color: #818cf8;
    }

    .tabs-toggle-btn i {
        font-size: 13px;
        transition: transform 0.3s ease;
    }

    .tabs-toggle-btn.expanded i {
        transform: rotate(180deg);
    }

    /* ===== Category Section Headers ===== */
    .category-section {
        padding: 0 1.25rem;
    }

    .category-section-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.85rem 0 0.5rem;
        margin-top: 0.25rem;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 0.75rem;
    }

    .category-section-header h6 {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .category-section-header .section-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
    }

    .category-section:last-child {
        padding-bottom: 1.25rem;
    }

    /* ===== Facility Card Styling - Premium Design ===== */
    .facility-card {
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        border: 1.5px solid #c5cdd8;
        border-radius: 0.75rem;
        height: 100%;
        position: relative;
        background: linear-gradient(145deg, #f0f5ff 0%, #edf2fb 50%, #e8eff9 100%);
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .facility-card:hover {
        background: linear-gradient(145deg, #e8f0fe 0%, #e3ecfa 50%, #dce6f6 100%);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1), 0 4px 10px rgba(0, 0, 0, 0.06);
        border-color: #94a3b8;
        transform: translateY(-4px);
    }

    .facility-card.chargeable {
        border: 1.5px solid #c5cdd8;
        border-top: 3px solid #f59e0b;
        background: linear-gradient(145deg, #fffcf0 0%, #fef9e7 50%, #fdf5dc 100%);
    }

    .facility-card.chargeable:hover {
        background: linear-gradient(145deg, #fef6d8 0%, #fdf0c8 50%, #fceabb 100%);
        border-color: #f59e0b;
        border-top: 3px solid #f59e0b;
        box-shadow: 0 10px 25px rgba(245, 158, 11, 0.15), 0 4px 10px rgba(0, 0, 0, 0.06);
    }

    .facility-card.free {
        border: 1.5px solid #c5cdd8;
        border-top: 3px solid #10b981;
        background: linear-gradient(145deg, #f0fdf8 0%, #e8faf2 50%, #dff7ec 100%);
    }

    .facility-card.free:hover {
        background: linear-gradient(145deg, #d8f5ea 0%, #ccf0e0 50%, #c0ebd6 100%);
        border-color: #10b981;
        border-top: 3px solid #10b981;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15), 0 4px 10px rgba(0, 0, 0, 0.06);
    }

    /* Card Body Layout */
    .facility-card-body {
        padding: 0.5rem 0.6rem 0.5rem;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* Icon Section - Premium Circle */
    .facility-icon-section {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        padding: 0.4rem;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8edf5 100%);
        border: 1px solid #e2e8f0;
        border-radius: 50%;
        margin: 0.35rem auto 0.35rem;
        transition: all 0.3s ease;
    }

    .facility-card:hover .facility-icon-section {
        background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
        border-color: #c7d2fe;
        transform: scale(1.05);
    }

    .facility-card.chargeable:hover .facility-icon-section {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fef3c7 100%);
        border-color: #fcd34d;
    }

    .facility-card.free:hover .facility-icon-section {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 50%, #d1fae5 100%);
        border-color: #6ee7b7;
    }

    .facility-icon {
        max-height: 24px;
        max-width: 24px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .facility-card:hover .facility-icon {
        transform: scale(1.08);
    }

    /* Card Content */
    .facility-card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .facility-card-title {
        font-size: 11.5px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        text-align: center;
        line-height: 1.25;
        min-height: 2em;
        display: flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0.01em;
    }

    .facility-card:hover .facility-card-title {
        color: #0f172a;
    }

    /* Status Badge - Premium Pill Style */
    .facility-status-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        z-index: 5;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(4px);
    }

    .facility-status-badge.chargeable {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: #78350f;
        border: none;
    }

    .facility-status-badge.free {
        background: linear-gradient(135deg, #34d399, #10b981);
        color: #fff;
        border: none;
    }

    /* Action Buttons - Premium Styling */
    .facility-card-actions {
        display: flex;
        justify-content: center;
        gap: 0.4rem;
        margin-top: 0.3rem;
        padding-top: 0.4rem;
        border-top: 1px solid #f1f5f9;
    }

    .btn-action {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: 1px solid transparent;
        background: #f8fafc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-action i {
        font-size: 12px;
        transition: all 0.2s ease;
    }

    .btn-action-edit {
        color: #059669;
        border-color: #d1fae5;
        background: #ecfdf5;
    }

    .btn-action-edit:hover {
        color: #fff;
        background: #059669;
        border-color: #059669;
    }

    .btn-action-delete {
        color: #dc2626;
        border-color: #fecaca;
        background: #fef2f2;
    }

    .btn-action-delete:hover {
        color: #fff;
        background: #dc2626;
        border-color: #dc2626;
    }

    /* ===== Modal Styling - Premium ===== */
    .facility-modal .modal-content {
        border: none;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15), 0 0 1px rgba(0, 0, 0, 0.1);
    }

    .facility-modal .modal-header {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #818cf8 100%);
        color: white;
        border-bottom: none;
        padding: 0.75rem 1.25rem;
        position: relative;
    }

    .facility-modal .modal-header::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #f59e0b, #10b981, #6366f1);
    }

    .facility-modal .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color:rgb(232, 236, 241);
    }

    .facility-modal .modal-body {
        padding: 1rem 1.25rem 0.85rem;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 40%);
    }

    .facility-modal .modal-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(145deg, #f0f4ff 0%, #e8edf5 100%);
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.6rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .facility-modal .modal-icon-wrapper:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    .facility-modal .modal-icon-wrapper img {
        height: 28px;
        width: 28px;
        object-fit: contain;
    }

    .facility-modal .modal-facility-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }

    .facility-modal .modal-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .facility-modal .modal-info-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.4rem 0.7rem;
        border-radius: 0.4rem;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        font-size: 12.5px;
        color: #334155;
    }

    .facility-modal .modal-info-item .info-icon {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 12px;
    }

    .facility-modal .modal-info-item .info-icon.category {
        background: #eef2ff;
        color: #6366f1;
    }

    .facility-modal .modal-info-item .info-icon.status {
        background: #ecfdf5;
        color: #10b981;
    }

    .facility-modal .modal-info-item .info-icon.status.chargeable {
        background: #fffbeb;
        color: #f59e0b;
    }

    .facility-modal .modal-info-item .info-icon.comment {
        background: #f0f9ff;
        color: #0ea5e9;
    }

    .facility-modal .modal-info-item .info-label {
        font-weight: 600;
        color: #64748b;
        min-width: 75px;
        font-size: 12px;
    }

    .facility-modal .modal-info-item .info-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 12.5px;
    }

    .facility-modal .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 0.6rem 1.25rem;
        background: #fff;
        gap: 0.4rem;
    }

    .facility-modal .modal-footer .btn {
        border-radius: 7px;
        font-weight: 600;
        font-size: 12px;
        padding: 0.35rem 1rem;
    }

    .facility-modal .modal-footer .btn-secondary {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #475569;
    }

    .facility-modal .modal-footer .btn-secondary:hover {
        background: #e2e8f0;
        color: #334155;
    }

    .facility-modal .modal-footer .btn-primary {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
    }

    .facility-modal .modal-footer .btn-primary:hover {
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        transform: translateY(-1px);
    }

    /* ===== Grid Layout: 5 Cards Per Row ===== */
    @media (min-width: 992px) {
        .facility-cards-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 0.75rem;
        }
        .facility-card-wrapper {
            flex: 0 0 calc(20% - 0.6rem) !important;
            max-width: calc(20% - 0.6rem) !important;
            width: calc(20% - 0.6rem) !important;
            margin-bottom: 0;
        }
    }
    
    @media (min-width: 768px) and (max-width: 991px) {
        .facility-card-wrapper {
            flex: 0 0 33.333333% !important;
            max-width: 33.333333% !important;
            width: 33.333333% !important;
            margin-bottom: 1rem;
        }
    }

    .facility-card-wrapper {
        margin-bottom: 1rem;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    /* Card hide/show animation for filtering */
    .facility-card-wrapper.hidden {
        display: none !important;
    }

    /* No results message */
    .no-results-msg {
        display: none;
        text-align: center;
        padding: 2.5rem 1rem;
        color: #94a3b8;
    }

    .no-results-msg i {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        display: block;
        color: #cbd5e1;
    }

    .no-results-msg p {
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }

    /* Summary bar */
    .facility-summary {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.5rem 1.25rem;
        font-size: 12px;
        color: #64748b;
        background: #f1f5f9;
        border-bottom: 1px solid #e2e8f0;
    }

    .facility-summary .summary-item {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .facility-summary .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .facility-summary .dot.free { background: #10b981; }
    .facility-summary .dot.chargeable { background: #f59e0b; }

    /* Responsive */
    @media (max-width: 768px) {
        .facility-toolbar { padding: 0.75rem 1rem 0.4rem; }
        .facility-search-box { max-width: 100%; }
        .category-tabs { gap: 0.3rem; }
        .category-tab { font-size: 11.5px; padding: 0.3rem 0.65rem; }
    }
</style>
@endsection
@extends('layouts.datatablecss')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center">
                        <h5 class="page-title mb-0">Facilities</h5>
                    </div>

                    <div class="d-flex align-items-center">
                        <!-- Add New Facility Button -->
                        @if(hasPermission('create facility'))
                        <a href="{{ route('facility.create') }}"
                            class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Add New Facility
                        </a>
                        @endif
                    </div>
                </div>

                <div style="padding: 15px 15px 0;">
                    <x-alert />
                </div>

                @php
                    $groupedFacilities = $facilities->groupBy(fn($facility) => $facility->categories->name ?? 'Uncategorized');
                    $totalFree = $facilities->where('is_chargeable', 0)->count();
                    $totalChargeable = $facilities->where('is_chargeable', 1)->count();
                @endphp

                <!-- Toolbar: Search + Category Tabs -->
                <div class="facility-toolbar">
                    <!-- Search Bar -->
                    <div class="facility-search-box">
                        <i class="ri-search-line search-icon"></i>
                        <input type="text" id="facilitySearch" placeholder="Search facilities...">
                    </div>

                    <!-- Category Filter Tabs -->
                    <div class="category-tabs-wrapper">
                        <div class="category-tabs collapsed" id="categoryTabs">
                            <span class="category-tab active" data-category="all">
                                All <span class="tab-count">{{ $facilities->count() }}</span>
                            </span>
                            @foreach($groupedFacilities as $category => $facilitiesGroup)
                            <span class="category-tab" data-category="{{ Str::slug($category) }}">
                                {{ $category }} <span class="tab-count">{{ $facilitiesGroup->count() }}</span>
                            </span>
                            @endforeach
                        </div>
                        <span class="tabs-toggle-btn" id="tabsToggle" style="display: none;">
                            <span class="toggle-text">More</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </span>
                    </div>
                </div>

                <!-- Summary Bar -->
                <div class="facility-summary">
                    <span class="summary-item">
                        <span class="dot free"></span> Free: <strong>{{ $totalFree }}</strong>
                    </span>
                    <span class="summary-item">
                        <span class="dot chargeable"></span> Chargeable: <strong>{{ $totalChargeable }}</strong>
                    </span>
                    <span class="summary-item" id="filteredCount" style="margin-left: auto;">
                        Showing <strong>{{ $facilities->count() }}</strong> of {{ $facilities->count() }} facilities
                    </span>
                </div>

                <!-- Facility Cards - All in One View -->
                <div id="facilityContainer">
                    @foreach($groupedFacilities as $category => $facilitiesGroup)
                    <div class="category-section" data-category="{{ Str::slug($category) }}">
                        <div class="category-section-header">
                            <h6>{{ $category }}</h6>
                            <span class="section-count">{{ $facilitiesGroup->count() }}</span>
                        </div>
                        <div class="row g-2 facility-cards-row">
                            @foreach($facilitiesGroup as $facility)
                            <div class="facility-card-wrapper col-lg-2 col-md-3 col-sm-4 col-6" 
                                 data-name="{{ strtolower($facility->name) }}"
                                 data-category="{{ Str::slug($category) }}">
                                <div class="card facility-card {{ $facility->is_chargeable ? 'chargeable' : 'free' }}">
                                    <!-- Status Badge -->
                                    @if($facility->is_chargeable)
                                    <span class="facility-status-badge chargeable">Chargeable</span>
                                    @else
                                    <span class="facility-status-badge free">Free</span>
                                    @endif
                                    
                                    <div class="facility-card-body">
                                        <!-- Icon Section -->
                                        <div class="facility-icon-section" 
                                             style="cursor: pointer;" 
                                             data-bs-toggle="modal"
                                             data-bs-target="#facilityModal-{{ $facility->id }}">
                                            <img src="{{ $facility->icon }}" alt="{{ $facility->name }}"
                                                class="facility-icon">
                                        </div>

                                        <!-- Card Content -->
                                        <div class="facility-card-content">
                                            <h6 class="facility-card-title" 
                                                style="cursor: pointer;" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#facilityModal-{{ $facility->id }}">
                                                {{ $facility->name }}
                                            </h6>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="facility-card-actions">
                                            @if(hasPermission('edit facility'))
                                            <a href="{{ route('facility.edit', $facility->id) }}" 
                                                class="btn-action btn-action-edit"
                                                title="Edit Facility">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                            @endif

                                            @if(hasPermission('delete facility'))
                                            <button type="button"
                                                class="btn-action btn-action-delete"
                                                title="Delete Facility"
                                                onclick="deleteFacility('{{ route('facility.destroy', $facility->id) }}', '{{ addslashes($facility->name) }}')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal -->
                            <div class="modal fade facility-modal" id="facilityModal-{{ $facility->id }}" tabindex="-1"
                                aria-labelledby="facilityModalLabel-{{ $facility->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="facilityModalLabel-{{ $facility->id }}">
                                                <i class="ri-building-2-line me-2"></i>Facility Details
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <!-- Icon -->
                                            <div class="modal-icon-wrapper">
                                                <img src="{{ $facility->icon }}" alt="{{ $facility->name }}">
                                            </div>
                                            <!-- Name -->
                                            <h5 class="modal-facility-name">{{ $facility->name }}</h5>
                                            <!-- Info List -->
                                            <ul class="modal-info-list text-start">
                                                <li class="modal-info-item">
                                                    <span class="info-icon category">
                                                        <i class="ri-folders-line"></i>
                                                    </span>
                                                    <span class="info-label">Category</span>
                                                    <span class="info-value">{{ $facility->categories->name ?? 'No category' }}</span>
                                                </li>
                                                <li class="modal-info-item">
                                                    <span class="info-icon status {{ $facility->is_chargeable ? 'chargeable' : '' }}">
                                                        <i class="ri-price-tag-3-line"></i>
                                                    </span>
                                                    <span class="info-label">Status</span>
                                                    <span class="info-value">
                                                        @if($facility->is_chargeable == 1)
                                                        <span class="badge bg-warning text-dark">Chargeable</span>
                                                        @else
                                                        <span class="badge bg-success">Free</span>
                                                        @endif
                                                    </span>
                                                </li>
                                                @if($facility->is_chargeable && $facility->chargable_comment)
                                                <li class="modal-info-item">
                                                    <span class="info-icon comment">
                                                        <i class="ri-chat-quote-line"></i>
                                                    </span>
                                                    <span class="info-label">Comment</span>
                                                    <span class="info-value" style="font-weight: 500;">{{ $facility->chargable_comment }}</span>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="ri-close-line me-1"></i>Close
                                            </button>
                                            @if(hasPermission('edit facility'))
                                            <a href="{{ route('facility.edit', $facility->id) }}" class="btn btn-primary">
                                                <i class="ri-pencil-line me-1"></i>Edit Facility
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    <!-- No Results Message -->
                    <div class="no-results-msg" id="noResults">
                        <i class="ri-search-line"></i>
                        <p>No facilities found matching your search.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>

<script>
$(document).ready(function() {
    const totalCount = {{ $facilities->count() }};
    let activeCategory = 'all';

    // ===== Tabs More/Less Toggle =====
    function checkTabsOverflow() {
        const tabs = document.getElementById('categoryTabs');
        const toggle = document.getElementById('tabsToggle');
        // Temporarily expand to measure full height
        tabs.classList.remove('collapsed');
        tabs.classList.add('expanded');
        const fullHeight = tabs.scrollHeight;
        tabs.classList.remove('expanded');
        tabs.classList.add('collapsed');
        // Show toggle only if content overflows 2 rows (72px)
        if (fullHeight > 76) {
            toggle.style.display = 'inline-flex';
        } else {
            toggle.style.display = 'none';
        }
    }

    checkTabsOverflow();
    $(window).on('resize', checkTabsOverflow);

    $('#tabsToggle').on('click', function() {
        const tabs = $('#categoryTabs');
        const btn = $(this);
        if (tabs.hasClass('collapsed')) {
            tabs.removeClass('collapsed').addClass('expanded');
            btn.addClass('expanded');
            btn.find('.toggle-text').text('Less');
        } else {
            tabs.removeClass('expanded').addClass('collapsed');
            btn.removeClass('expanded');
            btn.find('.toggle-text').text('More');
        }
    });

    // ===== Category Tab Filtering =====
    $('.category-tab').on('click', function() {
        $('.category-tab').removeClass('active');
        $(this).addClass('active');
        activeCategory = $(this).data('category');
        applyFilters();
    });

    // ===== Search Filtering =====
    $('#facilitySearch').on('input', function() {
        applyFilters();
    });

    // ===== Combined Filter Logic =====
    function applyFilters() {
        const searchTerm = $('#facilitySearch').val().toLowerCase().trim();
        let visibleCount = 0;

        // Show/hide category sections
        $('.category-section').each(function() {
            const sectionCategory = $(this).data('category');
            const categoryMatch = (activeCategory === 'all' || sectionCategory === activeCategory);

            if (!categoryMatch) {
                $(this).hide();
                return;
            }

            $(this).show();
            let sectionHasVisible = false;

            // Filter individual cards within visible sections
            $(this).find('.facility-card-wrapper').each(function() {
                const name = $(this).data('name');
                const matchesSearch = !searchTerm || name.indexOf(searchTerm) !== -1;

                if (matchesSearch) {
                    $(this).removeClass('hidden');
                    sectionHasVisible = true;
                    visibleCount++;
                } else {
                    $(this).addClass('hidden');
                }
            });

            // Hide section header if no cards visible in it
            if (!sectionHasVisible) {
                $(this).hide();
            }
        });

        // Update counter
        $('#filteredCount').html('Showing <strong>' + visibleCount + '</strong> of ' + totalCount + ' facilities');

        // Show/hide no results
        if (visibleCount === 0) {
            $('#noResults').show();
        } else {
            $('#noResults').hide();
        }
    }

    // Initialize DataTable (if table exists)
    if ($('.datatables-basic').length) {
        $('.datatables-basic').DataTable({
            responsive: true,
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: { search: "_INPUT_", searchPlaceholder: "Search..." },
            lengthMenu: [10, 25, 50, 100],
        });
    }
});

// Facility deletion function with SweetAlert
window.deleteFacility = function(deleteUrl, facilityName) {
    Swal.fire({
        title: 'Delete Facility?',
        text: `Are you sure you want to delete "${facilityName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteUrl;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
};
</script>

@endsection
