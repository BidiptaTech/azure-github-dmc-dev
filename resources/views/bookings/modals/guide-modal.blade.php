<!-- Guide Details Modal -->
@if(isset($svc['guide']) && $svc['guide'] > 0)
<div class="modal fade" id="guideDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="guideDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <!-- Compact Header -->
            <div class="modal-header border-0 py-2 px-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%);">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="text-white">
                        <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                            <i class="ri-user-voice-line me-1" style="font-size: 0.9rem;"></i>Guide Bookings - Tour #{{ $tour->tour_id }}
                        </h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            
            <div class="modal-body p-2" style="background-color: #f8f9fa;">
                @if(isset($serviceData['guide']) && count($serviceData['guide']) > 0)
                    @foreach($serviceData['guide'] as $index => $guideOrder)
                    @php
                        $guideData = is_string($guideOrder->data) ? json_decode($guideOrder->data, true) : $guideOrder->data;
                    @endphp
                    
                    @if(is_array($guideData))
                        @foreach($guideData as $booking)
                            <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #00cec9 !important;">
                                <!-- Compact Card Header -->
                                <div class="card-header border-0 py-1 px-2" style="background: linear-gradient(90deg, #00cec9 0%, #55a3ff 100%);">
                                    <div class="row align-items-center g-1">
                                        <div class="col-md-8">
                                            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                <i class="ri-user-voice-line me-1" style="font-size: 0.8rem;"></i>{{ $booking['guide_name'] ?? 'Guide Booking' }}
                                            </h6>
                                            <small class="text-white opacity-90" style="font-size: 0.7rem;">Guide Service • {{ $booking['hours'] ?? 'N/A' }}H</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">
                                                {{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2" style="background-color: #ffffff;">
                                    <!-- Guide Information & Image -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-8">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-user-voice-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Guide Information</h6>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Guide Name</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ $booking['guide_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                        <div class="fw-medium text-success" style="font-size: 0.75rem;">{{ $currency }} {{ number_format((float)($booking['basePrice'] ?? 0), 2) }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                        <div class="fw-medium text-warning" style="font-size: 0.75rem;">{{ $currency }} {{ number_format((float)($booking['surcharge'] ?? 0), 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @if(isset($booking['image']))
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <img src="{{ $booking['image'] }}" 
                                                         alt="{{ $booking['guide_name'] ?? 'Guide' }}" 
                                                         class="rounded-circle shadow-sm" 
                                                         style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #00cec9;">
                                                </div>
                                            @else
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 2px solid #e9ecef;">
                                                        <i class="ri-user-voice-line text-muted" style="font-size: 2rem;"></i>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Service Schedule & Group Information -->
                                    <div class="row mb-2 g-2">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
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
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Duration</small>
                                                        <span class="badge" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); color: white; font-size: 0.65rem; padding: 2px 4px;">{{ $booking['hours'] ?? 'N/A' }}H</span>
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
                                        <div class="col-md-6">
                                            <div class="bg-light rounded p-2 h-100">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ri-group-line text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Group Information</h6>
                                                </div>
                                                <div class="row g-1 mb-1">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #00cec9 !important;">
                                                            <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adults'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-white rounded p-1 border" style="border-color: #00cec9 !important;">
                                                            <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['children'] ?? 0 }}</div>
                                                            <small class="text-muted" style="font-size: 0.55rem;">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); color: white; font-size: 0.7rem; padding: 2px 4px;">
                                                        Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Guests
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pricing Breakdown -->
                                    <div class="bg-light rounded p-1 mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="rounded-circle p-1 me-1" style="background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ri-money-dollar-circle-line text-white" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing Breakdown</h6>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-md-4">
                                                <div class="text-center p-1 border rounded bg-white" style="border-color: #28a745 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Base Price</small>
                                                    <div class="fw-bold text-success" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)($booking['basePrice'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center p-1 border rounded bg-white" style="border-color: #ffc107 !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Surcharge</small>
                                                    <div class="fw-bold text-warning" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)($booking['surcharge'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center p-1 border rounded bg-white" style="border-color: #00cec9 !important; background: linear-gradient(135deg, rgba(0,206,201,0.1) 0%, rgba(85,163,255,0.1) 100%) !important;">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Total Amount</small>
                                                    <div class="fw-bold" style="font-size: 0.9rem; color: #00cec9;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-user-voice-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Guide Data Available</h4>
                        <p class="text-muted mb-4">Guide services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <!-- Compact Footer -->
            <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
