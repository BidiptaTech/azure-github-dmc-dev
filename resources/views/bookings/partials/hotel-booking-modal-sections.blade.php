{{-- Shared hotel booking display: Pro vs Lite extra_bed + rooms[] details --}}
@php
    $professional = !empty($professional);
    $hotelNightsModal = (int) ($booking['nights'] ?? 0);
    if ($hotelNightsModal <= 0 && isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1) {
        $checkInModal = \Carbon\Carbon::parse($booking['bookingDate'][0]);
        $checkOutModal = \Carbon\Carbon::parse(end($booking['bookingDate']));
        $hotelNightsModal = $checkInModal->diffInDays($checkOutModal);
    }
    $hotelNightsModal = max(0, (int) $hotelNightsModal);

    $extraBedModal = is_array($booking['extra_bed'] ?? null) ? $booking['extra_bed'] : [];
    $extraBedIsPro = isset($extraBedModal['enabled']) && $extraBedModal['enabled'];
    $extraBedIsLite = !$extraBedIsPro && (
        isset($extraBedModal['price_per_night'])
        || isset($extraBedModal['extra_persons'])
        || isset($extraBedModal['rooms'])
    );
    $extraBedShowModal = $extraBedIsPro || ($extraBedIsLite && (
        (float)($extraBedModal['total_cost'] ?? 0) > 0
        || (float)($extraBedModal['price_per_night'] ?? 0) > 0
    ));

    $hasCwbModal = isset($booking['child_with_bed']['enabled']) && $booking['child_with_bed']['enabled'];
    $hasCnbModal = isset($booking['child_without_bed']['enabled']) && $booking['child_without_bed']['enabled'];
    $showAddonsSection = $hasCwbModal || $hasCnbModal || $extraBedShowModal;
@endphp

@if(isset($booking['rooms']) && is_array($booking['rooms']) && count($booking['rooms']) > 0)
    @if($professional)
        <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
            <p class="svc-section-title">Room &amp; Accommodation</p>
            <div class="svc-dl">
                @foreach($booking['rooms'] as $roomIndex => $room)
                    @php
                        $numberOfRooms = max(1, (int)($room['number_of_rooms'] ?? 1));
                        $roomOccupancy = $room['occupancy'] ?? null;
                        $roomSelectedPersons = isset($room['selected_persons']) ? (int)$room['selected_persons'] : null;
                        $totalRoomPrice = 0;
                        if (isset($room['beds']) && is_array($room['beds'])) {
                            $totalRoomPrice = collect($room['beds'])->sum(fn ($b) => (float)($b['price'] ?? 0));
                        }
                        $nightsLabel = $hotelNightsModal > 0 ? ($hotelNightsModal . ' night' . ($hotelNightsModal === 1 ? '' : 's')) : 'nights TBD';
                        $roomMeta = $numberOfRooms . ' Room' . ($numberOfRooms > 1 ? 's' : '') . ' × ' . $nightsLabel;
                        if ($roomOccupancy) {
                            $roomMeta .= ' • ' . ucfirst($roomOccupancy);
                        }
                        if ($roomSelectedPersons !== null) {
                            $roomMeta .= ' • ' . $roomSelectedPersons . ' guest' . ($roomSelectedPersons > 1 ? 's' : '');
                        }
                    @endphp
                    <div class="svc-dl-row">
                        <span class="svc-dl-label">Room {{ $roomIndex + 1 }}</span>
                        <span class="svc-dl-value">{{ $room['room_type'] ?? 'Standard Room' }}</span>
                    </div>
                    <div class="svc-dl-row">
                        <span class="svc-dl-label">Qty × Nights</span>
                        <span class="svc-dl-value">{{ $roomMeta }}</span>
                    </div>
                    @if(isset($room['beds']) && is_array($room['beds']))
                        @foreach($room['beds'] as $bed)
                            @php
                                $mealPaxLabel = $roomSelectedPersons !== null ? $roomSelectedPersons : ($bed['head_count'] ?? 0);
                                $bedUnit = (float) ($bed['price'] ?? 0);
                                $meal1 = null;
                                if (isset($bed['selectedMeals']) && is_array($bed['selectedMeals'])) {
                                    if (isset($bed['selectedMeals']['meal_1']) && is_array($bed['selectedMeals']['meal_1'])) {
                                        $meal1 = $bed['selectedMeals']['meal_1'];
                                    } else {
                                        $firstMeal = reset($bed['selectedMeals']);
                                        $meal1 = is_array($firstMeal) ? $firstMeal : null;
                                    }
                                }
                                $lodgingTotal = (float) ($meal1['totals']['lodging'] ?? 0);
                                $mealsTotalFromPayload = (float) ($meal1['totals']['meals'] ?? $meal1['price'] ?? 0);
                                // Lodging = per-night room rate × rooms × nights (from enquiry payload when available)
                                $lodgingCalc = ($hotelNightsModal > 0 && $bedUnit > 0)
                                    ? ($bedUnit * $numberOfRooms * $hotelNightsModal)
                                    : 0;
                                $displayLodging = $lodgingTotal > 0 ? $lodgingTotal : $lodgingCalc;
                                $mealLabels = [];
                                if (isset($bed['selectedMeals']) && is_array($bed['selectedMeals'])) {
                                    foreach ($bed['selectedMeals'] as $meal) {
                                        if (!is_array($meal)) {
                                            continue;
                                        }
                                        $mealLabels[] = ($meal['type'] ?? 'Meal') . ' (' . $currency . ' ' . number_format((float)($meal['price'] ?? 0), 2) . ')';
                                    }
                                }
                            @endphp
                            <div class="svc-dl-row">
                                <span class="svc-dl-label">Bed</span>
                                <span class="svc-dl-value">{{ $bed['bed_type'] ?? 'Bed' }} · Pax {{ $mealPaxLabel }}</span>
                            </div>
                            <div class="svc-dl-row">
                                <span class="svc-dl-label">Room Rate</span>
                                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($bedUnit, 2) }}/night</span>
                            </div>
                            @if($hotelNightsModal > 0)
                            <div class="svc-dl-row full">
                                <span class="svc-dl-label">Room Calc</span>
                                <span class="svc-dl-value">
                                    {{ $numberOfRooms }} room(s) × {{ $hotelNightsModal }} night(s)
                                    @if($displayLodging > 0)
                                        · Lodging <span class="svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($displayLodging, 2) }}</span>
                                    @endif
                                </span>
                            </div>
                            @elseif($bedUnit > 0)
                            <div class="svc-dl-row">
                                <span class="svc-dl-label">Amount</span>
                                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($bedUnit, 2) }}</span>
                            </div>
                            @endif
                            @if($mealsTotalFromPayload > 0)
                            <div class="svc-dl-row">
                                <span class="svc-dl-label">Meals Total</span>
                                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($mealsTotalFromPayload, 2) }}</span>
                            </div>
                            @endif
                            @if(count($mealLabels))
                            <div class="svc-dl-row full">
                                <span class="svc-dl-label">Meals</span>
                                <span class="svc-dl-value svc-meal-plan">{{ implode(', ', $mealLabels) }}</span>
                            </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                @php $totalRooms = collect($booking['rooms'])->sum(fn ($r) => (int)($r['number_of_rooms'] ?? 1)); @endphp
                <div class="svc-dl-row full">
                    <span class="svc-dl-label">Summary</span>
                    <span class="svc-dl-value">{{ $totalRooms }} room(s)@if($hotelNightsModal > 0) × {{ $hotelNightsModal }} night(s)@endif • {{ ucfirst($booking['bookingType'] ?? 'Standard') }} · <span class="svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? $booking['price'] ?? 0), 2) }}</span></span>
                </div>
            </div>
        </div>
    @else
    <div class="bg-light rounded p-1 mb-2">
        <div class="d-flex align-items-center mb-1">
            <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                <i class="ri-door-line text-white" style="font-size: 0.7rem;"></i>
            </div>
            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Room & Accommodation Details</h6>
        </div>

        @foreach($booking['rooms'] as $roomIndex => $room)
            @php
                $numberOfRooms = max(1, (int)($room['number_of_rooms'] ?? 1));
                $roomOccupancy = $room['occupancy'] ?? null;
                $roomSelectedPersons = isset($room['selected_persons']) ? (int)$room['selected_persons'] : null;
                $totalRoomPrice = 0;
                if (isset($room['beds']) && is_array($room['beds'])) {
                    $totalRoomPrice = collect($room['beds'])->sum(fn ($b) => (float)($b['price'] ?? 0));
                }
            @endphp
            <div class="bg-white rounded p-1 mb-1 border" style="border-color: #74b9ff !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div>
                        <small class="fw-bold text-dark" style="font-size: 0.75rem;">Room {{ $roomIndex + 1 }}: {{ $room['room_type'] ?? 'Standard Room' }}</small>
                        <div>
                            <small class="text-muted" style="font-size: 0.65rem;">
                                {{ $numberOfRooms }} Room{{ $numberOfRooms > 1 ? 's' : '' }}
                                @if($roomOccupancy)
                                    • {{ ucfirst($roomOccupancy) }} occupancy
                                @endif
                                @if($roomSelectedPersons !== null)
                                    • {{ $roomSelectedPersons }} guest{{ $roomSelectedPersons > 1 ? 's' : '' }} (selected)
                                @endif
                            </small>
                        </div>
                    </div>
                    @if($totalRoomPrice > 0)
                        <span class="badge bg-success" style="font-size: 0.7rem;">{{ $currency }} {{ number_format($totalRoomPrice, 2) }}</span>
                    @endif
                </div>

                @if(isset($room['beds']) && is_array($room['beds']))
                    @foreach($room['beds'] as $bedIndex => $bed)
                        @php
                            $mealPaxLabel = $roomSelectedPersons !== null ? $roomSelectedPersons : ($bed['head_count'] ?? 0);
                        @endphp
                        <div class="bg-light rounded p-1 mb-1">
                            <div class="row g-1">
                                <div class="col-6">
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Bed Type: {{ $bed['bed_type'] ?? 'Bed' }}</small>
                                    @if(isset($bed['max_occupancy']))
                                        <small class="text-muted" style="font-size: 0.6rem;">Max: {{ $bed['max_occupancy'] }}</small>
                                    @endif
                                </div>
                                <div class="col-3">
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Meal Pax</small>
                                    <div class="fw-medium text-primary" style="font-size: 0.7rem;">{{ $mealPaxLabel }}</div>
                                </div>
                                <div class="col-3">
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Price</small>
                                    <div class="fw-bold text-success" style="font-size: 0.7rem;">{{ $currency }} {{ number_format((float)($bed['price'] ?? 0), 2) }}</div>
                                </div>
                                @if(isset($bed['selectedMeals']) && is_array($bed['selectedMeals']) && count($bed['selectedMeals']) > 0)
                                    <div class="col-12">
                                        <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Meals:</small>
                                        @foreach($bed['selectedMeals'] as $mealKey => $meal)
                                            <span class="badge bg-success me-1" style="font-size: 0.6rem;">{{ $meal['type'] ?? 'Meal' }} ({{ $currency }} {{ number_format((float)($meal['price'] ?? 0), 2) }})</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach

        <div class="bg-white rounded p-1 mt-1 border" style="border-color: #74b9ff !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="fw-bold text-dark" style="font-size: 0.75rem;">Hotel Booking Summary</small>
                    @php $totalRooms = collect($booking['rooms'])->sum(fn ($r) => (int)($r['number_of_rooms'] ?? 1)); @endphp
                    <div><small class="text-muted" style="font-size: 0.65rem;">{{ $totalRooms }} room(s) • {{ ucfirst($booking['bookingType'] ?? 'Standard') }} booking</small></div>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block" style="font-size: 0.65rem;">Total Amount</small>
                    <div class="fw-bold" style="font-size: 0.9rem; color: #74b9ff;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? $booking['price'] ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif

@if($showAddonsSection)
    @if($professional)
        <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
            <p class="svc-section-title">Additional Accommodation</p>
            <div class="svc-dl">
                @if($hasCwbModal)
                    @php
                        $cwb = $booking['child_with_bed'];
                        $cwbPrice = (float)($cwb['price'] ?? 0);
                        $cwbChildren = (int)($cwb['children'] ?? 0);
                        $cwbNights = max(1, $hotelNightsModal);
                        $cwbTotal = isset($cwb['total_cost']) ? (float)$cwb['total_cost'] : ($cwbPrice * $cwbChildren * $cwbNights);
                    @endphp
                    <div class="svc-dl-row">
                        <span class="svc-dl-label">Child with Bed</span>
                        <span class="svc-dl-value">{{ $cwbChildren }} child(ren) · {{ $currency }} {{ number_format($cwbPrice, 2) }}/night</span>
                    </div>
                    <div class="svc-dl-row full">
                        <span class="svc-dl-label">Calc</span>
                        <span class="svc-dl-value">{{ $currency }} {{ number_format($cwbPrice, 2) }} × {{ $cwbChildren }} × {{ $cwbNights }} night(s) = <span class="svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($cwbTotal, 2) }}</span></span>
                    </div>
                @endif
                @if($hasCnbModal)
                    @php
                        $cwob = $booking['child_without_bed'];
                        $cwobPrice = (float)($cwob['price'] ?? 0);
                        $cwobChildren = (int)($cwob['children'] ?? 0);
                        $cwobNights = max(1, $hotelNightsModal);
                        $cwobTotal = isset($cwob['total_cost']) ? (float)$cwob['total_cost'] : ($cwobPrice * $cwobChildren * $cwobNights);
                    @endphp
                    <div class="svc-dl-row">
                        <span class="svc-dl-label">Child without Bed</span>
                        <span class="svc-dl-value">{{ $cwobChildren }} child(ren) · {{ $currency }} {{ number_format($cwobPrice, 2) }}/night</span>
                    </div>
                    <div class="svc-dl-row full">
                        <span class="svc-dl-label">Calc</span>
                        <span class="svc-dl-value">{{ $currency }} {{ number_format($cwobPrice, 2) }} × {{ $cwobChildren }} × {{ $cwobNights }} night(s) = <span class="svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($cwobTotal, 2) }}</span></span>
                    </div>
                @endif
                @if($extraBedShowModal)
                    @if($extraBedIsLite)
                        @php
                            $ebPriceNight = (float)($extraBedModal['price_per_night'] ?? 0);
                            $ebExtraPersons = (int)($extraBedModal['extra_persons'] ?? 0);
                            $ebRooms = (int)($extraBedModal['rooms'] ?? 0);
                            $ebNights = (int)($extraBedModal['nights'] ?? $hotelNightsModal);
                            $ebTotal = isset($extraBedModal['total_cost'])
                                ? (float)$extraBedModal['total_cost']
                                : ($ebPriceNight * max(1, $ebExtraPersons) * max(1, $ebRooms) * max(1, $ebNights));
                        @endphp
                        <div class="svc-dl-row">
                            <span class="svc-dl-label">Extra Bed</span>
                            <span class="svc-dl-value">{{ $ebExtraPersons }} persons · {{ $ebRooms }} rooms · {{ $ebNights }} nights</span>
                        </div>
                        <div class="svc-dl-row full">
                            <span class="svc-dl-label">Calc</span>
                            <span class="svc-dl-value">{{ $currency }} {{ number_format($ebPriceNight, 2) }} × {{ max(1, $ebExtraPersons) }} × {{ max(1, $ebRooms) }} × {{ max(1, $ebNights) }} = <span class="svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($ebTotal, 2) }}</span></span>
                        </div>
                    @elseif($extraBedIsPro)
                        @php
                            $extraBedPrice = (float)($extraBedModal['price'] ?? 0);
                            $extraBedQty = (int)($extraBedModal['quantity'] ?? 0);
                            $extraBedNights = max(1, $hotelNightsModal);
                            $extraBedTotal = isset($extraBedModal['total_cost'])
                                ? (float)$extraBedModal['total_cost']
                                : ($extraBedPrice * $extraBedQty * $extraBedNights);
                        @endphp
                        <div class="svc-dl-row">
                            <span class="svc-dl-label">Extra Bed</span>
                            <span class="svc-dl-value">Qty {{ $extraBedQty }} · {{ $currency }} {{ number_format($extraBedPrice, 2) }}/night</span>
                        </div>
                        <div class="svc-dl-row full">
                            <span class="svc-dl-label">Calc</span>
                            <span class="svc-dl-value">{{ $currency }} {{ number_format($extraBedPrice, 2) }} × {{ $extraBedQty }} × {{ $extraBedNights }} night(s) = <span class="svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($extraBedTotal, 2) }}</span></span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @else
    <div class="bg-light rounded p-2 mb-2">
        <div class="d-flex align-items-center mb-1">
            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                <i class="ri-user-add-line text-white" style="font-size: 0.8rem;"></i>
            </div>
            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Additional Accommodation</h6>
        </div>
        <div class="row g-2">
            @if($hasCwbModal)
                @php
                    $cwb = $booking['child_with_bed'];
                    $cwbPrice = (float)($cwb['price'] ?? 0);
                    $cwbChildren = (int)($cwb['children'] ?? 0);
                    $cwbTotal = isset($cwb['total_cost']) ? (float)$cwb['total_cost'] : ($cwbPrice * $cwbChildren * $hotelNightsModal);
                @endphp
                <div class="col-md-4">
                    <div class="bg-white rounded p-2 border h-100" style="border-color: #74b9ff !important;">
                        <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;"><i class="ri-bed-line me-1" style="font-size: 0.8rem;"></i>Child with Bed</div>
                        <div class="row g-1">
                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Status</small><div class="fw-medium text-success" style="font-size: 0.75rem;">Yes</div></div>
                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($cwbPrice, 2) }}</div></div>
                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Children</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $cwbChildren }}</div></div>
                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNightsModal }}</div></div>
                            <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total</small><div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($cwbTotal, 2) }}</div></div>
                        </div>
                    </div>
                </div>
            @endif

            @if($hasCnbModal)
                @php
                    $cwob = $booking['child_without_bed'];
                    $cwobPrice = (float)($cwob['price'] ?? 0);
                    $cwobChildren = (int)($cwob['children'] ?? 0);
                    $cwobTotal = isset($cwob['total_cost']) ? (float)$cwob['total_cost'] : ($cwobPrice * $cwobChildren * $hotelNightsModal);
                @endphp
                <div class="col-md-4">
                    <div class="bg-white rounded p-2 border h-100" style="border-color: #74b9ff !important;">
                        <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;"><i class="ri-user-smile-line me-1" style="font-size: 0.8rem;"></i>Child without Bed</div>
                        <div class="row g-1">
                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Status</small><div class="fw-medium text-success" style="font-size: 0.75rem;">Yes</div></div>
                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($cwobPrice, 2) }}</div></div>
                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Children</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $cwobChildren }}</div></div>
                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNightsModal }}</div></div>
                            <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total</small><div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($cwobTotal, 2) }}</div></div>
                        </div>
                    </div>
                </div>
            @endif

            @if($extraBedShowModal)
                @if($extraBedIsLite)
                    @php
                        $ebPriceNight = (float)($extraBedModal['price_per_night'] ?? 0);
                        $ebExtraPersons = (int)($extraBedModal['extra_persons'] ?? 0);
                        $ebRooms = (int)($extraBedModal['rooms'] ?? 0);
                        $ebNights = (int)($extraBedModal['nights'] ?? $hotelNightsModal);
                        $ebTotal = isset($extraBedModal['total_cost'])
                            ? (float)$extraBedModal['total_cost']
                            : ($ebPriceNight * max(1, $ebExtraPersons) * max(1, $ebRooms) * max(1, $ebNights));
                    @endphp
                    <div class="col-md-4">
                        <div class="bg-white rounded p-2 border h-100" style="border-color: #74b9ff !important;">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;"><i class="ri-hotel-bed-line me-1" style="font-size: 0.8rem;"></i>Extra Bed</div>
                            <div class="row g-1">
                                <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($ebPriceNight, 2) }}</div></div>
                                <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Extra Persons</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $ebExtraPersons }}</div></div>
                                <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Rooms</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $ebRooms }}</div></div>
                                <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $ebNights }}</div></div>
                                <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total (Price × Persons × Rooms × Nights)</small><div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($ebTotal, 2) }}</div></div>
                            </div>
                        </div>
                    </div>
                @elseif($extraBedIsPro)
                    @php
                        $extraBedPrice = (float)($extraBedModal['price'] ?? 0);
                        $extraBedQty = (int)($extraBedModal['quantity'] ?? 0);
                        $extraBedTotal = isset($extraBedModal['total_cost'])
                            ? (float)$extraBedModal['total_cost']
                            : ($extraBedPrice * $extraBedQty * max(1, $hotelNightsModal));
                    @endphp
                    <div class="col-md-4">
                        <div class="bg-white rounded p-2 border h-100" style="border-color: #74b9ff !important;">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;"><i class="ri-hotel-bed-line me-1" style="font-size: 0.8rem;"></i>Extra Bed</div>
                            <div class="row g-1">
                                <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Status</small><div class="fw-medium text-success" style="font-size: 0.75rem;">Yes</div></div>
                                <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($extraBedPrice, 2) }}</div></div>
                                <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Quantity</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $extraBedQty }}</div></div>
                                <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNightsModal }}</div></div>
                                <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total (Price × Quantity × Nights)</small><div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($extraBedTotal, 2) }}</div></div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif
@endif
