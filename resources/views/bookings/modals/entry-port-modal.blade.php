<!-- Entry Port (Arrival) Details Modal -->
@if(isset($svc['entry_port']) && $svc['entry_port'] > 0)
<div class="modal fade" id="entry_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="entry_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header p-2 border-0" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-flight-land-line me-1" style="font-size: 0.9rem;"></i>Arrival Transfer - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['entry_port']) && count($serviceData['entry_port']) > 0)
                    @foreach($serviceData['entry_port'] as $index => $entryOrder)
                    @php
                        $entryData = is_string($entryOrder->data) ? json_decode($entryOrder->data, true) : $entryOrder->data;
                    @endphp
                    
                    @if(is_array($entryData))
                        @foreach($entryData as $booking)
                            @php
                                $entryTransferPrice = (float)($booking['totalPrice'] ?? 0);
                                $entryGuidePrice = 0;
                                if (isset($booking['guide_options']) && is_array($booking['guide_options'])) {
                                    $entryGuidePrice = (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0);
                                }
                                $entryCardTotal = $entryTransferPrice + $entryGuidePrice;
                            @endphp
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #00b894 !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #00b894 0%, #55a3ff 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-car-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">Arrival {{ $index + 1 }} • {{ ucfirst($booking['type'] ?? 'Standard') }}@if($entryGuidePrice > 0) • With Guide @endif</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                {{ $currency }} {{ number_format($entryCardTotal, 2) }}
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
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-calendar-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Service Schedule</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Date</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Time</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['entrytime'] ?? 'TBC' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Type</small>
                                                        <div><span class="badge bg-warning px-1 py-0" style="font-size: 0.65rem;">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Transfer</small>
                                                        <div><span class="badge bg-info px-1 py-0" style="font-size: 0.65rem;">Arrival</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #00b894 !important;">
                                                            <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #00b894 !important;">
                                                            <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                        Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Route Information -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-route-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Route Information</h6>
                                        </div>
                                        <div class="row g-1 mb-1">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Pickup</small>
                                                    <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                        <i class="ri-map-pin-line text-success me-1" style="font-size: 0.7rem;"></i>
                                                        <span class="text-truncate">{{ $booking['entrypickup'] ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Dropoff</small>
                                                    <div class="fw-medium d-flex align-items-center" style="font-size: 0.75rem;">
                                                        <i class="ri-map-pin-2-line text-danger me-1" style="font-size: 0.7rem;"></i>
                                                        <span class="text-truncate">{{ $booking['entrydropoff'] ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Compact Route Direction Visual -->
                                        <div class="d-flex align-items-center justify-content-center p-1 bg-white rounded">
                                            <span class="badge bg-success me-1" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['entrypickup'] ?? 'Pickup', 15) }}</span>
                                            <i class="ri-arrow-right-line text-primary mx-1" style="font-size: 0.8rem;"></i>
                                            <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 4px;">{{ Str::limit($booking['entrydropoff'] ?? 'Dropoff', 15) }}</span>
                                        </div>
                                    </div>

                                    <!-- Vehicle & Location Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100" style="overflow: hidden;">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
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
                                                                 style="width: 80px; height: 80px; object-fit: cover; object-position: center; border: 2px solid #00b894; cursor: pointer; display: block; margin: 0; padding: 0; background: #f8f9fa;"
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
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-map-pin-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Location Information</h6>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">City</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['city'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Country</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['country'] ?? 'N/A' }}</div>
                                                    </div>
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

                                    <!-- Arrival: Price breakdown (Transfer + Guide then Total) -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing</h6>
                                        </div>
                                        <div class="bg-white rounded p-2">
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <span class="text-muted" style="font-size: 0.8rem;">Transfer</span>
                                                <span class="fw-semibold text-dark">{{ $currency }} {{ number_format($entryTransferPrice, 2) }}</span>
                                            </div>
                                            @if($entryGuidePrice > 0)
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <span class="text-muted" style="font-size: 0.8rem;">Guide</span>
                                                <span class="fw-semibold text-info">{{ $currency }} {{ number_format($entryGuidePrice, 2) }}</span>
                                            </div>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center py-2 mt-1">
                                                <span class="fw-bold text-dark" style="font-size: 0.9rem;">Total</span>
                                                <span class="fw-bold text-success" style="font-size: 1rem;">{{ $currency }} {{ number_format($entryCardTotal, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $hasEntryGuide = isset($booking['guide_options']) && is_array($booking['guide_options']) && (
                                            !empty($booking['guide_options']['guide_required']) ||
                                            !empty($booking['guide_options']['guideName']) ||
                                            !empty($booking['guide_options']['guide_name']) ||
                                            !empty($booking['guide_options']['name']) ||
                                            (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['sell'] ?? 0) > 0
                                        );
                                    @endphp
                                    @if($hasEntryGuide)
                                    <!-- Guide Details (Arrival) -->
                                    <div class="bg-light rounded p-2 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-user-voice-line text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Details</h6>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_options']['guideName'] ?? $booking['guide_options']['guide_name'] ?? $booking['guide_options']['name'] ?? 'N/A' }}</div>
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">Service</small>
                                                    <span class="badge bg-info" style="font-size: 0.65rem;">{{ $booking['guide_options']['serviceType'] ?? $booking['guide_options']['service_type'] ?? 'N/A' }}</span>
                                                    <span class="badge bg-success ms-1" style="font-size: 0.65rem;">{{ $booking['guide_options']['language'] ?? $booking['guide_options']['languages'] ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-white rounded p-1">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Hours / Activity</small>
                                                    <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_options']['hours'] ?? $booking['guide_options']['service_hours'] ?? 'N/A' }} H</div>
                                                    @php $arrivalGuideCost = (float)($booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0); @endphp
                                                    @if($arrivalGuideCost > 0)
                                                    <div class="fw-bold text-success mt-1" style="font-size: 0.85rem;">{{ $currency }} {{ number_format($arrivalGuideCost, 2) }}</div>
                                                    @endif
                                                </div>
                                            </div>
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
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100%;">
                            <i class="ri-flight-land-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Arrival Transfer Data Available</h4>
                        <p class="text-muted mb-4">Entry port services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
