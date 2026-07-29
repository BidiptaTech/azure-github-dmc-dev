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
    .misc-price-table {
        font-size: 0.75rem;
        margin-bottom: 0;
        table-layout: fixed;
        width: 100%;
    }
    .misc-price-table th,
    .misc-price-table td {
        padding: 0.35rem 0.4rem;
        vertical-align: middle;
    }
    .misc-price-table thead th {
        font-size: 0.7rem;
        font-weight: 600;
        background: #f8f9fa;
        white-space: nowrap;
    }
    .misc-price-pair {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.25rem;
        align-items: end;
    }
    .misc-price-pair label {
        display: block;
        font-size: 0.65rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.1rem;
        line-height: 1;
    }
    .misc-price-table .form-control-sm {
        font-size: 0.75rem;
        padding: 0.2rem 0.35rem;
        height: calc(1.5em + 0.4rem + 2px);
        min-width: 0;
        width: 100%;
    }
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
    .misc-item-name {
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.2;
        word-break: break-word;
    }
    .misc-item-desc {
        font-size: 0.68rem;
        color: #6c757d;
        line-height: 1.2;
    }
    .misc-row-view .misc-price-input {
        background: #f8f9fa;
        pointer-events: none;
        border-color: #e9ecef;
    }
    .misc-row-edit .misc-price-input {
        background: #fff;
        pointer-events: auto;
    }
    .misc-hero {
        background: linear-gradient(135deg, #405189 0%, #5b6bb8 100%);
        color: #fff;
        border-radius: 0.5rem;
        padding: 0.85rem 1.1rem;
        margin-bottom: 1rem;
    }
    .misc-hero h5 {
        color: #fff;
        margin-bottom: 0.15rem;
        font-size: 1rem;
    }
    .misc-hero p {
        margin-bottom: 0;
        opacity: 0.9;
        font-size: 0.8rem;
    }
    .available-item-row:hover { background: #f8f9fb; }
    .misc-actions {
        white-space: nowrap;
    }
    .misc-actions .btn-group {
        display: inline-flex;
    }
    .misc-actions .btn {
        padding: 0.2rem 0.45rem;
        font-size: 0.72rem;
        line-height: 1.2;
    }
    .misc-actions .btn i { font-size: 0.85rem; }
    .misc-col-remove { width: 36px; }
    .misc-col-item { width: 20%; }
    .misc-col-price { width: 21%; }
    .misc-col-actions { width: 110px; }
    .misc-toolbar .form-select-sm,
    .misc-toolbar .form-control-sm {
        min-width: 90px;
        max-width: 110px;
    }
    .min-w-0 { min-width: 0; }
</style>
@endpush

@section('content')
@php
    $dmcCurrency = \App\Helpers\CommonHelper::getDmcCurrencyByCountry();
    $selectedCount = isset($selectedItems) ? count($selectedItems) : 0;
    $availableCount = count($availableItems ?? []);
@endphp

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y misc-page">
        <x-alert />

        <div class="misc-hero">
            <h5 class="d-flex align-items-center flex-wrap gap-2 mb-1">
                Miscellaneous Items & Pricing
                <x-currency-price-note />
            </h5>
            <p>Add items, set cost & sell, then save. Use Edit to update later.</p>
        </div>

        <!-- Selected Items -->
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
                    <table class="table table-bordered table-sm misc-price-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center misc-col-remove"></th>
                                <th class="misc-col-item">Item</th>
                                <th class="text-center misc-col-price">Adult</th>
                                <th class="text-center misc-col-price">Child</th>
                                <th class="text-center misc-col-price">Infant</th>
                                <th class="text-center misc-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="selectedItemsBody">
                            @foreach(($selectedItems ?? []) as $item)
                                @php
                                    $imageUrl = $item->image
                                        ? ((str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image))
                                        : '';
                                @endphp
                                <tr class="misc-selected-row misc-row-view"
                                    data-item-id="{{ $item->mis_id }}"
                                    data-display-name="{{ $item->item_name }}"
                                    data-description="{{ $item->description ?? '' }}"
                                    data-image-url="{{ $imageUrl }}"
                                    data-mode="view">
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-item-btn px-1"
                                                data-item-id="{{ $item->mis_id }}"
                                                title="Remove Item">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
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
                                                    <div class="misc-item-desc">{{ Str::limit($item->description, 40) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="hidden" name="selected_items[{{ $item->mis_id }}][selected]" value="1">
                                    </td>
                                    <td>
                                        <div class="misc-price-pair">
                                            <div>
                                                <label>Cost</label>
                                                <input type="number" step="0.01" min="0" inputmode="decimal"
                                                       class="form-control form-control-sm misc-price-input misc-cost-input"
                                                       name="selected_items[{{ $item->mis_id }}][adult_cost]"
                                                       data-sell-target="selected_items[{{ $item->mis_id }}][adult_price]"
                                                       value="{{ $item->adult_cost ?? 0 }}" placeholder="0.00" readonly>
                                            </div>
                                            <div>
                                                <label>Sell</label>
                                                <input type="number" step="0.01" min="0" inputmode="decimal"
                                                       class="form-control form-control-sm misc-price-input misc-sell-input"
                                                       name="selected_items[{{ $item->mis_id }}][adult_price]"
                                                       value="{{ $item->adult_price ?? 0 }}" placeholder="0.00" readonly>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="misc-price-pair">
                                            <div>
                                                <label>Cost</label>
                                                <input type="number" step="0.01" min="0" inputmode="decimal"
                                                       class="form-control form-control-sm misc-price-input misc-cost-input"
                                                       name="selected_items[{{ $item->mis_id }}][child_cost]"
                                                       data-sell-target="selected_items[{{ $item->mis_id }}][child_price]"
                                                       value="{{ $item->child_cost ?? 0 }}" placeholder="0.00" readonly>
                                            </div>
                                            <div>
                                                <label>Sell</label>
                                                <input type="number" step="0.01" min="0" inputmode="decimal"
                                                       class="form-control form-control-sm misc-price-input misc-sell-input"
                                                       name="selected_items[{{ $item->mis_id }}][child_price]"
                                                       value="{{ $item->child_price ?? 0 }}" placeholder="0.00" readonly>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="misc-price-pair">
                                            <div>
                                                <label>Cost</label>
                                                <input type="number" step="0.01" min="0" inputmode="decimal"
                                                       class="form-control form-control-sm misc-price-input misc-cost-input"
                                                       name="selected_items[{{ $item->mis_id }}][infant_cost]"
                                                       data-sell-target="selected_items[{{ $item->mis_id }}][infant_price]"
                                                       value="{{ $item->infant_cost ?? 0 }}" placeholder="0.00" readonly>
                                            </div>
                                            <div>
                                                <label>Sell</label>
                                                <input type="number" step="0.01" min="0" inputmode="decimal"
                                                       class="form-control form-control-sm misc-price-input misc-sell-input"
                                                       name="selected_items[{{ $item->mis_id }}][infant_price]"
                                                       value="{{ $item->infant_price ?? 0 }}" placeholder="0.00" readonly>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center misc-actions">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary edit-item-btn" data-item-id="{{ $item->mis_id }}" title="Edit">
                                                <i class="ri-pencil-line"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-primary quick-save-btn d-none" data-item-id="{{ $item->mis_id }}" title="Save">
                                                <i class="ri-check-line"></i> Save
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary cancel-edit-btn d-none" data-item-id="{{ $item->mis_id }}" title="Cancel">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <!-- Available Items -->
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
                                    <th style="width:30%;">Item Name</th>
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
                                        data-image-url="{{ $itemImageUrl }}">
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
                                        <td class="text-muted">{{ Str::limit($item->description ?? 'N/A', 100) }}</td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-success add-item-btn"
                                                    data-item-id="{{ $item->mis_id }}"
                                                    data-item-name="{{ $item->item_name }}">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function updateSelectedItemCount() {
        const count = document.querySelectorAll('#selectedItemsBody tr[data-item-id]').length;
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

    function setRowMode(row, mode) {
        if (!row) return;
        const isEdit = mode === 'edit';
        row.setAttribute('data-mode', mode);
        row.classList.toggle('misc-row-edit', isEdit);
        row.classList.toggle('misc-row-view', !isEdit);

        row.querySelectorAll('.misc-price-input').forEach(function (input) {
            input.readOnly = !isEdit;
        });

        const editBtn = row.querySelector('.edit-item-btn');
        const saveBtn = row.querySelector('.quick-save-btn');
        const cancelBtn = row.querySelector('.cancel-edit-btn');
        if (editBtn) editBtn.classList.toggle('d-none', isEdit);
        if (saveBtn) saveBtn.classList.toggle('d-none', !isEdit);
        if (cancelBtn) cancelBtn.classList.toggle('d-none', !isEdit);
    }

    function snapshotRowPrices(row) {
        const snapshot = {};
        row.querySelectorAll('.misc-price-input').forEach(function (input) {
            snapshot[input.name] = input.value;
        });
        row._priceSnapshot = snapshot;
    }

    function restoreRowPrices(row) {
        if (!row._priceSnapshot) return;
        Object.keys(row._priceSnapshot).forEach(function (name) {
            const input = row.querySelector('input[name="' + name.replace(/"/g, '\\"') + '"]');
            if (input) input.value = row._priceSnapshot[name];
        });
    }

    function buildSelectedItemRow(itemRow, prices, mode) {
        const itemId = itemRow.getAttribute('data-item-id');
        const name = itemRow.getAttribute('data-display-name') || '';
        const description = itemRow.getAttribute('data-description') || '';
        const imageUrl = itemRow.getAttribute('data-image-url') || '';
        const adultCost = prices?.adult_cost ?? 0;
        const adult = prices?.adult_price ?? 0;
        const childCost = prices?.child_cost ?? 0;
        const child = prices?.child_price ?? 0;
        const infantCost = prices?.infant_cost ?? 0;
        const infant = prices?.infant_price ?? 0;
        const isEdit = mode === 'edit';
        const readonlyAttr = isEdit ? '' : 'readonly';
        const imageHtml = imageUrl
            ? `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(name)}" class="misc-item-thumb">`
            : `<div class="misc-item-thumb-placeholder"><i class="ri-add-box-line text-muted"></i></div>`;
        const descHtml = description
            ? `<div class="misc-item-desc">${escapeHtml(description.length > 40 ? description.substring(0, 40) + '...' : description)}</div>`
            : '';

        const row = document.createElement('tr');
        row.className = `misc-selected-row ${isEdit ? 'misc-row-edit' : 'misc-row-view'}`;
        row.setAttribute('data-item-id', itemId);
        row.setAttribute('data-display-name', name);
        row.setAttribute('data-description', description);
        row.setAttribute('data-image-url', imageUrl);
        row.setAttribute('data-mode', mode);
        row.innerHTML = `
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn px-1" data-item-id="${escapeHtml(itemId)}" title="Remove Item">
                    <i class="ri-close-line"></i>
                </button>
            </td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    ${imageHtml}
                    <div class="min-w-0">
                        <div class="misc-item-name">${escapeHtml(name)}</div>
                        ${descHtml}
                    </div>
                </div>
                <input type="hidden" name="selected_items[${escapeHtml(itemId)}][selected]" value="1">
            </td>
            <td>
                <div class="misc-price-pair">
                    <div>
                        <label>Cost</label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="selected_items[${escapeHtml(itemId)}][adult_cost]" data-sell-target="selected_items[${escapeHtml(itemId)}][adult_price]" value="${adultCost}" placeholder="0.00" ${readonlyAttr}>
                    </div>
                    <div>
                        <label>Sell</label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="selected_items[${escapeHtml(itemId)}][adult_price]" value="${adult}" placeholder="0.00" ${readonlyAttr}>
                    </div>
                </div>
            </td>
            <td>
                <div class="misc-price-pair">
                    <div>
                        <label>Cost</label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="selected_items[${escapeHtml(itemId)}][child_cost]" data-sell-target="selected_items[${escapeHtml(itemId)}][child_price]" value="${childCost}" placeholder="0.00" ${readonlyAttr}>
                    </div>
                    <div>
                        <label>Sell</label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="selected_items[${escapeHtml(itemId)}][child_price]" value="${child}" placeholder="0.00" ${readonlyAttr}>
                    </div>
                </div>
            </td>
            <td>
                <div class="misc-price-pair">
                    <div>
                        <label>Cost</label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="selected_items[${escapeHtml(itemId)}][infant_cost]" data-sell-target="selected_items[${escapeHtml(itemId)}][infant_price]" value="${infantCost}" placeholder="0.00" ${readonlyAttr}>
                    </div>
                    <div>
                        <label>Sell</label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="selected_items[${escapeHtml(itemId)}][infant_price]" value="${infant}" placeholder="0.00" ${readonlyAttr}>
                    </div>
                </div>
            </td>
            <td class="text-center misc-actions">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary edit-item-btn ${isEdit ? 'd-none' : ''}" data-item-id="${escapeHtml(itemId)}" title="Edit">
                        <i class="ri-pencil-line"></i> Edit
                    </button>
                    <button type="button" class="btn btn-primary quick-save-btn ${isEdit ? '' : 'd-none'}" data-item-id="${escapeHtml(itemId)}" title="Save">
                        <i class="ri-check-line"></i> Save
                    </button>
                    <button type="button" class="btn btn-outline-secondary cancel-edit-btn ${isEdit ? '' : 'd-none'}" data-item-id="${escapeHtml(itemId)}" title="Cancel">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </td>`;
        return row;
    }

    function addItemToSelectedTable(itemRow, prices) {
        const itemId = itemRow.getAttribute('data-item-id');
        if (document.querySelector(`#selectedItemsBody tr[data-item-id="${itemId}"]`)) return;
        const tbody = document.getElementById('selectedItemsBody');
        if (!tbody) return;
        const row = buildSelectedItemRow(itemRow, prices, 'edit');
        tbody.insertBefore(row, tbody.firstChild);
        snapshotRowPrices(row);
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
        const imageHtml = itemRowData.imageUrl
            ? `<img src="${escapeHtml(itemRowData.imageUrl)}" alt="${escapeHtml(itemRowData.name)}" class="misc-item-thumb me-2">`
            : `<div class="misc-item-thumb-placeholder me-2"><i class="ri-add-box-line text-muted"></i></div>`;
        const desc = itemRowData.description || 'N/A';
        const descDisplay = desc.length > 100 ? desc.substring(0, 100) + '...' : desc;

        row.innerHTML = `
            <td><div class="d-flex align-items-center">${imageHtml}<strong>${escapeHtml(itemRowData.name)}</strong></div></td>
            <td class="text-muted">${escapeHtml(descDisplay)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-success add-item-btn" data-item-id="${escapeHtml(itemRowData.itemId)}" data-item-name="${escapeHtml(itemRowData.name)}">
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
        if (profitType === 'percentage') {
            sell = cost + (cost * profitAmount / 100);
        } else {
            sell = cost + profitAmount;
        }
        return Number(Math.max(0, sell).toFixed(2));
    }

    function updateSellFromCostInput(costInput) {
        if (!costInput) return;
        const row = costInput.closest('tr');
        if (!row || row.getAttribute('data-mode') !== 'edit') return;
        const sellName = costInput.getAttribute('data-sell-target');
        if (!sellName) return;
        const sellInput = row.querySelector('input[name="' + sellName.replace(/"/g, '\\"') + '"]');
        if (!sellInput || costInput.value === '' || costInput.value === null) return;
        sellInput.value = calculateSellFromCost(costInput.value);
    }

    function getRowPricePayload(row, itemId) {
        return {
            _token: '{{ csrf_token() }}',
            item_id: itemId,
            adult_cost: row.find(`input[name="selected_items[${itemId}][adult_cost]"]`).val() || 0,
            adult_price: row.find(`input[name="selected_items[${itemId}][adult_price]"]`).val() || 0,
            child_cost: row.find(`input[name="selected_items[${itemId}][child_cost]"]`).val() || 0,
            child_price: row.find(`input[name="selected_items[${itemId}][child_price]"]`).val() || 0,
            infant_cost: row.find(`input[name="selected_items[${itemId}][infant_cost]"]`).val() || 0,
            infant_price: row.find(`input[name="selected_items[${itemId}][infant_price]"]`).val() || 0
        };
    }

    // Snapshot existing rows on load
    document.querySelectorAll('#selectedItemsBody tr[data-item-id]').forEach(function (row) {
        snapshotRowPrices(row);
    });

    $(document).on('click', '.edit-item-btn', function () {
        const row = this.closest('tr');
        snapshotRowPrices(row);
        setRowMode(row, 'edit');
        const firstCost = row.querySelector('.misc-cost-input');
        if (firstCost) firstCost.focus();
    });

    $(document).on('click', '.cancel-edit-btn', function () {
        const row = this.closest('tr');
        restoreRowPrices(row);
        setRowMode(row, 'view');
    });

    $(document).on('input change', '.misc-cost-input', function () {
        updateSellFromCostInput(this);
    });

    $('#profit_type, #profit_on_cost').on('input change', function () {
        document.querySelectorAll('#selectedItemsBody tr.misc-row-edit .misc-cost-input').forEach(function (costInput) {
            updateSellFromCostInput(costInput);
        });
    });

    // Add Item
    $(document).on('click', '.add-item-btn', function (e) {
        e.preventDefault();
        const button = $(this);
        const itemId = button.data('item-id');
        const itemName = button.data('item-name');

        button.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Adding...');

        $.ajax({
            url: '{{ route("services.miscellaneous.select") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                item_id: itemId,
                preserve_existing_prices: 1,
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
                        addItemToSelectedTable(itemRow, response.prices || {
                            adult_price: 0, child_price: 0, infant_price: 0,
                            adult_cost: 0, child_cost: 0, infant_cost: 0
                        });
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Added',
                        text: response.action === 'restored'
                            ? itemName + ' restored with previous prices. Review and save if needed.'
                            : itemName + ' added. Set cost & sell prices, then Save.',
                        timer: 2200,
                        showConfirmButton: false
                    });
                } else {
                    button.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Add');
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

    // Remove Item
    $(document).on('click', '.remove-item-btn', function () {
        const button = $(this);
        const itemId = button.data('item-id');

        Swal.fire({
            title: 'Remove Item?',
            text: 'This item will be moved back to Available Items.',
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
                        const selectedRow = document.querySelector(`#selectedItemsBody tr[data-item-id="${itemId}"]`);
                        let itemData = { itemId: String(itemId), name: '', description: '', imageUrl: '' };
                        if (selectedRow) {
                            itemData = {
                                itemId: String(itemId),
                                name: selectedRow.getAttribute('data-display-name') || '',
                                description: selectedRow.getAttribute('data-description') || '',
                                imageUrl: selectedRow.getAttribute('data-image-url') || '',
                            };
                            selectedRow.remove();
                            updateSelectedItemCount();
                        }
                        restoreItemToAvailableTable(itemData);
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed',
                            text: response.message || 'Item has been removed.',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    } else {
                        button.prop('disabled', false);
                    }
                },
                error: function () {
                    button.prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to remove item. Please try again.' });
                }
            });
        });
    });

    // Quick Save Single Item -> then switch to view/edit mode
    $(document).on('click', '.quick-save-btn', function () {
        const button = $(this);
        const rowEl = button.closest('tr');
        const row = $(rowEl);
        const itemId = button.data('item-id');
        const data = getRowPricePayload(row, itemId);

        button.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i>');

        $.ajax({
            url: '{{ route("services.miscellaneous.select") }}',
            type: 'POST',
            data: data,
            success: function (response) {
                button.prop('disabled', false).html('<i class="ri-check-line"></i> Save');
                if (response.success) {
                    if (response.prices) {
                        row.find(`input[name="selected_items[${itemId}][adult_cost]"]`).val(response.prices.adult_cost ?? 0);
                        row.find(`input[name="selected_items[${itemId}][adult_price]"]`).val(response.prices.adult_price ?? 0);
                        row.find(`input[name="selected_items[${itemId}][child_cost]"]`).val(response.prices.child_cost ?? 0);
                        row.find(`input[name="selected_items[${itemId}][child_price]"]`).val(response.prices.child_price ?? 0);
                        row.find(`input[name="selected_items[${itemId}][infant_cost]"]`).val(response.prices.infant_cost ?? 0);
                        row.find(`input[name="selected_items[${itemId}][infant_price]"]`).val(response.prices.infant_price ?? 0);
                    }
                    snapshotRowPrices(rowEl);
                    setRowMode(rowEl, 'view');
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Prices saved. Click Edit to update them again.',
                        timer: 1600,
                        showConfirmButton: false
                    });
                }
            },
            error: function () {
                button.prop('disabled', false).html('<i class="ri-check-line"></i> Save');
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save prices. Please try again.' });
            }
        });
    });

    function saveAllPrices() {
        // Unlock all inputs so values submit, then post
        document.querySelectorAll('#selectedItemsBody .misc-price-input').forEach(function (input) {
            input.readOnly = false;
        });
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait while we save all prices.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        $('#priceUpdateForm').submit();
    }

    $('#availableItemSearch').on('keyup', function () {
        const searchTerm = $(this).val().toLowerCase();
        $('.available-item-row').each(function () {
            const itemName = $(this).data('item-name') || $(this).attr('data-item-name') || '';
            $(this).toggle(itemName.includes(searchTerm));
        });
    });
</script>
@endpush

@endsection
