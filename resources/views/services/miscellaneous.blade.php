@extends('layouts.layout')

@section('title', 'Miscellaneous Items')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .misc-page .card {
        border: none;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        border-radius: 0.5rem;
    }
    .misc-page .card-header {
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 0.75rem 1rem;
    }
    .misc-page .card-body { padding: 0.75rem 1rem; }
    .misc-hero {
        background: linear-gradient(135deg, #405189 0%, #5b6bb8 100%);
        color: #fff;
        border-radius: 0.5rem;
        padding: 0.85rem 1.1rem;
        margin-bottom: 1rem;
    }
    .misc-hero h5 { color: #fff; margin-bottom: 0.15rem; font-size: 1rem; }
    .misc-hero p { margin-bottom: 0; opacity: 0.9; font-size: 0.8rem; }
    .misc-item-thumb,
    .misc-item-thumb-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 0.3rem;
        flex-shrink: 0;
    }
    .misc-item-thumb { object-fit: cover; }
    .misc-item-thumb-placeholder {
        background: #f3f6f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .misc-item-name { font-size: 0.85rem; font-weight: 600; line-height: 1.2; }
    .misc-item-desc { font-size: 0.7rem; color: #6c757d; line-height: 1.2; }
    .misc-toolbar .form-select-sm,
    .misc-toolbar .form-control-sm {
        min-width: 90px;
        max-width: 110px;
    }
    .misc-item-card {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        background: #fff;
        overflow: hidden;
    }
    .misc-item-card:last-child { margin-bottom: 0; }
    .misc-item-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.65rem 0.85rem;
        background: #f8f9fb;
        border-bottom: 1px solid #eef1f5;
        cursor: pointer;
        user-select: none;
        position: relative;
    }
    .misc-item-card-header:hover { background: #f3f5f9; }
    .misc-item-card-header > .d-flex.min-w-0,
    .misc-item-card-header > .misc-card-title {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
    }
    .misc-card-actions {
        position: relative;
        z-index: 5;
        flex: 0 0 auto;
        pointer-events: auto;
    }
    .misc-card-actions .btn {
        pointer-events: auto;
        position: relative;
        z-index: 6;
    }
    .misc-item-card.collapsed .misc-item-card-body { display: none; }
    .misc-item-card.collapsed .misc-chevron { transform: rotate(-90deg); }
    .misc-chevron {
        transition: transform 0.15s ease;
        color: #6c757d;
        font-size: 1.1rem;
    }
    .misc-item-card-body { padding: 0.75rem 0.85rem; }
    .misc-locations-body {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        margin-bottom: 0.5rem;
    }
    .misc-location-row {
        border: 1px solid #eef1f5;
        border-radius: 0.45rem;
        padding: 0.65rem 0.75rem;
        background: #fafbfc;
    }
    .misc-loc-geo {
        display: grid;
        grid-template-columns: minmax(160px, 1fr) minmax(160px, 1fr) auto;
        gap: 0.5rem 0.75rem;
        align-items: end;
        margin-bottom: 0.65rem;
    }
    .misc-loc-geo .misc-field-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.2rem;
    }
    .misc-loc-prices {
        display: grid;
        grid-template-columns: repeat(3, minmax(120px, 1fr));
        gap: 0.5rem 0.75rem;
    }
    .misc-loc-select.form-select,
    .misc-loc-select.form-select-sm {
        display: block;
        width: 100%;
        min-width: 0;
        height: 32px;
        font-size: 0.8rem;
        line-height: 1.2;
        padding: 0.25rem 2rem 0.25rem 0.55rem;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.55rem center;
        background-size: 12px 10px;
        border: 1px solid #ced4da;
        border-radius: 0.35rem;
    }
    .misc-price-input.form-control-sm {
        font-size: 0.8rem;
        height: 32px;
        padding: 0.25rem 0.45rem;
        width: 100%;
    }
    .misc-price-pair {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.35rem;
        align-items: end;
    }
    .misc-price-pair label {
        display: block;
        font-size: 0.62rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.15rem;
        line-height: 1;
    }
    .misc-price-group-title {
        font-size: 0.68rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }
    .misc-item-card.misc-row-view .misc-price-input {
        background: #f8f9fa;
        pointer-events: none;
        border-color: #e9ecef;
    }
    .misc-item-card.misc-row-view .misc-loc-select {
        background-color: #f8f9fa;
        pointer-events: none;
        border-color: #e9ecef;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.55rem center;
        background-size: 12px 10px;
    }
    .misc-item-card.misc-row-edit .misc-price-input,
    .misc-item-card.misc-row-edit .misc-loc-select {
        background-color: #fff;
        pointer-events: auto;
    }
    .misc-item-card.misc-row-view .misc-loc-actions { visibility: hidden; }
    .misc-loc-actions .btn { height: 32px; }
    .available-item-row:hover { background: #f8f9fb; }
    .min-w-0 { min-width: 0; }
    .misc-item-meta { font-size: 0.7rem; color: #6c757d; }
    @media (max-width: 992px) {
        .misc-loc-geo { grid-template-columns: 1fr 1fr auto; }
        .misc-loc-prices { grid-template-columns: 1fr; }
    }
    @media (max-width: 576px) {
        .misc-loc-geo { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $dmcCurrency = \App\Helpers\CommonHelper::getDmcCurrencyByCountry();
    $selectedCount = isset($selectedItems) ? count($selectedItems) : 0;
    $availableCount = count($availableItems ?? []);
    $countryNames = $countryNames ?? [];
    $citiesByCountry = $citiesByCountry ?? [];
@endphp

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y misc-page">
        <x-alert />

        <div class="misc-hero">
            <h5 class="d-flex align-items-center flex-wrap gap-2 mb-1">
                Miscellaneous Items & Pricing
                <x-currency-price-note />
            </h5>
            <p>Select items, set cost & sell per country/city, then save. Same item can have different prices by city.</p>
        </div>

        <div class="card mb-4 {{ $selectedCount === 0 ? 'd-none' : '' }}" id="selectedItemsSection">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0" id="selectedItemsTitle">Your Selected Items ({{ $selectedCount }})</h5>
                        <small class="text-muted">Cost = fee · Sell = customer · {{ $dmcCurrency }}</small>
                    </div>
                    <div class="d-flex flex-wrap align-items-end gap-2 misc-toolbar">
                        <div>
                            <label for="profit_type" class="form-label mb-0 small"><strong>Profit</strong></label>
                            <select id="profit_type" class="form-select form-select-sm">
                                <option value="flat" selected>Flat</option>
                                <option value="percentage">%</option>
                            </select>
                        </div>
                        <div>
                            <label for="profit_on_cost" class="form-label mb-0 small"><strong>On Cost</strong></label>
                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm" id="profit_on_cost" placeholder="0.00">
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="saveAllPrices()">
                            <i class="ri-save-line me-1"></i>Save All
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="priceUpdateForm" action="{{ route('services.miscellaneous.update') }}" method="POST">
                    @csrf
                    <div id="selectedItemsBody">
                        @foreach(($selectedItems ?? []) as $item)
                            @php
                                $imageUrl = $item->image
                                    ? ((str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image))
                                    : '';
                                $locations = $item->locations ?? collect([(object)[
                                    'price_id' => '',
                                    'country' => $item->country ?? '',
                                    'city' => $item->city ?? '',
                                    'adult_price' => 0,
                                    'child_price' => 0,
                                    'infant_price' => 0,
                                    'adult_cost' => 0,
                                    'child_cost' => 0,
                                    'infant_cost' => 0,
                                ]]);
                            @endphp
                            <div class="misc-item-card misc-row-view"
                                 data-item-id="{{ $item->mis_id }}"
                                 data-display-name="{{ $item->item_name }}"
                                 data-description="{{ $item->description ?? '' }}"
                                 data-image-url="{{ $imageUrl }}"
                                 data-country="{{ $item->country ?? '' }}"
                                 data-city="{{ $item->city ?? '' }}"
                                 data-mode="view">
                                <div class="misc-item-card-header misc-toggle-card">
                                    <div class="d-flex align-items-center gap-2 min-w-0 misc-card-title">
                                        <i class="ri-arrow-down-s-line misc-chevron"></i>
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->item_name }}" class="misc-item-thumb">
                                        @else
                                            <div class="misc-item-thumb-placeholder">
                                                <i class="ri-add-box-line text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="misc-item-name">{{ $item->item_name }}</div>
                                            @if($item->description)
                                                <div class="misc-item-desc">{{ Str::limit($item->description, 50) }}</div>
                                            @endif
                                            <small class="text-muted misc-loc-count">{{ count($locations) }} location{{ count($locations) === 1 ? '' : 's' }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 misc-card-actions">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-item-btn" data-item-id="{{ $item->mis_id }}">
                                            <i class="ri-pencil-line"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary quick-save-btn d-none" data-item-id="{{ $item->mis_id }}">
                                            <i class="ri-check-line"></i> Save
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary cancel-edit-btn d-none" data-item-id="{{ $item->mis_id }}">
                                            <i class="ri-close-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="{{ $item->mis_id }}" title="Remove item">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="misc-item-card-body">
                                    <input type="hidden" name="selected_items[{{ $item->mis_id }}][selected]" value="1">
                                    <div class="misc-locations-body">
                                        @foreach($locations as $locIndex => $loc)
                                            @include('services.partials.misc-location-row', [
                                                'itemId' => $item->mis_id,
                                                'locIndex' => $locIndex,
                                                'loc' => $loc,
                                                'countryNames' => $countryNames,
                                                'citiesByCountry' => $citiesByCountry,
                                                'readonly' => true,
                                            ])
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-success add-location-btn d-none" data-item-id="{{ $item->mis_id }}">
                                        <i class="ri-add-line me-1"></i>Add location
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0" id="availableItemsTitle">Available Items ({{ $availableCount }})</h5>
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" class="form-control" id="availableItemSearch" placeholder="Search items...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="availableItemsTableWrapper" class="{{ $availableCount > 0 ? '' : 'd-none' }}">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:28%;">Item Name</th>
                                    <th style="width:18%;">Country</th>
                                    <th style="width:18%;">City</th>
                                    <th>Description</th>
                                    <th style="width:12%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="availableItemsBody">
                                @foreach($availableItems as $item)
                                    @php
                                        $itemImageUrl = $item->image
                                            ? ((str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image))
                                            : '';
                                    @endphp
                                    <tr class="available-item-row"
                                        data-item-id="{{ $item->mis_id }}"
                                        data-item-name="{{ strtolower($item->item_name) }}"
                                        data-display-name="{{ $item->item_name }}"
                                        data-description="{{ $item->description ?? '' }}"
                                        data-image-url="{{ $itemImageUrl }}"
                                        data-country="{{ $item->country ?? '' }}"
                                        data-city="{{ $item->city ?? '' }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($itemImageUrl)
                                                    <img src="{{ $itemImageUrl }}" alt="{{ $item->item_name }}" class="misc-item-thumb me-2">
                                                @else
                                                    <div class="misc-item-thumb-placeholder me-2">
                                                        <i class="ri-add-box-line text-muted"></i>
                                                    </div>
                                                @endif
                                                <strong>{{ $item->item_name }}</strong>
                                            </div>
                                        </td>
                                        <td class="misc-item-meta">{{ $item->country ?: '—' }}</td>
                                        <td class="misc-item-meta">{{ $item->city ?: '—' }}</td>
                                        <td class="text-muted">{{ Str::limit($item->description ?? 'N/A', 80) }}</td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-success add-item-btn"
                                                    data-item-id="{{ $item->mis_id }}"
                                                    data-item-name="{{ $item->item_name }}"
                                                    data-country="{{ $item->country ?? '' }}"
                                                    data-city="{{ $item->city ?? '' }}">
                                                <i class="ri-add-line me-1"></i>Add
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="availableItemsEmpty" class="alert alert-info mb-0 {{ $availableCount > 0 ? 'd-none' : '' }}">
                    <i class="ri-information-line me-2"></i>All available items have been selected.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const MISC_COUNTRIES = @json(array_values($countryNames ?? []));
    const MISC_CITIES_BY_COUNTRY = @json($citiesByCountry ?? []);

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function updateSelectedItemCount() {
        const count = document.querySelectorAll('#selectedItemsBody .misc-item-card[data-item-id]').length;
        const title = document.getElementById('selectedItemsTitle');
        const section = document.getElementById('selectedItemsSection');
        if (title) title.textContent = `Your Selected Items (${count})`;
        if (section) section.classList.toggle('d-none', count === 0);
    }

    function updateAvailableItemCount() {
        const count = document.querySelectorAll('#availableItemsBody .available-item-row').length;
        const title = document.getElementById('availableItemsTitle');
        const wrapper = document.getElementById('availableItemsTableWrapper');
        const empty = document.getElementById('availableItemsEmpty');
        if (title) title.textContent = `Available Items (${count})`;
        if (wrapper) wrapper.classList.toggle('d-none', count === 0);
        if (empty) empty.classList.toggle('d-none', count > 0);
    }

    function updateLocationCount(card) {
        const count = card.querySelectorAll('.misc-location-row').length;
        const el = card.querySelector('.misc-loc-count');
        if (el) el.textContent = count + ' location' + (count === 1 ? '' : 's');
    }

    function countryOptionsHtml(selected) {
        let html = '<option value="">Select country</option>';
        MISC_COUNTRIES.forEach(function (c) {
            html += `<option value="${escapeHtml(c)}" ${c === selected ? 'selected' : ''}>${escapeHtml(c)}</option>`;
        });
        return html;
    }

    function cityOptionsHtml(country, selected) {
        let html = '<option value="">Select city</option>';
        const cities = MISC_CITIES_BY_COUNTRY[country] || [];
        cities.forEach(function (city) {
            const name = city.name || city;
            html += `<option value="${escapeHtml(name)}" ${name === selected ? 'selected' : ''}>${escapeHtml(name)}</option>`;
        });
        if (selected && !cities.some(function (c) { return (c.name || c) === selected; })) {
            html += `<option value="${escapeHtml(selected)}" selected>${escapeHtml(selected)}</option>`;
        }
        return html;
    }

    function buildLocationRowHtml(itemId, locIndex, loc, readonly) {
        const priceId = loc?.price_id || '';
        const country = loc?.country || '';
        const city = loc?.city || '';
        const adultCost = loc?.adult_cost ?? 0;
        const adult = loc?.adult_price ?? 0;
        const childCost = loc?.child_cost ?? 0;
        const child = loc?.child_price ?? 0;
        const infantCost = loc?.infant_cost ?? 0;
        const infant = loc?.infant_price ?? 0;
        const ro = readonly ? 'readonly' : '';
        const dis = readonly ? 'disabled' : '';
        const prefix = `selected_items[${itemId}][locations][${locIndex}]`;

        return `
            <div class="misc-location-row" data-loc-index="${locIndex}" data-price-id="${escapeHtml(String(priceId))}">
                <input type="hidden" name="${prefix}[price_id]" value="${escapeHtml(String(priceId))}" class="misc-price-id">
                <div class="misc-loc-geo">
                    <div>
                        <label class="misc-field-label">Country</label>
                        <select class="form-select form-select-sm misc-loc-select misc-country-select" name="${prefix}[country]" data-no-select2="true" ${dis}>
                            ${countryOptionsHtml(country)}
                        </select>
                    </div>
                    <div>
                        <label class="misc-field-label">City</label>
                        <select class="form-select form-select-sm misc-loc-select misc-city-select" name="${prefix}[city]" data-no-select2="true" ${dis}>
                            ${cityOptionsHtml(country, city)}
                        </select>
                    </div>
                    <div class="misc-loc-actions d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-location-btn px-2" title="Remove location">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="misc-loc-prices">
                    <div>
                        <div class="misc-price-group-title">Adult</div>
                        <div class="misc-price-pair">
                            <div><label>Cost</label><input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="${prefix}[adult_cost]" data-sell-field="adult_price" value="${adultCost}" placeholder="0.00" ${ro}></div>
                            <div><label>Sell</label><input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="${prefix}[adult_price]" value="${adult}" placeholder="0.00" ${ro}></div>
                        </div>
                    </div>
                    <div>
                        <div class="misc-price-group-title">Child</div>
                        <div class="misc-price-pair">
                            <div><label>Cost</label><input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="${prefix}[child_cost]" data-sell-field="child_price" value="${childCost}" placeholder="0.00" ${ro}></div>
                            <div><label>Sell</label><input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="${prefix}[child_price]" value="${child}" placeholder="0.00" ${ro}></div>
                        </div>
                    </div>
                    <div>
                        <div class="misc-price-group-title">Infant</div>
                        <div class="misc-price-pair">
                            <div><label>Cost</label><input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="${prefix}[infant_cost]" data-sell-field="infant_price" value="${infantCost}" placeholder="0.00" ${ro}></div>
                            <div><label>Sell</label><input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="${prefix}[infant_price]" value="${infant}" placeholder="0.00" ${ro}></div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function reindexLocationRows(card) {
        const itemId = card.getAttribute('data-item-id');
        card.querySelectorAll('.misc-location-row').forEach(function (row, idx) {
            row.setAttribute('data-loc-index', idx);
            row.querySelectorAll('input, select').forEach(function (el) {
                if (!el.name) return;
                el.name = el.name.replace(
                    /selected_items\[\d+\]\[locations\]\[\d+\]/,
                    `selected_items[${itemId}][locations][${idx}]`
                );
            });
        });
        updateLocationCount(card);
    }

    function setCardMode(card, mode) {
        if (!card) return;
        const isEdit = mode === 'edit';
        card.setAttribute('data-mode', mode);
        card.classList.toggle('misc-row-edit', isEdit);
        card.classList.toggle('misc-row-view', !isEdit);
        card.classList.remove('collapsed');

        card.querySelectorAll('.misc-price-input').forEach(function (input) {
            input.readOnly = !isEdit;
        });
        card.querySelectorAll('.misc-loc-select').forEach(function (sel) {
            sel.disabled = !isEdit;
        });

        const editBtn = card.querySelector('.edit-item-btn');
        const saveBtn = card.querySelector('.quick-save-btn');
        const cancelBtn = card.querySelector('.cancel-edit-btn');
        const addLocBtn = card.querySelector('.add-location-btn');
        if (editBtn) editBtn.classList.toggle('d-none', isEdit);
        if (saveBtn) saveBtn.classList.toggle('d-none', !isEdit);
        if (cancelBtn) cancelBtn.classList.toggle('d-none', !isEdit);
        if (addLocBtn) addLocBtn.classList.toggle('d-none', !isEdit);
    }

    function snapshotCard(card) {
        card._snapshotHtml = card.querySelector('.misc-locations-body').innerHTML;
    }

    function restoreCard(card) {
        if (!card._snapshotHtml) return;
        card.querySelector('.misc-locations-body').innerHTML = card._snapshotHtml;
        updateLocationCount(card);
    }

    function buildSelectedItemCard(itemRow, locations, mode) {
        const itemId = itemRow.getAttribute('data-item-id');
        const name = itemRow.getAttribute('data-display-name') || '';
        const description = itemRow.getAttribute('data-description') || '';
        const imageUrl = itemRow.getAttribute('data-image-url') || '';
        const country = itemRow.getAttribute('data-country') || '';
        const city = itemRow.getAttribute('data-city') || '';
        const isEdit = mode === 'edit';
        const locs = (locations && locations.length) ? locations : [{
            price_id: '', country: country, city: city,
            adult_price: 0, child_price: 0, infant_price: 0,
            adult_cost: 0, child_cost: 0, infant_cost: 0
        }];

        const imageHtml = imageUrl
            ? `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(name)}" class="misc-item-thumb">`
            : `<div class="misc-item-thumb-placeholder"><i class="ri-add-box-line text-muted"></i></div>`;
        const descHtml = description
            ? `<div class="misc-item-desc">${escapeHtml(description.length > 50 ? description.substring(0, 50) + '...' : description)}</div>`
            : '';

        let locRows = '';
        locs.forEach(function (loc, idx) {
            locRows += buildLocationRowHtml(itemId, idx, loc, !isEdit);
        });

        const card = document.createElement('div');
        card.className = `misc-item-card ${isEdit ? 'misc-row-edit' : 'misc-row-view'}`;
        card.setAttribute('data-item-id', itemId);
        card.setAttribute('data-display-name', name);
        card.setAttribute('data-description', description);
        card.setAttribute('data-image-url', imageUrl);
        card.setAttribute('data-country', country);
        card.setAttribute('data-city', city);
        card.setAttribute('data-mode', mode);
        card.innerHTML = `
            <div class="misc-item-card-header misc-toggle-card">
                <div class="d-flex align-items-center gap-2 min-w-0 misc-card-title">
                    <i class="ri-arrow-down-s-line misc-chevron"></i>
                    ${imageHtml}
                    <div class="min-w-0">
                        <div class="misc-item-name">${escapeHtml(name)}</div>
                        ${descHtml}
                        <small class="text-muted misc-loc-count">${locs.length} location${locs.length === 1 ? '' : 's'}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1 misc-card-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary edit-item-btn ${isEdit ? 'd-none' : ''}" data-item-id="${escapeHtml(itemId)}"><i class="ri-pencil-line"></i> Edit</button>
                    <button type="button" class="btn btn-sm btn-primary quick-save-btn ${isEdit ? '' : 'd-none'}" data-item-id="${escapeHtml(itemId)}"><i class="ri-check-line"></i> Save</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary cancel-edit-btn ${isEdit ? '' : 'd-none'}" data-item-id="${escapeHtml(itemId)}"><i class="ri-close-line"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="${escapeHtml(itemId)}" title="Remove item"><i class="ri-delete-bin-line"></i></button>
                </div>
            </div>
            <div class="misc-item-card-body">
                <input type="hidden" name="selected_items[${escapeHtml(itemId)}][selected]" value="1">
                <div class="misc-locations-body">${locRows}</div>
                <button type="button" class="btn btn-sm btn-outline-success add-location-btn ${isEdit ? '' : 'd-none'}" data-item-id="${escapeHtml(itemId)}">
                    <i class="ri-add-line me-1"></i>Add location
                </button>
            </div>`;
        return card;
    }

    function addItemToSelected(itemRow, locations) {
        const itemId = itemRow.getAttribute('data-item-id');
        if (document.querySelector(`#selectedItemsBody .misc-item-card[data-item-id="${itemId}"]`)) return;
        const body = document.getElementById('selectedItemsBody');
        if (!body) return;
        const card = buildSelectedItemCard(itemRow, locations, 'edit');
        body.insertBefore(card, body.firstChild);
        snapshotCard(card);
        itemRow.remove();
        updateSelectedItemCount();
        updateAvailableItemCount();
    }

    function restoreItemToAvailableTable(itemRowData) {
        const tbody = document.getElementById('availableItemsBody');
        if (!tbody) return;
        const row = document.createElement('tr');
        row.className = 'available-item-row';
        row.setAttribute('data-item-id', itemRowData.itemId);
        row.setAttribute('data-item-name', (itemRowData.name || '').toLowerCase());
        row.setAttribute('data-display-name', itemRowData.name || '');
        row.setAttribute('data-description', itemRowData.description || '');
        row.setAttribute('data-image-url', itemRowData.imageUrl || '');
        row.setAttribute('data-country', itemRowData.country || '');
        row.setAttribute('data-city', itemRowData.city || '');
        const imageHtml = itemRowData.imageUrl
            ? `<img src="${escapeHtml(itemRowData.imageUrl)}" alt="${escapeHtml(itemRowData.name)}" class="misc-item-thumb me-2">`
            : `<div class="misc-item-thumb-placeholder me-2"><i class="ri-add-box-line text-muted"></i></div>`;
        const desc = itemRowData.description || 'N/A';
        const descDisplay = desc.length > 80 ? desc.substring(0, 80) + '...' : desc;
        row.innerHTML = `
            <td><div class="d-flex align-items-center">${imageHtml}<strong>${escapeHtml(itemRowData.name)}</strong></div></td>
            <td class="misc-item-meta">${escapeHtml(itemRowData.country || '—')}</td>
            <td class="misc-item-meta">${escapeHtml(itemRowData.city || '—')}</td>
            <td class="text-muted">${escapeHtml(descDisplay)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-success add-item-btn"
                    data-item-id="${escapeHtml(itemRowData.itemId)}"
                    data-item-name="${escapeHtml(itemRowData.name)}"
                    data-country="${escapeHtml(itemRowData.country || '')}"
                    data-city="${escapeHtml(itemRowData.city || '')}">
                    <i class="ri-add-line me-1"></i>Add
                </button>
            </td>`;
        tbody.appendChild(row);
        updateAvailableItemCount();
    }

    function calculateSellFromCost(costValue) {
        const profitType = ($('#profit_type').val() || 'flat').toLowerCase();
        const profit = parseFloat(String($('#profit_on_cost').val() || '0').replace(',', '.'));
        const cost = parseFloat(String(costValue || '0').replace(',', '.'));
        if (isNaN(cost)) return '';
        const profitAmount = isNaN(profit) ? 0 : profit;
        let sell = cost;
        if (profitType === 'percentage') sell = cost + (cost * profitAmount / 100);
        else sell = cost + profitAmount;
        return Number(Math.max(0, sell).toFixed(2));
    }

    function updateSellFromCostInput(costInput) {
        if (!costInput) return;
        const card = costInput.closest('.misc-item-card');
        if (!card || card.getAttribute('data-mode') !== 'edit') return;
        const row = costInput.closest('.misc-location-row');
        const field = costInput.getAttribute('data-sell-field');
        if (!row || !field) return;
        const sellInput = row.querySelector(`input[name$="[${field}]"]`);
        if (!sellInput || costInput.value === '' || costInput.value === null) return;
        sellInput.value = calculateSellFromCost(costInput.value);
    }

    function collectCardLocations(card) {
        const locations = [];
        if (!card) return locations;
        card.querySelectorAll('.misc-location-row').forEach(function (row) {
            const countrySelect = row.querySelector('.misc-country-select');
            const citySelect = row.querySelector('.misc-city-select');
            const countryWasDisabled = countrySelect ? countrySelect.disabled : false;
            const cityWasDisabled = citySelect ? citySelect.disabled : false;
            if (countrySelect) countrySelect.disabled = false;
            if (citySelect) citySelect.disabled = false;
            locations.push({
                price_id: row.querySelector('.misc-price-id')?.value || '',
                country: (countrySelect?.value || '').trim(),
                city: (citySelect?.value || '').trim(),
                adult_cost: row.querySelector('input[name$="[adult_cost]"]')?.value || 0,
                adult_price: row.querySelector('input[name$="[adult_price]"]')?.value || 0,
                child_cost: row.querySelector('input[name$="[child_cost]"]')?.value || 0,
                child_price: row.querySelector('input[name$="[child_price]"]')?.value || 0,
                infant_cost: row.querySelector('input[name$="[infant_cost]"]')?.value || 0,
                infant_price: row.querySelector('input[name$="[infant_price]"]')?.value || 0,
            });
            if (countrySelect) countrySelect.disabled = countryWasDisabled;
            if (citySelect) citySelect.disabled = cityWasDisabled;
        });
        return locations;
    }

    function applySavedLocations(card, locations) {
        const itemId = card.getAttribute('data-item-id');
        const body = card.querySelector('.misc-locations-body');
        let html = '';
        (locations || []).forEach(function (loc, idx) {
            html += buildLocationRowHtml(itemId, idx, loc, true);
        });
        if (!html) {
            html = buildLocationRowHtml(itemId, 0, {
                price_id: '', country: card.getAttribute('data-country') || '', city: card.getAttribute('data-city') || '',
                adult_price: 0, child_price: 0, infant_price: 0, adult_cost: 0, child_cost: 0, infant_cost: 0
            }, true);
        }
        body.innerHTML = html;
        updateLocationCount(card);
        snapshotCard(card);
    }

    document.querySelectorAll('#selectedItemsBody .misc-item-card').forEach(function (card) {
        snapshotCard(card);
    });

    // Native delegation so Edit/Save/Cancel always work (no jQuery dependency)
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.edit-item-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            const card = editBtn.closest('.misc-item-card');
            if (!card) return;
            snapshotCard(card);
            setCardMode(card, 'edit');
            return;
        }

        const cancelBtn = e.target.closest('.cancel-edit-btn');
        if (cancelBtn) {
            e.preventDefault();
            e.stopPropagation();
            const card = cancelBtn.closest('.misc-item-card');
            if (!card) return;
            restoreCard(card);
            setCardMode(card, 'view');
            return;
        }

        const addLocBtn = e.target.closest('.add-location-btn');
        if (addLocBtn) {
            e.preventDefault();
            e.stopPropagation();
            const card = addLocBtn.closest('.misc-item-card');
            if (!card) return;
            const itemId = card.getAttribute('data-item-id');
            const body = card.querySelector('.misc-locations-body');
            const idx = body.querySelectorAll('.misc-location-row').length;
            body.insertAdjacentHTML('beforeend', buildLocationRowHtml(itemId, idx, {
                price_id: '', country: '', city: '',
                adult_price: 0, child_price: 0, infant_price: 0,
                adult_cost: 0, child_cost: 0, infant_cost: 0
            }, false));
            updateLocationCount(card);
            return;
        }

        const toggleHeader = e.target.closest('.misc-toggle-card');
        if (toggleHeader && !e.target.closest('.misc-card-actions, button, a')) {
            toggleHeader.closest('.misc-item-card').classList.toggle('collapsed');
        }
    });

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('misc-country-select')) return;
        const row = e.target.closest('.misc-location-row');
        const citySelect = row && row.querySelector('.misc-city-select');
        if (citySelect) citySelect.innerHTML = cityOptionsHtml(e.target.value, '');
    });

    const $ = window.jQuery;
    if (!$) {
        console.error('jQuery is required for miscellaneous AJAX actions.');
        return;
    }

    $(document).on('click', '.remove-location-btn', function () {
        const card = this.closest('.misc-item-card');
        const row = this.closest('.misc-location-row');
        const priceId = row.getAttribute('data-price-id');
        const rows = card.querySelectorAll('.misc-location-row');

        if (rows.length <= 1) {
            Swal.fire({ icon: 'info', title: 'Keep one row', text: 'Use Remove item to delete the whole miscellaneous item.', timer: 2200, showConfirmButton: false });
            return;
        }

        const doRemove = function () {
            row.remove();
            reindexLocationRows(card);
        };

        if (!priceId) {
            doRemove();
            return;
        }

        $.ajax({
            url: '{{ route("services.miscellaneous.remove") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', price_id: priceId },
            success: function (response) {
                if (response.success) doRemove();
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to remove location.' });
            }
        });
    });

    $(document).on('input change', '.misc-cost-input', function () {
        updateSellFromCostInput(this);
    });

    $('#profit_type, #profit_on_cost').on('input change', function () {
        document.querySelectorAll('#selectedItemsBody .misc-item-card.misc-row-edit .misc-cost-input').forEach(function (costInput) {
            updateSellFromCostInput(costInput);
        });
    });

    $(document).on('click', '.add-item-btn', function (e) {
        e.preventDefault();
        const button = $(this);
        const itemId = button.data('item-id');
        const itemName = button.data('item-name');
        const country = button.attr('data-country') || '';
        const city = button.attr('data-city') || '';

        if (!country || !city) {
            Swal.fire({
                icon: 'warning',
                title: 'Country & City missing',
                text: 'This item has no country/city. Set them in Product Settings → Miscellaneous Items first.'
            });
            return;
        }

        button.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Adding...');

        $.ajax({
            url: '{{ route("services.miscellaneous.select") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                item_id: itemId,
                country: country,
                city: city,
                adult_price: 0,
                child_price: 0,
                infant_price: 0,
                adult_cost: 0,
                child_cost: 0,
                infant_cost: 0
            },
            success: function (response) {
                if (response.success) {
                    const itemRow = button.closest('.available-item-row').get(0);
                    if (itemRow) {
                        addItemToSelected(itemRow, response.locations || [response.prices || {
                            country: country, city: city,
                            adult_price: 0, child_price: 0, infant_price: 0,
                            adult_cost: 0, child_cost: 0, infant_cost: 0
                        }]);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Added',
                        text: itemName + ' added for ' + city + '. Set cost & sell, then Save.',
                        timer: 2200,
                        showConfirmButton: false
                    });
                } else {
                    button.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Add');
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Failed to add item.' });
                }
            },
            error: function (xhr) {
                button.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Add');
                const errorMessage = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Failed to add item. Please try again.';
                Swal.fire({ icon: 'error', title: 'Error', text: errorMessage });
            }
        });
    });

    $(document).on('click', '.remove-item-btn', function () {
        const button = $(this);
        const itemId = button.data('item-id');

        Swal.fire({
            title: 'Remove Item?',
            text: 'All country/city prices for this item will be removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove'
        }).then((result) => {
            if (!result.isConfirmed) return;
            button.prop('disabled', true);

            $.ajax({
                url: '{{ route("services.miscellaneous.remove") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', item_id: itemId },
                success: function (response) {
                    if (response.success) {
                        const selectedCard = document.querySelector(`#selectedItemsBody .misc-item-card[data-item-id="${itemId}"]`);
                        let itemData = { itemId: String(itemId), name: '', description: '', imageUrl: '', country: '', city: '' };
                        if (selectedCard) {
                            itemData = {
                                itemId: String(itemId),
                                name: selectedCard.getAttribute('data-display-name') || '',
                                description: selectedCard.getAttribute('data-description') || '',
                                imageUrl: selectedCard.getAttribute('data-image-url') || '',
                                country: selectedCard.getAttribute('data-country') || '',
                                city: selectedCard.getAttribute('data-city') || '',
                            };
                            selectedCard.remove();
                            updateSelectedItemCount();
                        }
                        restoreItemToAvailableTable(itemData);
                        Swal.fire({ icon: 'success', title: 'Removed', text: response.message || 'Item removed.', timer: 1800, showConfirmButton: false });
                    } else {
                        button.prop('disabled', false);
                    }
                },
                error: function () {
                    button.prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to remove item.' });
                }
            });
        });
    });

    $(document).on('click', '.quick-save-btn', function () {
        const button = $(this);
        const card = button.closest('.misc-item-card')[0];
        const itemId = button.data('item-id');
        const locations = collectCardLocations(card);

        if (!locations.length) {
            Swal.fire({ icon: 'warning', title: 'Nothing to save', text: 'Add at least one location row.' });
            return;
        }

        const incomplete = locations.find(function (l) { return !l.country || !l.city; });
        if (incomplete) {
            Swal.fire({ icon: 'warning', title: 'Country & City required', text: 'Please select country and city for every location row before saving.' });
            return;
        }

        const pairs = {};
        for (const loc of locations) {
            const key = (loc.country + '|' + loc.city).toLowerCase();
            if (pairs[key]) {
                Swal.fire({ icon: 'warning', title: 'Duplicate location', text: 'Each country/city combination can only appear once per item.' });
                return;
            }
            pairs[key] = true;
        }

        button.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i>');

        $.ajax({
            url: '{{ route("services.miscellaneous.select") }}',
            type: 'POST',
            dataType: 'json',
            data: {
                _token: '{{ csrf_token() }}',
                item_id: itemId,
                locations_json: JSON.stringify(locations)
            },
            success: function (response) {
                button.prop('disabled', false).html('<i class="ri-check-line"></i> Save');
                if (response.success) {
                    applySavedLocations(card, response.locations || locations);
                    setCardMode(card, 'view');
                    Swal.fire({ icon: 'success', title: 'Saved', text: 'Location prices saved.', timer: 1600, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Failed to save prices.' });
                }
            },
            error: function (xhr) {
                button.prop('disabled', false).html('<i class="ri-check-line"></i> Save');
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to save prices.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });

    window.saveAllPrices = function saveAllPrices() {
        let invalid = false;
        document.querySelectorAll('#selectedItemsBody .misc-item-card').forEach(function (card) {
            collectCardLocations(card).forEach(function (loc) {
                if (!loc.country || !loc.city) invalid = true;
            });
            card.querySelectorAll('.misc-price-input').forEach(function (input) { input.readOnly = false; });
            card.querySelectorAll('.misc-loc-select').forEach(function (sel) { sel.disabled = false; });
        });

        if (invalid) {
            Swal.fire({ icon: 'warning', title: 'Country & City required', text: 'Every location row needs a country and city before Save All.' });
            return;
        }

        Swal.fire({
            title: 'Saving...',
            text: 'Please wait while we save all prices.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        $('#priceUpdateForm').submit();
    };

    $('#availableItemSearch').on('keyup', function () {
        const searchTerm = $(this).val().toLowerCase();
        $('.available-item-row').each(function () {
            const itemName = $(this).data('item-name') || $(this).attr('data-item-name') || '';
            const country = ($(this).attr('data-country') || '').toLowerCase();
            const city = ($(this).attr('data-city') || '').toLowerCase();
            $(this).toggle(itemName.includes(searchTerm) || country.includes(searchTerm) || city.includes(searchTerm));
        });
    });
})();
</script>
@endpush
