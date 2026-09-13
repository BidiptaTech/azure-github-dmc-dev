<!-- Miscellaneous Details Modal -->
@if(isset($svc['miscellaneous']) && $svc['miscellaneous'] > 0)
    <div class="modal fade" id="miscellaneousDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="miscellaneousDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header border-0 py-2 px-3" style="background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="text-white">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">
                                <i class="ri-file-list-3-line me-1" style="font-size: 0.9rem;"></i>Miscellaneous - Tour #{{ $tour->tour_id }}
                            </h6>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('miscellaneous', {{ $tour->tour_id }})" aria-label="Close" style="font-size: 0.8rem;"></button>
                    </div>
                </div>
                <div class="modal-body p-2" style="background-color: #f8f9fa;">
                    @if(isset($serviceData['miscellaneous']) && count($serviceData['miscellaneous']) > 0)
                        @foreach($serviceData['miscellaneous'] as $index => $miscOrder)
                            @php
                                $miscData = is_string($miscOrder->data) ? json_decode($miscOrder->data, true) : $miscOrder->data;
                            @endphp
                            @if(is_array($miscData))
                                @foreach($miscData as $booking)
                                    <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px; overflow: hidden; border-left: 4px solid #7c3aed !important;">
                                        <div class="card-header border-0 py-2 px-3" style="background: linear-gradient(90deg, #7c3aed 0%, #a78bfa 100%);">
                                            <div class="row align-items-center g-1">
                                                <div class="col-md-8">
                                                    <h6 class="mb-0 fw-bold text-white" style="font-size: 0.85rem;">
                                                        <i class="ri-file-list-3-line me-1"></i>{{ $booking['itemName'] ?? 'Miscellaneous Item' }}
                                                    </h6>
                                                    <small class="text-white opacity-90" style="font-size: 0.7rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</small>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <span class="badge bg-white text-success px-2 py-1" style="font-size: 0.8rem;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-2" style="background-color: #ffffff;">
                                            <div class="row g-2 mb-2">
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Item</small>
                                                        <div class="fw-medium" style="font-size: 0.85rem;">{{ $booking['itemName'] ?? 'N/A' }}</div>
                                                        <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">Date</small>
                                                        <div class="fw-medium" style="font-size: 0.75rem;">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="bg-light rounded p-2">
                                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Pax</small>
                                                        <div class="row g-1">
                                                            <div class="col-4 text-center">
                                                                <div class="bg-white rounded p-1 border">
                                                                    <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $booking['adultsQty'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.6rem;">Adults</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-4 text-center">
                                                                <div class="bg-white rounded p-1 border">
                                                                    <div class="fw-bold text-warning" style="font-size: 0.9rem;">{{ $booking['childQty'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.6rem;">Child</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-4 text-center">
                                                                <div class="bg-white rounded p-1 border">
                                                                    <div class="fw-bold text-info" style="font-size: 0.9rem;">{{ $booking['infantQty'] ?? 0 }}</div>
                                                                    <small class="text-muted" style="font-size: 0.6rem;">Infant</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-light rounded p-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="ri-money-dollar-circle-line text-primary me-2" style="font-size: 0.9rem;"></i>
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.85rem;">Pricing</h6>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center py-1">
                                                    <span class="text-muted" style="font-size: 0.8rem;">Total</span>
                                                    <span class="fw-bold text-success" style="font-size: 1rem;">{{ $currency }} {{ number_format((float)($booking['totalPrice'] ?? 0), 2) }}</span>
                                                </div>
                                                @if(isset($booking['adultSell']) || isset($booking['childSell']) || isset($booking['infantSell']))
                                                <small class="text-muted" style="font-size: 0.7rem;">Adult: {{ $booking['adultSell'] ?? 0 }} / Child: {{ $booking['childSell'] ?? 0 }} / Infant: {{ $booking['infantSell'] ?? 0 }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ri-file-list-3-line text-muted" style="font-size: 2.5rem;"></i>
                            <h6 class="text-muted mt-2 mb-0">No miscellaneous data available</h6>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 py-2 px-3" style="background: #f8f9fa;">
                    <div class="d-flex gap-2 w-100 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1" onclick="closeServiceModal('miscellaneous', {{ $tour->tour_id }})" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
