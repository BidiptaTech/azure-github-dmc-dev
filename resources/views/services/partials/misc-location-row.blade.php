@php
    $locIndex = $locIndex ?? 0;
    $itemId = $itemId ?? 0;
    $loc = $loc ?? (object)[];
    $readonly = $readonly ?? true;
    $countryNames = $countryNames ?? [];
    $citiesByCountry = $citiesByCountry ?? [];
    $priceId = $loc->price_id ?? '';
    $country = $loc->country ?? '';
    $city = $loc->city ?? '';
    $prefix = "selected_items[{$itemId}][locations][{$locIndex}]";
    $cityOptions = $citiesByCountry[$country] ?? [];
@endphp
<div class="misc-location-row" data-loc-index="{{ $locIndex }}" data-price-id="{{ $priceId }}">
    <input type="hidden" name="{{ $prefix }}[price_id]" value="{{ $priceId }}" class="misc-price-id">
    <div class="misc-loc-geo">
        <div>
            <label class="misc-field-label">Country</label>
            <select class="form-select form-select-sm misc-loc-select misc-country-select"
                    name="{{ $prefix }}[country]" data-no-select2="true" {{ $readonly ? 'disabled' : '' }}>
                <option value="">Select country</option>
                @foreach($countryNames as $cName)
                    <option value="{{ $cName }}" {{ $country === $cName ? 'selected' : '' }}>{{ $cName }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="misc-field-label">City</label>
            <select class="form-select form-select-sm misc-loc-select misc-city-select"
                    name="{{ $prefix }}[city]" data-no-select2="true" {{ $readonly ? 'disabled' : '' }}>
                <option value="">Select city</option>
                @foreach($cityOptions as $cityOpt)
                    @php $cityName = is_array($cityOpt) ? ($cityOpt['name'] ?? '') : $cityOpt; @endphp
                    <option value="{{ $cityName }}" {{ $city === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                @endforeach
                @if($city !== '' && !collect($cityOptions)->contains(function ($opt) use ($city) {
                    return (is_array($opt) ? ($opt['name'] ?? '') : $opt) === $city;
                }))
                    <option value="{{ $city }}" selected>{{ $city }}</option>
                @endif
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
                <div>
                    <label>Cost</label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="{{ $prefix }}[adult_cost]" data-sell-field="adult_price" value="{{ $loc->adult_cost ?? 0 }}" placeholder="0.00" {{ $readonly ? 'readonly' : '' }}>
                </div>
                <div>
                    <label>Sell</label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="{{ $prefix }}[adult_price]" value="{{ $loc->adult_price ?? 0 }}" placeholder="0.00" {{ $readonly ? 'readonly' : '' }}>
                </div>
            </div>
        </div>
        <div>
            <div class="misc-price-group-title">Child</div>
            <div class="misc-price-pair">
                <div>
                    <label>Cost</label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="{{ $prefix }}[child_cost]" data-sell-field="child_price" value="{{ $loc->child_cost ?? 0 }}" placeholder="0.00" {{ $readonly ? 'readonly' : '' }}>
                </div>
                <div>
                    <label>Sell</label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="{{ $prefix }}[child_price]" value="{{ $loc->child_price ?? 0 }}" placeholder="0.00" {{ $readonly ? 'readonly' : '' }}>
                </div>
            </div>
        </div>
        <div>
            <div class="misc-price-group-title">Infant</div>
            <div class="misc-price-pair">
                <div>
                    <label>Cost</label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-cost-input" name="{{ $prefix }}[infant_cost]" data-sell-field="infant_price" value="{{ $loc->infant_cost ?? 0 }}" placeholder="0.00" {{ $readonly ? 'readonly' : '' }}>
                </div>
                <div>
                    <label>Sell</label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm misc-price-input misc-sell-input" name="{{ $prefix }}[infant_price]" value="{{ $loc->infant_price ?? 0 }}" placeholder="0.00" {{ $readonly ? 'readonly' : '' }}>
                </div>
            </div>
        </div>
    </div>
</div>
