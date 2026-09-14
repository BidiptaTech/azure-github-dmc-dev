<!-- Travel Point Details Modal -->
@if(isset($svc['travel_point']) && $svc['travel_point'] > 0)
    <div class="modal fade" id="travel_pointDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_pointModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                @php
                    $firstOrder = $serviceData['travel_point'][0] ?? null;
                    $firstBookingData = null;
                    $headerFromZone = 'N/A';
                    $headerToZone = 'N/A';
                    
                    if ($firstOrder) {
                        $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                        $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                        
                        // Get zone names for header
                        if(isset($firstBookingData['from_zone_id']) && $firstBookingData['from_zone_id']) {
                            $fromZone = \DB::table('zones')->where('zone_id', $firstBookingData['from_zone_id'])->first();
                            $headerFromZone = $fromZone ? $fromZone->zone_type : 'Zone ' . $firstBookingData['from_zone_id'];
                        }
                        
                        if(isset($firstBookingData['to_zone_id']) && $firstBookingData['to_zone_id']) {
                            $toZone = \DB::table('zones')->where('zone_id', $firstBookingData['to_zone_id'])->first();
                            $headerToZone = $toZone ? $toZone->zone_type : 'Zone ' . $firstBookingData['to_zone_id'];
                        }
                    }
                @endphp
                
                <!-- Modal Header -->
                <div class="modal-header p-2 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-route-line me-1" style="font-size: 0.9rem;"></i>Local-Tour Point to Point - Tour #{{ $tour->tour_id }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-2" style="background: #f8fafc;">
                    @if(isset($serviceData['travel_point']) && count($serviceData['travel_point']) > 0)
                        @foreach($serviceData['travel_point'] as $index => $pointOrder)
                            @php
                                $pointData = is_string($pointOrder->data) ? json_decode($pointOrder->data, true) : $pointOrder->data;
                            @endphp
                            
                            @if(is_array($pointData))
                                @foreach($pointData as $bookingIndex => $booking)
                                    @php
                                        // Fetch zone information
                                        $fromZoneName = 'N/A';
                                        $toZoneName = 'N/A';
                                        
                                        if(isset($booking['from_zone_id']) && $booking['from_zone_id']) {
                                            $fromZone = \DB::table('zones')->where('zone_id', $booking['from_zone_id'])->first();
                                            $fromZoneName = $fromZone ? $fromZone->zone_type : 'Zone ' . $booking['from_zone_id'];
                                        }
                                        
                                        if(isset($booking['to_zone_id']) && $booking['to_zone_id']) {
                                            $toZone = \DB::table('zones')->where('zone_id', $booking['to_zone_id'])->first();
                                            $toZoneName = $toZone ? $toZone->zone_type : 'Zone ' . $booking['to_zone_id'];
                                        }
                                    @endphp
                                    
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-2">
                                    @endif
                            
                                    <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                        <!-- Compact Card Header -->
                                        <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                            <div class="row align-items-center g-1">
                                                <div class="col-md-8">
                                                    <h6 class="card-title mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                        <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Point to Point Transfer' }}
                                                    </h6>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                        {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Compact Card Body -->
                                        <div class="card-body p-2" style="background: #ffffff;">
                                            <!-- Transfer Schedule & Group Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-primary rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Transfer Schedule</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Distance</small>
                                                                <span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                                <span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ $booking['type'] ?? 'Standard' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-success rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Adults</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Children</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['children'] ?? 0 }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Total Guests</small>
                                                                <span class="badge bg-primary px-1 py-0" style="font-size: 0.65rem;">{{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }}</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Night Service Timing</small>
                                                                <div class="fw-medium text-muted" style="font-size: 0.7rem;">{{ $booking['Night_Start_Time'] ?? 'N/A' }} - {{ $booking['Night_End_Time'] ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Route Details -->
                                            <div class="bg-light rounded p-2 mb-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="bg-warning rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-direction-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Details</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-start">
                                                            <div class="bg-success rounded-circle p-1 me-2 mt-1" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-play-circle-line text-white" style="font-size: 0.75rem;"></i>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Location</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                                <small class="text-success" style="font-size: 0.65rem;">Origin</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-start">
                                                            <div class="bg-danger rounded-circle p-1 me-2 mt-1" style="width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-flag-line text-white" style="font-size: 0.75rem;"></i>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Drop-off Location</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                                <small class="text-danger" style="font-size: 0.65rem;">Destination</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Vehicle & Pricing Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-8">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="bg-warning rounded-circle p-1 me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Vehicle Details</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Vehicle Name</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Service Type</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="bg-light rounded p-2 h-100 d-flex align-items-center justify-content-center">
                                                        @if(isset($booking['image']))
                                                            <img src="{{ $booking['image'] }}" 
                                                                 alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                                 class="img-fluid rounded shadow-sm" 
                                                                 style="height: 80px; width: 100%; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                                <i class="ri-car-line ri-32px text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pricing & Zone Summary -->
                                            <div class="bg-light rounded p-2">
                                                <div class="row g-2 align-items-center">
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Total Price</small>
                                                        <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">From Zone</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $fromZoneName }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">To Zone</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $toZoneName }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="ri-route-line text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-muted mb-0" style="font-size: 0.9rem;">No point to point transfer data available</h6>
                        </div>
                    @endif
                </div>

                <!-- Compact Modal Footer -->
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                    <div class="d-flex gap-2 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
