@php
    $pickerOptions = $pickerOptions ?? collect();
    $pickerSelected = collect($pickerSelected ?? [])->map(fn ($id) => (int) $id);
    $pickerHint = $pickerHint ?? '';
@endphp

<div class="dmc-picker">
    <div class="dmc-assigned-preview dmc-badge-wrap" id="assignedDmcPreview"></div>

    <div class="dmc-combo mt-2">
        <div class="input-group">
            <span class="input-group-text"><i class="ri-search-line"></i></span>
            <input type="text" class="form-control" id="dmcComboSearch" placeholder="Search and add DMC..." autocomplete="off">
            <button type="button" class="btn btn-outline-secondary" id="dmcComboToggle" title="Show DMCs">
                <i class="ri-arrow-down-s-line"></i>
            </button>
        </div>
        <div class="dmc-combo-dropdown d-none" id="dmcComboDropdown"></div>
    </div>

    <select name="dmc_id[]" id="dmc_id" class="d-none" multiple>
        @foreach($pickerOptions as $dmc)
            <option value="{{ $dmc->userId }}" @selected($pickerSelected->contains((int) $dmc->userId))>
                {{ $dmc->company_name ?: $dmc->name }}{{ $dmc->country ? ' (' . $dmc->country . ')' : '' }}
            </option>
        @endforeach
    </select>

    <div class="form-text mt-1">
        {{ $pickerHint }}
        <span id="dmcSlotHint"></span>
    </div>
</div>
