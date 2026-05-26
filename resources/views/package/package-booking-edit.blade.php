@extends('layouts.layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">Edit Package Booking</h4>
            <small class="text-muted">Booking ID: <strong>{{ $booking->booking_id }}</strong></small>
        </div>
        @php
            $returnUrl = request()->get('return_url');
            $backUrl = !empty($returnUrl) ? $returnUrl : route('package.booking.details', $booking->booking_id);
        @endphp
        <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>Back to details
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('package.booking.update', $booking->booking_id) }}" method="POST" id="editBookingForm">
        @csrf

        {{-- Booking summary (read-only) --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Package</label>
                        <div class="fw-semibold">
                            {{ data_get($booking->package, 'title') ?: data_get($booking->package, 'package_id') }}
                        </div>
                        <div class="text-muted small">
                            {{ data_get($booking->package, 'destination') }} — {{ data_get($booking->package, 'city') }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Travel Dates</label>
                        <div>
                            {{ $travelDates['start_date'] ?? '—' }} → {{ $travelDates['end_date'] ?? '—' }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Pax</label>
                        <div class="fw-semibold">{{ $totalPax }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Current Final Price</label>
                        <div class="fw-semibold text-primary">
                            SGD {{ number_format((float)($priceData['final_price'] ?? 0), 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Booked services (read-only) --}}
        <div class="card mb-3">
            <div class="card-header py-2 bg-light d-flex align-items-center justify-content-between">
                <strong><i class="ri-lock-2-line me-1 text-muted"></i>Booked Services (read-only)</strong>
                <span class="text-muted small">{{ (count($booked['hotels']) + count($booked['attractions']) + count($booked['restaurants'])) }} services</span>
            </div>
            <div class="card-body">
                @php
                    $sections = [
                        ['label' => 'Hotels',       'icon' => 'ri-hotel-line',      'items' => $booked['hotels'],      'nameKeys' => ['hotel_name','name']],
                        ['label' => 'Attractions',  'icon' => 'ri-map-pin-line',    'items' => $booked['attractions'], 'nameKeys' => ['name','attraction_name']],
                        ['label' => 'Restaurants',  'icon' => 'ri-restaurant-line', 'items' => $booked['restaurants'], 'nameKeys' => ['restaurant_name','name']],
                    ];
                @endphp
                @foreach($sections as $section)
                    <div class="mb-3">
                        <h6 class="mb-2"><i class="{{ $section['icon'] }} me-1"></i>{{ $section['label'] }}</h6>
                        @if(empty($section['items']))
                            <div class="text-muted small">No booked {{ strtolower($section['label']) }}.</div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($section['items'] as $item)
                                    @php
                                        $name = '';
                                        foreach ($section['nameKeys'] as $k) { if (!empty($item[$k])) { $name = $item[$k]; break; } }
                                        if ($name === '') { $name = $section['label']; }
                                        $itemType = $item['compulsory'] ?? false ? 'Compulsory' : (($item['addon'] ?? false) ? 'Add-on' : (($item['optional'] ?? false) ? 'Optional' : 'Booked'));
                                        $itemPrice = $item['total_price'] ?? $item['base_price'] ?? null;
                                    @endphp
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <span class="fw-semibold">{{ $name }}</span>
                                            <span class="badge bg-light text-dark ms-2">{{ $itemType }}</span>
                                        </div>
                                        @if($itemPrice !== null && is_numeric($itemPrice))
                                            <span class="text-muted small">SGD {{ number_format((float) $itemPrice, 2) }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Available add-ons (selectable) --}}
        <div class="card mb-3">
            <div class="card-header py-2 bg-light d-flex align-items-center justify-content-between">
                <strong><i class="ri-add-circle-line me-1 text-success"></i>Available Add-ons</strong>
                <span class="text-muted small">Tick to add to this booking</span>
            </div>
            <div class="card-body" id="availableAddonsBody">
                @php
                    $sectionsAvail = [
                        ['label' => 'Hotels',      'section' => 'hotels',      'icon' => 'ri-hotel-line',      'items' => $availableAddons['hotels'],      'idKey' => 'hotel_id',      'nameKeys' => ['hotel_name','name']],
                        ['label' => 'Attractions', 'section' => 'attractions', 'icon' => 'ri-map-pin-line',    'items' => $availableAddons['attractions'], 'idKey' => 'attraction_id', 'nameKeys' => ['name','attraction_name']],
                        ['label' => 'Restaurants', 'section' => 'restaurants', 'icon' => 'ri-restaurant-line', 'items' => $availableAddons['restaurants'], 'idKey' => 'restaurant_id', 'nameKeys' => ['restaurant_name','name']],
                    ];

                    $totalAvail = count($availableAddons['hotels']) + count($availableAddons['attractions']) + count($availableAddons['restaurants']);
                @endphp

                @if($totalAvail === 0)
                    <div class="text-muted small">No available add-ons left — everything was booked at the time of creation.</div>
                @else
                    @foreach($sectionsAvail as $s)
                        <div class="mb-3">
                            <h6 class="mb-2"><i class="{{ $s['icon'] }} me-1"></i>{{ $s['label'] }}</h6>
                            @if(empty($s['items']))
                                <div class="text-muted small">No {{ strtolower($s['label']) }} add-ons available.</div>
                            @else
                                <div class="list-group">
                                    @foreach($s['items'] as $item)
                                        @php
                                            $rawId = (string) ($item[$s['idKey']] ?? $item['id'] ?? '');
                                            $name = '';
                                            foreach ($s['nameKeys'] as $k) { if (!empty($item[$k])) { $name = $item[$k]; break; } }
                                            if ($name === '') { $name = $s['label']; }
                                            $finalPrice = isset($item['final_price']) && is_numeric($item['final_price']) ? (float) $item['final_price'] : null;
                                            $basePrice = isset($item['base_price']) && is_numeric($item['base_price']) ? (float) $item['base_price'] : 0;
                                            $guidePrice = is_array($item['guide'] ?? null) && isset($item['guide']['price']) ? (float) $item['guide']['price'] : 0;
                                            $transferPrice = isset($item['transfer_price']) && is_numeric($item['transfer_price']) ? (float) $item['transfer_price'] : 0;
                                            if ($s['section'] === 'attractions') {
                                                $perPax = $finalPrice !== null && $finalPrice > 0 ? $finalPrice : ($basePrice + $guidePrice + $transferPrice);
                                            } elseif ($s['section'] === 'restaurants') {
                                                $perPax = $finalPrice !== null && $finalPrice > 0 ? $finalPrice : ($basePrice + $transferPrice);
                                            } else {
                                                $perPax = 0;
                                            }
                                            $storedTotal = isset($item['total_price']) && is_numeric($item['total_price']) ? (float) $item['total_price'] : 0;
                                            $perUnit = $s['section'] === 'hotels' ? ($storedTotal > 0 ? $storedTotal : $basePrice) : $perPax;
                                            $checkboxId = 'addon-' . $s['section'] . '-' . md5($rawId . '-' . $name);
                                        @endphp
                                        <label class="list-group-item d-flex align-items-center gap-3 addon-row" for="{{ $checkboxId }}">
                                            <input type="checkbox"
                                                class="form-check-input addon-checkbox"
                                                id="{{ $checkboxId }}"
                                                data-section="{{ $s['section'] }}"
                                                data-id="{{ $rawId }}"
                                                data-unit-price="{{ $perUnit }}"
                                                data-section-type="{{ $s['section'] }}">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $name }}</div>
                                                <div class="text-muted small">
                                                    @if($s['section'] === 'hotels')
                                                        Flat price: SGD {{ number_format($perUnit, 2) }}
                                                    @else
                                                        Per pax: SGD {{ number_format($perPax, 2) }}
                                                    @endif
                                                </div>
                                            </div>
                                            @if($s['section'] !== 'hotels')
                                                <div style="max-width: 120px;">
                                                    <label class="form-label small mb-0">Pax</label>
                                                    <input type="number" min="1" step="1" value="{{ $totalPax }}"
                                                        class="form-control form-control-sm addon-pax"
                                                        data-section="{{ $s['section'] }}"
                                                        data-id="{{ $rawId }}"
                                                        disabled>
                                                </div>
                                            @endif
                                            <div class="text-end" style="min-width: 140px;">
                                                <div class="small text-muted">Line total</div>
                                                <div class="fw-semibold addon-line-total" data-section="{{ $s['section'] }}" data-id="{{ $rawId }}">
                                                    SGD 0.00
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Pricing preview --}}
        <div class="card mb-3">
            <div class="card-header py-2 bg-light">
                <strong><i class="ri-money-dollar-circle-line me-1"></i>Pricing Preview</strong>
            </div>
            <div class="card-body">
                @php
                    $currentTotal = (float) ($priceData['total_price'] ?? 0);
                    $currentFinal = (float) ($priceData['final_price'] ?? 0);
                    $markupType = $priceData['markup_type'] ?? 'flat';
                    $markupAmount = (float) ($priceData['markup_amount'] ?? 0);
                @endphp
                <div class="row small g-2">
                    <div class="col-md-3">
                        <div class="text-muted">Current Total</div>
                        <div class="fw-semibold">SGD {{ number_format($currentTotal, 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">Add-ons Delta</div>
                        <div class="fw-semibold text-success" id="addonDelta">SGD 0.00</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">New Total (before markup)</div>
                        <div class="fw-semibold" id="newTotal">SGD {{ number_format($currentTotal, 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted">New Final Price ({{ $markupType }} {{ $markupAmount }}{{ $markupType === 'percentage' ? '%' : '' }})</div>
                        <div class="fw-bold text-primary" id="newFinal">SGD {{ number_format($currentFinal, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small">Internal Notes (optional)</label>
            <textarea class="form-control" name="notes" rows="2">{{ $bookingDetails['notes'] ?? '' }}</textarea>
        </div>

        <input type="hidden" name="selected_addons" id="selected_addons_input" value="{}">

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('package.booking.details', $booking->booking_id) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                <i class="ri-save-line me-1"></i>Save changes
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const currentTotal = {{ (float) ($priceData['total_price'] ?? 0) }};
    const markupType = @json(strtolower((string) ($priceData['markup_type'] ?? 'flat')));
    const markupAmount = {{ (float) ($priceData['markup_amount'] ?? 0) }};

    const checkboxes = document.querySelectorAll('.addon-checkbox');
    const paxInputs = document.querySelectorAll('.addon-pax');
    const deltaEl = document.getElementById('addonDelta');
    const newTotalEl = document.getElementById('newTotal');
    const newFinalEl = document.getElementById('newFinal');
    const hiddenInput = document.getElementById('selected_addons_input');
    const saveBtn = document.getElementById('saveBtn');

    function ceilToFive(n) {
        const num = parseFloat(n) || 0;
        if (num <= 0) return 0;
        return Math.ceil(num / 5) * 5;
    }
    function money(n) {
        return 'SGD ' + (parseFloat(n) || 0).toFixed(2);
    }

    function findPaxInput(section, id) {
        return document.querySelector(`.addon-pax[data-section="${section}"][data-id="${CSS.escape(String(id))}"]`);
    }
    function findLineTotalEl(section, id) {
        return document.querySelector(`.addon-line-total[data-section="${section}"][data-id="${CSS.escape(String(id))}"]`);
    }

    function recompute() {
        let delta = 0;
        const selection = { hotels: [], attractions: [], restaurants: [], pax: {} };

        checkboxes.forEach(cb => {
            const section = cb.dataset.section;
            const id = cb.dataset.id;
            const unit = parseFloat(cb.dataset.unitPrice) || 0;
            const paxInput = findPaxInput(section, id);
            const lineEl = findLineTotalEl(section, id);

            if (!cb.checked) {
                if (paxInput) paxInput.disabled = true;
                if (lineEl) lineEl.textContent = money(0);
                return;
            }

            if (paxInput) paxInput.disabled = false;

            const pax = paxInput ? (parseInt(paxInput.value, 10) || 0) : 1;
            const line = section === 'hotels' ? unit : unit * pax;

            if (lineEl) lineEl.textContent = money(line);

            delta += line;

            selection[section] = selection[section] || [];
            selection[section].push(id);
            if (section !== 'hotels') {
                selection.pax[section.replace(/s$/, '') + ':' + id] = pax;
            }
        });

        deltaEl.textContent = money(delta);
        const newTotalRaw = currentTotal + delta;
        const newTotal = ceilToFive(newTotalRaw);
        let newFinal = newTotal;
        if (markupAmount > 0) {
            newFinal = markupType === 'percentage'
                ? newTotal + (newTotal * markupAmount / 100)
                : newTotal + markupAmount;
        }
        newFinal = ceilToFive(newFinal);

        newTotalEl.textContent = money(newTotal);
        newFinalEl.textContent = money(newFinal);

        hiddenInput.value = JSON.stringify(selection);

        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        saveBtn.disabled = !anyChecked;
    }

    checkboxes.forEach(cb => cb.addEventListener('change', recompute));
    paxInputs.forEach(inp => inp.addEventListener('input', recompute));
    recompute();
})();
</script>
@endsection
