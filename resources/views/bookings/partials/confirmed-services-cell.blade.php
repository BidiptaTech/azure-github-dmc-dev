{{--
  Confirmed bookings Services cell: country tabs + per-booking icons.

  Parent must assign returned vars for Payment Status column:
  @php
      $serviceCell = \App\Helpers\CommonHelper::buildTourServiceCellData(
          $tour,
          $serviceCountryScope ?? ['restricted' => false, 'countries' => []],
          'booking'
      );
      $orders = $serviceCell['orders'];
      $svc = $serviceCell['svc'];
      $serviceData = $serviceCell['serviceData'];
      $tabCountries = $serviceCell['tabCountries'];
      $svcByCountry = $serviceCell['svcByCountry'];
      $orderCountryMap = $serviceCell['orderCountryMap'];
      $pageCurrency = $currency ?? 'SGD';
      $debugInfo = [
          'tour_id' => $tour->tour_id,
          'orders_count' => $orders->count(),
          'svc' => $svc,
          'serviceData_keys' => array_keys($serviceData),
      ];
  @endphp
  @include('bookings.partials.confirmed-services-cell')
--}}
@php
    $icons = [
        'hotel' => 'ri-hotel-bed-line',
        'attraction' => 'ri-camera-line',
        'restaurant' => 'ri-restaurant-2-line',
        'guide' => 'ri-user-voice-line',
        'entry_port' => 'ri-flight-land-line',
        'exit_port' => 'ri-flight-takeoff-line',
        'travel_hourly' => 'ri-time-line',
        'travel_point' => 'ri-route-line',
        'local_transport' => 'ri-car-line',
        'miscellaneous' => 'ri-list-check-2',
    ];
    $serviceLabels = [
        'hotel' => 'Hotel',
        'attraction' => 'Attraction',
        'restaurant' => 'Restaurant',
        'guide' => 'Guide',
        'entry_port' => 'Arrival',
        'exit_port' => 'Departure',
        'travel_hourly' => 'Local-Tour Hourly',
        'travel_point' => 'Local-Tour Point to Point',
        'local_transport' => 'Local Transport',
        'miscellaneous' => 'Miscellaneous',
    ];
    $serviceColors = [
        'hotel' => '#4338ca',
        'attraction' => '#0f766e',
        'restaurant' => '#c2410c',
        'guide' => '#475569',
        'entry_port' => '#047857',
        'exit_port' => '#0369a1',
        'travel_hourly' => '#b45309',
        'travel_point' => '#5b21b6',
        'local_transport' => '#334155',
        'miscellaneous' => '#7c3aed',
    ];

    $tabCountries = $tabCountries ?? [];
    $svcByCountry = $svcByCountry ?? [];
    $serviceData = $serviceData ?? [];
    $orderCountryMap = $orderCountryMap ?? [];
    $debugInfo = $debugInfo ?? ['tour_id' => $tour->tour_id];
    $defaultCountry = $tabCountries[0] ?? null;
    $showTabs = count($tabCountries) > 1;
    $scope = $serviceCountryScope ?? ['restricted' => false, 'countries' => []];

    $orderCountryOf = function ($order) use ($orderCountryMap, $tabCountries) {
        return \App\Helpers\CommonHelper::resolveOrderServiceTabCountry($order, $orderCountryMap, $tabCountries);
    };
@endphp

<div class="services-country-cell"
     data-tour-id="{{ $tour->tour_id }}"
     data-selected-country="{{ $defaultCountry ?? '' }}">
    @if(!empty($tabCountries))
        @if($showTabs || !empty($scope['restricted']))
            <div class="services-country-tabs" role="tablist" aria-label="Service countries">
                @foreach($tabCountries as $index => $country)
                    <button type="button"
                            class="services-country-tab{{ $index === 0 ? ' is-active' : '' }}"
                            role="tab"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            data-country="{{ $country }}"
                            title="{{ $country }}"
                            onclick="selectServiceCountryTab(this)">
                        {{ $country }}
                    </button>
                @endforeach
            </div>
        @endif

        @foreach($tabCountries as $index => $country)
            @php $countrySvc = $svcByCountry[$country] ?? []; @endphp
            <div class="services-icons-wrap services-country-panel{{ $index === 0 ? ' is-active' : '' }}"
                 data-country="{{ $country }}"
                 @if($index !== 0) hidden @endif>
                @foreach($countrySvc as $key => $count)
                    @if(intval($count) > 0)
                        @php $bgColor = $serviceColors[$key] ?? '#6c757d'; @endphp
                        @if($key === 'restaurant')
                            @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                                @php $globalRestaurantCounter = 1; @endphp
                                @foreach($serviceData['restaurant'] as $restaurantOrderIndex => $restaurantOrder)
                                    @continue(strcasecmp($orderCountryOf($restaurantOrder), $country) !== 0)
                                    @php $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data; @endphp
                                    @if(is_array($restaurantData))
                                        @php $actualBookingIndex = 0; @endphp
                                        @foreach($restaurantData as $originalKey => $booking)
                                            @php
                                                $restaurantName = $booking['restaurantName'] ?? 'Restaurant';
                                                $actualCancelDateStr = $tour->auto_cancel_date ? \Carbon\Carbon::parse($tour->auto_cancel_date)->format('Y-m-d') : '';
                                                $tooltipText = 'Restaurant ' . $globalRestaurantCounter . ': ' . $restaurantName . ' (' . $country . ')';
                                                $isApproved = $restaurantOrder->is_approve == 1;
                                            @endphp
                                            <span class="service-icon-wrapper" data-tooltip="{{ e($tooltipText) }}">
                                                <span class="service-icon-badge @if($isApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                                      onclick="openIndividualRestaurantModal({{ $tour->tour_id }}, {{ $restaurantOrderIndex }}, {{ $actualBookingIndex }}, '{{ $actualCancelDateStr }}')"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ e($tooltipText) }}</span>
                                            </span>
                                            @php $actualBookingIndex++; $globalRestaurantCounter++; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @elseif($key === 'guide')
                            @if(isset($serviceData['guide']) && count($serviceData['guide']) > 0)
                                @php $globalGuideCounter = 1; @endphp
                                @foreach($serviceData['guide'] as $guideOrderIndex => $guideOrder)
                                    @continue(strcasecmp($orderCountryOf($guideOrder), $country) !== 0)
                                    @php $guideData = is_string($guideOrder->data) ? json_decode($guideOrder->data, true) : $guideOrder->data; @endphp
                                    @if(is_array($guideData))
                                        @php $actualBookingIndex = 0; @endphp
                                        @foreach($guideData as $originalKey => $booking)
                                            @php
                                                $guideName = $booking['guide_name'] ?? 'Guide';
                                                $tooltipText = 'Guide ' . $globalGuideCounter . ': ' . $guideName . ' (' . $country . ')';
                                                $isApproved = $guideOrder->is_approve == 1;
                                            @endphp
                                            <span class="service-icon-wrapper" data-tooltip="{{ e($tooltipText) }}">
                                                <span class="service-icon-badge @if($isApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                                      onclick="openIndividualGuideModal({{ $tour->tour_id }}, {{ $guideOrderIndex }}, {{ $actualBookingIndex }})"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ e($tooltipText) }}</span>
                                            </span>
                                            @php $actualBookingIndex++; $globalGuideCounter++; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @elseif($key === 'hotel')
                            @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                                @php $globalHotelCounter = 1; @endphp
                                @foreach($serviceData['hotel'] as $hotelOrderIndex => $hotelOrder)
                                    @continue(strcasecmp($orderCountryOf($hotelOrder), $country) !== 0)
                                    @php $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data; @endphp
                                    @if(is_array($hotelData))
                                        @php $actualBookingIndex = 0; @endphp
                                        @foreach($hotelData as $originalKey => $booking)
                                            @php
                                                $hotelName = $booking['hotelDetails']['hotel_name'] ?? 'Hotel';
                                                $actualCancelDateStr = $tour->auto_cancel_date ? \Carbon\Carbon::parse($tour->auto_cancel_date)->format('Y-m-d') : '';
                                                $tooltipText = 'Hotel ' . $globalHotelCounter . ': ' . $hotelName . ' (' . $country . ')';
                                                $isApproved = $hotelOrder->is_approve == 1;
                                            @endphp
                                            <span class="service-icon-wrapper" data-tooltip="{{ e($tooltipText) }}">
                                                <span class="service-icon-badge @if($isApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                                      onclick="openIndividualHotelModal({{ $tour->tour_id }}, {{ $hotelOrderIndex }}, {{ $actualBookingIndex }}, '{{ $actualCancelDateStr }}')"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ e($tooltipText) }}</span>
                                            </span>
                                            @php $actualBookingIndex++; $globalHotelCounter++; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @elseif($key === 'attraction')
                            @if(isset($serviceData['attraction']))
                                @php $globalAttractionCounter = 1; @endphp
                                @foreach($serviceData['attraction'] as $attractionOrderIndex => $order)
                                    @continue(strcasecmp($orderCountryOf($order), $country) !== 0)
                                    @php $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data; @endphp
                                    @if(is_array($orderData))
                                        @php $actualBookingIndex = 0; @endphp
                                        @foreach($orderData as $bookingIndex => $booking)
                                            @php
                                                $attractionName = $booking['AttractionName'] ?? 'Attraction';
                                                $actualCancelDateStr = $tour->auto_cancel_date ? \Carbon\Carbon::parse($tour->auto_cancel_date)->format('Y-m-d') : '';
                                                $tooltipText = 'Attraction ' . $globalAttractionCounter . ': ' . $attractionName . ' (' . $country . ')';
                                                $isApproved = $order->is_approve == 1;
                                            @endphp
                                            <span class="service-icon-wrapper" data-tooltip="{{ e($tooltipText) }}">
                                                <span class="service-icon-badge @if($isApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                                      onclick="openIndividualAttractionModal({{ $tour->tour_id }}, {{ $attractionOrderIndex }}, {{ $actualBookingIndex }}, '{{ $actualCancelDateStr }}')"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ e($tooltipText) }}</span>
                                            </span>
                                            @php $actualBookingIndex++; $globalAttractionCounter++; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @elseif($key === 'travel_hourly')
                            @if(isset($serviceData['travel_hourly']))
                                @php $globalTravelHourlyCounter = 1; @endphp
                                @foreach($serviceData['travel_hourly'] as $travelHourlyOrderIndex => $order)
                                    @continue(strcasecmp($orderCountryOf($order), $country) !== 0)
                                    @php $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data; @endphp
                                    @if(is_array($orderData))
                                        @php $actualBookingIndex = 0; @endphp
                                        @foreach($orderData as $bookingIndex => $booking)
                                            @php
                                                $vehicleName = $booking['vehicles_name'] ?? 'Local-Tour Hourly';
                                                $tooltipText = 'Local-Tour Hourly ' . $globalTravelHourlyCounter . ': ' . $vehicleName . ' (' . $country . ')';
                                                $isApproved = $order->is_approve == 1;
                                            @endphp
                                            <span class="service-icon-wrapper" data-tooltip="{{ e($tooltipText) }}">
                                                <span class="service-icon-badge @if($isApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                                      onclick="openIndividualTravelHourlyModal({{ $tour->tour_id }}, {{ $travelHourlyOrderIndex }}, {{ $actualBookingIndex }})"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ e($tooltipText) }}</span>
                                            </span>
                                            @php $actualBookingIndex++; $globalTravelHourlyCounter++; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @elseif($key === 'travel_point')
                            @if(isset($serviceData['travel_point']))
                                @php $globalTravelPointCounter = 1; @endphp
                                @foreach($serviceData['travel_point'] as $travelPointOrderIndex => $order)
                                    @continue(strcasecmp($orderCountryOf($order), $country) !== 0)
                                    @php $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data; @endphp
                                    @if(is_array($orderData))
                                        @php $actualBookingIndex = 0; @endphp
                                        @foreach($orderData as $bookingIndex => $booking)
                                            @php
                                                $vehicleName = $booking['vehicles_name'] ?? 'Local-Tour Point to Point';
                                                $tooltipText = 'Local-Tour Point to Point ' . $globalTravelPointCounter . ': ' . $vehicleName . ' (' . $country . ')';
                                                $isApproved = $order->is_approve == 1;
                                            @endphp
                                            <span class="service-icon-wrapper" data-tooltip="{{ e($tooltipText) }}">
                                                <span class="service-icon-badge @if($isApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                                      onclick="openIndividualTravelPointModal({{ $tour->tour_id }}, {{ $travelPointOrderIndex }}, {{ $actualBookingIndex }})"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ e($tooltipText) }}</span>
                                            </span>
                                            @php $actualBookingIndex++; $globalTravelPointCounter++; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @elseif($key === 'local_transport')
                            @if(isset($serviceData['local_transport']))
                                @php $globalLocalTransportCounter = 1; @endphp
                                @foreach($serviceData['local_transport'] as $localTransportOrderIndex => $order)
                                    @continue(strcasecmp($orderCountryOf($order), $country) !== 0)
                                    @php $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data; @endphp
                                    @if(is_array($orderData))
                                        @php $actualBookingIndex = 0; @endphp
                                        @foreach($orderData as $bookingIndex => $booking)
                                            @php
                                                $vehicleName = $booking['vehicles_name'] ?? 'Local Transport';
                                                $tooltipText = 'Local Transport ' . $globalLocalTransportCounter . ': ' . $vehicleName . ' (' . $country . ')';
                                                $isApproved = $order->is_approve == 1;
                                            @endphp
                                            <span class="service-icon-wrapper" data-tooltip="{{ e($tooltipText) }}">
                                                <span class="service-icon-badge @if($isApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                                      onclick="openIndividualLocalTransportModal({{ $tour->tour_id }}, {{ $localTransportOrderIndex }}, {{ $actualBookingIndex }})"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }}"></i>
                                                </span>
                                                <span class="service-icon-tooltip">{{ e($tooltipText) }}</span>
                                            </span>
                                            @php $actualBookingIndex++; $globalLocalTransportCounter++; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @elseif($key === 'miscellaneous')
                            @php
                                $label = $serviceLabels[$key] ?? 'Miscellaneous';
                                $tooltipText = $label . ': ' . $count . ' (' . $country . ')';
                                $isMiscApproved = false;
                                if (isset($serviceData[$key])) {
                                    foreach ($serviceData[$key] as $miscOrder) {
                                        if (strcasecmp($orderCountryOf($miscOrder), $country) !== 0) {
                                            continue;
                                        }
                                        if ($miscOrder->is_approve == 1) {
                                            $isMiscApproved = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <span class="service-icon-wrapper" data-tooltip="{{ $tooltipText }}">
                                <span class="service-icon-badge @if($isMiscApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                      data-service-country="{{ $country }}"
                                      onclick="openServiceModal('miscellaneous', {{ $tour->tour_id }}, event)"
                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                    <i class="{{ $icons[$key] }}"></i>
                                </span>
                                <span class="service-icon-tooltip">{{ $tooltipText }}</span>
                            </span>
                        @elseif(in_array($key, ['entry_port', 'exit_port']))
                            @php
                                $label = $serviceLabels[$key] ?? ucfirst($key);
                                $tooltipText = $label . ': ' . $count . ' (' . $country . ')';
                                $isServiceApproved = false;
                                if (isset($serviceData[$key])) {
                                    foreach ($serviceData[$key] as $serviceOrder) {
                                        if (strcasecmp($orderCountryOf($serviceOrder), $country) !== 0) {
                                            continue;
                                        }
                                        if ($serviceOrder->is_approve == 1) {
                                            $isServiceApproved = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <span class="service-icon-wrapper" data-tooltip="{{ $tooltipText }}">
                                <span class="service-icon-badge @if($isServiceApproved) service-icon-badge-approved @endif" style="--service-color: {{ $bgColor }};" data-clickable="true" role="button" tabindex="0"
                                      data-service-country="{{ $country }}"
                                      onclick="openServiceModal('{{ $key }}', {{ $tour->tour_id }}, event)"
                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                    <i class="{{ $icons[$key] }}"></i>
                                </span>
                                <span class="service-icon-tooltip">{{ $tooltipText }}</span>
                            </span>
                        @endif
                    @endif
                @endforeach
                @if(array_sum(array_map('intval', $countrySvc)) === 0)
                    <span class="text-muted small">No services</span>
                @endif
            </div>
        @endforeach
    @else
        <div class="services-icons-wrap">
            <span class="text-muted small">No services</span>
        </div>
    @endif
</div>
