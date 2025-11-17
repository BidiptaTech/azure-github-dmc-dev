<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tour Quotation</title>
    <style>
        * {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 24px;
            background: #f3f5fb;
            color: #0f172a;
        }
        .header-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px 28px;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
            box-shadow: 0 8px 18px rgba(15,23,42,0.04);
        }
        .header-top {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .dmc-logo {
            max-width: 120px;
            max-height: 60px;
            object-fit: contain;
        }
        .dmc-company-name {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            flex: 1;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0ebff;
            color: #1d4ed8;
            font-weight: 600;
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .header-card h1 {
            margin: 10px 0 4px;
            font-size: 26px;
            color: #0f172a;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .meta-tile {
            padding: 10px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-tile span {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .meta-tile strong {
            font-size: 14px;
            color: #0f172a;
            font-weight: 600;
        }
        .section {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .section-header h3 {
            margin: 0;
            font-size: 17px;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
            padding: 8px 12px;
            background: linear-gradient(135deg, #e0ebff 0%, #c7d2fe 100%);
            border-radius: 8px;
            display: inline-block;
            border: 2px solid #3b82f6;
        }
        .section-header span {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 12px;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .service-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            border: 2px solid #e2e8f0;
            display: flex;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(15,23,42,0.08);
            page-break-inside: avoid;
            break-inside: avoid;
            transition: all 0.2s;
        }
        .service-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
        }
        .service-body {
            flex: 1;
            min-width: 0;
        }
        .service-title {
            font-size: 16px;
            color: #0f172a;
            font-weight: 700;
            margin: 0 0 3px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            letter-spacing: -0.01em;
        }
        .service-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-weight: 500;
        }
        .chip-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 5px;
            margin-bottom: 8px;
        }
        .chip {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            font-weight: 500;
        }
        .notes {
            margin-top: 6px;
            font-size: 11px;
            color: #475569;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .rooms-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #fbbf24;
            box-shadow: 0 2px 4px rgba(251, 191, 36, 0.15);
        }
        .vehicle-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #10b981;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.15);
        }
        .vehicle-line {
            font-size: 14px;
            color: #064e3b;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .vehicle-meta {
            font-size: 11px;
            color: #065f46;
            margin: 0 0 5px;
            font-weight: 500;
        }
        .vehicle-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .vehicle-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #065f46;
            border: 1px solid #10b981;
            font-weight: 600;
        }
        .room-line {
            font-size: 14px;
            color: #78350f;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }
        .bed-list {
            margin: 0;
            padding-left: 16px;
            color: #92400e;
            font-size: 11px;
            line-height: 1.6;
        }
        .bed-list li {
            margin-bottom: 4px;
            font-weight: 500;
        }
        .hotel-info-block {
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3b82f6;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.2);
        }
        .hotel-info-line {
            font-size: 15px;
            color: #1e40af;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }
        .hotel-info-meta {
            font-size: 12px;
            color: #1e3a8a;
            margin: 4px 0;
            font-weight: 500;
        }
        .hotel-time-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        .hotel-time-chip {
            padding: 5px 10px;
            border-radius: 6px;
            background: #ffffff;
            font-size: 11px;
            color: #1e40af;
            border: 2px solid #3b82f6;
            font-weight: 600;
        }
        .detail-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            border: 2px solid #0ea5e9;
            box-shadow: 0 2px 4px rgba(14, 165, 233, 0.15);
        }
        .detail-line {
            font-size: 14px;
            color: #0c4a6e;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .detail-meta {
            font-size: 11px;
            color: #075985;
            margin: 0 0 4px;
        }
        .detail-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .detail-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #0c4a6e;
            border: 2px solid #0ea5e9;
            font-weight: 600;
        }
        .guide-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.15);
        }
        .guide-line {
            font-size: 14px;
            color: #78350f;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }
        .guide-meta {
            font-size: 11px;
            color: #92400e;
            margin: 0 0 5px;
            font-weight: 500;
        }
        .guide-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .guide-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #92400e;
            border: 2px solid #f59e0b;
            font-weight: 600;
        }
        .empty-state {
            background: #fff;
            border-radius: 14px;
            padding: 28px;
            text-align: center;
            border: 1px dashed #c7d2fe;
            color: #64748b;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @php
        $checkIn = $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('d M Y') : '-';
        $checkOut = $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('d M Y') : '-';
        $totalServices = collect($servicesByType ?? [])->flatten(1)->count();
    @endphp
    <div class="header-card">
        @if(!empty($dmcCompanyName) || (!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0))
            <div class="header-top">
                @if(!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0)
                    <img src="{{ $dmcLogo }}" class="dmc-logo" style="display:block;max-width:120px;max-height:60px;object-fit:contain;" />
                @endif
                @if(!empty($dmcCompanyName))
                    <div class="dmc-company-name">{{ $dmcCompanyName }}</div>
                @endif
            </div>
        @endif
        <span class="badge">Tour Quotation</span>
        <h1>{{ $tour->display_id ?? ('Tour #' . ($tour->tour_id ?? '-')) }}</h1>
        <p style="margin:0;color:#64748b;font-size:14px;">Generated on {{ $generatedAt->format('d M Y, H:i') }}</p>

        <div class="meta-grid">
            <div class="meta-tile">
                <span>Destination</span>
                <strong>{{ $tour->destination ?? 'Not specified' }}</strong>
            </div>
            <div class="meta-tile">
                <span>Travel Window</span>
                <strong>{{ $checkIn }} - {{ $checkOut }}</strong>
            </div>
            <div class="meta-tile">
                <span>Confirmed Services</span>
                <strong>{{ $totalServices }}</strong>
            </div>
        </div>
    </div>

    @if(empty($servicesByType))
        <div class="empty-state">
            No quotation items have been confirmed for this tour.
        </div>
    @else
        @foreach($servicesByType as $type => $cards)
            <div class="section">
                @php
                    $normalizedType = str_replace(' ', '_', strtolower($type));
                    $sectionLabel = ucwords(str_replace('_', ' ', $type));
                    if ($normalizedType === 'entry_port') {
                        $sectionLabel = 'Arrival';
                    } elseif ($normalizedType === 'exit_port') {
                        $sectionLabel = 'Departure';
                    }
                @endphp
                <div class="section-header">
                    <h3>{{ $sectionLabel }}</h3>
                    <span>{{ count($cards) }} service{{ count($cards) > 1 ? 's' : '' }}</span>
                </div>
                <div class="card-grid">
                    @foreach($cards as $card)
                        <div class="service-card">
                            <div class="service-icon">{{ $card['icon'] }}</div>
                            <div class="service-body">
                                <div class="service-title">{{ $card['title'] }}</div>
                                @if(!empty($card['subtitle']))
                                    <div class="service-subtitle">{{ $card['subtitle'] }}</div>
                                @endif
                                <div class="chip-row">
                                    @foreach($card['chips'] as $chip)
                                        <div class="chip">{{ $chip['label'] }}: {{ $chip['value'] }}</div>
                                    @endforeach
                                </div>
                                @php
                                    $vehicleData = isset($card['vehicle']) && is_array($card['vehicle']) ? $card['vehicle'] : [];
                                    $hasVehicle = count(array_filter($vehicleData)) > 0;
                                @endphp
                                @if($hasVehicle)
                                    <div class="vehicle-block">
                                        @if(!empty($vehicleData['name']))
                                            <div class="vehicle-line">Vehicle: {{ $vehicleData['name'] }}</div>
                                        @endif
                                        @php
                                            $vehicleMeta = array_filter([
                                                !empty($vehicleData['type']) ? 'Service Type: ' . $vehicleData['type'] : null,
                                                !empty($vehicleData['vehicle_model']) ? 'Model: ' . $vehicleData['vehicle_model'] : null,
                                                !empty($vehicleData['model_year']) ? 'Year: ' . $vehicleData['model_year'] : null,
                                            ]);
                                        @endphp
                                        @if(!empty($vehicleMeta))
                                            <div class="vehicle-meta">{{ implode(' • ', $vehicleMeta) }}</div>
                                        @endif
                                        <div class="vehicle-chips">
                                            @if(!empty($vehicleData['vehicle_type']))
                                                <span class="vehicle-chip">Category: {{ $vehicleData['vehicle_type'] }}</span>
                                            @endif
                                            @if(!empty($vehicleData['seating_capacity']))
                                                <span class="vehicle-chip">Seats: {{ $vehicleData['seating_capacity'] }}</span>
                                            @endif
                                            @if(!empty($vehicleData['travel_type']))
                                                @php
                                                    $travelLabel = '';
                                                    if (strtolower($vehicleData['travel_type']) === 'entry_port') {
                                                        $travelLabel = 'Arrival';
                                                    } elseif (strtolower($vehicleData['travel_type']) === 'exit_port') {
                                                        $travelLabel = 'Departure';
                                                    } else {
                                                        $travelLabel = ucwords(str_replace('_', ' ', $vehicleData['travel_type']));
                                                    }
                                                @endphp
                                                <span class="vehicle-chip">{{ $travelLabel }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @php
                                    $attractionData = isset($card['attraction']) && is_array($card['attraction']) ? $card['attraction'] : [];
                                    $hasAttraction = count(array_filter($attractionData)) > 0;
                                @endphp
                                @if($hasAttraction)
                                    <div class="detail-block">
                                        @if(!empty($attractionData['ticket_name']))
                                            <div class="detail-line">Ticket: {{ $attractionData['ticket_name'] }}</div>
                                        @endif
                                        <div class="detail-chips">
                                            @if(!empty($attractionData['adult_count']))
                                                <span class="detail-chip">Adults: {{ $attractionData['adult_count'] }}</span>
                                            @endif
                                            @if(!empty($attractionData['child_count']))
                                                <span class="detail-chip">Children: {{ $attractionData['child_count'] }}</span>
                                            @endif
                                            @if(!empty($attractionData['senior_count']))
                                                <span class="detail-chip">Seniors: {{ $attractionData['senior_count'] }}</span>
                                            @endif
                                            @if(!empty($attractionData['visit_time']))
                                                <span class="detail-chip">Visit: {{ $attractionData['visit_time'] }}</span>
                                            @endif
                                            @if(!empty($attractionData['transport_note']))
                                                <span class="detail-chip" style="background: #fee2e2; border-color: #fecaca; color: #991b1b;">{{ $attractionData['transport_note'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @php
                                    $restaurantData = isset($card['restaurant']) && is_array($card['restaurant']) ? $card['restaurant'] : [];
                                    $hasRestaurant = count(array_filter($restaurantData)) > 0;
                                @endphp
                                @if($hasRestaurant)
                                    <div class="detail-block">
                                        @if(!empty($restaurantData['meal_type']))
                                            <div class="detail-line">Meal Type: {{ $restaurantData['meal_type'] }}</div>
                                        @endif
                                        @if(!empty($restaurantData['ticket_name']))
                                            <div class="detail-line" style="margin-top:4px;">Ticket: {{ $restaurantData['ticket_name'] }}</div>
                                        @endif
                                        @if(!empty($restaurantData['meal_items']) && is_array($restaurantData['meal_items']) && count($restaurantData['meal_items']) > 0)
                                            <div class="detail-meta" style="margin-top:6px;">
                                                <strong>Menu Items:</strong>
                                                <ul style="margin:4px 0 0 0; padding-left:16px; font-size:11px; color:#075985;">
                                                    @foreach($restaurantData['meal_items'] as $mealItem)
                                                        <li>
                                                            @php
                                                                $itemName = $mealItem['item_name'] ?? null;
                                                                $name = $mealItem['name'] ?? null;
                                                                $displayText = '';
                                                                if ($itemName && $name && $itemName !== $name) {
                                                                    $displayText = $itemName . ' (' . $name . ')';
                                                                } elseif ($itemName) {
                                                                    $displayText = $itemName;
                                                                } elseif ($name) {
                                                                    $displayText = $name;
                                                                }
                                                            @endphp
                                                            @if($displayText)
                                                                {{ $displayText }}
                                                                @if(!empty($mealItem['quantity']) && $mealItem['quantity'] > 1)
                                                                    (x{{ $mealItem['quantity'] }})
                                                                @endif
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <div class="detail-chips" style="margin-top:6px;">
                                            @if(!empty($restaurantData['adult_count']))
                                                <span class="detail-chip">Adults: {{ $restaurantData['adult_count'] }}</span>
                                            @endif
                                            @if(!empty($restaurantData['child_count']))
                                                <span class="detail-chip">Children: {{ $restaurantData['child_count'] }}</span>
                                            @endif
                                            @if(!empty($restaurantData['senior_count']))
                                                <span class="detail-chip">Seniors: {{ $restaurantData['senior_count'] }}</span>
                                            @endif
                                            @if(!empty($restaurantData['visit_time']))
                                                <span class="detail-chip">Visit: {{ $restaurantData['visit_time'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @php
                                    $guideData = isset($card['guide']) && is_array($card['guide']) ? $card['guide'] : [];
                                    $hasGuide = count(array_filter($guideData)) > 0;
                                @endphp
                                @if($hasGuide)
                                    <div class="guide-block">
                                        @if(!empty($guideData['guide_name']))
                                            <div class="guide-line">Guide: {{ $guideData['guide_name'] }}</div>
                                        @endif
                                        <div class="guide-chips">
                                            @if(!empty($guideData['hours']))
                                                <span class="guide-chip">Hours: {{ $guideData['hours'] }}</span>
                                            @endif
                                            @if(!empty($guideData['entry_time']))
                                                <span class="guide-chip">Time: {{ $guideData['entry_time'] }}</span>
                                            @endif
                                            @if(!empty($guideData['languages']) && is_array($guideData['languages']) && count($guideData['languages']) > 0)
                                                <span class="guide-chip">Languages: {{ implode(', ', $guideData['languages']) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($card['hotel_info']['name']))
                                    <div class="hotel-info-block">
                                        <div class="hotel-info-line">
                                            Hotel: {{ $card['hotel_info']['name'] }}
                                        </div>
                                        @if(!empty($card['hotel_info']['location']))
                                            <div class="hotel-info-meta">
                                                Location: {{ $card['hotel_info']['location'] }}
                                            </div>
                                        @endif
                                        <div class="hotel-time-chips">
                                            @if(!empty($card['hotel_info']['check_in_time']))
                                                @php
                                                    try {
                                                        $checkInTime = \Carbon\Carbon::createFromFormat('H:i:s', $card['hotel_info']['check_in_time'])->format('g:i A');
                                                    } catch (\Exception $e) {
                                                        try {
                                                            $checkInTime = \Carbon\Carbon::parse($card['hotel_info']['check_in_time'])->format('g:i A');
                                                        } catch (\Exception $e2) {
                                                            $checkInTime = $card['hotel_info']['check_in_time'];
                                                        }
                                                    }
                                                @endphp
                                                <span class="hotel-time-chip">Check-in: {{ $checkInTime }}</span>
                                            @endif
                                            @if(!empty($card['hotel_info']['check_out_time']))
                                                @php
                                                    try {
                                                        $checkOutTime = \Carbon\Carbon::createFromFormat('H:i:s', $card['hotel_info']['check_out_time'])->format('g:i A');
                                                    } catch (\Exception $e) {
                                                        try {
                                                            $checkOutTime = \Carbon\Carbon::parse($card['hotel_info']['check_out_time'])->format('g:i A');
                                                        } catch (\Exception $e2) {
                                                            $checkOutTime = $card['hotel_info']['check_out_time'];
                                                        }
                                                    }
                                                @endphp
                                                <span class="hotel-time-chip">Check-out: {{ $checkOutTime }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($card['rooms']))
                                    <div class="rooms-block">
                                        @foreach($card['rooms'] as $room)
                                            <div class="room-line">
                                                Room Type: {{ $room['name'] ?? 'Room' }}
                                            </div>
                                            @if(!empty($room['beds']))
                                                <ul class="bed-list">
                                                    @foreach($room['beds'] as $bed)
                                                        <li>
                                                            <strong>{{ $bed['type'] ?? 'Bed' }}</strong>
                                                            @if(!empty($bed['occupancy']))
                                                                • Capacity: {{ $bed['occupancy'] }} pax
                                                            @endif
                                                            @if(!empty($bed['meal']))
                                                                • Meal Plan: {{ $bed['meal'] }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($card['notes']))
                                    <div class="notes">{{ $card['notes'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</body>
</html>

