<!-- Travel Hourly Details Modal -->
@if(isset($svc['travel_hourly']) && $svc['travel_hourly'] > 0)
    <div class="modal fade" id="travel_hourlyDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_hourlyModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
                @php
                    $firstOrder = $serviceData['travel_hourly'][0] ?? null;
                    $firstBookingData = null;
                    if ($firstOrder) {
                        $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                        $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                    }
                @endphp
                
                <!-- Compact Modal Header -->
                <div class="modal-header p-2 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-time-line me-1" style="font-size: 0.9rem;"></i>Local-Tour Hourly - Tour #{{ $tour->tour_id }} • {{ $firstBookingData['city'] ?? 'Location not specified' }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>

                <!-- Compact Modal Body -->
                <div class="modal-body p-2" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['travel_hourly']) && count($serviceData['travel_hourly']) > 0)
                        @foreach($serviceData['travel_hourly'] as $index => $hourlyOrder)
                            @php
                                $hourlyData = is_string($hourlyOrder->data) ? json_decode($hourlyOrder->data, true) : $hourlyOrder->data;
                            @endphp
                            
                            @if(is_array($hourlyData))
                                @php $actualBookingIndex = 0; @endphp
                                @foreach($hourlyData as $bookingIndex => $booking)
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-2">
                                    @endif
                            
                                    <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #667eea !important;">
                                        <!-- Compact Card Header -->
                                        <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                            <div class="row align-items-center g-1">
                                                <div class="col-md-8">
                                                    <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                        <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Hourly Tour Booking' }}
                                                    </h6>
                                                    <small class="text-white opacity-90" style="font-size: 0.7rem;">Hourly Tour {{ $index + 1 }} • {{ ucfirst($booking['type'] ?? 'Standard') }}</small>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                        {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body p-2" style="background-color: #ffffff;">
                                            <!-- Service Schedule & Group Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Hours</small>
                                                                <span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">{{ $booking['selectedHours'] ?? 'N/A' }}H</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                                <span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                        </div>
                                                        <div class="row g-1 mb-1">
                                                            <div class="col-6 text-center">
                                                                <div class="bg-white rounded p-1 border" style="border-color: #667eea !important;">
                                                                    <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 text-center">
                                                                <div class="bg-white rounded p-1 border" style="border-color: #667eea !important;">
                                                                    <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-center">
                                                            <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                                Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                            </span>
                                                        </div>
                                                        @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                        <div class="bg-white rounded p-1 mt-1">
                                                            <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Night Service</small>
                                                            <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pickup Location & Vehicle Information -->
                                            <div class="row mb-2 g-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pickup Location</h6>
                                                        </div>
                                                        <div class="row g-1">
                                                            <div class="col-12">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup Point</small>
                                                                <div class="fw-medium text-truncate" style="font-size: 0.75rem;">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                                <i class="ri-car-line text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Vehicle Details</h6>
                                                        </div>
                                                        <div class="row g-1 mb-2">
                                                            <div class="col-6">
                                                                <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Vehicle</small>
                                                                <div class="fw-medium text-truncate" style="font-size: 0.75rem;" title="{{ $booking['vehicles_name'] ?? 'N/A' }}">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted d-block mb-0" style="font-size: 0.65rem;">Service</small>
                                                                <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['type'] ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                        <!-- Compact Vehicle Image Display -->
                                                        <div class="d-flex justify-content-center align-items-center" style="min-height: 80px; width: 100%; overflow: hidden; position: relative;">
                                                            @if(isset($booking['image']) && $booking['image'])
                                                                <div class="position-relative" style="width: 80px; height: 80px; flex-shrink: 0; overflow: hidden;">
                                                                    <img src="{{ $booking['image'] }}" 
                                                                         alt="Vehicle Image" 
                                                                         class="rounded-circle shadow-sm" 
                                                                         style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #667eea; cursor: pointer; display: block; margin: 0; padding: 0; background: #f8f9fa;"
                                                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm\' style=\'width: 80px; height: 80px; border: 2px solid #e9ecef;\'><i class=\'ri-car-line text-muted\' style=\'font-size: 2rem;\'></i></div>';">
                                                                </div>
                                                            @else
                                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef; flex-shrink: 0;">
                                                                    <i class="ri-car-line text-muted" style="font-size: 2rem;"></i>
                                                                </div>
                                                            @endif
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
                        <div class="text-center py-3">
                            <div class="bg-light rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="ri-time-line text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <h6 class="text-muted mb-0" style="font-size: 0.9rem;">No hourly tour data available</h6>
                        </div>
                    @endif
                </div>

                <!-- Compact Modal Footer -->
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                    <div class="d-flex gap-2 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
