    @php
        // Reusable hotel modal partial.
        // Different booking-status pages (confirmed/definite/actual/etc.) need small UI changes,
        // so we allow turning sections on/off via optional variables.
        // IMPORTANT: these defaults must be defined even when $svc['hotel'] is 0,
        // because the edit modal block is outside the hotel-details wrapper.
        $hotelConfirmModalShowBookingStatus = $hotelConfirmModalShowBookingStatus ?? true;
        $hotelConfirmModalShowEditModal = $hotelConfirmModalShowEditModal ?? true;
        $hotelConfirmModalShowMailPreviewInDetails = $hotelConfirmModalShowMailPreviewInDetails ?? true;
        $hotelConfirmModalNoDataVariant = $hotelConfirmModalNoDataVariant ?? 'withNote'; // withNote|simple
        $hotelConfirmModalFooterVariant = $hotelConfirmModalFooterVariant ?? 'modern'; // modern|compact
    @endphp

    @if(isset($svc['hotel']) && $svc['hotel'] > 0)
    <div class="modal fade" id="hotelDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="hotelDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <!-- Compact Header (same style as Restaurant modal) -->
                <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h5 class="mb-0 fw-bold">
                                <i class="ri-hotel-line me-2"></i>Hotel Bookings
                            </h5>
                            <small class="opacity-90">Tour #{{ $tour->tour_id }}</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-3" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                        @foreach($serviceData['hotel'] as $hotelOrderIndex => $hotelOrder)
                        @php
                            $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                        @endphp
                        
                        @if(is_array($hotelData))
                            @foreach($hotelData as $bookingIndex => $booking)
                                <div class="card mb-3 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; border-left: 4px solid #74b9ff !important;" data-hotel-order="{{ $hotelOrderIndex }}" data-booking-index="{{ $bookingIndex }}">
                                    <!-- Compact Card Header -->
                                    <div class="card-header border-0 py-2 px-3" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%);">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-8">
                                                <h6 class="mb-0 fw-bold text-white">
                                                    <i class="ri-hotel-line me-1"></i>{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel Booking' }}
                                                </h6>
                                                <small class="text-white opacity-90">Booking {{ $hotelOrderIndex + 1 }} • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-white text-success px-3 py-2" style="font-size: 0.95rem;">
                                                    {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body p-3" style="background-color: #ffffff;">
                                        <!-- Customer & Address (compact like Restaurant) -->
                                        <div class="row mb-3 g-3">
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-user-line text-white" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Customer Details</h6>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Full Name</small>
                                                        <div class="fw-medium" style="font-size: 0.85rem;">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Email</small>
                                                        <div class="fw-medium text-primary" style="font-size: 0.85rem;">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Phone</small>
                                                        <div class="fw-medium" style="font-size: 0.85rem;">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-map-pin-line text-white" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Address</h6>
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.85rem;">
                                                        @if($booking['address1'] ?? false)
                                                            <div>{{ $booking['address1'] }}</div>
                                                        @endif
                                                        @if($booking['address2'] ?? false)
                                                            <div>{{ $booking['address2'] }}</div>
                                                        @endif
                                                        @if($booking['state'] ?? false)
                                                            <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                        @endif
                                                        @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                            <div>Address not provided</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stay & Hotel Details (compact) -->
                                        <div class="row mb-3 g-3">
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-calendar-line text-white" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Stay Schedule</h6>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Check-in</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0)
                                                                {{ \Carbon\Carbon::parse($booking['bookingDate'][0])->format('D, M d, Y') }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['checkInTime']))
                                                            <small class="text-primary" style="font-size: 0.75rem;">{{ $booking['hotelDetails']['checkInTime'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Check-out</small>
                                                        <div class="fw-bold text-danger" style="font-size: 0.9rem;">
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                {{ \Carbon\Carbon::parse(end($booking['bookingDate']))->format('D, M d, Y') }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['checkOutTime']))
                                                            <small class="text-danger" style="font-size: 0.75rem;">{{ $booking['hotelDetails']['checkOutTime'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Nights</small>
                                                        @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                            @php
                                                                $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                                                $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                                                $nights = $checkIn->diffInDays($checkOut);
                                                            @endphp
                                                            <span class="badge" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); color: white; font-size: 0.8rem;">{{ $nights }} Night{{ $nights > 1 ? 's' : '' }}</span>
                                                        @else
                                                            <span class="badge bg-secondary" style="font-size: 0.8rem;">TBD</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-light rounded p-2 h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ri-building-line text-white" style="font-size: 0.9rem;"></i>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Hotel Details</h6>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Location</small>
                                                        <div class="fw-medium" style="font-size: 0.85rem;">{{ $booking['hotelDetails']['location'] ?? 'Not specified' }}</div>
                                                    </div>
                                                    @if(isset($booking['hotelDetails']['cancellation_charge']) && !empty($booking['hotelDetails']['cancellation_charge']))
                                                    <div class="mb-1">
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Cancellation</small>
                                                        <div class="fw-medium text-warning" style="font-size: 0.8rem;">{{ $booking['hotelDetails']['cancellation_charge'] }}</div>
                                                    </div>
                                                    @endif
                                                    @if(isset($booking['hotelDetails']['image']))
                                                        <div class="mt-2">
                                                            <img src="{{ $booking['hotelDetails']['image'] }}" alt="{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel' }}" class="img-fluid rounded" style="height: 80px; width: 100%; object-fit: cover;">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Room & Accommodation Details (compact) -->
                                        @if(isset($booking['rooms']) && is_array($booking['rooms']))
                                            <div class="bg-light rounded p-2 mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-door-line text-white" style="font-size: 0.9rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Room & Accommodation</h6>
                                                </div>
                                                @foreach($booking['rooms'] as $roomIndex => $room)
                                                    <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 3px solid #74b9ff !important;">
                                                        <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%);">
                                                            <div class="row align-items-center g-1">
                                                                <div class="col-md-8">
                                                                    <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                                        <i class="ri-door-line me-1"></i>Room {{ $roomIndex + 1 }}: {{ $room['room_type'] ?? 'Standard Room' }}
                                                                    </h6>
                                                                    <small class="text-white opacity-90" style="font-size: 0.7rem;">ID: {{ $room['room_id'] ?? 'N/A' }}</small>
                                                                </div>
                                                                <div class="col-md-4 text-end">
                                                                    @if(isset($room['beds']) && is_array($room['beds']))
                                                                        @php $totalRoomPrice = collect($room['beds'])->sum('price'); @endphp
                                                                        <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">{{ $currency }} {{ number_format($totalRoomPrice, 2) }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-body p-2" style="background-color: #ffffff;">
                                                            @if(isset($room['beds']) && is_array($room['beds']))
                                                                @foreach($room['beds'] as $bedIndex => $bed)
                                                                    <div class="bg-light rounded p-2 mb-2">
                                                                        <div class="row g-2">
                                                                            <div class="col-md-6">
                                                                                <div class="d-flex align-items-center mb-1">
                                                                                    <i class="ri-hotel-bed-line text-primary me-1" style="font-size: 0.9rem;"></i>
                                                                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">{{ $bed['bed_type'] ?? 'Bed' }}</h6>
                                                                                </div>
                                                                                <div class="row g-1">
                                                                                    <div class="col-6">
                                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Guests</small>
                                                                                        <div class="fw-medium text-primary" style="font-size: 0.8rem;">{{ $bed['head_count'] ?? 0 }}</div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Price</small>
                                                                                        <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format($bed['price'] ?? 0, 2) }}</div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                @if(isset($bed['selectedMeals']) && is_array($bed['selectedMeals']))
                                                                                    <small class="text-muted d-block mb-1" style="font-size: 0.7rem;">Meals</small>
                                                                                    @foreach($bed['selectedMeals'] as $mealKey => $meal)
                                                                                        <div class="d-flex justify-content-between align-items-center mb-0 py-0" style="font-size: 0.8rem;">
                                                                                            <span>{{ $meal['type'] ?? 'Meal' }}</span>
                                                                                            <span class="badge bg-success" style="font-size: 0.7rem;">{{ $currency }} {{ number_format((float)($meal['price'] ?? 0), 2) }}</span>
                                                                                        </div>
                                                                                    @endforeach
                                                                                @endif
                                                                                @if(isset($bed['mealTypes']) && is_array($bed['mealTypes']))
                                                                                    <div class="mt-1">
                                                                                        <small class="text-muted" style="font-size: 0.65rem;">Options: </small>
                                                                                        @foreach($bed['mealTypes'] as $mealType)
                                                                                            <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ $mealType }}</span>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                                <div class="mt-2 text-center">
                                                    <span class="badge" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); color: white; font-size: 0.8rem;">{{ count($booking['rooms']) }} room(s) • Total {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Child Accommodation (child_with_bed / child_without_bed) -->
                                        @php
                                            $hotelNights = 0;
                                            if (isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1) {
                                                $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                                $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                                $hotelNights = $checkIn->diffInDays($checkOut);
                                            }
                                            // Check if child accommodation data exists (with or without enabled flag)
                                            $hasChildWithBed = isset($booking['child_with_bed']) && is_array($booking['child_with_bed']) && (
                                                (isset($booking['child_with_bed']['enabled']) && $booking['child_with_bed']['enabled']) ||
                                                (isset($booking['child_with_bed']['price']) && $booking['child_with_bed']['price'] > 0) ||
                                                (isset($booking['child_with_bed']['children']) && $booking['child_with_bed']['children'] > 0)
                                            );
                                            $hasChildWithoutBed = isset($booking['child_without_bed']) && is_array($booking['child_without_bed']) && (
                                                (isset($booking['child_without_bed']['enabled']) && $booking['child_without_bed']['enabled']) ||
                                                (isset($booking['child_without_bed']['price']) && $booking['child_without_bed']['price'] > 0) ||
                                                (isset($booking['child_without_bed']['children']) && $booking['child_without_bed']['children'] > 0)
                                            );
                                        @endphp
                                        @if($hasChildWithBed || $hasChildWithoutBed)
                                        <div class="bg-light rounded p-2 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-user-add-line text-white" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Child Accommodation</h6>
                                            </div>
                                            <div class="row g-2">
                                                @if($hasChildWithBed)
                                                @php
                                                    $cwb = $booking['child_with_bed'];
                                                    $cwbPrice = (float)($cwb['price'] ?? 0);
                                                    $cwbChildren = (int)($cwb['children'] ?? 0);
                                                    $cwbTotal = isset($cwb['total_cost']) ? (float)$cwb['total_cost'] : ($cwbPrice * $cwbChildren * $hotelNights);
                                                @endphp
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-2 border h-100" style="border-color: #74b9ff !important;">
                                                        <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;"><i class="ri-bed-line me-1" style="font-size: 0.8rem;"></i>Child with Bed</div>
                                                        <div class="row g-1">
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Status</small><div class="fw-medium text-success" style="font-size: 0.75rem;">Yes</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($cwbPrice, 2) }}</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Children</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $cwbChildren }}</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNights }}</div></div>
                                                            <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total (Price × Children × Nights)</small><div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($cwbTotal, 2) }}</div></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @if($hasChildWithoutBed)
                                                @php
                                                    $cwob = $booking['child_without_bed'];
                                                    $cwobPrice = (float)($cwob['price'] ?? 0);
                                                    $cwobChildren = (int)($cwob['children'] ?? 0);
                                                    $cwobTotal = isset($cwob['total_cost']) ? (float)$cwob['total_cost'] : ($cwobPrice * $cwobChildren * $hotelNights);
                                                @endphp
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-2 border h-100" style="border-color: #74b9ff !important;">
                                                        <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;"><i class="ri-user-smile-line me-1" style="font-size: 0.8rem;"></i>Child without Bed</div>
                                                        <div class="row g-1">
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Status</small><div class="fw-medium text-success" style="font-size: 0.75rem;">Yes</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Price/Night</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($cwobPrice, 2) }}</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Children</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $cwobChildren }}</div></div>
                                                            <div class="col-6"><small class="text-muted" style="font-size: 0.65rem;">Nights</small><div class="fw-medium" style="font-size: 0.75rem;">{{ $hotelNights }}</div></div>
                                                            <div class="col-12 pt-1 border-top mt-1"><small class="text-muted" style="font-size: 0.65rem;">Total (Price × Children × Nights)</small><div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($cwobTotal, 2) }}</div></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Transfer (compact) -->
                                        @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                            <div class="bg-light rounded p-2 mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-car-line text-white" style="font-size: 0.9rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Transfer</h6>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <div class="bg-white rounded p-2 border" style="border-color: #74b9ff !important;">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">Type / Way</small>
                                                            <span class="badge bg-primary me-1" style="font-size: 0.7rem;">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                            <span class="badge bg-info" style="font-size: 0.7rem;">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                            @if(isset($booking['transfer_options']['destination_name']) && !empty($booking['transfer_options']['destination_name']))
                                                                <div class="mt-1 fw-medium text-primary" style="font-size: 0.8rem;"><i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['destination_name'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="bg-white rounded p-2 border" style="border-color: #74b9ff !important;">
                                                            @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']) && isset($booking['transfer_options']['vehicle_details']['vehicle_name']))
                                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle</small>
                                                                <div class="fw-medium" style="font-size: 0.85rem;">{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] }}</div>
                                                            @endif
                                                            @if(isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0)
                                                                <div class="fw-bold text-success mt-1" style="font-size: 0.9rem;">{{ $currency }} {{ number_format((float)($booking['transfer_options']['cost'] ?? 0), 2) }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                    <div class="mt-2 d-flex align-items-center">
                                                        <i class="ri-map-pin-2-line text-info me-1" style="font-size: 0.85rem;"></i>
                                                        <small class="text-muted" style="font-size: 0.75rem;">Pickup: </small>
                                                        <span class="fw-medium text-info ms-1" style="font-size: 0.85rem;">{{ $booking['transfer_options']['pickup_location_name'] }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Special Requests (compact) -->
                                        @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                            <div class="bg-light rounded p-2 mb-3">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-message-line text-white" style="font-size: 0.9rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Special Requests</h6>
                                                </div>
                                                <p class="mb-0 text-dark" style="font-size: 0.85rem;">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        @endif

                                        @if($hotelConfirmModalShowBookingStatus)
                                            <!-- Booking Status -->
                                            <div class="bg-light rounded p-1">
                                                @php
                                                    $actualCancelDateStr = $tour->auto_cancel_date ? \Carbon\Carbon::parse($tour->auto_cancel_date)->format('Y-m-d') : '';
                                                @endphp
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-settings-line text-white" style="font-size: 0.7rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.8rem;">Booking Status</h6>
                                                </div>
                                                @if($hotelOrder->is_approve == 1)
                                                    <div class="alert alert-success mb-0 py-1 px-2" style="border-radius: 6px; font-size: 0.75rem;">
                                                        <i class="ri-check-circle-fill me-1" style="font-size: 0.7rem;"></i>
                                                        <strong>Approved</strong>
                                                        @if($hotelOrder->reference_id)
                                                            <span class="ms-1">• Ref: {{ $hotelOrder->reference_id }}</span>
                                                        @endif
                                                        @if($hotelOrder->display_due_date)
                                                            <span class="ms-1">• Due: {{ \Carbon\Carbon::parse($hotelOrder->display_due_date)->format('d-m-Y') }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    @if(auth()->user()->role_id == 11 || auth()->user()->role_id == 34 || auth()->user()->role_id == 33 || auth()->user()->role_id == 37 || auth()->user()->role_id == 38 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125 || auth()->user()->role_id == 128 || auth()->user()->role_id == 129 || auth()->user()->role_id == 130 || auth()->user()->role_id == 131 || auth()->user()->role_id == 132 || auth()->user()->role_id == 134 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138)
                                                    <div class="d-flex gap-1 flex-wrap">
                                                        @if(auth()->user()->role_id == 11 || auth()->user()->role_id == 34 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125 || auth()->user()->role_id == 128 || auth()->user()->role_id == 131 || auth()->user()->role_id == 132 || auth()->user()->role_id == 134 || auth()->user()->role_id == 135 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138)
                                                        <button type="button" 
                                                                class="btn btn-sm px-2 py-1" 
                                                                onclick="editIndividualHotel({{ $tour->tour_id }}, {{ $hotelOrderIndex }}, {{ $bookingIndex }}, '{{ $actualCancelDateStr }}')"
                                                                style="border-radius: 6px; background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); border: none; color: white; font-size: 0.75rem;">
                                                            <i class="ri-edit-line me-1" style="font-size: 0.7rem;"></i>Edit
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-outline-success btn-sm px-2 py-1" 
                                                                onclick="approveIndividualHotel({{ $tour->tour_id }}, {{ $hotelOrderIndex }}, {{ $bookingIndex }}, '{{ addslashes($actualCancelDateStr) }}')"
                                                                style="border-radius: 6px; font-size: 0.75rem;">
                                                            <i class="ri-check-line me-1" style="font-size: 0.7rem;"></i>Approve
                                                        </button>
                                                        @endif
                                                        @if(auth()->user()->role_id == 11 || auth()->user()->role_id == 34 || auth()->user()->role_id == 33 || auth()->user()->role_id == 37 || auth()->user()->role_id == 38 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125 || auth()->user()->role_id == 128 || auth()->user()->role_id == 129 || auth()->user()->role_id == 130 || auth()->user()->role_id == 131 || auth()->user()->role_id == 132 || auth()->user()->role_id == 134 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138)
                                                        <button type="button" 
                                                                class="btn btn-outline-danger btn-sm px-2 py-1" 
                                                                onclick="rejectIndividualHotel({{ $tour->tour_id }}, {{ $hotelOrderIndex }}, {{ $bookingIndex }}, '{{ $actualCancelDateStr }}')"
                                                                style="border-radius: 6px; font-size: 0.75rem;">
                                                            <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Reject
                                                        </button>
                                                        @endif
                                                    </div>
                                                    @else
                                                    <div class="text-muted small" style="font-size: 0.75rem;"><i class="ri-information-line me-1" style="font-size: 0.7rem;"></i>Pending approval</div>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @endforeach
                    @else
                        @if($hotelConfirmModalNoDataVariant === 'simple')
                            <div class="text-center py-4">
                                <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="ri-hotel-line text-muted" style="font-size: 2rem;"></i>
                                </div>
                                <h5 class="text-dark mb-2" style="font-size: 1.1rem;">No Hotel Data Available</h5>
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">Hotel services are booked but detailed information is not available at this moment.</p>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                    <i class="ri-hotel-line text-muted" style="font-size: 2rem;"></i>
                                </div>
                                <h6 class="text-dark mb-2" style="font-size: 1rem;">No Hotel Data Available</h6>
                                <p class="text-muted mb-3" style="font-size: 0.85rem;">Hotel services are booked but detailed information is not available.</p>
                                <div class="alert alert-light border" style="max-width: 360px; margin: 0 auto; font-size: 0.8rem;">
                                    <i class="ri-information-line text-primary me-1"></i>
                                    <strong>Note:</strong> {{ $svc['hotel'] }} hotel service(s) are associated with this tour.
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                @if($hotelConfirmModalFooterVariant === 'compact')
                    <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                        <div class="d-flex gap-2 w-100 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                                <i class="ri-close-line me-1" style="font-size: 0.7rem;"></i>Close
                            </button>
                        </div>
                    </div>
                @else
                    <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                        <div class="d-flex justify-content-between w-100">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeHotelModal({{ $tour->tour_id }})" style="border-radius: 25px;">
                                <i class="ri-close-line me-2"></i>Close
                            </button>
                            @if($hotelConfirmModalShowMailPreviewInDetails)
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-info px-4 py-2" onclick="openHotelMailPreview({{ $tour->tour_id }}, 0, 0)" style="border-radius: 25px;">
                                        <i class="ri-mail-line me-2"></i>Mail Preview
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Hotel Edit Modal -->
    @if($hotelConfirmModalShowEditModal && isset($svc['hotel']) && $svc['hotel'] > 0)
    <div class="modal fade" id="editHotelModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="editHotelModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <!-- Modal Header -->
                <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                            <i class="ri-hotel-line text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-1" id="editHotelModalLabel{{ $tour->tour_id }}">
                                Edit Hotel Booking Dates
                            </h5>
                            @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                                @php
                                    $firstHotelOrder = $serviceData['hotel'][0];
                                    $firstHotelData = is_string($firstHotelOrder->data) ? json_decode($firstHotelOrder->data, true) : $firstHotelOrder->data;
                                    $firstBooking = is_array($firstHotelData) ? $firstHotelData[0] : null;
                                @endphp
                                <p class="text-white-50 mb-0 small">{{ $firstBooking['hotel_name'] ?? 'Hotel Booking' }}</p>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeEditHotelModal({{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Travel Date Range Info -->
                    <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(45deg, #e3f2fd, #f0f8ff); border-radius: 12px;">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-information-line me-2 text-info"></i>
                            <strong class="text-info">Travel Date Range</strong>
                        </div>
                        <p class="mb-0 text-muted small">
                            Hotel dates must be within the travel period: 
                            <strong class="text-primary">
                                @if($tour->check_in_time && $tour->check_out_time)
                                    {{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}
                                @else
                                    Travel dates not specified
                                @endif
                            </strong>
                        </p>
                    </div>

                    <form id="editHotelForm{{ $tour->tour_id }}">
                        @csrf
                        <input type="hidden" name="tour_id" value="{{ $tour->tour_id }}">
                        <input type="hidden" name="travel_start" value="{{ $tour->check_in_time }}">
                        <input type="hidden" name="travel_end" value="{{ $tour->check_out_time }}">
                        
                        <!-- Hotel Selection (if multiple hotels) -->
                        @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 1)
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="ri-hotel-line me-2"></i>Select Hotel to Edit
                            </label>
                            <select class="form-select" name="hotel_index" id="hotelSelect{{ $tour->tour_id }}" onchange="loadHotelDates({{ $tour->tour_id }})">
                                @foreach($serviceData['hotel'] as $index => $hotelOrder)
                                    @php
                                        $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                                        $firstBooking = is_array($hotelData) ? $hotelData[0] : null;
                                    @endphp
                                    @if($firstBooking)
                                        <option value="{{ $index }}">{{ $firstBooking['hotel_name'] ?? "Hotel " . ($index + 1) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="hotel_index" value="0">
                        @endif

                        <!-- Date Range Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="ri-calendar-check-line me-2 text-success"></i>Check-in Date
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       name="check_in_date" 
                                       id="checkInDate{{ $tour->tour_id }}"
                                       @if($tour->check_in_time) min="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" @endif
                                       @if($tour->check_out_time) max="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" @endif
                                       required>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>
                                    Must be within travel dates
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="ri-calendar-close-line me-2 text-danger"></i>Check-out Date
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       name="check_out_date" 
                                       id="checkOutDate{{ $tour->tour_id }}"
                                       @if($tour->check_in_time) min="{{ \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') }}" @endif
                                       @if($tour->check_out_time) max="{{ \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') }}" @endif
                                       required>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>
                                    Must be after check-in date
                                </div>
                            </div>
                        </div>

                        <!-- Current Booking Summary -->
                        <div class="card border-0 bg-light mb-4" style="border-radius: 12px;">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary rounded-circle p-2 me-3">
                                        <i class="ri-file-list-line text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">Current Booking Summary</h6>
                                </div>
                                <div class="row" id="currentBookingSummary{{ $tour->tour_id }}">
                                    @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                                        @php
                                            $firstHotelOrder = $serviceData['hotel'][0];
                                            $firstHotelData = is_string($firstHotelOrder->data) ? json_decode($firstHotelOrder->data, true) : $firstHotelOrder->data;
                                            $firstBooking = is_array($firstHotelData) ? $firstHotelData[0] : null;
                                        @endphp
                                        @if($firstBooking)
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Hotel Name</small>
                                            <div class="fw-medium">{{ $firstBooking['hotel_name'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Location</small>
                                            <div class="fw-medium">{{ $firstBooking['location'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Total Rooms</small>
                                            <div class="fw-medium">{{ $firstBooking['total_rooms'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted">Current Price</small>
                                            <div class="fw-medium text-success">{{ $firstBooking['price'] ?? 'N/A' }}</div>
                                        </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- <!-- Reason for Change -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="ri-message-3-line me-2"></i>Reason for Date Change
                            </label>
                            <textarea class="form-control" 
                                      name="change_reason" 
                                      id="changeReason{{ $tour->tour_id }}"
                                      rows="3" 
                                      placeholder="Please specify the reason for changing hotel dates..."
                                      required></textarea>
                        </div> --}}
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                    <button type="button" class="btn btn-outline-info px-4 py-2" onclick="openHotelMailPreview({{ $tour->tour_id }}, 0, 0)" style="border-radius: 25px;">
                        <i class="ri-mail-line me-2"></i>Mail Preview
                    </button>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2 me-2" onclick="closeEditHotelModal({{ $tour->tour_id }})" style="border-radius: 25px;">
                            <i class="ri-close-line me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2" onclick="saveHotelDateChanges({{ $tour->tour_id }})" style="border-radius: 25px;">
                            <i class="ri-save-line me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
