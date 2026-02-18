@extends('layouts.layout')
@section('title', 'Multi Restaurants')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<style>
    /* Select2 Styling */
    .select2-container .select2-selection--multiple {
        height: auto !important;
        line-height: 1.4;
        padding: 2px 6px;
        border-radius: 0.375rem;
        border-color: #d9dee3;
        min-height: calc(1.5em + 0.7rem);
        font-size: 0.8rem;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #696cff;
        border: 1px solid #696cff;
        color: #fff;
        border-radius: 0.25rem;
        padding: 1px 5px;
        margin-right: 3px;
        margin-top: 2px;
        font-size: 0.7rem;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 4px;
    }
    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 3px;
    }
    .select2-container .select2-results__option {
        padding: 4px 6px;
        font-size: 0.75rem;
    }
    /* Make sure theme doesn't override dropdown font size */
    .select2-container--bootstrap-5 .select2-results__option {
        font-size: 0.75rem !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #696cff;
    }
    .select2-dropdown {
        border-color: #d9dee3;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        font-size: 0.8rem;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 4px;
        padding: 6px;
        border-color: #d9dee3;
        font-size: 0.8rem;
    }
    .select2-results__options {
        max-height: 200px;
        overflow-y: auto;
    }

    .inline-form-row {
        transition: all 0.3s ease;
    }
    .inline-form-row.editing-mode {
        background: transparent !important;
    }
    .inline-form-row.new-row {
        /* No special border styling */
    }
    
    /* Compact form styling */
    .inline-form-row .card-body {
        padding: 0.75rem;
    }
    .inline-form-row .form-label {
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .inline-form-row .form-control,
    .inline-form-row .form-select {
        font-size: 0.85rem;
        padding: 0.55rem 0.75rem;
        height: calc(1.5em + 1.3rem);
    }
    .inline-form-row .input-group-text {
        font-size: 0.85rem;
        padding: 0.55rem 0.75rem;
    }
    .inline-form-row small.text-muted {
        font-size: 0.7rem;
        display: block;
        margin-top: 0.25rem;
    }
    .inline-form-row .btn {
        font-size: 0.8rem;
        padding: 0.35rem 0.65rem;
    }

    /* Show selected restaurant names as cards/tags inside the select box */
    .restaurants-select + .select2-container .select2-selection--multiple .select2-selection__rendered {
        padding-left: 6px;
        padding-right: 6px;
    }
    .restaurants-select + .select2-container .select2-selection--multiple .select2-selection__choice {
        display: inline-flex !important;
        align-items: center;
        max-width: 180px;
    }
    .restaurants-select + .select2-container .select2-selection--multiple .select2-selection__choice__display {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Ensure input-group $ icon and textbox share same height */
    .inline-form-row .input-group .input-group-text,
    .inline-form-row .input-group .form-control {
        height: calc(1.5em + 1.3rem);
    }
    .inline-form-row .select2-container .select2-selection--multiple {
        min-height: calc(1.5em + 1.3rem);
        padding: 4px 8px;
    }

    /* Toggle switch for status */
    .status-toggle {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .status-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .status-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }
    .status-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    .status-toggle input:checked + .status-slider {
        background-color: #28a745;
    }
    .status-toggle input:checked + .status-slider:before {
        transform: translateX(26px);
    }

    /* Align price action buttons vertically with price inputs */
    .inline-form-row .price-actions {
        display: flex;
        align-items: flex-end;
        height: 100%;
    }
    
    /* Restaurant icons preview */
    .restaurant-icons-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 0.25rem;
        min-height: 50px;
    }
    .restaurant-icon-item {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #696cff;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .restaurant-icon-item:hover {
        transform: scale(1.1);
        z-index: 10;
    }
    .restaurant-icon-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .restaurant-icon-item .icon-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease-in-out;
        margin-bottom: 8px;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .restaurant-icon-item:hover .icon-tooltip {
        opacity: 1;
    }
    .restaurant-icon-item .icon-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #333;
    }
    
    .form-control-sm, .form-select-sm {
        font-size: 0.8rem;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    .action-buttons {
        white-space: nowrap;
    }
    .restaurant-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        max-width: 300px;
    }
    .restaurant-badge {
        background: #696cff;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
    }
    .editing-mode .view-content {
        display: none !important;
    }
    .editing-mode .edit-content {
        display: block !important;
    }
    .view-content {
        display: block;
    }
    .edit-content {
        display: none !important;
    }
    .editing-mode {
        background: #fff3cd !important;
    }
    .table-responsive {
        overflow-x: visible;
    }
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Multi Restaurants</h5>
                    </div>

                    {{-- Admin filter by DMC/company --}}
                    @if(auth()->user()->role_id == 1 && isset($companyFilters) && $companyFilters->count())
                        <form method="GET" action="{{ route('multiResturant.index') }}" class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 me-2"><strong>Filter by Company</strong></label>
                            <select name="dmc_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All</option>
                                @foreach($companyFilters as $company)
                                    <option value="{{ $company->dmcId }}"
                                        {{ (string)($selectedDmcId ?? '') === (string)$company->dmcId ? 'selected' : '' }}>
                                        {{ $company->company_name ?? ('DMC #' . $company->dmcId) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        @endif

                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 15px;">
                        <div class="d-flex">
                            <i class="fas fa-check-circle me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 15px;">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row g-3" style="margin: 15px;">
                    @if(auth()->user()->role_id == 11 && empty($hasPackageForDmc))
                    <!-- New Package Row (inline create form) -->
                    <div class="col-12">
                        <div class="card inline-form-row new-row" id="newPackageRow">
                            <div class="card-body">
                                <form id="createForm" method="POST" action="{{ route('multiResturant.store') }}" class="row g-2 align-items-end">
                                    @csrf
                                    <!-- First Row: Package Name -->
                                    <div class="col-12">
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <label class="form-label">
                                                    <strong>Package Name</strong> <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="package_name"
                                                       placeholder="Enter Package Name" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Second Row: Restaurants -->
                                    <div class="col-12">
                                        <div class="row g-2">
                                            <div class="col-md-12">
                                                <label class="form-label">
                                                    <strong>Restaurants</strong> <span class="text-danger">*</span>
                                                </label>
                                                <select name="restaurants[]" id="createRestaurantsSelect" class="form-select restaurants-select" multiple required>
                                                    @foreach($restaurants ?? [] as $restaurant)
                                                        <option data-image="{{ $restaurant->master_image ?? '' }}"
                                                                data-name="{{ $restaurant->name }}"
                                                                value="{{ $restaurant->restaurant_id }}">
                                                            {{ $restaurant->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="restaurant-icons-preview" id="createRestaurantIcons"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Third Row: Breakfast, Lunch, Dinner -->
                                    <div class="col-12">
                                        <div class="row g-2">
                                            <!-- Breakfast -->
                                            <div class="col-md-4">
                                                <label class="form-label">
                                                    <strong>Breakfast</strong> <span class="text-danger">*</span>
                                                </label>
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <select name="breakfast_on" class="form-select w-auto" required>
                                                        <option value="1">On</option>
                                                        <option value="0" selected>Off</option>
                                                    </select>
                                                    <input type="time" name="breakfast_start_time"
                                                           class="form-control"
                                                           placeholder="Start time">
                                                    <span class="mx-1">to</span>
                                                    <input type="time" name="breakfast_end_time"
                                                           class="form-control"
                                                           placeholder="End time">
                                                </div>
                                                <small class="text-muted">Start/end time is required when Breakfast is On.</small>
                                            </div>

                                            <!-- Lunch -->
                                            <div class="col-md-4">
                                                <label class="form-label">
                                                    <strong>Lunch</strong> <span class="text-danger">*</span>
                                                </label>
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <select name="lunch_on" class="form-select w-auto" required>
                                                        <option value="1">On</option>
                                                        <option value="0" selected>Off</option>
                                                    </select>
                                                    <input type="time" name="lunch_start_time"
                                                           class="form-control"
                                                           placeholder="Start time">
                                                    <span class="mx-1">to</span>
                                                    <input type="time" name="lunch_end_time"
                                                           class="form-control"
                                                           placeholder="End time">
                                                </div>
                                                <small class="text-muted">Start/end time is required when Lunch is On.</small>
                                            </div>

                                            <!-- Dinner -->
                                            <div class="col-md-4">
                                                <label class="form-label">
                                                    <strong>Dinner</strong> <span class="text-danger">*</span>
                                                </label>
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <select name="dinner_on" class="form-select w-auto" required>
                                                        <option value="1">On</option>
                                                        <option value="0" selected>Off</option>
                                                    </select>
                                                    <input type="time" name="dinner_start_time"
                                                           class="form-control"
                                                           placeholder="Start time">
                                                    <span class="mx-1">to</span>
                                                    <input type="time" name="dinner_end_time"
                                                           class="form-control"
                                                           placeholder="End time">
                                                </div>
                                                <small class="text-muted">Start/end time is required when Dinner is On.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fourth Row: Adult Price, Child Price, Status, Submit Button -->
                                    <div class="col-12">
                                        <div class="row g-2 align-items-end">
                                            <!-- Adult Price -->
                                            <div class="col-md-3">
                                                <label class="form-label">
                                                    <strong>Adult Price</strong> <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" min="0"
                                                           class="form-control"
                                                           name="price"
                                                           placeholder="0.00" required>
                                                </div>
                                            </div>

                                            <!-- Child Price -->
                                            <div class="col-md-3">
                                                <label class="form-label">
                                                    <strong>Child Price</strong>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="1" min="0"
                                                           class="form-control"
                                                           name="child_price"
                                                           placeholder="0 (optional)">
                                                </div>
                                            </div>

                                            <!-- Status -->
                                            <div class="col-md-2">
                                                <label class="form-label">
                                                    <strong>Status</strong>
                                                </label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="status-toggle">
                                                        <input type="checkbox" id="createStatusCheckbox" checked>
                                                        <span class="status-slider"></span>
                                                    </label>
                                                    <input type="hidden" name="status" value="1" id="createStatusHidden">
                                                    <span class="status-label" style="font-size: 0.75rem;">Active</span>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="price-actions gap-2">
                                                    <button type="submit" class="btn btn-primary">
                                                        Submit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                    @forelse($multiRestaurants ?? [] as $key => $item)
                        @php 
                            $encId = isset($item->package_unique_id) ? Crypt::encrypt($item->package_unique_id) : '';
                            $restaurantIds = $item->getRestaurantsAsArray();
                            $selectedIds = array_map('intval', $restaurantIds);
                            $selectedRestaurants = collect($restaurants ?? [])->filter(function($r) use ($restaurantIds) {
                                return in_array($r->restaurant_id, $restaurantIds) || in_array($r->id, $restaurantIds);
                            });

                            // Get company name from User table based on dmc_id
                            $companyName = 'N/A';
                            if (!empty($item->dmc_id)) {
                                $dmcUser = \App\Models\User::where('dmcId', $item->dmc_id)->first();
                                $companyName = $dmcUser->company_name ?? 'N/A';
                            }

                            // Prepare meal toggle + time values from stored columns
                            $bOn = old('breakfast_on') !== null ? old('breakfast_on') : ($item->breakfast ?? 0);
                            $lOn = old('lunch_on') !== null ? old('lunch_on') : ($item->lunch ?? 0);
                            $dOn = old('dinner_on') !== null ? old('dinner_on') : ($item->dinner ?? 0);

                            $bStart = $bEnd = '';
                            if (!empty($item->breakfast_time)) {
                                $parts = explode('-', $item->breakfast_time);
                                $bStart = $parts[0] ?? '';
                                $bEnd   = $parts[1] ?? '';
                            }

                            $lStart = $lEnd = '';
                            if (!empty($item->lunch_time)) {
                                $parts = explode('-', $item->lunch_time);
                                $lStart = $parts[0] ?? '';
                                $lEnd   = $parts[1] ?? '';
                            }

                            $dStart = $dEnd = '';
                            if (!empty($item->dinner_time)) {
                                $parts = explode('-', $item->dinner_time);
                                $dStart = $parts[0] ?? '';
                                $dEnd   = $parts[1] ?? '';
                            }
                        @endphp
                        <div class="col-12">
                            <div class="card inline-form-row" data-id="{{ $item->id }}" id="row_{{ $item->id }}">
                                <div class="card-body">
                                    <!-- View Mode -->
                                    <div class="view-content">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-2">
                                                <div>
                                                    <h6 class="mb-0" style="font-size: 0.85rem;">{{ $item->package_name ?? 'N/A' }}</h6>
                                                    <small class="text-muted" style="font-size: 0.7rem;">ID: {{ $item->package_id ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div>
                                                    <strong style="font-size: 0.8rem;">Company:</strong>
                                                    <div style="font-size: 0.75rem;">{{ $companyName }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="restaurant-badges">
                                                    @foreach($selectedRestaurants->take(5) as $rest)
                                                        <span class="restaurant-badge" style="font-size: 0.7rem;">{{ $rest->name }}</span>
                                                    @endforeach
                                                    @if($selectedRestaurants->count() > 5)
                                                        <span class="restaurant-badge" style="font-size: 0.7rem;">+{{ $selectedRestaurants->count() - 5 }} more</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                            <div class="d-flex flex-column">
                                                    <strong style="font-size: 0.8rem;">Adult: ${{ number_format($item->adult_price ?? 0, 2) }}</strong>
                                                    @if(!is_null($item->child_price ?? null))
                                                        <small class="text-muted" style="font-size: 0.7rem;">Child: ${{ number_format($item->child_price ?? 0, 2) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                    @if(($item->status ?? 0) == 1)
                                        <span class="badge bg-success" style="font-size: 0.7rem;">Active</span>
                                    @else
                                        <span class="badge bg-danger" style="font-size: 0.7rem;">Inactive</span>
                                    @endif
                                            </div>
                                            <div class="col-md-2 text-end">
                                    @if($encId)
                                    <a href="{{ route('multiResturant.show', $encId) }}"
                                        class="btn btn-primary btn-sm rounded-circle"
                                                    style="width: 32px; height: 32px; padding: 0;" title="View">
                                                    <i class="fas fa-eye"></i>
                                    </a>
                                    @if(Auth::user()->role_id == 1 || in_array(Auth::user()->role_id, [11, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138]))
                                                @if(Auth::user()->role_id != 1)
                                                <button type="button" class="btn btn-info btn-sm rounded-circle edit-btn"
                                                    style="width: 32px; height: 32px; padding: 0;" title="Edit"
                                                    data-id="{{ $item->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @endif
                                    <button type="button"
                                        class="btn btn-danger btn-sm rounded-circle"
                                                    style="width: 32px; height: 32px; padding: 0;"
                                                    onclick="setDeleteForm('{{ route('multiResturant.destroy', $encId) }}', '{{ $item->package_name ?? 'this item' }}')"
                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                    @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Mode -->
                                    <div class="edit-content" style="display: none;">
                                        <form class="updateForm row g-2 align-items-end" id="updateForm_{{ $item->id }}" method="POST" action="{{ route('multiResturant.update', $encId) }}">
                                            @csrf
                                            @method('PUT')
                                            <!-- First Row: Package Name -->
                                            <div class="col-12">
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            <strong>Package Name</strong> <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" name="package_name" 
                                                               value="{{ old('package_name', $item->package_name) }}" required>
                                                        <small class="text-muted">ID: {{ $item->package_id }}</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Second Row: Restaurants -->
                                            <div class="col-12">
                                                <div class="row g-2">
                                                    <div class="col-md-12">
                                                        <label class="form-label">
                                                            <strong>Restaurants</strong> <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="restaurants[]" id="updateRestaurantsSelect_{{ $item->id }}" class="form-select restaurants-select" multiple required form="updateForm_{{ $item->id }}">
                                                            @foreach($restaurants ?? [] as $restaurant)
                                                                <option
                                                                    data-image="{{ $restaurant->master_image ?? '' }}"
                                                                    data-name="{{ $restaurant->name }}"
                                                                    value="{{ $restaurant->restaurant_id }}"
                                                                    {{ in_array((int) $restaurant->restaurant_id, $selectedIds) ? 'selected' : '' }}
                                                                >
                                                                    {{ $restaurant->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        {{-- Icons from multi_restaurants → selected IDs → Restaurant table (no Select2 dependency) --}}
                                                        <div class="restaurant-icons-preview" id="updateRestaurantIcons_{{ $item->id }}">
                                                            @foreach($selectedRestaurants as $rest)
                                                                @php
                                                                    $img = $rest->master_image ?? '';
                                                                    $imgSrc = $img ? ((strpos($img, 'http') === 0 || strpos($img, '/') === 0) ? $img : '/'.$img) : 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect fill="#ddd" width="40" height="40"/><text x="50%" y="50%" fill="#999" text-anchor="middle" dy=".3em" font-size="8">No img</text></svg>');
                                                                @endphp
                                                                <div class="restaurant-icon-item" data-value="{{ $rest->restaurant_id }}" title="{{ e($rest->name) }}">
                                                                    <img src="{{ $imgSrc }}" alt="{{ e($rest->name) }}">
                                                                    <span class="icon-tooltip">{{ e($rest->name) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Third Row: Breakfast, Lunch, Dinner -->
                                            <div class="col-12">
                                                <div class="row g-2">
                                                    <!-- Breakfast -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">
                                                            <strong>Breakfast</strong> <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                                            <select name="breakfast_on" class="form-select w-auto" required>
                                                                <option value="1" {{ $bOn == 1 ? 'selected' : '' }}>On</option>
                                                                <option value="0" {{ $bOn == 0 ? 'selected' : '' }}>Off</option>
                                                            </select>
                                                            <input type="time" name="breakfast_start_time"
                                                                   class="form-control"
                                                                   value="{{ old('breakfast_start_time', $bStart) }}">
                                                            <span class="mx-1">to</span>
                                                            <input type="time" name="breakfast_end_time"
                                                                   class="form-control"
                                                                   value="{{ old('breakfast_end_time', $bEnd) }}">
                                                        </div>
                                                        <small class="text-muted">Start/end time is required when Breakfast is On.</small>
                                                    </div>

                                                    <!-- Lunch -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">
                                                            <strong>Lunch</strong> <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                                            <select name="lunch_on" class="form-select w-auto" required>
                                                                <option value="1" {{ $lOn == 1 ? 'selected' : '' }}>On</option>
                                                                <option value="0" {{ $lOn == 0 ? 'selected' : '' }}>Off</option>
                                                            </select>
                                                            <input type="time" name="lunch_start_time"
                                                                   class="form-control"
                                                                   value="{{ old('lunch_start_time', $lStart) }}">
                                                            <span class="mx-1">to</span>
                                                            <input type="time" name="lunch_end_time"
                                                                   class="form-control"
                                                                   value="{{ old('lunch_end_time', $lEnd) }}">
                                                        </div>
                                                        <small class="text-muted">Start/end time is required when Lunch is On.</small>
                                                    </div>

                                                    <!-- Dinner -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">
                                                            <strong>Dinner</strong> <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                                            <select name="dinner_on" class="form-select w-auto" required>
                                                                <option value="1" {{ $dOn == 1 ? 'selected' : '' }}>On</option>
                                                                <option value="0" {{ $dOn == 0 ? 'selected' : '' }}>Off</option>
                                                            </select>
                                                            <input type="time" name="dinner_start_time"
                                                                   class="form-control"
                                                                   value="{{ old('dinner_start_time', $dStart) }}">
                                                            <span class="mx-1">to</span>
                                                            <input type="time" name="dinner_end_time"
                                                                   class="form-control"
                                                                   value="{{ old('dinner_end_time', $dEnd) }}">
                                                        </div>
                                                        <small class="text-muted">Start/end time is required when Dinner is On.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Fourth Row: Adult Price, Child Price, Status, Submit/Cancel Buttons -->
                                            <div class="col-12">
                                                <div class="row g-2 align-items-end">
                                                    <!-- Adult Price -->
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            <strong>Adult Price</strong> <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" step="0.01" min="0"
                                                                   class="form-control"
                                                                   name="price" 
                                                                   value="{{ old('price', $item->adult_price) }}" required>
                                                        </div>
                                                    </div>

                                                    <!-- Child Price -->
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            <strong>Child Price</strong>
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" step="1" min="0"
                                                                   class="form-control"
                                                                   name="child_price"
                                                                   value="{{ old('child_price', $item->child_price ?? null) }}"
                                                                   placeholder="0 (optional)">
                                                        </div>
                                                    </div>

                                                    <!-- Status -->
                                                    <div class="col-md-2">
                                                        <label class="form-label">
                                                            <strong>Status</strong>
                                                        </label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <label class="status-toggle">
                                                                <input type="checkbox" class="status-checkbox" data-item-id="{{ $item->id }}" {{ (old('status', $item->status) == 1) ? 'checked' : '' }}>
                                                                <span class="status-slider"></span>
                                                            </label>
                                                            <input type="hidden" name="status" value="{{ old('status', $item->status) }}" id="updateStatusHidden_{{ $item->id }}">
                                                            <span class="status-label" style="font-size: 0.75rem;">{{ (old('status', $item->status) == 1) ? 'Active' : 'Inactive' }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Buttons -->
                                                    <div class="col-md-2">
                                                        <label class="form-label">&nbsp;</label>
                                                        <div class="price-actions gap-2">
                                                            <button type="submit" class="btn btn-success">
                                                                Submit
                                                            </button>
                                                            <button type="button" class="btn btn-secondary cancel-edit-btn" data-id="{{ $item->id }}">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-4">
                                    <p class="text-muted mb-0">No multi restaurant packages found.</p>
                                </div>
                            </div>
                        </div>
                        @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this multi restaurant package?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Derive icons container id from select id (create: createRestaurantsSelect -> createRestaurantIcons; edit: updateRestaurantsSelect_N -> updateRestaurantIcons_N)
        function getIconsContainerId(selectId) {
            if (!selectId) return '';
            return selectId.replace('RestaurantsSelect', 'RestaurantIcons');
        }
        // Update restaurant icons from current select value (works with Select2: use .val() which Select2 keeps in sync)
        function updateRestaurantIcons(selectId, containerId) {
            if (!containerId) containerId = getIconsContainerId(selectId);
            var $select = $('#' + selectId);
            var $container = $('#' + containerId);
            if (!$select.length || !$container.length) return;
            $container.empty();
            var selectedValues = $select.val();
            if (!selectedValues || (Array.isArray(selectedValues) && selectedValues.length === 0)) {
                $container.html('<small class="text-muted">No restaurants selected</small>');
                return;
            }
            if (!Array.isArray(selectedValues)) {
                selectedValues = [selectedValues];
            }
            var placeholderImg = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect fill="%23ddd" width="40" height="40"/><text x="50%" y="50%" fill="%23999" text-anchor="middle" dy=".3em" font-size="8">No img</text></svg>';
            selectedValues.forEach(function(value) {
                if (!value || value === '') return;
                var $option = $select.find('option').filter(function() { return $(this).val() == value; }).first();
                var image = $option.length ? ($option.data('image') || $option.attr('data-image') || '') : '';
                var name = $option.length ? ($option.data('name') || $option.attr('data-name') || $option.text()) : String(value);
                var imgSrc = image ? (image.indexOf('http') === 0 || image.indexOf('/') === 0 ? image : ('/' + image)) : placeholderImg;
                var safeName = name.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var $icon = $('<div class="restaurant-icon-item" data-value="' + value + '" title="' + safeName + '"><img src="' + imgSrc + '" alt="' + safeName + '"><span class="icon-tooltip">' + safeName + '</span></div>');
                $container.append($icon);
            });
        }

        // Status toggle handler for create form
        $('#createStatusCheckbox').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('#createStatusHidden').val(isChecked ? '1' : '0');
            $(this).closest('.d-flex').find('.status-label').text(isChecked ? 'Active' : 'Inactive');
        });

        // Initialize Select2 for create form
        $('#createRestaurantsSelect').select2({
            theme: 'bootstrap-5',
            placeholder: "Select Restaurants",
            allowClear: true,
            width: '100%',
            dropdownParent: $('body')
        });
        
        // Single delegated handler: any restaurant select (create or edit) change updates its icons
        $(document).on('change', '.restaurants-select', function() {
            var selectId = $(this).attr('id');
            if (selectId) updateRestaurantIcons(selectId, getIconsContainerId(selectId));
        });
        
        // Initialize icons for create form on load
        updateRestaurantIcons('createRestaurantsSelect', 'createRestaurantIcons');

        // Note: DataTable removed - using card-based layout instead

        // Edit button click handler
        $(document).on('click', '.edit-btn', function() {
            var rowId = $(this).data('id');
            var $row = $('#row_' + rowId);
            
            // Close any other open edit forms
            $('.inline-form-row').not($row).each(function() {
                var $otherRow = $(this);
                $otherRow.removeClass('editing-mode');
                $otherRow.find('.view-content').show();
                $otherRow.find('.edit-content').hide();
                $otherRow.find('.restaurants-select').select2('destroy');
            });
            
            // Toggle edit mode for this row
            $row.addClass('editing-mode');
            $row.find('.view-content').hide();
            $row.find('.edit-content').show();
            
            // Reinitialize Select2 for the edit form
            setTimeout(function() {
                var selectId = $row.find('.restaurants-select').attr('id');
                var containerId = selectId.replace('Select', 'Icons');
                var $select = $row.find('.restaurants-select');
                
                // Get selected values BEFORE initializing Select2
                var selectedValues = [];
                $select.find('option:selected').each(function() {
                    var val = $(this).val();
                    if (val && val !== '') {
                        selectedValues.push(val);
                    }
                });
                
                // Initialize Select2 - same as create form
                $select.select2({
                    theme: 'bootstrap-5',
                    placeholder: "Select Restaurants",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('body')
                });
                
                // Ensure Select2 recognizes the selected values
                if (selectedValues.length > 0) {
                    $select.val(selectedValues).trigger('change');
                }
                
                // Icons already rendered server-side; update on Select2 select/unselect (and change is handled by delegated handler above)
                $select.on('select2:select select2:unselect', function() {
                    updateRestaurantIcons(selectId, containerId);
                });

                // Status toggle handler for edit form
                $row.find('.status-checkbox').off('change').on('change', function() {
                    var isChecked = $(this).is(':checked');
                    var itemId = $(this).data('item-id');
                    $('#updateStatusHidden_' + itemId).val(isChecked ? '1' : '0');
                    $(this).closest('.d-flex').find('.status-label').text(isChecked ? 'Active' : 'Inactive');
                });
            }, 100);
        });

        // Cancel edit button click handler
        $(document).on('click', '.cancel-edit-btn', function() {
            var rowId = $(this).data('id');
            var $row = $('#row_' + rowId);
            
            $row.removeClass('editing-mode');
            $row.find('.view-content').show();
            $row.find('.edit-content').hide();
            
            // Destroy Select2 instance
            $row.find('.restaurants-select').off('change').select2('destroy');
        });

        function validateMealTimes($form) {
            const meals = ['breakfast', 'lunch', 'dinner'];
            for (const meal of meals) {
                const onVal = $form.find(`[name="${meal}_on"]`).val();
                const start = $form.find(`[name="${meal}_start_time"]`).val();
                const end   = $form.find(`[name="${meal}_end_time"]`).val();

                if (onVal === '1') {
                    if (!start || !end) {
                        alert(meal.charAt(0).toUpperCase() + meal.slice(1) + ' time is required when it is On');
                        return false;
                    }
                }
            }
            return true;
        }

        // Form validation for create form
        $('#createForm').on('submit', function(e) {
            // Ensure status hidden input is set correctly
            var statusChecked = $(this).find('.status-toggle input[type="checkbox"]').is(':checked');
            $(this).find('#createStatusHidden').val(statusChecked ? '1' : '0');
            
            var restaurants = $(this).find('.restaurants-select').val();
            if (!restaurants || restaurants.length === 0) {
                e.preventDefault();
                alert('Please select at least one restaurant');
                return false;
            }
            if (!validateMealTimes($(this))) {
                e.preventDefault();
                return false;
            }
        });

        // Form validation for update forms
        $(document).on('submit', '.updateForm', function(e) {
            var $row = $(this).closest('.inline-form-row');
            
            // Ensure status hidden input is set correctly
            var statusChecked = $row.find('.status-checkbox').is(':checked');
            $row.find('input[id^="updateStatusHidden_"]').val(statusChecked ? '1' : '0');
            
            var restaurants = $row.find('.restaurants-select').val();
            if (!restaurants || restaurants.length === 0) {
                e.preventDefault();
                alert('Please select at least one restaurant');
                return false;
            }
            if (!validateMealTimes($(this))) {
                e.preventDefault();
                return false;
            }
        });
    });

    function resetNewForm() {
        $('#createForm')[0].reset();
        $('#createForm').find('.restaurants-select').val(null).trigger('change');
        updateRestaurantIcons('createRestaurantsSelect', 'createRestaurantIcons');
    }

    function setDeleteForm(action, itemName) {
        document.getElementById('deleteForm').action = action;
        var modalBody = document.querySelector('#deleteModal .modal-body p:first-child');
        if (modalBody) {
            modalBody.innerHTML = 'Are you sure you want to delete "<strong>' + itemName + '</strong>"?';
        }
        try {
            if (typeof bootstrap !== 'undefined') {
                var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            } else {
                document.getElementById('deleteModal').style.display = 'block';
                document.getElementById('deleteModal').classList.add('show');
                document.body.classList.add('modal-open');
            }
        } catch (e) {
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('deleteModal').classList.add('show');
            document.body.classList.add('modal-open');
        }
    }

    $('#deleteForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).text('Deleting...');
    });
    
    $('#deleteModal').on('hidden.bs.modal', function() {
        $('#deleteForm').find('button[type="submit"]').prop('disabled', false).text('Delete');
    });
</script>
@endsection
