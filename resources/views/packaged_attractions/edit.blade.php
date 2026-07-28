@extends('layouts.layout')

@section('css')
<!-- Include necessary CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

<style>
    /* Select2 Styling */
    .select2-container .select2-selection--multiple {
        height: auto !important;
        line-height: 1.5;
        padding: 8px 12px;
        border-radius: 0.375rem;
        border-color: #d9dee3;
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
        padding: 2px 8px;
        margin-right: 5px;
        margin-top: 2px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 5px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
        opacity: 0.8;
    }
    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 3px;
    }
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #696cff;
    }
    .select2-dropdown {
        border-color: #d9dee3;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 4px;
        padding: 8px;
        border-color: #d9dee3;
    }
    /* Limit dropdown height */
    .select2-results__options {
        max-height: 250px;
        overflow-y: auto;
    }
    /* End of Select2 styling */
    
    .attraction-preview {
        background: #f8f9fa;
        border-radius: 0.375rem;
        padding: 15px;
        margin-top: 15px;
        border: 1px solid #d9dee3;
    }
    .attraction-preview-item {
        display: flex;
        align-items: center;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 0.375rem;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .attraction-preview-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .attraction-preview-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 0.25rem;
        margin-right: 15px;
    }
    .attraction-preview-item .attraction-info {
        flex-grow: 1;
    }
    .image-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
    }
    .image-preview-item {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 0.375rem;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .image-preview-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .image-preview-item .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255,255,255,0.8);
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #dc3545;
        transition: all 0.2s ease;
    }
    .image-preview-item .remove-image:hover {
        background: rgba(255,255,255,1);
        transform: scale(1.1);
    }
    .custom-file-upload {
        display: inline-block;
        padding: 10px 20px;
        cursor: pointer;
        background: linear-gradient(45deg, #696cff, #8083ff);
        color: white;
        border-radius: 0.375rem;
        transition: all 0.3s ease;
    }
    .custom-file-upload:hover {
        background: linear-gradient(45deg, #5d60ff, #7073ff);
        transform: translateY(-2px);
    }
    .form-label strong {
        color: #566a7f;
    }
    .card {
        border: none;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        border-radius: 0.5rem;
    }
    .card-header {
        background: linear-gradient(45deg, #696cff, #8083ff);
        color: white;
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .btn-primary {
        background: linear-gradient(45deg, #696cff, #8083ff);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(45deg, #5d60ff, #7073ff);
    }
    .form-control:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
    }
    .section-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #405189;
        margin-bottom: 0.5rem;
        padding-bottom: 0.25rem;
        border-bottom: 1px solid #e9ecef;
    }
    .package-price-table { font-size: 0.8125rem; margin-bottom: 0; }
    .package-price-table th,
    .package-price-table td { padding: 0.35rem 0.5rem; vertical-align: middle; }
    .package-price-table thead th { font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
    .package-price-table .form-control { max-width: 100%; }
    .package-price-table .age-badge { font-size: 0.7rem; padding: 0.2em 0.45em; }
    .existing-images {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }
    .existing-image {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 0.375rem;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .existing-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .existing-image .remove-existing {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255,255,255,0.8);
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #dc3545;
    }
</style>
@endsection

@section('content')
@php
    $dmcCurrency = \App\Helpers\CommonHelper::getDmcCurrencyByCountry();
@endphp
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center flex-wrap gap-2">
                    Edit Packaged Attraction
                    <x-currency-price-note />
                </span>
                <a href="{{ route('packaged-attractions.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </h5>
            <x-alert />
            <form id="packagedAttractionForm" method="POST" action="{{ route('packaged-attractions.update', Crypt::encrypt($packagedAttraction->package_attraction_id)) }}"
                enctype="multipart/form-data" class="card-body">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Package Attraction ID -->
                    <div class="col-md-6 mb-3">
                        <label for="package_attraction_name" class="form-label">
                            <strong>Package Name</strong><span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="package_attraction_name" name="package_attraction_name" 
                               placeholder="Enter Package Name" value="{{ $packagedAttraction->name }}" required>
                        <small class="text-muted">Package ID: {{ $packagedAttraction->package_attraction_id }}</small>
                        @error('package_attraction_name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">
                            <strong>Status</strong>
                        </label>
                        <select class="form-select" id="status" name="status">
                            <option value="1" {{ $packagedAttraction->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $packagedAttraction->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                @php
                    $selectedAttractions = json_decode($packagedAttraction->attractions, true) ?? [];
                    $selectedAttractionIds = array_map('strval', $selectedAttractions);
                    $attractionCountries = ($attractions ?? collect())->pluck('country')->filter()->unique()->sort()->values();
                    $filterCountry = old('country', $packagedAttraction->country ?? $dmcCountry ?? '');
                    $filterCity = old('city', $packagedAttraction->city ?? '');
                @endphp

                <div class="row">
                    <!-- Country (read-only on edit) -->
                    <div class="col-md-4 mb-3">
                        <label for="attractionCountryFilter" class="form-label">
                            <strong>Country</strong>
                        </label>
                        <input type="hidden" name="country" value="{{ $filterCountry }}">
                        <select class="form-select bg-light" id="attractionCountryFilter" disabled aria-readonly="true" tabindex="-1">
                            <option value="">All Countries</option>
                            @foreach($attractionCountries as $country)
                                <option value="{{ $country }}" {{ $filterCountry === $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Country cannot be changed when editing a package.</small>
                    </div>

                    <!-- City (read-only on edit) -->
                    <div class="col-md-4 mb-3">
                        <label for="attractionCityFilter" class="form-label">
                            <strong>City</strong>
                        </label>
                        <input type="hidden" name="city" value="{{ $filterCity }}">
                        <select class="form-select bg-light" id="attractionCityFilter" disabled aria-readonly="true" tabindex="-1">
                            <option value="">All Cities</option>
                        </select>
                        <small class="text-muted">City cannot be changed when editing a package.</small>
                    </div>
                </div>

                <div class="row">
                    <!-- Attractions Selection -->
                    <div class="col-md-12 mb-3">
                        <label for="attractionsSelect" class="form-label">
                            <strong>Select Attractions</strong><span class="text-danger">*</span>
                        </label>
                        <select name="attractions[]" id="attractionsSelect" class="form-select" multiple required>
                            <option value="">Select Attractions</option>
                            @foreach($attractions ?? [] as $attraction)
                                <option
                                    data-image="{{ $attraction->master_image }}"
                                    data-country="{{ $attraction->country }}"
                                    data-city="{{ $attraction->location }}"
                                    value="{{ $attraction->id }}"
                                    {{ in_array((string) $attraction->id, $selectedAttractionIds, true) ? 'selected' : '' }}
                                >
                                    {{ $attraction->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('attractions')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Attractions Preview -->
                <div class="attraction-preview mb-4" id="attractionPreview">
                    <h6 class="mb-3">Selected Attractions</h6>
                    <div id="selectedAttractions" class="selected-attractions">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-md-3 mb-2">
                        <label for="profit_type" class="form-label"><strong>Profit Type</strong></label>
                        <select id="profit_type" name="profit_type" class="form-select form-select-sm">
                            <option value="flat" {{ old('profit_type', 'flat') === 'flat' ? 'selected' : '' }}>Flat</option>
                            <option value="percentage" {{ old('profit_type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="profit_on_cost" class="form-label"><strong>Profit On Cost</strong></label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm" id="profit_on_cost" name="profit_on_cost" placeholder="0.00" value="{{ old('profit_on_cost') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="section-title">
                        <i class="ri-money-dollar-circle-line me-1"></i> Pricing
                        <small class="text-muted fw-normal">(Cost = attraction fee · Sell = customer pays · {{ $dmcCurrency }})</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm package-price-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:20%">Age</th>
                                    <th>Cost <span class="text-danger">*</span></th>
                                    <th>Sell <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-info-subtle text-info age-badge">Child</span></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm package-price-input package-cost-input" id="child_cost_price" name="child_cost_price" data-sell-target="child_price" placeholder="0.00" value="{{ old('child_cost_price', $packagedAttraction->child_cost_price) }}" required>
                                        @error('child_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm package-price-input package-sell-input" id="child_price" name="child_price" placeholder="0.00" value="{{ old('child_price', $packagedAttraction->child_price) }}" required>
                                        @error('child_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary-subtle text-primary age-badge">Adult</span></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm package-price-input package-cost-input" id="adult_cost_price" name="adult_cost_price" data-sell-target="adult_price" placeholder="0.00" value="{{ old('adult_cost_price', $packagedAttraction->adult_cost_price) }}" required>
                                        @error('adult_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm package-price-input package-sell-input" id="adult_price" name="adult_price" placeholder="0.00" value="{{ old('adult_price', $packagedAttraction->adult_price) }}" required>
                                        @error('adult_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary-subtle text-secondary age-badge">Senior</span></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm package-price-input package-cost-input" id="senior_citizen_cost_price" name="senior_citizen_cost_price" data-sell-target="senior_citizen_price" placeholder="0.00" value="{{ old('senior_citizen_cost_price', $packagedAttraction->senior_citizen_cost_price) }}" required>
                                        @error('senior_citizen_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm package-price-input package-sell-input" id="senior_citizen_price" name="senior_citizen_price" placeholder="0.00" value="{{ old('senior_citizen_price', $packagedAttraction->senior_citizen_price) }}" required>
                                        @error('senior_citizen_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="vehicle_included" name="vehicle_included" value="1"
                                   {{ old('vehicle_included', $packagedAttraction->vehicle_included) ? 'checked' : '' }}>
                            <label class="form-check-label" for="vehicle_included">
                                <strong>Vehicle Included</strong>
                            </label>
                        </div>
                        @error('vehicle_included')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="guide_included" name="guide_included" value="1"
                                   {{ old('guide_included', $packagedAttraction->guide_included) ? 'checked' : '' }}>
                            <label class="form-check-label" for="guide_included">
                                <strong>Guide Included</strong>
                            </label>
                        </div>
                        @error('guide_included')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Description -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="description" class="form-label">
                            <strong>Description</strong>
                        </label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="5" placeholder="Enter package description">{{ $packagedAttraction->description }}</textarea>
                        @error('description')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Existing Images -->
                @if($packagedAttraction->image)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">
                            <strong>Existing Images</strong>
                        </label>
                        <div class="existing-images">
                            @php
                                $images = json_decode($packagedAttraction->image, true) ?? [];
                                if(!is_array($images) && !empty($packagedAttraction->image)) {
                                    $images = [$packagedAttraction->image];
                                }
                            @endphp
                            
                            @foreach($images as $index => $image)
                                <div class="existing-image">
                                    <img src="{{ asset($image) }}" alt="Packaged Attraction Image">
                                    <input type="hidden" name="existing_images[]" value="{{ $image }}">
                                    <div class="remove-existing" data-image="{{ $image }}">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Image Upload -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="image" class="form-label">
                            <strong>Add New Images</strong>
                        </label>
                        <div class="input-group">
                            <label class="custom-file-upload">
                                <input type="file" id="image" name="image[]" class="d-none" multiple accept="image/*">
                                <i class="fas fa-cloud-upload-alt me-2"></i> Choose Images
                            </label>
                            <span id="file-chosen" class="ms-3 align-self-center text-muted">No files selected</span>
                        </div>
                        <small class="text-muted">You can select multiple images</small>
                        @error('image')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Image Preview -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="image-preview-container" id="imagePreviewContainer">
                            <!-- Image previews will be added here dynamically -->
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="row">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Packaged Attraction
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Include necessary JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script>
    $(document).ready(function() {
        function clampPackagePriceInput(el) {
            if (!el || el.value === '' || el.value === null) return;
            const n = parseFloat(String(el.value).replace(',', '.'));
            if (isNaN(n)) return;
            el.value = Number(n.toFixed(2));
        }

        function calculateSellFromCost(costValue) {
            const profitType = ($('#profit_type').val() || 'flat').toLowerCase();
            const profit = parseFloat(String($('#profit_on_cost').val() || '0').replace(',', '.'));
            const cost = parseFloat(String(costValue || '0').replace(',', '.'));

            if (isNaN(cost)) return '';

            const profitAmount = isNaN(profit) ? 0 : profit;
            let sell = cost;

            if (profitType === 'percentage') {
                sell = cost + (cost * profitAmount / 100);
            } else {
                sell = cost + profitAmount;
            }

            return Number(Math.max(0, sell).toFixed(2));
        }

        function updateSellFromCostInput(costInput) {
            if (!costInput) return;
            const sellId = costInput.getAttribute('data-sell-target');
            const sellInput = sellId ? document.getElementById(sellId) : null;
            if (!sellInput) return;

            if (costInput.value === '' || costInput.value === null) {
                return;
            }

            sellInput.value = calculateSellFromCost(costInput.value);
            sellInput.dataset.autoFilled = '1';
        }

        function updateAllSellPricesFromCost() {
            document.querySelectorAll('.package-cost-input').forEach(function (costInput) {
                updateSellFromCostInput(costInput);
            });
        }

        document.querySelectorAll('.package-price-input').forEach(function (el) {
            el.addEventListener('blur', function () { clampPackagePriceInput(this); });
            if (el.value !== '') {
                clampPackagePriceInput(el);
            }
        });

        document.querySelectorAll('.package-cost-input').forEach(function (costInput) {
            costInput.addEventListener('input', function () {
                updateSellFromCostInput(this);
            });
            costInput.addEventListener('change', function () {
                updateSellFromCostInput(this);
            });
        });

        document.querySelectorAll('.package-sell-input').forEach(function (sellInput) {
            sellInput.addEventListener('input', function () {
                this.dataset.autoFilled = '0';
            });
        });

        $('#profit_type, #profit_on_cost').on('input change', function () {
            updateAllSellPricesFromCost();
        });

        // Initialize Select2
        if (typeof $.fn.select2 !== 'undefined') {
            $("#attractionsSelect").select2({
                theme: 'bootstrap-5',
                placeholder: "Search and Select Attractions",
                allowClear: true,
                width: '100%'
            });
        } else {
            console.error("Select2 plugin is not available");
        }
        
        // Initialize Summernote
        if (typeof $.fn.summernote !== 'undefined') {
            $('#description').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        } else {
            console.error("Summernote plugin is not available");
        }
        
        const savedFilterCity = @json($filterCity);

        // Build attractions data map from blade
        const allAttractionOptions = [];
        $('#attractionsSelect option').each(function() {
            if ($(this).val()) {
                allAttractionOptions.push({
                    id: String($(this).val()),
                    name: $(this).text(),
                    image: $(this).data('image'),
                    country: $(this).data('country') || '',
                    city: $(this).data('city') || '',
                    element: $(this).clone()
                });
            }
        });

        function filterAttractions() {
            const selectedCountry = $('#attractionCountryFilter').val();
            const selectedCity = $('#attractionCityFilter').val();
            const currentSelected = ($('#attractionsSelect').val() || []).map(String);

            $('#attractionsSelect').empty();

            allAttractionOptions.forEach(function(opt) {
                const matchCountry = !selectedCountry || opt.country === selectedCountry;
                const matchCity = !selectedCity || opt.city === selectedCity;
                const keepSelected = currentSelected.includes(opt.id);
                if (keepSelected || (matchCountry && matchCity)) {
                    const el = opt.element.clone();
                    if (currentSelected.includes(opt.id)) {
                        el.prop('selected', true);
                    }
                    $('#attractionsSelect').append(el);
                }
            });

            $('#attractionsSelect').trigger('change');
            updateAttractionPreview();
        }

        function populateCities(country) {
            const selectedCity = $('#attractionCityFilter').val() || savedFilterCity;
            const cities = [...new Set(
                allAttractionOptions
                    .filter(o => !country || o.country === country)
                    .map(o => o.city)
                    .filter(c => c)
            )].sort();

            $('#attractionCityFilter').empty().append('<option value="">All Cities</option>');
            cities.forEach(function(city) {
                $('#attractionCityFilter').append($('<option>', {
                    value: city,
                    text: city,
                    selected: selectedCity === city
                }));
            });
        }

        populateCities($('#attractionCountryFilter').val());
        filterAttractions();

        // Handle attraction selection change
        $('#attractionsSelect').on('change', function() {
            updateAttractionPreview();
        });
        
        // Handle file input change for image preview
        $('#image').on('change', function(e) {
            const fileInput = e.target;
            const fileCount = fileInput.files.length;
            
            // Update file chosen text
            $('#file-chosen').text(fileCount > 0 ? `${fileCount} file(s) selected` : 'No files selected');
            
            // Clear previous previews
            $('#imagePreviewContainer').empty();
            
            // Create previews for each selected file
            if (fileCount > 0) {
                for (let i = 0; i < fileCount; i++) {
                    const file = fileInput.files[i];
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const preview = $(`
                            <div class="image-preview-item">
                                <img src="${e.target.result}" alt="Preview">
                                <div class="remove-image" data-index="${i}">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        `);
                        
                        $('#imagePreviewContainer').append(preview);
                    }
                    
                    reader.readAsDataURL(file);
                }
            }
        });
        
        // Handle removing images from preview
        $(document).on('click', '.remove-image', function() {
            const index = $(this).data('index');
            const container = $(this).closest('.image-preview-item');
            
            // Remove the preview
            container.remove();
            
            // Create a new FileList without the removed file
            // Note: FileList is immutable, so we need to recreate the input
            const input = document.getElementById('image');
            const dt = new DataTransfer();
            
            // Add all files except the one to be removed
            for (let i = 0; i < input.files.length; i++) {
                if (i !== index) {
                    dt.items.add(input.files[i]);
                }
            }
            
            // Update the input files
            input.files = dt.files;
            
            // Update file chosen text
            const fileCount = input.files.length;
            $('#file-chosen').text(fileCount > 0 ? `${fileCount} file(s) selected` : 'No files selected');
            
            // Reindex remaining remove buttons
            $('.remove-image').each(function(idx) {
                $(this).data('index', idx);
            });
        });
        
        // Handle removing existing images
        $(document).on('click', '.remove-existing', function() {
            const image = $(this).data('image');
            const container = $(this).closest('.existing-image');
            
            // Add hidden field to track removal
            $('<input>').attr({
                type: 'hidden',
                name: 'remove_images[]',
                value: image
            }).appendTo('#packagedAttractionForm');
            
            // Remove the preview
            container.remove();
        });
        
        // Form validation
        $('#packagedAttractionForm').on('submit', function(e) {
            const attractions = $('#attractionsSelect').val();
            if (!attractions || attractions.length === 0) {
                e.preventDefault();
                alert('Please select at least one attraction');
                return false;
            }
            
            return true;
        });
    });
    
    function updateAttractionPreview() {
        const selectedOptions = $('#attractionsSelect option:selected');
        const previewContainer = $('#selectedAttractions');
        
        // Clear previous content
        previewContainer.empty();
        
        if (selectedOptions.length === 0) {
            previewContainer.html('<div class="text-muted text-center py-3">No attractions selected</div>');
            return;
        }
        
        // Add each selected attraction to the preview
        selectedOptions.each(function() {
            const attractionId = $(this).val();
            const attractionName = $(this).text();
            const attractionImage = $(this).data('image');
            const previewItem = $(`
                <div class="attraction-preview-item" data-id="${attractionId}">
                    <img src="${attractionImage}" alt="${attractionName}">
                    <div class="attraction-info">
                        <h6 class="mb-1">${attractionName}</h6>
                        <p class="text-muted mb-0">ID: ${attractionId}</p>
                    </div>
                </div>
            `);
            
            previewContainer.append(previewItem);
        });
    }
</script>
@endsection 