<!-- Attraction Details Modal -->
@if(isset($svc['attraction']) && $svc['attraction'] > 0)
<div class="modal fade" id="attractionDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="attractionDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <!-- Compact Header -->
            <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-building-2-line me-1" style="font-size: 0.9rem;"></i>Attraction Enquiries - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['attraction']) && count($serviceData['attraction']) > 0)
                    @foreach($serviceData['attraction'] as $index => $attractionOrder)
                    @php
                        $attractionData = is_string($attractionOrder->data) ? json_decode($attractionOrder->data, true) : $attractionOrder->data;
                    @endphp
                    
                    @if(is_array($attractionData))
                        @php $actualBookingIndex = 0; @endphp
                        @foreach($attractionData as $originalKey => $booking)
                            @php $bookingIndex = $actualBookingIndex; @endphp
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #fd9853 !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #fd9853 0%, #fe7854 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-building-2-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['AttractionName'] ?? 'Attraction Booking' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ $booking['ticketName'] ?? 'Standard Ticket' }} • Enquiry {{ $index + 1 }}</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2" style="background-color: #ffffff;">
                                    <!-- Visit & Guest Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Visit Schedule</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Visit Date</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Visit Time</small>
                                                        <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $booking['visitTime'] ?? 'Full Day' }}</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Selection Type</small>
                                                        <span class="badge bg-info" style="font-size: 0.65rem; padding: 2px 6px;">{{ ucfirst($booking['Selection'] ?? 'Standard') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guest Information</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-4 text-center">
                                                        <div class="bg-white rounded p-1">
                                                            <div class="fw-bold text-success" style="font-size: 0.85rem;">{{ $booking['adultCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="bg-white rounded p-1">
                                                            <div class="fw-bold text-warning" style="font-size: 0.85rem;">{{ $booking['childCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Children</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <div class="bg-white rounded p-1">
                                                            <div class="fw-bold text-info" style="font-size: 0.85rem;">{{ $booking['seniorCount'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.6rem;">Seniors</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center mt-1">
                                                    <span class="badge" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); color: white; font-size: 0.65rem; padding: 2px 6px;">
                                                        Total: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) + ($booking['seniorCount'] ?? 0) }} Guests
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Attraction Details -->
                                    <div class="bg-light rounded p-1 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-building-2-line text-white" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Attraction Details</h6>
                                        </div>
                                        <div class="bg-white rounded p-1">
                                            <div class="row g-1">
                                                <div class="col-4">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Ticket ID</small>
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['ticketId'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">NRI Status</small>
                                                    <span class="badge bg-info" style="font-size: 0.65rem;">{{ ucfirst($booking['nri'] ?? 'N/A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ticket & Pricing Details -->
                                    @if(isset($booking['ticket_details']))
                                    <div class="bg-light rounded p-1 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-ticket-line text-white" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Ticket & Pricing</h6>
                                        </div>
                                        
                                        <!-- Pricing Cards -->
                                        <div class="row g-1 mb-1">
                                            <div class="col-4">
                                                <div class="bg-white border rounded p-1 text-center" style="border-color: #28a745 !important;">
                                                    <small class="text-success fw-bold d-block" style="font-size: 0.7rem;">Adult</small>
                                                    <div class="fw-bold text-success" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="bg-white border rounded p-1 text-center" style="border-color: #ffc107 !important;">
                                                    <small class="text-warning fw-bold d-block" style="font-size: 0.7rem;">Child</small>
                                                    <div class="fw-bold text-warning" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="bg-white border rounded p-1 text-center" style="border-color: #17a2b8 !important;">
                                                    <small class="text-info fw-bold d-block" style="font-size: 0.7rem;">Senior</small>
                                                    <div class="fw-bold text-info" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Booking Summary -->
                                        <div class="bg-white rounded p-1 border" style="border-color: #fd9853 !important;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="fw-bold text-dark" style="font-size: 0.75rem;">Booking Summary</small>
                                                    <div class="d-flex gap-1 flex-wrap">
                                                        @if($booking['adultCount'] ?? 0 > 0)
                                                            <span class="badge bg-success" style="font-size: 0.6rem;">{{ $booking['adultCount'] }} × {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</span>
                                                        @endif
                                                        @if($booking['childCount'] ?? 0 > 0)
                                                            <span class="badge bg-warning" style="font-size: 0.6rem;">{{ $booking['childCount'] }} × {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</span>
                                                        @endif
                                                        @if($booking['seniorCount'] ?? 0 > 0)
                                                            <span class="badge bg-info" style="font-size: 0.6rem;">{{ $booking['seniorCount'] }} × {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Total</small>
                                                    <div class="fw-bold" style="font-size: 0.9rem; color: #fd9853;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(isset($booking['ticket_details']['description']) && !empty($booking['ticket_details']['description']))
                                        <!-- Ticket Description -->
                                        <div class="bg-white rounded p-1 mt-1 border-start border-3" style="border-color: #fd9853 !important;">
                                            <small class="fw-bold text-dark d-block" style="font-size: 0.75rem;">Ticket Info</small>
                                            <div class="text-muted" style="font-size: 0.7rem;">{!! $booking['ticket_details']['description'] !!}</div>
                                        </div>
                                        @endif
                                    </div>
                                    @endif

                                    <!-- Transfer Options -->
                                    @if(isset($booking['transfer_options']) && is_array($booking['transfer_options']) && isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'true' || $booking['transfer_options']['transfer_required'] === 'Yes'))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
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
                                                            @if(isset($booking['transfer_options']['pickup_location_name']) && !empty($booking['transfer_options']['pickup_location_name']))
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                    <i class="ri-map-pin-line me-1"></i>{{ $booking['transfer_options']['pickup_location_name'] }}
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
                                                                    $attractionTransferCostDisplay = $booking['transfer_options']['cost'] ?? 0;
                                                                    if (isset($tour) && $tour->is_pro == 1 && isset($booking['transfer_options']['totalPrice'])) {
                                                                        $attractionTransferCostDisplay = $booking['transfer_options']['totalPrice'];
                                                                    }
                                                                @endphp
                                                                @if($attractionTransferCostDisplay > 0)
                                                                <div class="col-12">
                                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Cost</small>
                                                                    <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)$attractionTransferCostDisplay, 2) }}</div>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        @elseif(isset($booking['transfer_options']['vehicle_id']))
                                                            <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle ID</small>
                                                            <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['transfer_options']['vehicle_id'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Guide Options -->
                                    @if(isset($booking['guide_options']) && is_array($booking['guide_options']) && isset($booking['guide_options']['guide_required']) && ($booking['guide_options']['guide_required'] === true || $booking['guide_options']['guide_required'] === 'true' || $booking['guide_options']['guide_required'] === 'Yes'))
                                        <div class="bg-light rounded p-1 mb-2">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ri-user-star-line text-white" style="font-size: 0.7rem;"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Details</h6>
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <div class="row g-1">
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                                <div class="fw-medium text-primary" style="font-size: 0.75rem;">
                                                                    <i class="ri-user-line me-1"></i>{{ $booking['guide_options']['guide_name'] ?? 'N/A' }}
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Duration</small>
                                                                <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['guide_options']['package_hours'] ?? 'N/A' }} Hrs</span>
                                                            </div>
                                                            @if(isset($booking['guide_options']['pickup_time']) && !empty($booking['guide_options']['pickup_time']))
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Time</small>
                                                                <div class="fw-medium text-success" style="font-size: 0.75rem;">
                                                                    @php
                                                                        $displayPickupTime = $booking['guide_options']['pickup_time'] ?? '';
                                                                        if (str_contains($displayPickupTime, ' - ')) {
                                                                            $parts = explode(' - ', $displayPickupTime);
                                                                            $displayPickupTime = $parts[0];
                                                                        }
                                                                        $formattedPickupTime = $displayPickupTime;
                                                                        if (!empty($displayPickupTime)) {
                                                                            try {
                                                                                $timeObj = \Carbon\Carbon::createFromFormat('H:i', $displayPickupTime);
                                                                                $formattedPickupTime = $timeObj->format('h:i A');
                                                                            } catch (\Exception $e) {
                                                                                try {
                                                                                    $timeObj = \Carbon\Carbon::createFromFormat('h:i A', $displayPickupTime);
                                                                                    $formattedPickupTime = $timeObj->format('h:i A');
                                                                                } catch (\Exception $e2) {
                                                                                    try {
                                                                                        $timeObj = \Carbon\Carbon::parse($displayPickupTime);
                                                                                        $formattedPickupTime = $timeObj->format('h:i A');
                                                                                    } catch (\Exception $e3) {}
                                                                                }
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <i class="ri-time-line me-1"></i>{{ $formattedPickupTime }}
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-white rounded p-1">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                                <div class="fw-medium text-primary" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($booking['guide_options']['base_price'] ?? 0, 2) }}</div>
                                                            </div>
                                                            @if(isset($booking['guide_options']['surcharge']) && $booking['guide_options']['surcharge'] > 0)
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                                <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $currency }} {{ number_format($booking['guide_options']['surcharge'], 2) }}</div>
                                                            </div>
                                                            @endif
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Total Guide Cost</small>
                                                                <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format($booking['guide_options']['total_price'] ?? 0, 2) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                            @php $actualBookingIndex++; @endphp
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="ri-building-2-line text-muted" style="font-size: 1.5rem;"></i>
                        </div>
                        <h6 class="text-dark mb-1" style="font-size: 0.9rem;">No Attraction Data Available</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Attraction services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <!-- Compact Footer -->
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
