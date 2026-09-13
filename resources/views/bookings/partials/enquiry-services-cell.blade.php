{{--
  Services cell with per-country tabs.

  @include('bookings.partials.enquiry-services-cell', [
      'tour' => $tour,
      'serviceCountryScope' => $serviceCountryScope ?? ['restricted' => false, 'countries' => []],
  ])
--}}
@php
    $scope = $serviceCountryScope ?? ['restricted' => false, 'countries' => []];

    // Prefer preloaded relation (BookingsController::hydrateTourNegotiationCurrencyData)
    $orders = collect($tour->booking ?? []);
    if ($orders->isEmpty()) {
        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)
            ->where('bookingType', 'enquiry')
            ->get();
    } else {
        $orders = $orders->filter(function ($order) {
            $bookingType = strtolower(trim((string) ($order->bookingType ?? '')));
            return $bookingType === '' || $bookingType === 'enquiry';
        })->values();
    }

    $tourCountries = \App\Helpers\CommonHelper::parseTourDestinationCountries($tour->destination ?? null);
    $tabCountries = \App\Helpers\CommonHelper::resolveTourServiceTabCountries(
        $tour->destination ?? null,
        $orders,
        $scope
    );

    $serviceTypes = [
        'hotel', 'attraction', 'restaurant', 'guide',
        'entry_port', 'exit_port', 'travel_hourly', 'travel_point',
        'local_transport', 'miscellaneous',
    ];
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
        'miscellaneous' => 'ri-file-list-3-line',
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

    // country => [type => count]
    $svcByCountry = [];
    foreach ($tabCountries as $country) {
        $svcByCountry[$country] = array_fill_keys($serviceTypes, 0);
    }

    foreach ($orders as $order) {
        $type = (string) ($order->type ?? '');
        if (!in_array($type, $serviceTypes, true)) {
            continue;
        }

        $resolved = \App\Helpers\CommonHelper::resolveBookingServiceCountry($order, $tourCountries, []);
        if ($resolved === '' || $resolved === 'Other') {
            // Fall back to first tour country when we cannot place the booking
            $resolved = $tabCountries[0] ?? ($tourCountries[0] ?? 'Other');
        }

        $canonical = \App\Helpers\CommonHelper::matchTourCountryName($resolved, $tabCountries) ?? $resolved;
        if (!\App\Helpers\CommonHelper::isServiceCountryAllowed($canonical, $scope)) {
            continue;
        }

        if (!isset($svcByCountry[$canonical])) {
            // Order country outside destination tabs but allowed — still show a tab
            $tabCountries[] = $canonical;
            $svcByCountry[$canonical] = array_fill_keys($serviceTypes, 0);
        }

        $svcByCountry[$canonical][$type]++;
    }

    // Drop empty "Other" tab if nothing was assigned to it
    if (isset($svcByCountry['Other']) && array_sum($svcByCountry['Other']) === 0) {
        unset($svcByCountry['Other']);
        $tabCountries = array_values(array_filter($tabCountries, fn ($c) => $c !== 'Other'));
    }

    // If restricted scope produced no tabs but tour has destination countries, still show allowed ones
    if (empty($tabCountries) && !empty($scope['restricted']) && !empty($scope['countries'])) {
        $tabCountries = $scope['countries'];
        foreach ($tabCountries as $country) {
            $svcByCountry[$country] = $svcByCountry[$country] ?? array_fill_keys($serviceTypes, 0);
        }
    }

    $defaultCountry = $tabCountries[0] ?? null;
    $showTabs = count($tabCountries) > 1;
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
            @php $svc = $svcByCountry[$country] ?? array_fill_keys($serviceTypes, 0); @endphp
            <div class="services-icons-wrap services-country-panel{{ $index === 0 ? ' is-active' : '' }}"
                 data-country="{{ $country }}"
                 @if($index !== 0) hidden @endif>
                @foreach($svc as $svcKey => $count)
                    @if(intval($count) > 0)
                        @php
                            $label = $serviceLabels[$svcKey] ?? ucfirst($svcKey);
                            $tooltipText = $label . ': ' . $count . ' (' . $country . ')';
                            $bgColor = $serviceColors[$svcKey] ?? '#6c757d';
                        @endphp
                        <span class="service-icon-wrapper" data-tooltip="{{ $tooltipText }}">
                            <span class="service-icon-badge"
                                  style="--service-color: {{ $bgColor }};"
                                  data-clickable="true"
                                  data-service-country="{{ $country }}"
                                  onclick="openServiceModal('{{ $svcKey }}', {{ $tour->tour_id }}, event)"
                                  role="button"
                                  tabindex="0">
                                <i class="{{ $icons[$svcKey] }}"></i>
                            </span>
                            <span class="service-icon-tooltip">{{ $tooltipText }}</span>
                        </span>
                    @endif
                @endforeach
                @if(array_sum(array_map('intval', $svc)) === 0)
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
