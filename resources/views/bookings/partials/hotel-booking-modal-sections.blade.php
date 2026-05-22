{{-- Shared hotel booking display: Pro (enabled/price/quantity) vs Lite (price_per_night/extra_persons/rooms/nights) extra_bed + rooms[] details --}}
@php
    $hotelNightsModal = 0;
    if (isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1) {
        $checkInModal = \Carbon\Carbon::parse($booking['bookingDate'][0]);
        $checkOutModal = \Carbon\Carbon::parse(end($booking['bookingDate']));
        $hotelNightsModal = $checkInModal->diffInDays($checkOutModal);
    }

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

@if($showAddonsSection)
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
