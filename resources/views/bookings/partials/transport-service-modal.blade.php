{{--
  Professional Arrival / Departure / Local Transport modal.

  @include('bookings.partials.transport-service-modal', [
      'tour' => $tour,
      'serviceKey' => 'entry_port'|'exit_port'|'local_transport',
      'serviceData' => $serviceData,
      'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
      'showTransportActions' => true|false,
  ])
--}}
@php
    $serviceKey = $serviceKey ?? 'entry_port';
    $showTransportActions = $showTransportActions ?? false;
    $pageCurrency = $pageCurrency ?? ($currency ?? 'SGD');

    $transportMeta = [
        'entry_port' => [
            'title' => 'Arrival Transfer',
            'icon' => 'ri-flight-land-line',
            'label' => 'Arrival',
            'subtitlePrefix' => 'Arrival',
            'emptyTitle' => 'No Arrival Transfer Data Available',
            'emptyHint' => 'Entry port services are booked but detailed information is not available.',
            'editFn' => 'editArrivalBooking',
            'rejectFn' => 'rejectArrivalBooking',
            'modalLabelId' => 'entry_portDetailsModalLabel',
        ],
        'exit_port' => [
            'title' => 'Departure Transfer',
            'icon' => 'ri-flight-takeoff-line',
            'label' => 'Departure',
            'subtitlePrefix' => 'Departure',
            'emptyTitle' => 'No Departure Transfer Data Available',
            'emptyHint' => 'Exit port services are booked but detailed information is not available.',
            'editFn' => 'editDepartureBooking',
            'rejectFn' => 'rejectDepartureBooking',
            'modalLabelId' => 'exit_portDetailsModalLabel',
        ],
        'local_transport' => [
            'title' => 'Local Transport',
            'icon' => 'ri-car-line',
            'label' => 'Local',
            'subtitlePrefix' => 'Local Transport',
            'emptyTitle' => 'No Local Transport Data Available',
            'emptyHint' => 'Local transport services are booked but detailed information is not available.',
            'editFn' => 'editIndividualLocalTransport',
            'rejectFn' => 'rejectIndividualLocalTransport',
            'modalLabelId' => 'local_transportModalLabel',
        ],
    ];
    $meta = $transportMeta[$serviceKey] ?? $transportMeta['entry_port'];
    $ordersList = $serviceData[$serviceKey] ?? [];
    $roleId = auth()->user()->role_id ?? null;
    $canEditTransport = in_array((int) $roleId, [11, 34, 124, 125, 128, 131, 132, 134, 135, 137, 138], true);
    $canRejectTransport = in_array((int) $roleId, [11, 34, 33, 37, 38, 124, 125, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138], true);
    $tourTypeIs1 = isset($tour->tour_type) && (int) $tour->tour_type === 1;
@endphp

@if(isset($svc[$serviceKey]) && $svc[$serviceKey] > 0)
<div class="modal fade svc-modal" id="{{ $serviceKey }}DetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="{{ $meta['modalLabelId'] }}{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $meta['modalLabelId'] }}{{ $tour->tour_id }}">
                    <i class="{{ $meta['icon'] }} me-1"></i>{{ $meta['title'] }} — Tour #{{ $tour->tour_id }}
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('{{ $serviceKey }}', {{ $tour->tour_id }})" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(is_array($ordersList) && count($ordersList) > 0)
                    @foreach($ordersList as $index => $transportOrder)
                        @php
                            $currency = \App\Helpers\CommonHelper::resolveOrderDisplayCurrency($transportOrder, $pageCurrency);
                            $orderCountry = trim((string) ($transportOrder->resolved_service_country ?? $transportOrder->country ?? ''));
                            $payload = is_string($transportOrder->data) ? json_decode($transportOrder->data, true) : $transportOrder->data;
                        @endphp

                        @if(is_array($payload))
                            @php $actualBookingIndex = 0; @endphp
                            @foreach($payload as $originalKey => $booking)
                                @if(!is_array($booking))
                                    @php $actualBookingIndex++; @endphp
                                    @continue
                                @endif
                                @php
                                    $bookingIndex = $actualBookingIndex;

                                    $adults = (int) ($booking['adults'] ?? 0);
                                    $children = (int) ($booking['children'] ?? 0);
                                    $guestTotal = $adults + $children;

                                    $transferPrice = (float) ($booking['totalPrice'] ?? 0);
                                    $guideCost = 0.0;
                                    $go = (isset($booking['guide_options']) && is_array($booking['guide_options'])) ? $booking['guide_options'] : [];
                                    if (!empty($go)) {
                                        $guideCost = (float) ($go['cost'] ?? $go['Cost'] ?? $go['sell'] ?? $go['Sell'] ?? $go['total_price'] ?? 0);
                                    }
                                    $cardTotal = $transferPrice;
                                    if ((int) ($tour->is_pro ?? 0) === 1 && $guideCost > 0) {
                                        $cardTotal += $guideCost;
                                    }

                                    $bookingDateRaw = $booking['bookingDate'] ?? null;
                                    try {
                                        $bookingDateLabel = $bookingDateRaw ? \Carbon\Carbon::parse($bookingDateRaw)->format('M d, Y') : 'N/A';
                                    } catch (\Throwable $e) {
                                        $bookingDateLabel = (string) ($bookingDateRaw ?: 'N/A');
                                    }
                                    $timeLabel = $booking['entrytime'] ?? ($booking['time'] ?? 'TBC');
                                    $typeLabel = ucfirst((string) ($booking['type'] ?? 'Standard'));
                                    $vehicleName = $booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? 'Vehicle Transfer');

                                    if ($serviceKey === 'exit_port') {
                                        $pickup = $tourTypeIs1
                                            ? ($booking['pickupLocation'] ?? $booking['exitpickup'] ?? 'N/A')
                                            : ($booking['exitpickup'] ?? $booking['pickupLocation'] ?? 'N/A');
                                        $dropoff = $tourTypeIs1
                                            ? ($booking['dropoffLocation'] ?? $booking['exitdropoff'] ?? 'N/A')
                                            : ($booking['exitdropoff'] ?? $booking['dropoffLocation'] ?? 'N/A');
                                    } elseif ($serviceKey === 'local_transport') {
                                        $pickup = $booking['entrypickup'] ?? ($booking['pickupLocation'] ?? ($booking['pickup'] ?? 'N/A'));
                                        $dropoff = $booking['dropoffLocation'] ?? ($booking['entrydropoff'] ?? ($booking['dropoff'] ?? 'N/A'));
                                    } else {
                                        $pickup = $booking['entrypickup'] ?? ($booking['pickupLocation'] ?? 'N/A');
                                        $dropoff = $booking['entrydropoff'] ?? ($booking['dropoffLocation'] ?? 'N/A');
                                    }

                                    $fromZoneName = null;
                                    $toZoneName = null;
                                    if ($serviceKey === 'local_transport') {
                                        $fromZoneName = 'N/A';
                                        $toZoneName = 'N/A';
                                        if (!empty($booking['from_zone_id'])) {
                                            $fromZone = \DB::table('zones')->where('zone_id', $booking['from_zone_id'])->first();
                                            $fromZoneName = $fromZone ? $fromZone->zone_type : ('Zone ' . $booking['from_zone_id']);
                                        }
                                        if (!empty($booking['to_zone_id'])) {
                                            $toZone = \DB::table('zones')->where('zone_id', $booking['to_zone_id'])->first();
                                            $toZoneName = $toZone ? $toZone->zone_type : ('Zone ' . $booking['to_zone_id']);
                                        }
                                        if (($pickup === 'N/A' || $pickup === '') && $fromZoneName) {
                                            $pickup = $fromZoneName;
                                        }
                                        if (($dropoff === 'N/A' || $dropoff === '') && $toZoneName) {
                                            $dropoff = $toZoneName;
                                        }
                                    }

                                    $cityLabel = trim((string) ($booking['city'] ?? ''));
                                    if ($cityLabel === '') {
                                        $cityLabel = 'N/A';
                                    }
                                    $countryLabel = $orderCountry !== '' ? $orderCountry : trim((string) ($booking['country'] ?? ''));
                                    if ($countryLabel === '') {
                                        $countryLabel = 'N/A';
                                    }

                                    $hasGuide = !empty($go) && (
                                        !empty($go['guide_required']) ||
                                        !empty($go['guideName']) ||
                                        !empty($go['guide_name']) ||
                                        !empty($go['name']) ||
                                        $guideCost > 0
                                    );
                                    $guideName = $go['guideName'] ?? ($go['guide_name'] ?? ($go['name'] ?? 'N/A'));
                                    if (is_array($guideName)) {
                                        $guideName = implode(', ', $guideName);
                                    }
                                    $guideService = $go['serviceType'] ?? ($go['service_type'] ?? 'N/A');
                                    if (is_array($guideService)) {
                                        $guideService = implode(', ', $guideService);
                                    }
                                    $guideLang = $go['language'] ?? ($go['languages'] ?? 'N/A');
                                    if (is_array($guideLang)) {
                                        $guideLang = implode(', ', $guideLang);
                                    }
                                    $guideHours = $go['hours'] ?? ($go['service_hours'] ?? 'N/A');
                                    if (is_array($guideHours)) {
                                        $guideHours = implode(', ', $guideHours);
                                    }
                                    $guideActivity = $go['tour_activity'] ?? ($go['tourActivity'] ?? ($go['Activity'] ?? ''));

                                    $vehicleImage = $booking['image'] ?? null;
                                    $distanceLabel = $booking['distance'] ?? null;
                                    $subtitleBits = $meta['subtitlePrefix'] . ' ' . ($index + 1) . ' • ' . $typeLabel;
                                    if ($serviceKey === 'local_transport' && $distanceLabel !== null && $distanceLabel !== '') {
                                        $subtitleBits = $meta['subtitlePrefix'] . ' ' . ($index + 1) . ' • ' . $distanceLabel . ' km • ' . $typeLabel;
                                    }
                                @endphp

                                <div class="svc-panel svc-country-item" data-service-country="{{ $orderCountry !== '' ? $orderCountry : 'Other' }}">
                                    <div class="svc-panel-head">
                                        <div class="svc-panel-head-main">
                                            @if(!empty($vehicleImage))
                                                <img src="{{ $vehicleImage }}" alt="" class="svc-thumb" onerror="this.style.display='none'">
                                            @else
                                                <div class="svc-thumb svc-thumb-fallback"><i class="ri-car-line"></i></div>
                                            @endif
                                            <div>
                                                <p class="svc-title">{{ $vehicleName }}</p>
                                                <p class="svc-subtitle">{{ $subtitleBits }}</p>
                                            </div>
                                        </div>
                                        <div class="svc-price">{{ $currency }} {{ number_format($cardTotal, 2) }}</div>
                                    </div>

                                    <div class="svc-section mb-0" style="border:0;border-radius:0;">
                                        <p class="svc-section-title">Service Schedule</p>
                                        <div class="svc-dl">
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Date</span>
                                                <span class="svc-dl-value">{{ $bookingDateLabel }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Time</span>
                                                <span class="svc-dl-value">{{ $timeLabel }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Type</span>
                                                <span class="svc-dl-value">{{ $typeLabel }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Transfer</span>
                                                <span class="svc-dl-value">{{ $meta['label'] }}</span>
                                            </div>
                                            @if($serviceKey === 'local_transport' && $distanceLabel !== null && $distanceLabel !== '')
                                            <div class="svc-dl-row full">
                                                <span class="svc-dl-label">Distance</span>
                                                <span class="svc-dl-value">{{ $distanceLabel }} km</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                        <p class="svc-section-title">Group Information</p>
                                        <div class="svc-guest-grid">
                                            <div class="svc-guest-box">
                                                <div class="num">{{ $adults }}</div>
                                                <div class="lbl">Adults</div>
                                            </div>
                                            <div class="svc-guest-box">
                                                <div class="num">{{ $children }}</div>
                                                <div class="lbl">Children</div>
                                            </div>
                                        </div>
                                        <div class="svc-total-bar">Total: {{ $guestTotal }} Guest{{ $guestTotal === 1 ? '' : 's' }}</div>
                                    </div>

                                    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                        <p class="svc-section-title">Route Information</p>
                                        <div class="svc-dl">
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Pickup</span>
                                                <span class="svc-dl-value"><i class="ri-map-pin-line me-1" style="color:var(--svc-accent);"></i>{{ $pickup }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Dropoff</span>
                                                <span class="svc-dl-value"><i class="ri-map-pin-2-line me-1" style="color:var(--svc-danger);"></i>{{ $dropoff }}</span>
                                            </div>
                                            @if($serviceKey === 'local_transport')
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">From Zone</span>
                                                <span class="svc-dl-value">{{ $fromZoneName ?? 'N/A' }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">To Zone</span>
                                                <span class="svc-dl-value">{{ $toZoneName ?? 'N/A' }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($hasGuide)
                                    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                        <p class="svc-section-title">Guide Details</p>
                                        <div class="svc-dl">
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Guide</span>
                                                <span class="svc-dl-value">{{ $guideName }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Service</span>
                                                <span class="svc-dl-value">{{ $guideService }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Language</span>
                                                <span class="svc-dl-value">{{ $guideLang }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Hours</span>
                                                <span class="svc-dl-value">{{ $guideHours }}{{ is_numeric($guideHours) ? ' H' : '' }}</span>
                                            </div>
                                            @if($guideActivity !== '' && $guideActivity !== null)
                                            <div class="svc-dl-row full">
                                                <span class="svc-dl-label">Activity</span>
                                                <span class="svc-dl-value">{{ $guideActivity }}</span>
                                            </div>
                                            @endif
                                            @if($guideCost > 0)
                                            <div class="svc-dl-row full">
                                                <span class="svc-dl-label">Guide Cost</span>
                                                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($guideCost, 2) }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                        <p class="svc-section-title">Vehicle &amp; Location</p>
                                        <div class="svc-dl">
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Vehicle</span>
                                                <span class="svc-dl-value">{{ $vehicleName }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Service</span>
                                                <span class="svc-dl-value">{{ $typeLabel }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">City</span>
                                                <span class="svc-dl-value">{{ $cityLabel }}</span>
                                            </div>
                                            <div class="svc-dl-row">
                                                <span class="svc-dl-label">Country</span>
                                                <span class="svc-dl-value">{{ $countryLabel }}</span>
                                            </div>
                                            @if(!empty($booking['Night_Start_Time']) && !empty($booking['Night_End_Time']))
                                            <div class="svc-dl-row full">
                                                <span class="svc-dl-label">Night Service</span>
                                                <span class="svc-dl-value">{{ $booking['Night_Start_Time'] }} – {{ $booking['Night_End_Time'] }}</span>
                                            </div>
                                            @endif
                                            <div class="svc-dl-row full">
                                                <span class="svc-dl-label">Total Price</span>
                                                <span class="svc-dl-value svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($cardTotal, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if(!empty($booking['specialRequests']))
                                    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                        <p class="svc-section-title">Special Requests</p>
                                        <div class="svc-dl">
                                            <div class="svc-dl-row full">
                                                <span class="svc-dl-value">{{ $booking['specialRequests'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    @if($showTransportActions)
                                    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                        <p class="svc-section-title">Booking Status</p>
                                        <div class="svc-actions">
                                            @php $isTransportApproved = (int) ($transportOrder->is_approve ?? 0) === 1; @endphp
                                            @if($isTransportApproved && $serviceKey !== 'local_transport')
                                                <span class="svc-status-pill is-approved"><i class="ri-check-line"></i> Approved
                                                    @if(!empty($transportOrder->reference_id))
                                                        · Ref: {{ $transportOrder->reference_id }}
                                                    @endif
                                                    @if(!empty($transportOrder->display_due_date))
                                                        · Due: {{ \Carbon\Carbon::parse($transportOrder->display_due_date)->format('d-m-Y') }}
                                                    @endif
                                                </span>
                                            @elseif($canEditTransport || $canRejectTransport)
                                                @if($isTransportApproved && $serviceKey === 'local_transport')
                                                    <span class="svc-status-pill is-approved me-1"><i class="ri-check-line"></i> Approved</span>
                                                @endif
                                                @if($canEditTransport)
                                                <button type="button" class="btn btn-sm svc-btn svc-btn-edit"
                                                        onclick="{{ $meta['editFn'] }}({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})">
                                                    <i class="ri-edit-line me-1"></i>Edit
                                                </button>
                                                @endif
                                                @if($canRejectTransport)
                                                <button type="button" class="btn btn-sm svc-btn svc-btn-reject"
                                                        onclick="{{ $meta['rejectFn'] }}({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})">
                                                    <i class="ri-close-line me-1"></i>Reject
                                                </button>
                                                @endif
                                            @else
                                                <span class="svc-status-pill"><i class="ri-time-line"></i> Pending approval</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                @php $actualBookingIndex++; @endphp
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="{{ $meta['icon'] }} text-muted" style="font-size:1.75rem;"></i>
                        <h6 class="text-dark mt-2 mb-1">{{ $meta['emptyTitle'] }}</h6>
                        <p class="text-muted mb-0" style="font-size:0.85rem;">{{ $meta['emptyHint'] }}</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm svc-footer-btn" onclick="closeServiceModal('{{ $serviceKey }}', {{ $tour->tour_id }})">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
