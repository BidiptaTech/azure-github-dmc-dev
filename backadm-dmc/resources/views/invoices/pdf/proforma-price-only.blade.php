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
            color: #000;
            padding: 20px;
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
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
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
        .text-center {
            text-align: center;
        }
        .mb-2 {
            margin-bottom: 8px;
        }
        .mt-2 {
            margin-top: 8px;
        }
        .header-top {
            text-align: center;
            margin-bottom: 16px;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .dmc-logo-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .dmc-logo {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
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
        @if($dmcLogoSrc)
        <div class="header-top">
            <div class="dmc-logo-wrapper">
                <img src="{{ $dmcLogoSrc }}" class="dmc-logo" />
            </div>
        </div>
        @endif
        <h1>PROFORMA INVOICE</h1>
        <p><strong>{{ $dmcCompanyName }}</strong></p>
        <p>Proforma Number: <strong>{{ $invoice->proforma_number ?? 'DRAFT' }}</strong></p>
    </div>

    <!-- Client/Guest Information -->
    <table class="invoice-info">
        <tr>
            <td colspan="3"><strong>Client/Guest Information:</strong></td>
        </tr>
        @php
            $clientDetails = $invoice->client_details ?? [];
        @endphp
        <tr>
            <td>Address:</td>
            <td colspan="2">{{ $clientDetails['address'] ?? '' }}</td>
        </tr>
        <tr>
            <td>State:</td>
            <td>{{ $clientDetails['city'] ?? '' }}</td>
            <td>Postal Code: {{ $clientDetails['postal_code'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Email:</td>
            <td>{{ $clientDetails['email'] ?? '' }}</td>
            <td>Phone: {{ $clientDetails['phone'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Booking ID:</td>
            <td>{{ $clientDetails['booking_id'] ?? '' }}</td>
            <td>Lead Guest: {{ $clientDetails['lead_guest_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td>No. of Adults:</td>
            <td>{{ $invoice->no_of_adults ?? 0 }}</td>
            <td>No. of Children: {{ $invoice->no_of_children ?? 0 }}</td>
        </tr>
        <tr>
            <td>No. of Infants:</td>
            <td colspan="2">{{ $invoice->no_of_infants ?? 0 }}</td>
        </tr>
    </table>

    <!-- Proposal Details -->
    <table class="invoice-info" style="width: 50%; float: right;">
        <tr>
            <td><strong>Proposal Details:</strong></td>
        </tr>
        <tr>
            <td>Postal / Pin: {{ $clientDetails['postal_code'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Proposal Date: {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('jS M Y') : '' }}</td>
        </tr>
        <tr>
            <td>Proposal Validity: {{ $invoice->validity_date ? \Carbon\Carbon::parse($invoice->validity_date)->format('jS M Y') : '' }}</td>
        </tr>
        <tr>
            <td>Proposal Sent by: {{ $invoice->sent_by ?? '' }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <!-- Travel Company/Agent Information -->
    @php
        $travelCompany = $invoice->travel_company_details ?? [];
    @endphp
    @if(!empty($travelCompany))
    <table class="invoice-info">
        <tr>
            <td colspan="2"><strong>Travel Company / Agent Name:</strong> {{ $travelCompany['name'] ?? '' }}</td>
        </tr>
        @if(!empty($travelCompany['company_name']))
        <tr>
            <td>Travel Agency:</td>
            <td>{{ $travelCompany['company_name'] ?? '' }}</td>
        </tr>
        @endif
        <tr>
            <td>Address:</td>
            <td>{{ $travelCompany['address'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Contact Person:</td>
            <td>{{ $travelCompany['contact_person'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Phone:</td>
            <td>{{ $travelCompany['phone'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Email:</td>
            <td>{{ $travelCompany['email'] ?? '' }}</td>
        </tr>
    </table>
    @endif

    <!-- Travel Dates & Destination -->
    <table class="invoice-info">
        <tr>
            <td><strong>Destination:</strong> {{ $invoice->destination ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Travel Date:</strong></td>
            <td><strong>From:</strong> {{ $invoice->travel_from_date ? \Carbon\Carbon::parse($invoice->travel_from_date)->format('jS M Y') : '' }}</td>
            <td><strong>To:</strong> {{ $invoice->travel_to_date ? \Carbon\Carbon::parse($invoice->travel_to_date)->format('jS M Y') : '' }}</td>
            <td><strong>Duration / No of Days:</strong> {{ $invoice->duration_days ?? '' }} days</td>
        </tr>
    </table>

    <!-- Service Description (Without Prices) -->
    <div class="section-title">Description</div> 
    
    @php
        $allItems = $invoice->items ?? collect([]);
        $hotelItems = $allItems->where('item_type', 'hotel');
        $entryPortItems = $allItems->where('item_type', 'entry_port');
        $attractionItems = $allItems->where('item_type', 'attraction');
        $restaurantItems = $allItems->where('item_type', 'restaurant');
        $guideItems = $allItems->where('item_type', 'guide');
        $travelPointItems = $allItems->where('item_type', 'travel_point');
        $travelHourlyItems = $allItems->where('item_type', 'travel_hourly');
        $localTransportItems = $allItems->where('item_type', 'local_transport');
        $exitPortItems = $allItems->where('item_type', 'exit_port');
        $otherItems = $allItems->whereNotIn('item_type', ['hotel', 'entry_port', 'attraction', 'restaurant', 'guide', 'travel_point', 'travel_hourly', 'local_transport', 'exit_port']);
    @endphp

    @if($hotelItems->count() > 0)
    <!-- Hotel Services Table (No Prices) -->
    <div class="section-title">Hotel Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Hotel Name</th>
                <th>Room Category</th>
                <th>Check in</th>
                <th>Check out</th>
                <th>No. of days</th>
                <th>Total Pax</th>
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($entryPortItems->count() > 0)
    <!-- Arrival Services Table (No Prices) -->
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
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($attractionItems->count() > 0)
    <!-- Attraction Services Table (No Prices) -->
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($restaurantItems->count() > 0)
    <!-- Restaurant Services Table (No Prices) -->
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($guideItems->count() > 0)
    <!-- Guide Services Table (No Prices) -->
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($travelPointItems->count() > 0)
    <!-- Travel Point Services Table (No Prices) -->
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
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($travelHourlyItems->count() > 0)
    <!-- Travel Hourly Services Table (No Prices) -->
    <div class="section-title">Hourly Tour Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Pickup Location</th>
                <th>Dropoff Location</th>
                <th>Vehicle Name</th>
                <th>Pickup Date</th>
                <th>Total Persons</th>
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
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($localTransportItems->count() > 0)
    <!-- Local Transport Services Table (No Prices) -->
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
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($exitPortItems->count() > 0)
    <!-- Departure Services Table (No Prices) -->
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
                            $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                            $timeParts = explode(':', $timeStr);
                            if (count($timeParts) >= 2) {
                                $hour = (int)$timeParts[0];
                                $minute = (int)$timeParts[1];
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
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    @if($otherItems->count() > 0)
    <!-- Other Services Table (No Prices) -->
    <div class="section-title">Other Services</div>
    <div style="page-break-inside: avoid;">
    <table style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Description</th>
                <th>Details</th>
                <th>Adults</th>
                <th>Children</th>
                <th>Infants</th>
            </tr>
        </thead>
        <tbody>
            @foreach($otherItems as $item)
            @php
                $serviceDetails = $item->service_details ?? [];
            @endphp
            <tr>
                <td>{{ $item->description ?? '' }}</td>
                <td>{{ $serviceDetails['details'] ?? '' }}</td>
                <td>{{ $item->quantity_adults ?? 0 }}</td>
                <td>{{ $item->quantity_children ?? 0 }}</td>
                <td>{{ $item->quantity_infants ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif

    <!-- Summary Table (Prices Only) -->
    <div class="section-title">Price Summary</div>
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

