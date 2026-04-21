@php
    /** @var \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection $bookings */
@endphp

@foreach($bookings as $b)
    @php
        $services = \App\Helpers\CommonHelper::getPackageBookingServices((string) $b->booking_id, $b->package_id ?? null, $b->dmc_id ?? null);
        $hasHotels = !empty($services['selected_hotels'] ?? []);
        $hasAttractions = !empty($services['selected_attractions'] ?? []);
        $hasRestaurants = !empty($services['selected_restaurants'] ?? []);
        $hasArrival = !empty($services['arrival_data'] ?? []);
        $hasDeparture = !empty($services['departure_data'] ?? []);
    @endphp

    @if($hasHotels)
        <div class="modal fade" id="hotelDetailsModal{{ $b->booking_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="ri-hotel-line me-1"></i>Hotels - {{ $b->booking_id }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @foreach(($services['selected_hotels'] ?? []) as $h)
                            @php
                                $hotelName = $h['hotel_name'] ?? $h['name'] ?? 'Hotel';
                                $city = $h['city'] ?? '';
                                $nights = (int) ($h['nights'] ?? 0);
                                $start = $h['tour_start_date'] ?? null;
                                $finalPrice = $h['base_price'] ?? null; // user: base_price is final price
                                $isCompulsory = (int)($h['compulsory'] ?? 0) === 1;
                                $isOptional = (int)($h['optional'] ?? 0) === 1;
                                $isAddon = (int)($h['addon'] ?? 0) === 1;
                            @endphp

                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">{{ $hotelName }}</div>
                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                            @if($city !== '')
                                                <span class="badge bg-label-info"><i class="ri-map-pin-line me-1"></i>{{ $city }}</span>
                                            @endif
                                            @if($start)
                                                <span class="badge bg-label-secondary"><i class="ri-calendar-line me-1"></i>{{ \Carbon\Carbon::parse($start)->format('d M Y') }}</span>
                                            @endif
                                            @if($nights > 0)
                                                <span class="badge bg-label-primary"><i class="ri-moon-line me-1"></i>{{ $nights }} nights</span>
                                            @endif
                                            @if($isCompulsory)
                                                <span class="badge bg-label-success">Compulsory</span>
                                            @elseif($isOptional)
                                                <span class="badge bg-label-warning">Optional</span>
                                            @elseif($isAddon)
                                                <span class="badge bg-label-dark">Addon</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <small class="text-muted d-block">Final Price</small>
                                        <div class="fw-bold text-primary" style="font-size: 1.05rem;">
                                            {{ $finalPrice !== null ? number_format((float)$finalPrice, 2) : '—' }}
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($h['rooms']) && is_array($h['rooms']))
                                    <div class="mt-3">
                                        <small class="text-muted d-block mb-2">Rooms</small>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="min-width: 180px;">Room Type</th>
                                                        <th style="min-width: 120px;">Bed Type</th>
                                                        <th style="width: 90px;">Qty</th>
                                                        <th style="width: 120px;">Extra Bed</th>
                                                        <th style="width: 140px;">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($h['rooms'] as $r)
                                                        <tr>
                                                            <td>{{ $r['room_type_name'] ?? 'Room' }}</td>
                                                            <td>{{ $r['bed_type'] ?? '—' }}</td>
                                                            <td>{{ $r['quantity'] ?? 1 }}</td>
                                                            <td>
                                                                @if(!empty($r['extra_bed']))
                                                                    Yes @if(!empty($r['extra_bed_type'])) ({{ $r['extra_bed_type'] }}) @endif
                                                                @else
                                                                    No
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ isset($r['weekend_price']) ? number_format((float)$r['weekend_price'], 2) : '—' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($hasAttractions)
        <div class="modal fade" id="attractionDetailsModal{{ $b->booking_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="ri-building-2-line me-1"></i>Attractions - {{ $b->booking_id }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @php $attractionTotal = 0.0; @endphp
                        @foreach(($services['selected_attractions'] ?? []) as $a)
                            @php
                                $name = $a['name'] ?? 'Attraction';
                                $location = $a['location'] ?? '';
                                $start = $a['tour_start_date'] ?? null;
                                $base = (float) ($a['base_price'] ?? 0);
                                $guideName = data_get($a, 'guide.name');
                                $guidePrice = (float) (data_get($a, 'guide.price') ?? 0);
                                $transferEnabled = !empty($a['transfer']);
                                $transferPrice = $transferEnabled ? (float) ($a['transfer_price'] ?? 0) : 0.0;
                                $final = $base + $guidePrice + $transferPrice;
                                $attractionTotal += $final;
                                $isCompulsory = (int)($a['compulsory'] ?? 0) === 1;
                                $isOptional = (int)($a['optional'] ?? 0) === 1;
                                $isAddon = (int)($a['addon'] ?? 0) === 1;
                            @endphp

                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">{{ $name }}</div>
                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                            @if($location !== '')
                                                <span class="badge bg-label-info"><i class="ri-map-pin-line me-1"></i>{{ $location }}</span>
                                            @endif
                                            @if($start)
                                                <span class="badge bg-label-secondary"><i class="ri-calendar-line me-1"></i>{{ \Carbon\Carbon::parse($start)->format('d M Y') }}</span>
                                            @endif
                                            @if(!empty($a['ticket_name']))
                                                <span class="badge bg-label-primary">Ticket: {{ $a['ticket_name'] }}</span>
                                            @endif
                                            @if($isCompulsory)
                                                <span class="badge bg-label-success">Compulsory</span>
                                            @elseif($isOptional)
                                                <span class="badge bg-label-warning">Optional</span>
                                            @elseif($isAddon)
                                                <span class="badge bg-label-dark">Addon</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <small class="text-muted d-block">Final Price</small>
                                        <div class="fw-bold text-info" style="font-size: 1.05rem;">
                                            {{ number_format($final, 2) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mt-3">
                                    <div class="col-md-4">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <small class="text-muted d-block">Attraction Price</small>
                                            <div class="fw-semibold">{{ number_format($base, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <small class="text-muted d-block">Guide</small>
                                            <div class="fw-semibold">
                                                {{ $guideName ? $guideName : '—' }}
                                                <span class="text-muted">{{ $guidePrice > 0 ? '(' . number_format($guidePrice, 2) . ')' : '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <small class="text-muted d-block">Transfer</small>
                                            <div class="fw-semibold">
                                                @if($transferEnabled)
                                                    {{ ($a['vehicle_name'] ?? 'Vehicle') }} ({{ number_format($transferPrice, 2) }})
                                                @else
                                                    —
                                                @endif
                                            </div>
                                            @if($transferEnabled)
                                                <small class="text-muted d-block">
                                                    {{ ($a['pickup_name'] ?? '') }} → {{ ($a['dropoff_name'] ?? '') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <div class="fw-semibold">Total (Attractions + Guide + Transfer)</div>
                            <div class="fw-bold text-info">{{ number_format($attractionTotal, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($hasRestaurants)
        <div class="modal fade" id="restaurantDetailsModal{{ $b->booking_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="ri-restaurant-line me-1"></i>Restaurants - {{ $b->booking_id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @php $restaurantTotal = 0.0; @endphp
                        @foreach(($services['selected_restaurants'] ?? []) as $r)
                            @php
                                $name = $r['restaurant_name'] ?? $r['name'] ?? 'Restaurant';
                                $mealLabel = $r['meal_type_label'] ?? '';
                                $start = $r['tour_start_date'] ?? null;
                                $base = (float) ($r['base_price'] ?? 0);
                                $adultPrice = $r['adult_price'] ?? null;
                                $childPrice = $r['child_price'] ?? null;
                                $transferEnabled = !empty($r['transfer']);
                                $transferPrice = $transferEnabled ? (float) ($r['transfer_price'] ?? 0) : 0.0;
                                $final = $base + $transferPrice; // base_price is final restaurant price + optional transfer
                                $restaurantTotal += $final;
                                $isCompulsory = (int)($r['compulsory'] ?? 0) === 1;
                                $isOptional = (int)($r['optional'] ?? 0) === 1;
                                $isAddon = (int)($r['addon'] ?? 0) === 1;
                            @endphp

                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">{{ $name }}</div>
                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                            @if($mealLabel !== '')
                                                <span class="badge bg-label-warning"><i class="ri-restaurant-line me-1"></i>{{ $mealLabel }}</span>
                                            @endif
                                            @if($start)
                                                <span class="badge bg-label-secondary"><i class="ri-calendar-line me-1"></i>{{ \Carbon\Carbon::parse($start)->format('d M Y') }}</span>
                                            @endif
                                            @if($isCompulsory)
                                                <span class="badge bg-label-success">Compulsory</span>
                                            @elseif($isOptional)
                                                <span class="badge bg-label-warning">Optional</span>
                                            @elseif($isAddon)
                                                <span class="badge bg-label-dark">Addon</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <small class="text-muted d-block">Final Price</small>
                                        <div class="fw-bold text-warning" style="font-size: 1.05rem;">
                                            {{ number_format($final, 2) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mt-3">
                                    <div class="col-md-4">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <small class="text-muted d-block">Restaurant Price</small>
                                            <div class="fw-semibold">{{ number_format($base, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <small class="text-muted d-block">Per Pax</small>
                                            <div class="fw-semibold">
                                                @if($adultPrice !== null) Adult: {{ number_format((float)$adultPrice, 2) }} @else Adult: — @endif
                                            </div>
                                            <div class="fw-semibold">
                                                @if($childPrice !== null) Child: {{ number_format((float)$childPrice, 2) }} @else Child: — @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-2 h-100 bg-light">
                                            <small class="text-muted d-block">Transfer</small>
                                            <div class="fw-semibold">
                                                @if($transferEnabled)
                                                    {{ ($r['vehicle_name'] ?? 'Vehicle') }} ({{ number_format($transferPrice, 2) }})
                                                @else
                                                    —
                                                @endif
                                            </div>
                                            @if($transferEnabled)
                                                <small class="text-muted d-block">
                                                    {{ ($r['pickup_name'] ?? '') }} → {{ ($r['dropoff_name'] ?? '') }}
                                                </small>
                                                <small class="text-muted d-block">{{ $r['transfer_type'] ?? '' }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <div class="fw-semibold">Total (Restaurants + Transfer)</div>
                            <div class="fw-bold text-warning">{{ number_format($restaurantTotal, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($hasArrival)
        <div class="modal fade" id="arrivalDetailsModal{{ $b->booking_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="ri-flight-land-line me-1"></i>Arrival - {{ $b->booking_id }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @php $arr = $services['arrival_data'] ?? []; @endphp
                        @php
                            $enabled = (bool) ($arr['enabled'] ?? true);
                            $arrTotal = 0.0;
                        @endphp

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-label-success">Enabled: {{ $enabled ? 'Yes' : 'No' }}</span>
                            <span class="badge bg-label-success">Pickup Port ID: {{ $arr['pickup_port_id'] ?? '—' }}</span>
                            <span class="badge bg-label-success">Dropoff Hotel ID: {{ $arr['dropoff_hotel_id'] ?? '—' }}</span>
                            @if(!empty($arr['tour_start_date']))
                                <span class="badge bg-label-secondary"><i class="ri-calendar-line me-1"></i>{{ \Carbon\Carbon::parse($arr['tour_start_date'])->format('d M Y') }}</span>
                            @endif
                        </div>

                        @if($enabled && !empty($arr['vehicles']) && is_array($arr['vehicles']))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Type</th>
                                            <th>Transfer</th>
                                            <th style="width: 90px;">Qty</th>
                                            <th style="width: 140px;">Unit</th>
                                            <th style="width: 160px;">Selected Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($arr['vehicles'] as $v)
                                            @php
                                                $qty = (int) ($v['qty'] ?? 1);
                                                $unit = (float) ($v['unit_price'] ?? 0);
                                                $selected = (float) ($v['selected_price'] ?? ($unit * $qty));
                                                $arrTotal += $selected;
                                            @endphp
                                            <tr>
                                                <td>{{ $v['vehicle_name'] ?? 'Vehicle' }}</td>
                                                <td>{{ $v['vehicle_type'] ?? '—' }}</td>
                                                <td>{{ $v['selected_transfer_type'] ?? '—' }}</td>
                                                <td>{{ $qty }}</td>
                                                <td>{{ number_format($unit, 2) }}</td>
                                                <td class="fw-semibold">{{ number_format($selected, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                <div class="fw-semibold">Total (Arrival Transfers)</div>
                                <div class="fw-bold text-success">{{ number_format($arrTotal, 2) }}</div>
                            </div>
                        @else
                            <div class="text-muted">Arrival transfer is disabled or no vehicles selected.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($hasDeparture)
        <div class="modal fade" id="departureDetailsModal{{ $b->booking_id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title"><i class="ri-flight-takeoff-line me-1"></i>Departure - {{ $b->booking_id }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @php $dep = $services['departure_data'] ?? []; @endphp
                        @php
                            $enabled = (bool) ($dep['enabled'] ?? true);
                            $depTotal = 0.0;
                        @endphp

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-label-secondary">Enabled: {{ $enabled ? 'Yes' : 'No' }}</span>
                            <span class="badge bg-label-secondary">Pickup Hotel ID: {{ $dep['pickup_hotel_id'] ?? '—' }}</span>
                            <span class="badge bg-label-secondary">Dropoff Port ID: {{ $dep['dropoff_port_id'] ?? '—' }}</span>
                            @if(!empty($dep['tour_start_date']))
                                <span class="badge bg-label-secondary"><i class="ri-calendar-line me-1"></i>{{ \Carbon\Carbon::parse($dep['tour_start_date'])->format('d M Y') }}</span>
                            @endif
                        </div>

                        @if($enabled && !empty($dep['vehicles']) && is_array($dep['vehicles']))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Type</th>
                                            <th>Transfer</th>
                                            <th style="width: 90px;">Qty</th>
                                            <th style="width: 140px;">Unit</th>
                                            <th style="width: 160px;">Selected Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dep['vehicles'] as $v)
                                            @php
                                                $qty = (int) ($v['qty'] ?? 1);
                                                $unit = (float) ($v['unit_price'] ?? 0);
                                                $selected = (float) ($v['selected_price'] ?? ($unit * $qty));
                                                $depTotal += $selected;
                                            @endphp
                                            <tr>
                                                <td>{{ $v['vehicle_name'] ?? 'Vehicle' }}</td>
                                                <td>{{ $v['vehicle_type'] ?? '—' }}</td>
                                                <td>{{ $v['selected_transfer_type'] ?? '—' }}</td>
                                                <td>{{ $qty }}</td>
                                                <td>{{ number_format($unit, 2) }}</td>
                                                <td class="fw-semibold">{{ number_format($selected, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                <div class="fw-semibold">Total (Departure Transfers)</div>
                                <div class="fw-bold text-secondary">{{ number_format($depTotal, 2) }}</div>
                            </div>
                        @else
                            <div class="text-muted">Departure transfer is disabled or no vehicles selected.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

