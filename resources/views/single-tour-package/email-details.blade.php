
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Quotation - Email Template</title>
    <style>
        /* Copy Button Styles */
        .copy-button-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .copy-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .copy-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
        }
        .copy-button:active {
            transform: translateY(0);
        }
        .copy-button.copied {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .copy-button-icon {
            font-size: 16px;
        }
        .copy-success-message {
            position: fixed;
            top: 80px;
            right: 20px;
            background: #10b981;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            z-index: 1001;
            display: none;
            animation: slideInRight 0.3s ease;
        }
        .copy-success-message.show {
            display: block;
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .help-link {
            position: fixed;
            top: 20px;
            right: 200px;
            background: #17a2b8;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            z-index: 1000;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(23, 162, 184, 0.3);
        }
        .help-link:hover {
            background: #138496;
        }
    </style>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #212529;
            padding: 10px;
            background: #ffffff;
        }
        #emailContent {
            white-space: pre-wrap;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Copy Button Container -->
    <div class="copy-button-container">
        <a href="#" class="help-link" onclick="document.getElementById('instructionsBox').style.display=document.getElementById('instructionsBox').style.display==='none'?'block':'none'; return false;" title="How to paste in Gmail">❓ Help</a>
        <button class="copy-button" id="copyEmailButton" onclick="copyEmailContent()">
            <span class="copy-button-icon">📋</span>
            <span id="copyButtonText">Copy Email Content</span>
        </button>
    </div>
    
    <!-- Success Message -->
    <div class="copy-success-message" id="copySuccessMessage">
        ✓ Content copied! Paste into Gmail compose (Ctrl+V or Cmd+V)
    </div>
    
    <!-- Instructions -->
    <div style="position: fixed; top: 80px; right: 20px; background: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 8px; max-width: 320px; z-index: 999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none;" id="instructionsBox">
        <strong style="display: block; margin-bottom: 8px; color: #856404;">📧 How to paste in Gmail:</strong>
        <ol style="margin: 0; padding-left: 20px; font-size: 12px; color: #856404; line-height: 1.6;">
            <li>Click "Copy Email Content" button above</li>
            <li>Open Gmail and click "Compose"</li>
            <li>Click in the compose area</li>
            <li>Press <strong>Ctrl+V</strong> (or <strong>Cmd+V</strong> on Mac)</li>
            <li>Or right-click and select "Paste"</li>
            <li>The formatted content will appear</li>
        </ol>
        <div style="margin-top: 8px; padding: 8px; background: #fff; border-radius: 4px; font-size: 11px; color: #856404;">
            <strong>Note:</strong> If formatting doesn't appear, try right-clicking in Gmail compose and selecting "Paste without formatting" first, then paste again normally.
        </div>
        <button onclick="document.getElementById('instructionsBox').style.display='none'" style="margin-top: 8px; padding: 4px 8px; background: #856404; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 11px; width: 100%;">Got it</button>
    </div>

    <!-- Email Content Container (for copying) - Plain Text Email Format -->
    <div id="emailContent" style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; max-width: 800px; margin: 0 auto;">
@php
    $checkIn = $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('d M Y') : '-';
    $checkOut = $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('d M Y') : '-';
    $bookingId = $bookingDetails['booking_id'] ?? ($tour->display_id ?? ('DMC-' . ($tour->tour_id ?? 'N/A')));
    $leadGuestName = $bookingDetails['lead_guest_name'] ?? '';
    $proposalSentBy = $proposalDetails['proposal_sent_by'] ?? '—';
    $proposalDate = $proposalDetails['proposal_date'] ?? ($generatedAt->format('d M Y') ?? 'N/A');
    $proposalValidity = $proposalDetails['proposal_validity'] ?? 'N/A';
    $noOfAdults = $bookingDetails['no_of_adults'] ?? ($tour->adult ?? 0);
    $noOfChildren = $bookingDetails['no_of_children'] ?? ($tour->child ?? 0);
    $noOfInfants = $bookingDetails['no_of_infants'] ?? ($tour->infant ?? 0);
    $companyName = $dmcDetails['company_name'] ?? $dmcCompanyName ?? 'DMC Name';
    $companyAddress = $dmcDetails['address'] ?? ($dmcDetails['company_address'] ?? 'N/A');
    $companyTel = $dmcDetails['tel'] ?? ($dmcDetails['telephone'] ?? ($dmcDetails['phone'] ?? 'N/A'));
    $companyEmail = $dmcDetails['email'] ?? ($dmcDetails['company_email'] ?? 'N/A');
    $destination = $travelDetails['destination'] ?? ($tour->destination ?? 'N/A');
    $travelDateFrom = $travelDetails['travel_date_from'] ?? ($checkIn ?? 'N/A');
    $travelDateTo = $travelDetails['travel_date_to'] ?? ($checkOut ?? 'N/A');
    $duration = $travelDetails['duration'] ?? ($tourDuration ?? 'N/A');
    
    // Format travel dates with day names
    $travelDateFromFormatted = 'N/A';
    $travelDateToFormatted = 'N/A';
    if ($travelDateFrom !== 'N/A') {
        try {
            $fromDate = \Carbon\Carbon::parse($travelDateFrom);
            $travelDateFromFormatted = $fromDate->format('l- d/m/Y');
        } catch (\Exception $e) {
            $travelDateFromFormatted = $travelDateFrom;
        }
    }
    if ($travelDateTo !== 'N/A') {
        try {
            $toDate = \Carbon\Carbon::parse($travelDateTo);
            $travelDateToFormatted = $toDate->format('l- d/m/Y');
        } catch (\Exception $e) {
            $travelDateToFormatted = $travelDateTo;
        }
    }
    
    // Calculate duration if not provided
    if ($duration === 'N/A' && $travelDateFrom !== 'N/A' && $travelDateTo !== 'N/A') {
        try {
            $from = \Carbon\Carbon::parse($travelDateFrom);
            $to = \Carbon\Carbon::parse($travelDateTo);
            $nights = $from->diffInDays($to);
            $days = $nights + 1;
            $duration = $days . ' Days';
        } catch (\Exception $e) {
            $duration = 'N/A';
        }
    }
    
    // Agent details
    $agentName = !empty($agentDetails) ? ($agentDetails['name'] ?? ($agentDetails['company_name'] ?? '')) : '';
    $agentAddress = !empty($agentDetails) ? ($agentDetails['address'] ?? '') : '';
    $contactPerson = !empty($agentDetails) ? ($agentDetails['contact_person'] ?? '') : '';
    $agentPhone = !empty($agentDetails) ? ($agentDetails['phone'] ?? '') : '';
    $agentEmail = !empty($agentDetails) ? ($agentDetails['email'] ?? '') : '';
    
    // Get infant price
    $infantPrice = 0;
    if (isset($tourPrices['segregated']['hotel']['baby_cot']) && is_numeric($tourPrices['segregated']['hotel']['baby_cot'])) {
        $infantPrice = floatval($tourPrices['segregated']['hotel']['baby_cot']);
    }
@endphp
QUOTATION & CONFIRMATION VOUCHER

{{ $companyName }}
{{ $companyAddress }}
Tel: {{ $companyTel }}
Email: {{ $companyEmail }}

BOOKING & PROPOSAL DETAILS
Booking ID: {{ $bookingId }}
Lead Guest: {{ $leadGuestName ?: 'N/A' }}
Adults: {{ $noOfAdults }}
Children: {{ $noOfChildren }}
Infants: {{ $noOfInfants }}

Proposal Date: {{ $proposalDate }}
Proposal Validity: {{ $proposalValidity }}
Proposal Sent By: {{ $proposalSentBy }}

TRAVEL COMPANY / AGENT
@if($agentName){{ $agentName }}
@endif
@if($agentAddress){{ $agentAddress }}
@endif
@if($contactPerson)Contact Person: {{ $contactPerson }}
@endif
@if($agentPhone)Tel: {{ $agentPhone }}
@endif
@if($agentEmail)Email: {{ $agentEmail }}
@endif

TRAVEL SUMMARY
Destination: {{ $destination }}
Travel Dates: {{ $travelDateFromFormatted }} – {{ $travelDateToFormatted }}
Duration: {{ $duration }}

PASSENGER DETAILS
@php
    $passengers = $bookingDetails['passengers'] ?? [];
    if (empty($passengers) && !empty($leadGuestName)) {
        $passengers = [[
            'salutation' => $bookingDetails['salutation'] ?? 'Mr',
            'first_name' => $bookingDetails['lead_guest_name'] ?? '',
            'passenger_type' => $bookingDetails['passenger_type'] ?? 'Adult',
            'gender' => $bookingDetails['gender'] ?? 'M',
            'mobile_phone' => $bookingDetails['phone'] ?? '—',
            'email' => $bookingDetails['email'] ?? '—'
        ]];
    }
@endphp
@if(!empty($passengers) && is_array($passengers))
    @foreach($passengers as $passenger)
Salutation: {{ $passenger['salutation'] ?? 'Mr' }}
Name: {{ $passenger['first_name'] ?? '—' }}
Passenger Type: {{ $passenger['passenger_type'] ?? 'Adult' }}
Gender: {{ $passenger['gender'] ?? 'M' }}
Mobile Phone: {{ $passenger['mobile_phone'] ?? ($passenger['phone'] ?? '—') }}
Email: {{ $passenger['email'] ?? '—' }}

    @endforeach
@else
Salutation: {{ $bookingDetails['salutation'] ?? 'Mr' }}
Name: {{ $leadGuestName ?? '—' }}
Passenger Type: {{ $bookingDetails['passenger_type'] ?? 'Adult' }}
Gender: {{ $bookingDetails['gender'] ?? 'M' }}
Mobile Phone: {{ $bookingDetails['phone'] ?? '—' }}
Email: {{ $bookingDetails['email'] ?? '—' }}

@endif

HOTEL OPTIONS
OPTION 1
@if(!empty($hotelOptions) && count($hotelOptions) > 0)
    @php
        $allHotels = $hotelOptions;
        $firstHotel = $allHotels[0] ?? null;
        $additionalHotels = array_slice($allHotels, 1);
    @endphp
    @if($firstHotel)
        @php
            $totalRooms = 0;
            if (isset($firstHotel['no_of_rooms'])) {
                $totalRooms = (int)($firstHotel['no_of_rooms']['single'] ?? 0) + 
                             (int)($firstHotel['no_of_rooms']['double'] ?? 0) + 
                             (int)($firstHotel['no_of_rooms']['triple'] ?? 0);
            }
        @endphp
Hotel Name: {{ $firstHotel['hotel_name'] ?? 'N/A' }} ({{ $totalRooms }} {{ $totalRooms == 1 ? 'room' : 'rooms' }})
Hotel Category: {{ $firstHotel['hotel_category'] ?? 'N/A' }}

Room Pricing:
        @foreach($firstHotel['room_categories'] as $roomCategory)
{{ !empty($roomCategory['name']) ? $roomCategory['name'] : 'N/A' }}:
  Single: {{ is_numeric($roomCategory['single_price']) ? number_format($roomCategory['single_price'], 2) : '100.00' }}
  Double: {{ is_numeric($roomCategory['double_price']) ? number_format($roomCategory['double_price'], 2) : '150.00' }}
  Triple: {{ (is_numeric($roomCategory['triple_price']) && floatval($roomCategory['triple_price']) > 0) ? number_format($roomCategory['triple_price'], 2) : 'N/A' }}
  Child: {{ (isset($roomCategory['child_price']) && is_numeric($roomCategory['child_price'])) ? number_format($roomCategory['child_price'], 2) : '0.00' }}
  Infant: {{ number_format($infantPrice, 2) }}

        @endforeach
Total:
  Single: {{ number_format(floatval($firstHotel['first_total']['single'] ?? 0), 2) }}
  Double: {{ number_format(floatval($firstHotel['first_total']['double'] ?? 0), 2) }}
  Triple: {{ (floatval($firstHotel['first_total']['triple'] ?? 0) > 0) ? number_format(floatval($firstHotel['first_total']['triple'] ?? 0), 2) : 'N/A' }}
  Child: {{ number_format(floatval($firstHotel['first_total']['child'] ?? 0), 2) }}
  Infant: {{ number_format($infantPrice, 2) }}

        @if(count($additionalHotels) > 0)
Supplemental Cost:
            @foreach($additionalHotels as $hotel)
                @php
                    $singleRooms = (int)($hotel['no_of_rooms']['single'] ?? 0);
                    $doubleRooms = (int)($hotel['no_of_rooms']['double'] ?? 0);
                    $tripleRooms = (int)($hotel['no_of_rooms']['triple'] ?? 0);
                    $totalRooms = $singleRooms + $doubleRooms + $tripleRooms;
                @endphp
                @foreach($hotel['room_categories'] as $roomCategory)
                    @php
                        $roomCategoryName = !empty($roomCategory['name']) ? $roomCategory['name'] : 'N/A';
                    @endphp
{{ $hotel['hotel_name'] ?? 'N/A' }} - {{ $roomCategoryName }} - {{ $totalRooms }} {{ $totalRooms == 1 ? 'room' : 'rooms' }}:
  Single: {{ is_numeric($roomCategory['single_price']) ? number_format($roomCategory['single_price'], 2) : '0.00' }}
  Double: {{ is_numeric($roomCategory['double_price']) ? number_format($roomCategory['double_price'], 2) : '0.00' }}
  Triple: {{ (is_numeric($roomCategory['triple_price']) && floatval($roomCategory['triple_price']) > 0) ? number_format($roomCategory['triple_price'], 2) : 'N/A' }}
  Child: {{ (isset($roomCategory['child_price']) && is_numeric($roomCategory['child_price'])) ? number_format($roomCategory['child_price'], 2) : '0.00' }}
  Infant: {{ number_format($infantPrice, 2) }}

                @endforeach
            @endforeach
            @php
                $additionalHotelsTotalSingle = 0;
                $additionalHotelsTotalDouble = 0;
                $additionalHotelsTotalTriple = 0;
                $additionalHotelsTotalChild = 0;
                $additionalHotelsTotalInfant = 0;
                foreach($additionalHotels as $hotel) {
                    $additionalHotelsTotalSingle += floatval($hotel['first_total']['single'] ?? 0);
                    $additionalHotelsTotalDouble += floatval($hotel['first_total']['double'] ?? 0);
                    $additionalHotelsTotalTriple += floatval($hotel['first_total']['triple'] ?? 0);
                    $additionalHotelsTotalChild += floatval($hotel['first_total']['child'] ?? 0);
                    $additionalHotelsTotalInfant += floatval($hotel['first_total']['infant'] ?? 0);
                    $additionalHotelsTotalSingle += floatval($hotel['supplemental_cost']['single'] ?? 0);
                    $additionalHotelsTotalDouble += floatval($hotel['supplemental_cost']['double'] ?? 0);
                    $additionalHotelsTotalTriple += floatval($hotel['supplemental_cost']['triple'] ?? 0);
                    $additionalHotelsTotalChild += floatval($hotel['supplemental_cost']['child'] ?? 0);
                    $additionalHotelsTotalInfant += floatval($hotel['supplemental_cost']['infant'] ?? 0);
                }
                $optionFirstTotalSingle = floatval($firstHotel['first_total']['single'] ?? 0);
                $optionFirstTotalDouble = floatval($firstHotel['first_total']['double'] ?? 0);
                $optionFirstTotalTriple = floatval($firstHotel['first_total']['triple'] ?? 0);
                $optionFirstTotalChild = floatval($firstHotel['first_total']['child'] ?? 0);
                $optionFirstTotalInfant = $infantPrice;
                $optionSupplementalSingle = floatval($firstHotel['supplemental_cost']['single'] ?? 0);
                $optionSupplementalDouble = floatval($firstHotel['supplemental_cost']['double'] ?? 0);
                $optionSupplementalTriple = floatval($firstHotel['supplemental_cost']['triple'] ?? 0);
                $optionSupplementalChild = floatval($firstHotel['supplemental_cost']['child'] ?? 0);
                $optionFinalTotalSingle = $optionFirstTotalSingle + $optionSupplementalSingle + $additionalHotelsTotalSingle;
                $optionFinalTotalDouble = $optionFirstTotalDouble + $optionSupplementalDouble + $additionalHotelsTotalDouble;
                $optionFinalTotalTriple = $optionFirstTotalTriple + $optionSupplementalTriple + $additionalHotelsTotalTriple;
                $optionFinalTotalChild = $optionFirstTotalChild + $optionSupplementalChild + $additionalHotelsTotalChild;
                $optionFinalTotalInfant = $optionFirstTotalInfant + $optionSupplementalInfant + $additionalHotelsTotalInfant;
            @endphp
Final Total:
  Single: {{ number_format($optionFinalTotalSingle, 2) }}
  Double: {{ number_format($optionFinalTotalDouble, 2) }}
  Triple: {{ ($optionFinalTotalTriple > 0) ? number_format($optionFinalTotalTriple, 2) : 'N/A' }}
  Child: {{ number_format($optionFinalTotalChild, 2) }}
  Infant: {{ number_format($optionFinalTotalInfant, 2) }}

        @endif
    @endif
@endif

@if(empty($servicesByType))
No quotation items have been confirmed for this tour.
@else
    @foreach($servicesByType as $type => $cards)
        @php
            $normalizedType = str_replace(' ', '_', strtolower($type));
            if ($normalizedType === 'hotel') {
                continue;
            }
            $sectionLabel = ucwords(str_replace('_', ' ', $type));
            if ($normalizedType === 'entry_port') {
                $sectionLabel = 'Arrival Services';
            } elseif ($normalizedType === 'exit_port') {
                $sectionLabel = 'Departure Services';
            } elseif ($normalizedType === 'attraction' || $normalizedType === 'attraction_package') {
                $sectionLabel = 'Attraction Services';
            } elseif ($normalizedType === 'restaurant') {
                $sectionLabel = 'Restaurant Services';
            } elseif ($normalizedType === 'guide') {
                $sectionLabel = 'Guide Services';
            } elseif (in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly'])) {
                $sectionLabel = 'Transfer Services';
            } else {
                $sectionLabel = ucwords(str_replace('_', ' ', $type)) . ' Services';
            }
        @endphp
{{ strtoupper($sectionLabel) }}
@if($normalizedType === 'entry_port')
    @foreach($cards as $card)
        @php
            $pickup = '';
            $dropoff = '';
            $pickupDate = '';
            $entryTime = '';
            foreach ($card['chips'] ?? [] as $chip) {
                if (strtolower($chip['label']) === 'pickup') $pickup = $chip['value'];
                if (strtolower($chip['label']) === 'dropoff') $dropoff = $chip['value'];
                if (strtolower($chip['label']) === 'date') $pickupDate = $chip['value'];
                if (strtolower($chip['label']) === 'time') $entryTime = $chip['value'];
            }
            $vehicleData = $card['vehicle'] ?? [];
            $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? 'N/A';
            if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
            } else {
                $transferType = $transferTypeRaw;
            }
            $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
            $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
            $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
            $maxPassengerWithLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
            $maxLuggageCapacity = 'N/A';
            $maxPassengerWithoutLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
            $portName = $pickup ?: 'N/A';
            $flightData = $card['entry_port_flight'] ?? [];
            $flightName = $flightData['flight_name'] ?? 'TBA';
            $flightNo = $flightData['flight_no'] ?? 'TBA';
            $originDepartureTime = $flightData['origin_departure_time'] ?? 'TBA';
            $originDepartureTerminal = $flightData['origin_departure_terminal'] ?? 'TBA';
            $destinationArrivalTime = $flightData['destination_arrival_time'] ?? ($entryTime ?: 'TBA');
            $destinationArrivalTerminal = $flightData['destination_arrival_terminal'] ?? 'TBA';
        @endphp
Port of Arrival Transfer:
  Port Name: {{ $portName }}
  Transfer Type: {{ $transferType }}
  Vehicle Type / Seater: {{ $vehicleTypeSeater }}
  Vehicle No: {{ $vehicleNumber }}
  Vehicle Brand: {{ $vehicleBrand }}
  Max Passenger capacity with luggage: {{ $maxPassengerWithLuggage }}
  Max Luggage capacity: {{ $maxLuggageCapacity }}
  Max Passenger Capacity without luggage: {{ $maxPassengerWithoutLuggage }}

Flight Details:
  Flight Name: {{ $flightName }}
  Flight No.: {{ $flightNo }}
  Origin Departure Time: {{ $originDepartureTime }}
  Origin Departure Terminal: {{ $originDepartureTerminal }}
  Destination Arrival Time: {{ $destinationArrivalTime }}
  Destination Arrival Terminal: {{ $destinationArrivalTerminal }}

    @endforeach
@elseif($normalizedType === 'exit_port')
    @foreach($cards as $card)
        @php
            $pickup = '';
            $dropoff = '';
            $pickupDate = '';
            $exitTime = '';
            foreach ($card['chips'] ?? [] as $chip) {
                if (strtolower($chip['label']) === 'pickup') $pickup = $chip['value'];
                if (strtolower($chip['label']) === 'dropoff') $dropoff = $chip['value'];
                if (strtolower($chip['label']) === 'date') $pickupDate = $chip['value'];
                if (strtolower($chip['label']) === 'time') $exitTime = $chip['value'];
            }
            $vehicleData = $card['vehicle'] ?? [];
            $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? 'N/A';
            if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
            } else {
                $transferType = $transferTypeRaw;
            }
            $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
            $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
            $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
            $maxPassengerWithLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
            $maxLuggageCapacity = 'N/A';
            $maxPassengerWithoutLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
            $portName = $dropoff ?: 'N/A';
            $flightData = $card['exit_port_flight'] ?? [];
            $flightName = $flightData['flight_name'] ?? 'TBA';
            $flightNo = $flightData['flight_no'] ?? 'TBA';
            $originDepartureTime = $flightData['origin_departure_time'] ?? ($exitTime ?: 'TBA');
            $originDepartureTerminal = $flightData['origin_departure_terminal'] ?? 'TBA';
            $destinationArrivalTime = $flightData['destination_arrival_time'] ?? 'TBA';
            $destinationArrivalTerminal = $flightData['destination_arrival_terminal'] ?? 'TBA';
        @endphp
Port of Departure Transfer:
  Port Name: {{ $portName }}
  Transfer Type: {{ $transferType }}
  Vehicle Type / Seater: {{ $vehicleTypeSeater }}
  Vehicle No: {{ $vehicleNumber }}
  Vehicle Brand: {{ $vehicleBrand }}
  Max Passenger capacity with luggage: {{ $maxPassengerWithLuggage }}
  Max Luggage capacity: {{ $maxLuggageCapacity }}
  Max Passenger Capacity without luggage: {{ $maxPassengerWithoutLuggage }}

Flight Details:
  Flight Name: {{ $flightName }}
  Flight No.: {{ $flightNo }}
  Origin Departure Time: {{ $originDepartureTime }}
  Origin Departure Terminal: {{ $originDepartureTerminal }}
  Destination Arrival Time: {{ $destinationArrivalTime }}
  Destination Arrival Terminal: {{ $destinationArrivalTerminal }}

    @endforeach
@elseif($normalizedType === 'attraction' || $normalizedType === 'attraction_package')
    @foreach($cards as $card)
        @php
            $attractionData = $card['attraction'] ?? [];
            $attractionTiming = $attractionData['visit_time'] ?? 'N/A';
            $transferRequired = $attractionData['transfer_required'] ?? 'N/A';
            $transferTypeRaw = $attractionData['transfer_type'] ?? 'N/A';
            if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
            } else {
                $transferType = $transferTypeRaw;
            }
        @endphp
Attraction Name: {{ $card['title'] ?? 'N/A' }}
Attraction Timing: {{ $attractionTiming }}
Transfer: {{ $transferRequired }}
Transfer Type: {{ $transferType }}

    @endforeach
@elseif($normalizedType === 'restaurant')
    @foreach($cards as $card)
        @php
            $restaurantData = $card['restaurant'] ?? [];
            $mealPlan = $restaurantData['meal_plan'] ?? 'N/A';
            $mealType = $restaurantData['meal_type'] ?? 'N/A';
            $transferRequired = $restaurantData['transfer_required'] ?? 'N/A';
            $transferTypeRaw = $restaurantData['transfer_type'] ?? 'N/A';
            if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
            } else {
                $transferType = $transferTypeRaw;
            }
        @endphp
Restaurant Name: {{ $card['title'] ?? 'N/A' }}
Meal Plan: {{ $mealPlan }}
Meal Type: {{ $mealType }}
Transfer: {{ $transferRequired }}
Transfer Type: {{ $transferType }}

    @endforeach
@elseif($normalizedType === 'guide')
    @foreach($cards as $card)
        @php
            $guideData = $card['guide'] ?? [];
            $guideName = $guideData['guide_name'] ?? $card['title'] ?? 'N/A';
            $languageProficiency = $guideData['language_proficiency'] ?? 'N/A';
            $totalExperience = $guideData['total_experience'] ?? 'N/A';
        @endphp
Tour Guide Name: {{ $guideName }}
Language Proficiency: {{ $languageProficiency }}
Total Experience: {{ $totalExperience }}

    @endforeach
@elseif(in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly']))
    @foreach($cards as $card)
        @php
            $vehicleData = $card['vehicle'] ?? [];
            $transferTypeRaw = $vehicleData['transfer_type'] ?? 'N/A';
            if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
            } else {
                $transferType = $transferTypeRaw;
            }
            $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
            $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
            $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
            $maxPassengerCapacity = $vehicleData['max_passenger_capacity'] ?? $vehicleData['seating_capacity'] ?? 'N/A';
        @endphp
Transfer Type: {{ $transferType }}
Vehicle Type / Seater: {{ $vehicleTypeSeater }}
Vehicle No: {{ $vehicleNumber }}
Vehicle Brand: {{ $vehicleBrand }}
Max Passenger Capacity: {{ $maxPassengerCapacity }}

    @endforeach
@else
    @foreach($cards as $card)
        @php
            $dateValue = '';
            $timeValue = '';
            foreach ($card['chips'] ?? [] as $chip) {
                if (strtolower($chip['label']) === 'date') $dateValue = $chip['value'];
                if (strtolower($chip['label']) === 'time') $timeValue = $chip['value'];
            }
        @endphp
Service Name: {{ $card['title'] ?? 'N/A' }}
Date: {{ $dateValue ?: 'N/A' }}
Time: {{ $timeValue ?: 'N/A' }}
Location: {{ $card['subtitle'] ?? 'N/A' }}
Details: {{ $card['notes'] ?? 'N/A' }}

    @endforeach
@endif
    @endforeach
@endif

IMPORTANT NOTES
*Please note that this is not a tour itinerary / schedule, a confirmed tour itinerary / schedule is only generated post confirmation of the tour and payment is completed.

*The above quotation only specifies the optionwise costs based on the tour requirements with standard exclusions & Inclusions as mentioned above.
    </div>
    <!-- End of Email Content Container -->

    <script>
        async function copyEmailContent() {
            const emailContent = document.getElementById('emailContent');
            const copyButton = document.getElementById('copyEmailButton');
            const copyButtonText = document.getElementById('copyButtonText');
            const successMessage = document.getElementById('copySuccessMessage');
            
            try {
                // Get the plain text content (not HTML)
                const textContent = emailContent.innerText || emailContent.textContent || '';
                
                // Copy as plain text for email
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(textContent);
                    showSuccess('Email content copied! Paste into Gmail compose using Ctrl+V (or Cmd+V on Mac)');
                    return;
                }
                
                // Fallback: Use execCommand
                const textArea = document.createElement('textarea');
                textArea.value = textContent;
                textArea.style.position = 'fixed';
                textArea.style.left = '-9999px';
                textArea.style.top = '0';
                document.body.appendChild(textArea);
                textArea.select();
                
                try {
                    const success = document.execCommand('copy');
                    if (success) {
                        showSuccess('Email content copied! Paste into Gmail compose using Ctrl+V');
                    } else {
                        throw new Error('execCommand failed');
                    }
                } catch (e) {
                    alert('Please manually select all content (Ctrl+A) and copy (Ctrl+C), then paste into Gmail compose.');
                }
                
                document.body.removeChild(textArea);
                
                function showSuccess(message) {
                    copyButton.classList.add('copied');
                    copyButtonText.textContent = 'Copied!';
                    if (message) {
                        successMessage.textContent = '✓ ' + message;
                    }
                    successMessage.classList.add('show');
                    
                    setTimeout(() => {
                        copyButton.classList.remove('copied');
                        copyButtonText.textContent = 'Copy Email Content';
                        successMessage.textContent = '✓ Content copied! Paste into Gmail compose (Ctrl+V or Cmd+V)';
                        successMessage.classList.remove('show');
                    }, 4000);
                }
            } catch (err) {
                console.error('Copy error:', err);
                alert('Copy failed. Please manually select all content (Ctrl+A) and copy (Ctrl+C), then paste into Gmail compose.');
            }
        }
        
        // Attach event listener
        document.addEventListener('DOMContentLoaded', function() {
            const copyButton = document.getElementById('copyEmailButton');
            if (copyButton) {
                copyButton.onclick = copyEmailContent;
            }
        });
    </script>
</body>
</html>
