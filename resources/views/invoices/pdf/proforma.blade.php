<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proforma Invoice - {{ $invoice->proforma_number ?? 'DRAFT' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
            background-color: #ffffff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        thead {
            display: table-header-group;
        }
        tbody {
            display: table-row-group;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            padding: 6px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            page-break-after: avoid;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding: 5px;
        }
        .header-left {
            width: 25%;
        }
        .header-center {
            width: 45%;
            text-align: center;
            padding-top: 15px;
        }
        .header-right {
            width: 30%;
            text-align: right;
        }
        .header h1 {
            font-size: 36px;
            margin-bottom: 0;
            font-weight: bold;
            margin-top: 0;
            color: #333;
            letter-spacing: 2px;
        }
        .header .dmc-name {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 0;
            color: #333;
        }
        .invoice-number-badge {
            background-color: #20B2AA;
            color: #ffffff;
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }
        .invoice-number-badge strong {
            display: block;
            margin-bottom: 3px;
        }
        .info-section {
            margin-bottom: 20px;
            background-color: #f5f5f5;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e0e0e0;
        }
        .info-section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 12px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ccc;
        }
        .info-row {
            margin-bottom: 8px;
            font-size: 11px;
            line-height: 1.6;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 140px;
            color: #555;
        }
        .info-value {
            display: inline;
            color: #333;
        }
        .info-box-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-box-container-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }
        .info-box-container-table td {
            vertical-align: top;
            padding: 0;
            width: 50%;
        }
        .info-box-left {
            padding-right: 10px;
        }
        .info-box-right {
            padding-left: 10px;
        }
        .currency-conversion-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .invoice-info {
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
            padding: 8px 0;
            border-bottom: 2px solid #333;
        }
        .currency-section {
            margin-top: 20px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .currency-table {
            width: 50%;
            float: right;
        }
        .currency-table th {
            background-color: #4CAF50;
            color: white;
        }
        .payment-terms {
            background-color: #FFC0CB;
            padding: 10px;
            margin-top: 20px;
            clear: both;
        }
        .bank-details {
            background-color: #FFC0CB;
            padding: 10px;
            margin-top: 20px;
        }
        .footer-note {
            margin-top: 20px;
            padding: 10px;
            background-color: #fff3cd;
            font-size: 10px;
            color: #856404;
            clear: both;
        }
        .text-right {
            text-align: right;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
            padding: 8px 0;
            border-bottom: 2px solid #333;
        }
        .text-center {
            text-align: center;
        }
        .mb-2 {
            margin-bottom: 8px;
        }
        .mt-2 {
            margin-top: 8px;
        }
        .dmc-logo-wrapper {
            text-align: left;
            padding: 0;
        }
        .dmc-logo-wrapper img {
            max-width: 150px;
            max-height: 80px;
            height: auto;
        }
        .dmc-logo {
            max-width: 90px;
            max-height: 90px;
            object-fit: contain;
        }
        .client-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .client-info-table th {
            background-color: #555;
            color: #ffffff;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .client-info-table td {
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #ffffff;
            font-size: 11px;
        }
        .footer-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
        }
        .footer-company-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .footer-contact {
            font-size: 11px;
            color: #666;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <!-- Header -->
    @php
        $dmcUser = $invoice->dmc;
        // Resolve root DMC through created_by chain (for sales head / managers)
        $rootDmc = $dmcUser;
        $visited = [];
        while ($rootDmc && $rootDmc->role_id != 11 && $rootDmc->created_by && !in_array($rootDmc->created_by, $visited)) {
            $visited[] = $rootDmc->created_by;
            $rootDmc = \App\Models\User::where('userId', $rootDmc->created_by)->first();
        }
        if (!$rootDmc) {
            $rootDmc = $dmcUser;
        }
        $dmcLogo = $rootDmc->logo ?? $dmcUser->logo ?? null;
        $dmcCompanyName = $rootDmc->company_name ?? $dmcUser->company_name ?? 'DMC Name';

        // Build a data URI for DomPDF from local path or remote URL
        $dmcLogoSrc = null;
        if ($dmcLogo) {
            try {
                // If it's already a data URI, just use it
                if (preg_match('/^data:image\\//i', $dmcLogo)) {
                    $dmcLogoSrc = $dmcLogo;
                } else {
                    // Decide source: remote URL or local file
                    if (preg_match('/^https?:\\/\\//i', $dmcLogo)) {
                        $logoContent = @file_get_contents($dmcLogo);
                    } else {
                        $logoPath = public_path(ltrim($dmcLogo, '/'));
                        $logoContent = @file_get_contents($logoPath);
                    }
                    if ($logoContent) {
                        $base64 = base64_encode($logoContent);
                        $dmcLogoSrc = 'data:image/png;base64,' . $base64;
                    }
                }
            } catch (\Exception $e) {
                $dmcLogoSrc = null;
            }
        }
    @endphp
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    @if($dmcLogoSrc)
                    <div class="dmc-logo-wrapper">
                        <img src="{{ $dmcLogoSrc }}" class="dmc-logo" />
                    </div>
                    @endif
                </td>
                <td class="header-center">
                    <h1>PROFORMA INVOICE</h1>
                    <div class="dmc-name">{{ $dmcCompanyName }}</div>
                </td>
                <td class="header-right">
                    <div class="invoice-number-badge">
                        <strong>Proforma Number:</strong>
                        {{ $invoice->proforma_number ?? 'DRAFT' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @php
        $clientDetails = $invoice->client_details ?? [];
        $travelCompany = $invoice->travel_company_details ?? [];
    @endphp

    <!-- Client/Guest Information -->
    <div class="info-section">
        <div class="info-section-title">Client/Guest Information</div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $clientDetails['address'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">State:</span>
            <span class="info-value">{{ $clientDetails['city'] ?? '' }}</span>
            <span class="info-label" style="margin-left: 30px;">Postal Code:</span>
            <span class="info-value">{{ $clientDetails['postal_code'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $clientDetails['email'] ?? '' }}</span>
            <span class="info-label" style="margin-left: 30px;">Phone:</span>
            <span class="info-value">{{ $clientDetails['phone'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Booking ID:</span>
            <span class="info-value">{{ $clientDetails['booking_id'] ?? '' }}</span>
            <span class="info-label" style="margin-left: 30px;">Lead Guest:</span>
            <span class="info-value">{{ $clientDetails['lead_guest_name'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">No. of Adults:</span>
            <span class="info-value">{{ $invoice->no_of_adults ?? 0 }}</span>
            <span class="info-label" style="margin-left: 30px;">No. of Children:</span>
            <span class="info-value">{{ $invoice->no_of_children ?? 0 }}</span>
            <span class="info-label" style="margin-left: 30px;">No. of Infants:</span>
            <span class="info-value">{{ $invoice->no_of_infants ?? 0 }}</span>
        </div>
    </div>

    <!-- Travel Company / Agent -->
    <div class="info-section">
        <div class="info-section-title">Travel Company / Agent</div>
        @if(!empty($travelCompany['company_name']))
        <div class="info-row">
            <span class="info-label">Travel Agency:</span>
            <span class="info-value">{{ $travelCompany['company_name'] ?? '' }}</span>
        </div>
        @endif
        @if(!empty($travelCompany))
        <div class="info-row">
            <span class="info-label">Travel Agent:</span>
            <span class="info-value">{{ $travelCompany['name'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Address:</span>
            <span class="info-value">{{ $travelCompany['address'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact Person:</span>
            <span class="info-value">{{ $travelCompany['contact_person'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value">{{ $travelCompany['phone'] ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $travelCompany['email'] ?? '' }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Proposal Date:</span>
            <span class="info-value">{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('jS M Y') : '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Proposal Validity:</span>
            <span class="info-value">{{ $invoice->validity_date ? \Carbon\Carbon::parse($invoice->validity_date)->format('jS M Y') : '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Proposal Sent By:</span>
            <span class="info-value">{{ $invoice->sent_by ?? '' }}</span>
        </div>
    </div>

    <!-- Travel Summary -->
    <div class="info-section">
        <div class="info-section-title">Travel Summary</div>
        <div class="info-row">
            <span class="info-label">Destination:</span>
            <span class="info-value">{{ $invoice->destination ?? '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Travel Date:</span>
            <span class="info-value">
                <strong>From:</strong> {{ $invoice->travel_from_date ? \Carbon\Carbon::parse($invoice->travel_from_date)->format('jS M Y') : '' }}
                <strong style="margin-left: 15px;">To:</strong> {{ $invoice->travel_to_date ? \Carbon\Carbon::parse($invoice->travel_to_date)->format('jS M Y') : '' }}
                <strong style="margin-left: 15px;">Duration / No of Days:</strong> {{ $invoice->duration_days ?? '' }} days
            </span>
        </div>
    </div>

    <!-- Service Description -->
    <div class="section-title">Description</div>
    
    @php
        $allItems = $invoice->items ?? collect([]);
        $hotelItems = $allItems->where('item_type', 'hotel');
        $entryPortItems = $allItems->where('item_type', 'entry_port');
        $attractionItems = $allItems->where('item_type', 'attraction');
        $restaurantItems = $allItems->where('item_type', 'restaurant');
        $guideItems = $allItems->where('item_type', 'guide');
        
        // Helper function to get attraction prices (base and grand total)
        $getAttractionPrices = function($item, $serviceDetails) use ($invoice) {
            $basePrice = 0;
            $transferCost = 0;
            $guideTotalPrice = 0;
            
            // If service_details has breakdown, use it
            if (isset($serviceDetails['attraction_base_price']) || isset($serviceDetails['transfer_cost']) || isset($serviceDetails['guide_total_price'])) {
                $basePrice = $serviceDetails['attraction_base_price'] ?? 0;
                $transferCost = $serviceDetails['transfer_cost'] ?? 0;
                $guideTotalPrice = $serviceDetails['guide_total_price'] ?? 0;
                return [
                    'base' => $basePrice,
                    'transfer' => $transferCost,
                    'guide' => $guideTotalPrice,
                    'total' => $basePrice + $transferCost + $guideTotalPrice
                ];
            }
            
            // Try to get from order data
            if ($invoice->tour) {
                $orders = \App\Models\Order::where('tour_id', $invoice->tour->tour_id)
                    ->where('type', 'attraction')
                    ->whereNull('deleted_at')
                    ->get();
                
                foreach ($orders as $order) {
                    $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                    if (!is_array($orderData)) continue;
                    
                    $bookings = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
                    foreach ($bookings as $booking) {
                        if (!is_array($booking)) continue;
                        
                        // Try to match by attraction name
                        $itemAttractionName = $serviceDetails['attraction_name'] ?? '';
                        $bookingAttractionName = $booking['AttractionName'] ?? '';
                        
                        if ($itemAttractionName && $bookingAttractionName && 
                            strtolower(trim($itemAttractionName)) === strtolower(trim($bookingAttractionName))) {
                            $basePrice = (float)($booking['price'] ?? $booking['totalPrice'] ?? 0);
                            $transferCost = isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0 ? (float) $booking['transfer_options']['cost'] : 0;
                            $guideTotalPrice = isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0 ? (float) $booking['guide_options']['total_price'] : 0;
                            return [
                                'base' => $basePrice,
                                'transfer' => $transferCost,
                                'guide' => $guideTotalPrice,
                                'total' => $basePrice + $transferCost + $guideTotalPrice
                            ];
                        }
                    }
                }
            }
            
            // Final fallback: try to extract from item's total_price (assume it's the grand total)
            // For old invoices, we can't determine breakdown, so show total_price as both
            $fallbackTotal = $item->total_price ?? 0;
            return [
                'base' => $fallbackTotal, // Can't determine breakdown for old invoices
                'transfer' => 0,
                'guide' => 0,
                'total' => $fallbackTotal
            ];
        };
        
        // Helper function to get restaurant prices (base and grand total)
        $getRestaurantPrices = function($item, $serviceDetails) use ($invoice) {
            $basePrice = 0;
            $transferCost = 0;
            
            // If service_details has breakdown, use it
            if (isset($serviceDetails['restaurant_base_price']) || isset($serviceDetails['transfer_cost'])) {
                $basePrice = $serviceDetails['restaurant_base_price'] ?? 0;
                $transferCost = $serviceDetails['transfer_cost'] ?? 0;
                return [
                    'base' => $basePrice,
                    'transfer' => $transferCost,
                    'total' => $basePrice + $transferCost
                ];
            }
            
            // Try to get from order data
            if ($invoice->tour) {
                $orders = \App\Models\Order::where('tour_id', $invoice->tour->tour_id)
                    ->where('type', 'restaurant')
                    ->whereNull('deleted_at')
                    ->get();
                
                foreach ($orders as $order) {
                    $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                    if (!is_array($orderData)) continue;
                    
                    $bookings = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
                    foreach ($bookings as $booking) {
                        if (!is_array($booking)) continue;
                        
                        // Try to match by restaurant name
                        $itemRestaurantName = $serviceDetails['restaurant_name'] ?? '';
                        $bookingRestaurantName = $booking['restaurantName'] ?? '';
                        
                        if ($itemRestaurantName && $bookingRestaurantName && 
                            strtolower(trim($itemRestaurantName)) === strtolower(trim($bookingRestaurantName))) {
                            $basePrice = (float)($booking['mealPrice'] ?? $booking['totalPrice'] ?? 0);
                            $transferCost = isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0 ? (float) $booking['transfer_options']['cost'] : 0;
                            return [
                                'base' => $basePrice,
                                'transfer' => $transferCost,
                                'total' => $basePrice + $transferCost
                            ];
                        }
                    }
                }
            }
            
            // Final fallback: try to extract from item's total_price
            $fallbackTotal = $item->total_price ?? 0;
            return [
                'base' => $fallbackTotal, // Can't determine breakdown for old invoices
                'transfer' => 0,
                'total' => $fallbackTotal
            ];
        };
        $travelPointItems = $allItems->where('item_type', 'travel_point');
        $travelHourlyItems = $allItems->where('item_type', 'travel_hourly');
        $localTransportItems = $allItems->where('item_type', 'local_transport');
        $exitPortItems = $allItems->where('item_type', 'exit_port');
        $otherItems = $allItems->whereNotIn('item_type', ['hotel', 'entry_port', 'attraction', 'restaurant', 'guide', 'travel_point', 'travel_hourly', 'local_transport', 'exit_port']);
    @endphp

    @if($hotelItems->count() > 0)
    <!-- Hotel Services Table -->
    <div class="section-title">Hotel Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Hotel Name</th>
                <th>Room Category</th>
                <th>Check in</th>
                <th>Check out</th>
                <th>No. of Nights</th>
                <th>Total Pax</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hotelItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $checkInDate = $serviceDetails['check_in_date'] ?? '';
                $checkInTime = $serviceDetails['check_in_time'] ?? '';
                $checkOutDate = $serviceDetails['check_out_date'] ?? '';
                $checkOutTime = $serviceDetails['check_out_time'] ?? '';
                
                $checkInDisplay = '';
                if ($checkInDate) {
                    try {
                        $checkInCarbon = \Carbon\Carbon::parse($checkInDate);
                        if ($checkInTime) {
                            $timeParts = explode(':', $checkInTime);
                            if (count($timeParts) >= 2) {
                                $checkInCarbon->setTime((int)$timeParts[0], (int)$timeParts[1]);
                                $checkInDisplay = $checkInCarbon->format('jS M Y, h:i A');
                            } else {
                                $checkInDisplay = $checkInCarbon->format('jS M Y');
                            }
                        } else {
                            $checkInDisplay = $checkInCarbon->format('jS M Y');
                        }
                    } catch (\Exception $e) {
                        $checkInDisplay = '';
                    }
                }
                
                $checkOutDisplay = '';
                if ($checkOutDate) {
                    try {
                        $checkOutCarbon = \Carbon\Carbon::parse($checkOutDate);
                        if ($checkOutTime) {
                            $timeParts = explode(':', $checkOutTime);
                            if (count($timeParts) >= 2) {
                                $checkOutCarbon->setTime((int)$timeParts[0], (int)$timeParts[1]);
                                $checkOutDisplay = $checkOutCarbon->format('jS M Y, h:i A');
                            } else {
                                $checkOutDisplay = $checkOutCarbon->format('jS M Y');
                            }
                        } else {
                            $checkOutDisplay = $checkOutCarbon->format('jS M Y');
                        }
                    } catch (\Exception $e) {
                        $checkOutDisplay = '';
                    }
                }
            @endphp
            <tr>
                <td>{{ $serviceDetails['hotel_name'] ?? ($item->description ?? '') }}</td>
                <td>{{ $serviceDetails['room_category'] ?? '' }}</td>
                <td>{{ $checkInDisplay }}</td>
                <td>{{ $checkOutDisplay }}</td>
                <td>{{ $serviceDetails['no_of_days'] ?? '' }}</td>
                <td>{{ $serviceDetails['total_pax'] ?? 0 }}</td>
                <td class="text-right">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($entryPortItems->count() > 0)
    <!-- Arrival Services Table -->
    <div class="section-title">Arrival Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Entry Pickup</th>
                <th>Entry Dropoff</th>
                <th>Vehicle Name</th>
                <th>Type</th>
                <th>Pickup Date</th>
                <th>Total Persons</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entryPortItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $pickupDate = $serviceDetails['pickup_date'] ?? '';
                $entryTime = $serviceDetails['entrytime'] ?? '';
                $pickupDateDisplay = '';
                if ($pickupDate) {
                    try {
                        $pickupCarbon = \Carbon\Carbon::parse($pickupDate);
                        if ($entryTime) {
                            // Parse time (format: "01:00 AM" or "01:00:00")
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
                                // Handle AM/PM
                                if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                    $hour += 12;
                                } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                    $hour = 0;
                                }
                                $pickupCarbon->setTime($hour, $minute);
                                $pickupDateDisplay = $pickupCarbon->format('jS M Y, h:i A');
                            } else {
                                $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                            }
                        } else {
                            $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                        }
                    } catch (\Exception $e) {
                        $pickupDateDisplay = '';
                    }
                }
                $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
            @endphp
            <tr>
                <td>{{ $serviceDetails['entrypickup'] ?? '' }}</td>
                <td>{{ $serviceDetails['entrydropoff'] ?? '' }}</td>
                <td>{{ $serviceDetails['vehicle_name'] ?? '' }}</td>
                <td>{{ $serviceDetails['vehicle_type'] ?? '' }}</td>
                <td>{{ $pickupDateDisplay }}</td>
                <td>{{ $totalPersons }}</td>
                <td class="text-right">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($attractionItems->count() > 0)
    <!-- Attraction Services Table -->
    <div class="section-title">Attraction Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Attraction Name</th>
                <th>Ticket Details</th>
                <th>Visit Date</th>
                <th>Transfer</th>
                <th>Type</th>
                <th>Way</th>
                <th>Vehicle Details</th>
                <th>Adults</th>
                <th>Children</th>
                <th>Infants</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attractionItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $visitDate = $serviceDetails['booking_date'] ?? '';
                $visitDateDisplay = '';
                if ($visitDate) {
                    try {
                        $visitDateDisplay = \Carbon\Carbon::parse($visitDate)->format('jS M Y');
                    } catch (\Exception $e) {
                        $visitDateDisplay = '';
                    }
                }
                $transferRequired = $serviceDetails['transfer_required'] ?? 'No';
                $transferType = $serviceDetails['transfer_type'] ?? '';
                $transferWay = $serviceDetails['transfer_way'] ?? '';
                $vehicleDetails = $serviceDetails['vehicle_details'] ?? '';
                
                // Calculate prices: Attraction Price + Transfer Price + Guide Price
                $prices = $getAttractionPrices($item, $serviceDetails);
                $basePrice = $prices['base'];
                $grandTotal = $prices['total'];
            @endphp
            <tr>
                <td>{{ $serviceDetails['attraction_name'] ?? ($item->description ?? '') }}</td>
                <td>{{ $serviceDetails['ticket_details'] ?? '' }}</td>
                <td>{{ $visitDateDisplay }}</td>
                <td>{{ $transferRequired }}</td>
                <td>{{ $transferType ?: '' }}</td>
                <td>{{ $transferWay ?: '' }}</td>
                <td>{{ $vehicleDetails ?: '' }}</td>
                <td>{{ $item->quantity_adults ?? 0 }}</td>
                <td>{{ $item->quantity_children ?? 0 }}</td>
                <td>{{ $item->quantity_infants ?? 0 }}</td>
                <td class="text-right">{{ number_format($basePrice, 2) }}</td>
                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($restaurantItems->count() > 0)
    <!-- Restaurant Services Table -->
    <div class="section-title">Restaurant Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Restaurant Name</th>
                <th>Meal Type</th>
                <th>Booking Date</th>
                <th>Transfer</th>
                <th>Type</th>
                <th>Way</th>
                <th>Vehicle Details</th>
                <th>Adults</th>
                <th>Children</th>
                <th>Infants</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($restaurantItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $visitDate = $serviceDetails['booking_date'] ?? '';
                $visitDateDisplay = '';
                if ($visitDate) {
                    try {
                        $visitDateDisplay = \Carbon\Carbon::parse($visitDate)->format('jS M Y');
                    } catch (\Exception $e) {
                        $visitDateDisplay = '';
                    }
                }
                $transferRequired = $serviceDetails['transfer_required'] ?? 'No';
                $transferType = $serviceDetails['transfer_type'] ?? '';
                $transferWay = $serviceDetails['transfer_way'] ?? '';
                $vehicleDetails = $serviceDetails['vehicle_details'] ?? '';
                
                // Calculate prices: Restaurant Price + Transfer Price
                $prices = $getRestaurantPrices($item, $serviceDetails);
                $basePrice = $prices['base'];
                $grandTotal = $prices['total'];
            @endphp
            <tr>
                <td>{{ $serviceDetails['restaurant_name'] ?? ($item->description ?? '') }}</td>
                <td>{{ $serviceDetails['meal_type'] ?? '' }}</td>
                <td>{{ $visitDateDisplay }}</td>
                <td>{{ $transferRequired }}</td>
                <td>{{ $transferType ?: '' }}</td>
                <td>{{ $transferWay ?: '' }}</td>
                <td>{{ $vehicleDetails ?: '' }}</td>
                <td>{{ $item->quantity_adults ?? 0 }}</td>
                <td>{{ $item->quantity_children ?? 0 }}</td>
                <td>{{ $item->quantity_infants ?? 0 }}</td>
                <td class="text-right">{{ number_format($basePrice, 2) }}</td>
                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($guideItems->count() > 0)
    <!-- Guide Services Table -->
    <div class="section-title">Guide Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Guide Name</th>
                <th>Pick Up Date</th>
                <th>Hours</th>
                <th>Adults</th>
                <th>Children</th>
                <th>Infants</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guideItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $pickupDate = $serviceDetails['pickup_date'] ?? '';
                $pickupDateDisplay = '';
                if ($pickupDate) {
                    try {
                        $pickupDateDisplay = \Carbon\Carbon::parse($pickupDate)->format('jS M Y');
                    } catch (\Exception $e) {
                        $pickupDateDisplay = '';
                    }
                }
            @endphp
            <tr>
                <td>{{ $serviceDetails['guide_name'] ?? ($item->description ?? '') }}</td>
                <td>{{ $pickupDateDisplay }}</td>
                <td>{{ $serviceDetails['hours'] ?? 0 }}</td>
                <td>{{ $item->quantity_adults ?? 0 }}</td>
                <td>{{ $item->quantity_children ?? 0 }}</td>
                <td>{{ $item->quantity_infants ?? 0 }}</td>
                <td class="text-right">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($travelPointItems->count() > 0)
    <!-- Travel Point Services Table -->
    <div class="section-title">Point to Point Transfer Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Pickup Location</th>
                <th>Dropoff Location</th>
                <th>Vehicle Name</th>
                <th>Pickup Date</th>
                <th>Total Persons</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($travelPointItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $pickupDate = $serviceDetails['pickup_date'] ?? '';
                $entryTime = $serviceDetails['entrytime'] ?? '';
                $pickupDateDisplay = '';
                if ($pickupDate) {
                    try {
                        $pickupCarbon = \Carbon\Carbon::parse($pickupDate);
                        if ($entryTime) {
                            // Parse time (format: "06:00 AM" or "06:00:00")
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
                                // Handle AM/PM
                                if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                    $hour += 12;
                                } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                    $hour = 0;
                                }
                                $pickupCarbon->setTime($hour, $minute);
                                $pickupDateDisplay = $pickupCarbon->format('jS M Y, h:i A');
                            } else {
                                $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                            }
                        } else {
                            $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                        }
                    } catch (\Exception $e) {
                        $pickupDateDisplay = '';
                    }
                }
                $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
            @endphp
            <tr>
                <td>{{ $serviceDetails['entrypickup'] ?? '' }}</td>
                <td>{{ $serviceDetails['entrydropoff'] ?? '' }}</td>
                <td>{{ $serviceDetails['vehicle_name'] ?? '' }}</td>
                <td>{{ $pickupDateDisplay }}</td>
                <td>{{ $totalPersons }}</td>
                <td class="text-right">{{ number_format(round($item->unit_price ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format(round($item->total_price ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($travelHourlyItems->count() > 0)
    <!-- Travel Hourly Services Table -->
    <div class="section-title">Hourly Tour Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Pickup Location</th>
                <th>Vehicle Name</th>
                <th>Pickup Date</th>
                <th>Total Persons</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($travelHourlyItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $pickupDate = $serviceDetails['pickup_date'] ?? '';
                $entryTime = $serviceDetails['entrytime'] ?? '';
                $pickupDateDisplay = '';
                if ($pickupDate) {
                    try {
                        $pickupCarbon = \Carbon\Carbon::parse($pickupDate);
                        if ($entryTime) {
                            // Parse time (format: "09:00 AM" or "09:00:00")
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
                                // Handle AM/PM
                                if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                    $hour += 12;
                                } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                    $hour = 0;
                                }
                                $pickupCarbon->setTime($hour, $minute);
                                $pickupDateDisplay = $pickupCarbon->format('jS M Y, h:i A');
                            } else {
                                $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                            }
                        } else {
                            $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                        }
                    } catch (\Exception $e) {
                        $pickupDateDisplay = '';
                    }
                }
                $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
            @endphp
            <tr>
                <td>{{ $serviceDetails['entrypickup'] ?? '' }}</td>
                <td>{{ $serviceDetails['vehicle_name'] ?? '' }}</td>
                <td>{{ $pickupDateDisplay }}</td>
                <td>{{ $totalPersons }}</td>
                <td class="text-right">{{ number_format(round($item->unit_price ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format(round($item->total_price ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($localTransportItems->count() > 0)
    <!-- Local Transport Services Table -->
    <div class="section-title">Local Transport Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Pickup Location</th>
                <th>Dropoff Location</th>
                <th>Vehicle Name</th>
                <th>Pickup Date</th>
                <th>Total Persons</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($localTransportItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $pickupDate = $serviceDetails['pickup_date'] ?? '';
                $entryTime = $serviceDetails['entrytime'] ?? '';
                $pickupDateDisplay = '';
                if ($pickupDate) {
                    try {
                        $pickupCarbon = \Carbon\Carbon::parse($pickupDate);
                        if ($entryTime) {
                            // Parse time (format: "04:00 AM" or "04:00:00")
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
                                // Handle AM/PM
                                if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                    $hour += 12;
                                } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                    $hour = 0;
                                }
                                $pickupCarbon->setTime($hour, $minute);
                                $pickupDateDisplay = $pickupCarbon->format('jS M Y, h:i A');
                            } else {
                                $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                            }
                        } else {
                            $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                        }
                    } catch (\Exception $e) {
                        $pickupDateDisplay = '';
                    }
                }
                $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
            @endphp
            <tr>
                <td>{{ $serviceDetails['entrypickup'] ?? '' }}</td>
                <td>{{ $serviceDetails['entrydropoff'] ?? '' }}</td>
                <td>{{ $serviceDetails['vehicle_name'] ?? '' }}</td>
                <td>{{ $pickupDateDisplay }}</td>
                <td>{{ $totalPersons }}</td>
                <td class="text-right">{{ number_format(round($item->unit_price ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format(round($item->total_price ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($exitPortItems->count() > 0)
    <!-- Departure Services Table -->
    <div class="section-title">Departure Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Exit Pickup</th>
                <th>Exit Dropoff</th>
                <th>Vehicle Name</th>
                <th>Type</th>
                <th>Exit Pickup Date</th>
                <th>Total Persons</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($exitPortItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
                $exitPickupDate = $serviceDetails['exitpickupdate'] ?? '';
                $entryTime = $serviceDetails['entrytime'] ?? '';
                $exitPickupDateDisplay = '';
                if ($exitPickupDate) {
                    try {
                        $exitPickupCarbon = \Carbon\Carbon::parse($exitPickupDate);
                        if ($entryTime) {
                            // Parse time (format: "01:00 AM" or "01:00:00")
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
                                // Handle AM/PM
                                if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                    $hour += 12;
                                } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                    $hour = 0;
                                }
                                $exitPickupCarbon->setTime($hour, $minute);
                                $exitPickupDateDisplay = $exitPickupCarbon->format('jS M Y, h:i A');
                            } else {
                                $exitPickupDateDisplay = $exitPickupCarbon->format('jS M Y');
                            }
                        } else {
                            $exitPickupDateDisplay = $exitPickupCarbon->format('jS M Y');
                        }
                    } catch (\Exception $e) {
                        $exitPickupDateDisplay = '';
                    }
                }
                $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
            @endphp
            <tr>
                <td>{{ $serviceDetails['exitpickup'] ?? '' }}</td>
                <td>{{ $serviceDetails['exitdropoff'] ?? '' }}</td>
                <td>{{ $serviceDetails['vehicle_name'] ?? '' }}</td>
                <td>{{ $serviceDetails['vehicle_type'] ?? '' }}</td>
                <td>{{ $exitPickupDateDisplay }}</td>
                <td>{{ $totalPersons }}</td>
                <td class="text-right">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($otherItems->count() > 0)
    <!-- Other Services Table -->
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Service Name</th>
                <th>Description</th>
                <th>Adults</th>
                <th>Children</th>
                <th>Infants</th>
                <th>Unit Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
                <th>Total Price ({{ $invoice->base_currency ?? 'SGD' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($otherItems as $item)
            <tr>
                <td>{{ ucfirst($item->item_type ?? 'Service') }}</td>
                <td>{{ $item->description ?? '' }}</td>
                <td>{{ $item->quantity_adults ?? 0 }}</td>
                <td>{{ $item->quantity_children ?? 0 }}</td>
                <td>{{ $item->quantity_infants ?? 0 }}</td>
                <td class="text-right">{{ number_format($item->unit_price ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($allItems->count() === 0)
    <table>
        <tbody>
            <tr>
                <td colspan="8" class="text-center">No items found</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Summary Table -->
    <table>
        <tfoot>
            @php
                // Use values computed by service (handles newly added services)
                $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
                $ordersTotal = $notes['orders_total'] ?? null;
                $baseAmount = $notes['base_amount'] ?? null;
                $actualAmount = $ordersTotal !== null ? $ordersTotal : $invoice->items->sum('total_price');
                if ($baseAmount === null) {
                    $neg = $invoice->getNegotiatedAmount();
                    $baseAmount = $neg ?? $actualAmount;
                }
                $negotiatedAmount = $baseAmount;
                $discount = $actualAmount - $baseAmount;
                
                // Get tour status and check if GST should be calculated
                $tour = $invoice->tour;
                $tourStatus = $tour->tour_status ?? '';
                $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
                $shouldShowTax = in_array($tourStatus, $statusesWithTax);
                
                // Get tax breakdown from notes
                $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
                $taxBreakdown = $notes['tax_breakdown'] ?? [];
                $gstAmount = $invoice->gst_amount ?? 0;
                $finalPrice = $baseAmount + $gstAmount;
                
                // Get payment information
                $paymentReceived = $invoice->payment_received ?? 0;
                $outstandingBalance = $invoice->outstanding_balance ?? $finalPrice;
            @endphp
            <tr>
                <td colspan="7" class="text-right"><strong>Total (Actual Amount):</strong></td>
                <td class="text-right"><strong>{{ number_format(round($actualAmount)) }}</strong></td>
            </tr>
            @if($negotiatedAmount !== null)
            <tr style="background-color: #e7f3ff;">
                <td colspan="7" class="text-right"><strong>Last Negotiated Amount:</strong></td>
                <td class="text-right"><strong>{{ number_format(round($negotiatedAmount)) }}</strong></td>
            </tr>
            @if($discount > 0)
            <tr style="background-color: #d4edda;">
                <td colspan="7" class="text-right"><strong>Discount:</strong></td>
                <td class="text-right"><strong>-{{ number_format(round($discount)) }}</strong></td>
            </tr>
            @elseif($discount < 0)
            <tr style="background-color: #fff3cd;">
                <td colspan="7" class="text-right"><strong>Additional Charges:</strong></td>
                <td class="text-right"><strong>{{ number_format(round(abs($discount))) }}</strong></td>
            </tr>
            @endif
            @endif
            
            @if($shouldShowTax && $gstAmount > 0)
            <!-- Tax Breakdown -->
            @if(!empty($taxBreakdown))
                @foreach($taxBreakdown as $taxName => $taxValue)
                <tr style="background-color: #fff3cd;">
                    <td colspan="7" class="text-right"><strong>{{ $taxName }}:</strong></td>
                    <td class="text-right"><strong>{{ number_format(round($taxValue)) }}</strong></td>
                </tr>
                @endforeach
            @else
            <tr style="background-color: #fff3cd;">
                <td colspan="7" class="text-right"><strong>Total Vat / GST Tax:</strong></td>
                <td class="text-right"><strong>{{ number_format(round($gstAmount)) }}</strong></td>
            </tr>
            @endif
            @endif
            
            <tr style="background-color: #d4edda;">
                <td colspan="7" class="text-right"><strong>Final Price:</strong></td>
                <td class="text-right"><strong>{{ number_format(round($finalPrice)) }}</strong></td>
            </tr>
            
            @if($shouldShowTax)
            <!-- Payment Information -->
            <tr style="background-color: #d1ecf1;">
                <td colspan="7" class="text-right"><strong>Payment Received:</strong></td>
                <td class="text-right"><strong>{{ number_format(round($paymentReceived)) }}</strong></td>
            </tr>
            <tr style="background-color: #f8d7da;">
                <td colspan="7" class="text-right"><strong>Outstanding Balance:</strong></td>
                <td class="text-right"><strong>{{ number_format(round($outstandingBalance)) }}</strong></td>
            </tr>
            @endif
        </tfoot>
    </table>

    <!-- Currency Conversion -->
    <div class="currency-section">
        <table class="currency-table">
            <thead>
                <tr>
                    <th colspan="3" class="text-center">Currency Conversion</th>
                </tr>
                <tr>
                    <th>USD</th>
                    <th>SGD</th>
                    <th>INR</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currencyConversion = $invoice->currency_conversion ?? [];
                    // Currency conversion should show Outstanding Balance
                    $outstandingBalanceForCurrency = $invoice->outstanding_balance ?? 0;
                @endphp
                <tr>
                    <td>{{ number_format(round($currencyConversion['USD'] ?? 0)) }}</td>
                    <td>{{ number_format(round($currencyConversion['SGD'] ?? $outstandingBalanceForCurrency)) }}</td>
                    <td>{{ number_format(round($currencyConversion['INR'] ?? 0)) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- Payment Terms and Bank Details -->
    @php
        // Fetch bank details from database based on tour's dmc_id
        $tour = $invoice->tour;
        $dmcId = $tour->dmc_id ?? $invoice->dmc_id ?? null;
        $bankDetail = null;
        $paymentTerms = [];
        $bankDetailsData = [];
        
        if ($dmcId) {
            // Fetch active bank details for this DMC
            $bankDetail = \App\Models\BankDetail::where('dmc_id', $dmcId)
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->first();
        }
        
        // Use database bank details if found, otherwise fall back to invoice stored data
        if ($bankDetail) {
            $paymentTerms = $bankDetail->payment_terms ?? [];
            $bankDetailsData = [
                'account_name' => $bankDetail->account_name ?? '',
                'account_number' => $bankDetail->account_number ?? '',
                'bank_address' => $bankDetail->bank_address ?? '',
                'ifsc_code' => $bankDetail->ifsc ?? null,
                'swift_bic_iban' => $bankDetail->swift_bic_iban ?? null,
                'bank_code' => $bankDetail->bank_code ?? null,
                'branch_code' => $bankDetail->branch_code ?? null,
                'aba_routing_number' => $bankDetail->aba_routing ?? null,
            ];
        } else {
            // Fallback to invoice stored data
            $paymentTerms = $invoice->payment_terms ?? [];
            $bankDetailsData = $invoice->bank_details ?? [];
        }
        
        // If no payment terms found, use default
        if (empty($paymentTerms)) {
            $dmcCompanyName = $invoice->dmc->company_name ?? 'DMC';
            $dmcEmail = $invoice->dmc->email ?? 'dmc email';
            $paymentTerms = [
                'Please pay the amount before the payment due date to avoid auto release of booking. Bank details are mentioned below.',
                'Payment/Remittance to be made in the currency as stated in the invoice.',
                'Bank charges to be borne by remitter. Please ensure that ' . $dmcCompanyName . ' receive full payment as per Invoice.',
                'To ensure prompt credit, please send payment details along with remittance advice at ' . $dmcEmail . '.',
                'Interest @ 18% will be charged on all overdues.',
                'This document is not a voucher. Voucher will be issued after the receipt of full monies & necessary documents.',
            ];
        }
    @endphp
    
    @if(!empty($paymentTerms))
    <div class="payment-terms">
        <strong>Payment Terms :</strong>
        <ol style="margin-left: 20px; margin-top: 5px;">
            @foreach($paymentTerms as $term)
            <li>{{ $term }}</li>
            @endforeach
        </ol>
    </div>
    @endif

    @if(!empty($bankDetailsData))
    <div class="bank-details">
        <strong>Bank Details:</strong>
        <table style="margin-top: 10px; background-color: white; width: 100%;">
            <tr>
                <td style="width: 40%;">Account Name</td>
                <td>{{ $bankDetailsData['account_name'] ?? '' }}</td>
            </tr>
            <tr>
                <td>Account Number</td>
                <td>{{ $bankDetailsData['account_number'] ?? '' }}</td>
            </tr>
            <tr>
                <td>Bank Address</td>
                <td>{{ $bankDetailsData['bank_address'] ?? '' }}</td>
            </tr>
            @if(!empty($bankDetailsData['ifsc_code']))
            <tr>
                <td>IFSC (For India only)</td>
                <td>{{ $bankDetailsData['ifsc_code'] }}</td>
            </tr>
            @endif
            @if(!empty($bankDetailsData['swift_bic_iban']))
            <tr>
                <td>SWIFT / BIC / IBAN Code (as applicable for international, Europe transfers)</td>
                <td>{{ $bankDetailsData['swift_bic_iban'] }}</td>
            </tr>
            @endif
            @if(!empty($bankDetailsData['bank_code']))
            <tr>
                <td>Bank Code (For Singapore)</td>
                <td>{{ $bankDetailsData['bank_code'] }}</td>
            </tr>
            @endif
            @if(!empty($bankDetailsData['branch_code']))
            <tr>
                <td>Branch Code (For Singapore)</td>
                <td>{{ $bankDetailsData['branch_code'] }}</td>
            </tr>
            @endif
            @if(!empty($bankDetailsData['aba_routing_number']))
            <tr>
                <td>ABA / Routing Number (For USA only)</td>
                <td>{{ $bankDetailsData['aba_routing_number'] }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <!-- Footer Note -->
    @php
        $tour = $invoice->tour;
        $tourStatus = $tour->tour_status ?? '';
        $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
        $shouldShowTax = in_array($tourStatus, $statusesWithTax);
    @endphp
    <div class="footer-note">
        @if($shouldShowTax)
        <strong>*Note:</strong> This is a Proforma Invoice. GST/Taxes are calculated based on the negotiated amount. This invoice is for price sharing and advance collection purposes only. No accounting entries will be made until converted to Final Invoice.
        @else
        <strong>*Note:</strong> This is a Proforma Invoice. No GST is applicable. This invoice is for price sharing and advance collection purposes only. No accounting entries will be made until converted to Final Invoice.
        @endif
    </div>

</body>
</html>

