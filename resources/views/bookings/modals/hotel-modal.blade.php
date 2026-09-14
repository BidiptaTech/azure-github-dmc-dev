<!-- Hotel Details Modal -->
@if(isset($svc['hotel']) && $svc['hotel'] > 0)
<div class="modal fade" id="hotelDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="hotelDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <!-- Compact Header -->
            <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-hotel-line me-1" style="font-size: 0.9rem;"></i>Hotel Enquiries - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                    @foreach($serviceData['hotel'] as $index => $hotelOrder)
                    @php
                        $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                    @endphp
                    
                    @if(is_array($hotelData))
                        @foreach($hotelData as $bookingIndex => $booking)
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #74b9ff !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-hotel-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel Bookings' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">Enquiry {{ $index + 1 }} • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2" style="background-color: #ffffff;">
                                    <!-- Customer Details & Address -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-user-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Customer Details</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Full Name</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Email Address</small>
                                                        <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['email'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Phone Number</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Address</h6>
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
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
                                                        <div class="text-muted">Address not provided</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stay Information & Hotel Details -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-check-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Stay Schedule</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Check-in</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.75rem;">
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0)
                                                                {{ \Carbon\Carbon::parse($booking['bookingDate'][0])->format('M d, Y') }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['checkInTime']))
                                                            <small class="text-primary fw-medium" style="font-size: 0.65rem;">{{ $booking['hotelDetails']['checkInTime'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Check-out</small>
                                                        <div class="fw-bold text-danger" style="font-size: 0.75rem;">
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                {{ \Carbon\Carbon::parse(end($booking['bookingDate']))->format('M d, Y') }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        @if(isset($booking['hotelDetails']['checkOutTime']))
                                                            <small class="text-danger fw-medium" style="font-size: 0.65rem;">{{ $booking['hotelDetails']['checkOutTime'] }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Total Nights</small>
                                                        <div>
                                                            @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                                @php
                                                                    $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                                                    $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                                                    $nights = $checkIn->diffInDays($checkOut);
                                                                @endphp
                                                                <span class="badge" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); color: white; font-size: 0.65rem; padding: 2px 4px;">{{ $nights }} Night{{ $nights > 1 ? 's' : '' }}</span>
                                                            @else
                                                                <span class="badge bg-secondary" style="font-size: 0.65rem; padding: 2px 4px;">Duration TBD</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-building-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Hotel Details</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Location</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['hotelDetails']['location'] ?? 'Location not specified' }}</div>
                                                    </div>
                                                    @if(isset($booking['hotelDetails']['cancellation_charge']) && !empty($booking['hotelDetails']['cancellation_charge']))
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Cancellation Policy</small>
                                                        <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $booking['hotelDetails']['cancellation_charge'] }}</div>
                                                    </div>
                                                    @endif
                                                </div>
                                                @if(isset($booking['hotelDetails']['image']))
                                                    <div class="mt-1">
                                                        <img src="{{ $booking['hotelDetails']['image'] }}" 
                                                             alt="{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel' }}" 
                                                             class="img-fluid rounded shadow-sm" 
                                                             style="height: 60px; width: 100%; object-fit: cover;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Room & Accommodation Details -->
                                    @if(isset($booking['rooms']) && is_array($booking['rooms']))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-door-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Room & Accommodation Details</h6>
                                            </div>
                                            
                                            @foreach($booking['rooms'] as $roomIndex => $room)
                                                <div class="bg-white rounded p-1 mb-1 border" style="border-color: #74b9ff !important;">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <div>
                                                            <small class="fw-bold text-dark" style="font-size: 0.75rem;">Room {{ $roomIndex + 1 }}: {{ $room['room_type'] ?? 'Standard Room' }}</small>
                                                        </div>
                                                        @if(isset($room['beds']) && is_array($room['beds']))
                                                            @php $totalRoomPrice = collect($room['beds'])->sum('price'); @endphp
                                                            <span class="badge bg-success" style="font-size: 0.7rem;">{{ $currency }} {{ number_format($totalRoomPrice, 2) }}</span>
                                                        @endif
                                                    </div>
                                                    
                                                    @if(isset($room['beds']) && is_array($room['beds']))
                                                        @foreach($room['beds'] as $bedIndex => $bed)
                                                            <div class="bg-light rounded p-1 mb-1">
                                                                <div class="row g-1">
                                                                    <div class="col-6">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Bed Type: {{ $bed['bed_type'] ?? 'Bed' }}</small>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Guests</small>
                                                                        <div class="fw-medium text-primary" style="font-size: 0.7rem;">{{ $bed['head_count'] ?? 0 }}</div>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Price</small>
                                                                        <div class="fw-bold text-success" style="font-size: 0.7rem;">{{ $currency }} {{ number_format($bed['price'] ?? 0, 2) }}</div>
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
                                            
                                            <!-- Booking Summary -->
                                            <div class="bg-white rounded p-1 mt-1 border" style="border-color: #74b9ff !important;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="fw-bold text-dark" style="font-size: 0.75rem;">Hotel Booking Summary</small>
                                                        <div><small class="text-muted" style="font-size: 0.65rem;">{{ count($booking['rooms']) }} room(s) • {{ ucfirst($booking['bookingType'] ?? 'Standard') }} booking</small></div>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Total Amount</small>
                                                        <div class="fw-bold" style="font-size: 0.9rem; color: #74b9ff;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                    </div>
                                                </div>
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
                                    @endphp
                                    @if((isset($booking['child_with_bed']['enabled']) && $booking['child_with_bed']['enabled']) || (isset($booking['child_without_bed']['enabled']) && $booking['child_without_bed']['enabled']))
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-user-add-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Child Accommodation</h6>
                                        </div>
                                        <div class="row g-2">
                                            @if(isset($booking['child_with_bed']['enabled']) && $booking['child_with_bed']['enabled'])
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
                                            @if(isset($booking['child_without_bed']['enabled']) && $booking['child_without_bed']['enabled'])
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

                                    <!-- Transfer Options -->
                                    @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-car-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Details</h6>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                <span class="badge bg-primary" style="font-size: 0.65rem;">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Way</small>
                                                                <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                            </div>
                                                            @if(isset($booking['transfer_options']['destination_name']) && !empty($booking['transfer_options']['destination_name']))
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Destination</small>
                                                                <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                    <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['destination_name'] }}
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']))
                                                            <div class="row g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">
                                                                        <i class="ri-car-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] ?? 'N/A' }}
                                                                    </div>
                                                                    @if(isset($booking['transfer_options']['vehicle_details']['vehicle_type']))
                                                                        <small class="text-muted" style="font-size: 0.6rem;">Type: {{ $booking['transfer_options']['vehicle_details']['vehicle_type'] }}</small>
                                                                    @endif
                                                                </div>
                                                                @if(isset($booking['transfer_options']['vehicle_details']['seating_capacity']))
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Capacity</small>
                                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_details']['seating_capacity'] }} passengers</div>
                                                                </div>
                                                                @endif
                                                                @php
                                                                    // For PRO tours, show total transfer price (base × pax) when available
                                                                    $transferCostDisplay = $booking['transfer_options']['cost'] ?? 0;
                                                                    if (isset($tour) && $tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                                                        $transferCostDisplay = $booking['transfer_options']['totalPrice'];
                                                                    }
                                                                @endphp
                                                                @if($transferCostDisplay > 0)
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                    <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)$transferCostDisplay, 2) }}</div>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle ID</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                <div class="col-12">
                                                    <div class="bg-info bg-opacity-10 rounded p-1 mt-1">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                        <div class="fw-medium text-info" style="font-size: 0.75rem;">{{ $booking['transfer_options']['pickup_location_name'] }}</div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Special Requests -->
                                    @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background-color: #6f42c1; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-message-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Special Requests</h6>
                                            </div>
                                            <div class="bg-white rounded p-1">
                                                <p class="mb-0 text-dark" style="font-size: 0.75rem;">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-hotel-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Hotel Data Available</h4>
                        <p class="text-muted mb-4">Hotel services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <!-- Compact Footer -->
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
 </div>
@endif