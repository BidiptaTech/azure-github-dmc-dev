{{--
  Professional Hotel / Attraction / Restaurant / Guide modal (enquiry & follow-up lists).

  @include('bookings.partials.enquiry-service-modal', [
      'tour' => $tour,
      'serviceKey' => 'hotel'|'attraction'|'restaurant'|'guide',
      'serviceData' => $serviceData,
      'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
  ])
--}}
@php
    $serviceKey = $serviceKey ?? 'hotel';
    $pageCurrency = $pageCurrency ?? ($currency ?? 'SGD');

    $enquiryMeta = [
        'hotel' => [
            'title' => 'Hotel Enquiries',
            'icon' => 'ri-hotel-line',
            'emptyTitle' => 'No Hotel Data Available',
            'emptyHint' => 'Hotel services are booked but detailed information is not available.',
            'closeKey' => 'hotel',
            'labelId' => 'hotelDetailsModalLabel',
            'dataKey' => 'hotel',
        ],
        'attraction' => [
            'title' => 'Attraction Enquiries',
            'icon' => 'ri-building-2-line',
            'emptyTitle' => 'No Attraction Data Available',
            'emptyHint' => 'Attraction services are booked but detailed information is not available.',
            'closeKey' => 'attraction',
            'labelId' => 'attractionDetailsModalLabel',
            'dataKey' => 'attraction',
        ],
        'restaurant' => [
            'title' => 'Restaurant Bookings',
            'icon' => 'ri-restaurant-2-line',
            'emptyTitle' => 'No Restaurant Data Available',
            'emptyHint' => 'Restaurant services are booked but detailed information is not available.',
            'closeKey' => 'restaurant',
            'labelId' => 'restaurantDetailsModalLabel',
            'dataKey' => 'restaurant',
        ],
        'guide' => [
            'title' => 'Guide Bookings',
            'icon' => 'ri-user-voice-line',
            'emptyTitle' => 'No Guide Data Available',
            'emptyHint' => 'Guide services are booked but detailed information is not available.',
            'closeKey' => 'guide',
            'labelId' => 'guideDetailsModalLabel',
            'dataKey' => 'guide',
        ],
    ];
    $meta = $enquiryMeta[$serviceKey] ?? $enquiryMeta['hotel'];
    $dataKey = $meta['dataKey'];
    $ordersList = $serviceData[$dataKey] ?? [];
@endphp

@if(isset($svc[$dataKey]) && $svc[$dataKey] > 0)
<div class="modal fade svc-modal" id="{{ $dataKey }}DetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="{{ $meta['labelId'] }}{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $meta['labelId'] }}{{ $tour->tour_id }}">
                    <i class="{{ $meta['icon'] }} me-1"></i>{{ $meta['title'] }} — Tour #{{ $tour->tour_id }}
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('{{ $meta['closeKey'] }}', {{ $tour->tour_id }})" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(is_array($ordersList) && count($ordersList) > 0)
                    @foreach($ordersList as $index => $serviceOrder)
                        @php
                            $currency = \App\Helpers\CommonHelper::resolveOrderDisplayCurrency($serviceOrder, $pageCurrency);
                            $orderCountry = trim((string) ($serviceOrder->resolved_service_country ?? $serviceOrder->country ?? ''));
                            $payload = is_string($serviceOrder->data) ? json_decode($serviceOrder->data, true) : $serviceOrder->data;
                        @endphp

                        @if(is_array($payload))
                            @foreach($payload as $booking)
                                @if(!is_array($booking))
                                    @continue
                                @endif

                                <div class="svc-country-item" data-service-country="{{ $orderCountry !== '' ? $orderCountry : 'Other' }}">
                                @if($serviceKey === 'hotel')
                                    @include('bookings.partials.enquiry-service-cards.hotel', [
                                        'tour' => $tour,
                                        'booking' => $booking,
                                        'currency' => $currency,
                                        'orderCountry' => $orderCountry,
                                        'index' => $index,
                                    ])
                                @elseif($serviceKey === 'attraction')
                                    @include('bookings.partials.enquiry-service-cards.attraction', [
                                        'tour' => $tour,
                                        'booking' => $booking,
                                        'currency' => $currency,
                                        'orderCountry' => $orderCountry,
                                        'index' => $index,
                                    ])
                                @elseif($serviceKey === 'restaurant')
                                    @include('bookings.partials.enquiry-service-cards.restaurant', [
                                        'tour' => $tour,
                                        'booking' => $booking,
                                        'currency' => $currency,
                                        'orderCountry' => $orderCountry,
                                        'index' => $index,
                                    ])
                                @elseif($serviceKey === 'guide')
                                    @include('bookings.partials.enquiry-service-cards.guide', [
                                        'tour' => $tour,
                                        'booking' => $booking,
                                        'currency' => $currency,
                                        'orderCountry' => $orderCountry,
                                        'index' => $index,
                                    ])
                                @endif
                                </div>
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
                <button type="button" class="btn btn-outline-secondary btn-sm svc-footer-btn" onclick="closeServiceModal('{{ $meta['closeKey'] }}', {{ $tour->tour_id }})">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
