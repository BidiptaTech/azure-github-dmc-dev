<!-- Restaurant Details Modal -->
@if(isset($svc['restaurant']) && $svc['restaurant'] > 0)
<div class="modal fade" id="restaurantDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="restaurantDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <!-- Compact Header -->
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h5 class="mb-0 fw-bold">
                            <i class="ri-restaurant-2-line me-2"></i>Restaurant Bookings
                        </h5>
                        <small class="opacity-90">Tour #{{ $tour->tour_id }}</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" aria-label="Close"></button>
                </div>
            </div>
            
            <div class="modal-body p-3" style="background-color: #f8f9fa;">
                @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                    @foreach($serviceData['restaurant'] as $index => $restaurantOrder)
                    @php
                        $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data;
                    @endphp
                    
                    @if(is_array($restaurantData))
                        @php $actualBookingIndex = 0; @endphp
                        @foreach($restaurantData as $originalKey => $booking)
                            @php $bookingIndex = $actualBookingIndex; @endphp
                            <div class="card mb-3 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; border-left: 4px solid #fd79a8 !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-2 px-3" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%);">
                                    <div class="row align-items-center g-2">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white">
                                                <i class="ri-restaurant-2-line me-1"></i>{{ $booking['restaurantName'] ?? 'Restaurant Booking' }}
                                            </h6>
                                            <small class="text-white opacity-90">{{ ucfirst($booking['mealType'] ?? 'Meal') }} • {{ $booking['mealSpecificType'] ?? 'Standard' }}</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-3 py-2" style="font-size: 0.95rem;">
                                                {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-3" style="background-color: #ffffff;">
                                    <!-- Customer & Reservation Details -->
                                    <div class="row mb-3 g-3">
                                      
                                        <div class="col-md-12">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-line text-white" style="font-size: 0.9rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Reservation Details</h6>
                                                </div>
                                                <div class="mb-1">
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Dining Date</small>
                                                    <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                </div>
                                                <div class="mb-1">
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Dining Time</small>
                                                    <div class="fw-medium text-primary" style="font-size: 0.85rem;">{{ $booking['visitTime'] ?? 'TBC' }}</div>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #fd79a8 !important;">
                                                            <div class="fw-bold text-success" style="font-size: 1rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #fd79a8 !important;">
                                                            <div class="fw-bold text-warning" style="font-size: 1rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); color: white; font-size: 0.8rem; padding: 2px 6px;">
                                                        Party: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                      <!-- Menu & Meal Details -->
                                      @if(isset($booking['MealDescription']) && is_array($booking['MealDescription']))
                                    <div class="bg-light rounded p-2 mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-restaurant-line text-white" style="font-size: 0.9rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Menu Items ({{ count($booking['MealDescription']) }})</h6>
                                        </div>
                                        
                                        @foreach($booking['MealDescription'] as $index => $meal)
                                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 3px solid #fd79a8 !important;">
                                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%);">
                                                    <div class="row align-items-center g-1">
                                                        <div class="col-md-8">
                                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                                <i class="ri-restaurant-2-line me-1"></i>Menu Item {{ $index + 1 }}
                                                            </h6>
                                                            <div class="d-flex gap-1 mt-1">
                                                                <span class="badge bg-white bg-opacity-20 text-white border-0 px-2 py-0" style="font-size: 0.65rem;">
                                                                    {{ $meal['category'] ?? 'Category' }}
                                                                </span>
                                                                <span class="badge bg-white bg-opacity-20 text-white border-0 px-2 py-0" style="font-size: 0.65rem;">
                                                                    {{ $meal['item_type'] ?? 'Type' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 text-end">
                                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                                {{ $currency }} {{ number_format((float)($meal['price'] ?? 0), 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="card-body p-2" style="background-color: #ffffff;">
                                                    <div class="row g-2">
                                                        <div class="col-md-4">
                                                            <div class="bg-light rounded p-1 text-center">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Quantity</small>
                                                                <div class="fw-bold text-primary" style="font-size: 1.2rem;">{{ $meal['quantity'] ?? 1 }}</div>
                                                                <small class="text-muted" style="font-size: 0.65rem;">{{ ($meal['quantity'] ?? 1) == 1 ? 'item' : 'items' }}</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="bg-light rounded p-1 text-center">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Unit Price</small>
                                                                <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format((float)($meal['price'] ?? 0), 2) }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="bg-light rounded p-1 text-center" style="background: linear-gradient(135deg, rgba(253,121,168,0.1) 0%, rgba(253,203,110,0.1) 100%) !important;">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Subtotal</small>
                                                                <div class="fw-bold" style="font-size: 1rem; color: #fd79a8;">{{ $currency }} {{ number_format((float)(($meal['price'] ?? 0) * ($meal['quantity'] ?? 1)), 2) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        <!-- Order Summary -->
                                        <div class="bg-light rounded p-2 mt-2">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                                                        <i class="ri-receipt-line me-1"></i>Order Summary
                                                    </h6>
                                                    <small class="text-muted" style="font-size: 0.7rem;">
                                                        {{ count($booking['MealDescription']) }} item(s) • {{ $booking['mealType'] ?? 'Meal' }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Grand Total</small>
                                                    <div class="fw-bold" style="font-size: 1.2rem; color: #fd79a8;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Transfer Options -->
                                    @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success rounded-circle p-2 me-3">
                                                    <i class="ri-car-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Transfer Details</h6>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="bg-light rounded p-3 h-100">
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Transfer Type</small>
                                                            <div class="fw-medium">
                                                                <span class="badge bg-primary">{{ $booking['transfer_options']['type'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Transfer Way</small>
                                                            <div class="fw-medium">
                                                                <span class="badge bg-info">{{ $booking['transfer_options']['way'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                        <div class="mb-0">
                                                            <small class="text-muted d-block">Pickup Location</small>
                                                            <div class="fw-medium text-primary">
                                                                <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['pickup_location_name'] }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <div class="bg-light rounded p-3 h-100">
                                                        @if(isset($booking['transfer_options']['vehicle_details']) && is_array($booking['transfer_options']['vehicle_details']))
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Vehicle</small>
                                                                <div class="fw-medium">
                                                                    <i class="ri-car-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['vehicle_name'] ?? 'N/A' }}
                                                                </div>
                                                                @if(isset($booking['transfer_options']['vehicle_details']['vehicle_type']))
                                                                    <small class="text-muted">Type: {{ $booking['transfer_options']['vehicle_details']['vehicle_type'] }}</small>
                                                                @endif
                                                            </div>
                                                            @if(isset($booking['transfer_options']['vehicle_details']['seating_capacity']))
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Seating Capacity</small>
                                                                <div class="fw-medium">
                                                                    <i class="ri-user-line me-1"></i>{{ $booking['transfer_options']['vehicle_details']['seating_capacity'] }} passengers
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Vehicle ID</small>
                                                                <div class="fw-medium">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                            </div>
                                                        @endif
                                                        
                                                        @php
                                                            // For PRO tours, show total transfer price (base × pax) when available
                                                            $restaurantTransferCostDisplay = $booking['transfer_options']['cost'] ?? 0;
                                                            if (isset($tour) && $tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                                                $restaurantTransferCostDisplay = $booking['transfer_options']['totalPrice'];
                                                            }
                                                        @endphp
                                                        @if($restaurantTransferCostDisplay > 0)
                                                        <div class="mb-0">
                                                            <small class="text-muted d-block">Transfer Cost</small>
                                                            <div class="fs-5 fw-bold text-success">
                                                                <i class="ri-money-dollar-circle-line me-1"></i>{{ $currency }} {{ number_format((float)$restaurantTransferCostDisplay, 2) }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Guide Options -->
                                    @if(isset($booking['guide_options']) && is_array($booking['guide_options']) && (isset($booking['guide_options']['guideId']) || isset($booking['guide_options']['guide_id']) || isset($booking['guide_options']['guideName']) || isset($booking['guide_options']['guide_name']) || isset($booking['guide_options']['name'])))
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-info rounded-circle p-2 me-3">
                                                    <i class="ri-user-voice-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Guide Details</h6>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="bg-light rounded p-3 h-100">
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Guide Name</small>
                                                            <div class="fw-medium">
                                                                <i class="ri-user-voice-line me-1"></i>{{ $booking['guide_options']['guideName'] ?? $booking['guide_options']['guide_name'] ?? $booking['guide_options']['name'] ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Service Type</small>
                                                            <div class="fw-medium">
                                                                <span class="badge bg-info">{{ $booking['guide_options']['serviceType'] ?? $booking['guide_options']['service_type'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Language</small>
                                                            <div class="fw-medium">
                                                                <span class="badge bg-success">{{ $booking['guide_options']['language'] ?? $booking['guide_options']['languages'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                        @if(isset($booking['guide_options']['tourActivity']) || isset($booking['guide_options']['tour_activity']) || isset($booking['guide_options']['Activity']))
                                                        <div class="mb-0">
                                                            <small class="text-muted d-block">Tour Activity</small>
                                                            <div class="fw-medium text-primary">
                                                                <i class="ri-map-pin-line me-1"></i>{{ $booking['guide_options']['tourActivity'] ?? $booking['guide_options']['tour_activity'] ?? $booking['guide_options']['Activity'] ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <div class="bg-light rounded p-3 h-100">
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Service Hours</small>
                                                            <div class="fw-medium">
                                                                <i class="ri-time-line me-1"></i>{{ $booking['guide_options']['hours'] ?? $booking['guide_options']['service_hours'] ?? 'N/A' }} Hours
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Group Size</small>
                                                            <div class="row g-1">
                                                                <div class="col-6">
                                                                    <div class="bg-white rounded p-1 text-center border">
                                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['guide_options']['adultsQty'] ?? $booking['guide_options']['adults_qty'] ?? 0 }}</div>
                                                                        <small class="text-muted" style="font-size: 0.65rem;">Adults</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="bg-white rounded p-1 text-center border">
                                                                        <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['guide_options']['childQty'] ?? $booking['guide_options']['child_qty'] ?? 0 }}</div>
                                                                        <small class="text-muted" style="font-size: 0.65rem;">Children</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(isset($booking['guide_options']['cost']) && $booking['guide_options']['cost'] > 0)
                                                        <div class="mb-0">
                                                            <small class="text-muted d-block">Guide Cost</small>
                                                            <div class="fs-5 fw-bold text-info">
                                                                <i class="ri-money-dollar-circle-line me-1"></i>{{ $currency }} {{ number_format((float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0), 2) }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Pricing Overview -->
                                    <div class="bg-light rounded p-2 mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.9rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Pricing Overview</h6>
                                        </div>
                                        @php
                                            $vehicleCost = 0;
                                            if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                                                // PRO tours: prefer totalPrice (base × pax) when available
                                                if ($tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                                    $vehicleCost = (float) $booking['transfer_options']['totalPrice'];
                                                } else {
                                                    $vehicleCost = (float) $booking['transfer_options']['cost'];
                                                }
                                            }
                                            $guideCost = 0;
                                            if (isset($booking['guide_options']) && is_array($booking['guide_options'])) {
                                                $guideCostValue = $booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0;
                                                if ($guideCostValue > 0) {
                                                    $guideCost = (float) $guideCostValue;
                                                }
                                            }
                                            $mealPrice = $booking['mealPrice'] ?? $booking['totalPrice'] ?? 0;
                                            $totalPrice = $mealPrice + $vehicleCost + $guideCost;
                                            $hasTransfer = $vehicleCost > 0;
                                            $hasGuide = $guideCost > 0;
                                        @endphp
                                        <div class="row g-2">
                                            <div class="col-md-{{ $hasTransfer && $hasGuide ? '3' : ($hasTransfer || $hasGuide ? '4' : '6') }}">
                                                <div class="text-center p-2 border rounded bg-white" style="border-color: #28a745 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Meal Price</small>
                                                    <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format($mealPrice, 2) }}</div>
                                                </div>
                                            </div>
                                            @if($hasTransfer)
                                            <div class="col-md-{{ $hasGuide ? '3' : '4' }}">
                                                <div class="text-center p-2 border rounded bg-white" style="border-color: #17a2b8 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle Price</small>
                                                    <div class="fw-bold text-info" style="font-size: 0.8rem;">{{ $currency }} {{ number_format($vehicleCost, 2) }}</div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($hasGuide)
                                            <div class="col-md-{{ $hasTransfer ? '3' : '4' }}">
                                                <div class="text-center p-2 border rounded bg-white" style="border-color: #00cec9 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Guide Price</small>
                                                    <div class="fw-bold" style="font-size: 0.8rem; color: #00cec9;">{{ $currency }} {{ number_format($guideCost, 2) }}</div>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="col-md-{{ $hasTransfer && $hasGuide ? '3' : ($hasTransfer || $hasGuide ? '4' : '6') }}">
                                                <div class="text-center p-2 border rounded bg-white" style="border-color: #fd79a8 !important; background: linear-gradient(135deg, rgba(253,121,168,0.1) 0%, rgba(253,203,110,0.1) 100%) !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Grand Total</small>
                                                    <div class="fw-bold" style="font-size: 1.1rem; color: #fd79a8;">{{ $currency }} {{ number_format($totalPrice, 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                  

                               
                                </div>
                            </div>
                            @php $actualBookingIndex++; @endphp
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-restaurant-2-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Restaurant Data Available</h4>
                        <p class="text-muted mb-4">Restaurant services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <!-- Compact Footer with Buttons in One Row -->
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
