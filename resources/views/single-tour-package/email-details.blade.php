
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Quotation - Email Template</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background:#ffffff; color:#111827;">
    <!-- Copy Button Container -->
    <div style="position:fixed; top:20px; right:20px; z-index:1000;">
        <a href="#"
           onclick="document.getElementById('instructionsBox').style.display=document.getElementById('instructionsBox').style.display==='none'?'block':'none'; return false;"
           title="How to paste in Gmail"
           style="position:fixed; top:20px; right:200px; background:#17a2b8; color:#ffffff; padding:8px 12px; border-radius:6px; font-size:12px; cursor:pointer; z-index:1000; text-decoration:none; box-shadow:0 2px 6px rgba(23,162,184,0.3);">
            ❓ Help
        </a>
        <button id="copyEmailButton"
                onclick="copyEmailContent()"
                style="background:#4f46e5; color:#ffffff; border:none; padding:12px 18px; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 8px 20px rgba(79,70,229,0.25);">
            <span style="font-size:16px; vertical-align:middle;">📋</span>
            <span id="copyButtonText" style="vertical-align:middle; margin-left:8px;">Copy Email Content</span>
        </button>
    </div>
    
    <!-- Success Message -->
    <div id="copySuccessMessage" style="position:fixed; top:80px; right:20px; background:#10b981; color:#ffffff; padding:12px 16px; border-radius:10px; font-size:13px; font-weight:700; box-shadow:0 8px 20px rgba(16,185,129,0.25); z-index:1001; display:none;">
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
    <div id="emailContent" style="margin:0 auto; max-width:760px;">
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

    // Logo: use the same dynamic source strategy as sidebar.blade.php
    $logoSrc = null;
    $masterLogo = '';
    try {
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $brandUser = $currentUser;
        if ($currentUser) {
            $dmcId = \App\Helpers\CommonHelper::getDmcId($currentUser);
            if (!empty($dmcId)) {
                $dmcUser = \App\Models\User::where('userId', $dmcId)->first();
                if ($dmcUser) {
                    $brandUser = $dmcUser;
                }
            }
        }

        $masterLogo = \App\Helpers\CommonHelper::masterSettingsName('logo')['master_value'] ?? '';
        $logoSrc = trim((string)($brandUser->logo ?? ''));
        if ($logoSrc === '') {
            $logoSrc = trim((string)$masterLogo);
        }
    } catch (\Throwable $e) {
        $logoSrc = null;
    }

    // Additional fallbacks from already-available variables in this view
    if (empty($logoSrc) && !empty($brandLogo)) {
        $logoSrc = (string)$brandLogo;
    } elseif (empty($logoSrc) && !empty($dmcLogo)) {
        $logoSrc = (string)$dmcLogo;
    } elseif (empty($logoSrc) && !empty($dmcDetails) && is_array($dmcDetails)) {
        $logoSrc = $dmcDetails['logo'] ?? ($dmcDetails['company_logo'] ?? ($dmcDetails['logo_url'] ?? null));
        $logoSrc = !empty($logoSrc) ? (string)$logoSrc : null;
    }

    // Make logo URL absolute for Gmail/Outlook rendering
    $logoSrcAbs = null;
    if (!empty($logoSrc)) {
        // If it's already a data URI, keep as-is (do NOT run through asset()/url())
        // Note: data URIs won't reliably render in all email clients, but should preview correctly.
        $trimmedLogo = trim((string)$logoSrc);
        if (stripos($trimmedLogo, 'data:') === 0) {
            $logoSrcAbs = $trimmedLogo;
        } else {
        $isAbsolute = false;
        try {
            $isAbsolute = filter_var($logoSrc, FILTER_VALIDATE_URL) !== false;
        } catch (\Throwable $e) {
            $isAbsolute = false;
        }

        if ($isAbsolute) {
            $logoSrcAbs = $logoSrc;
        } else {
            // Try common Laravel path patterns: "/...", "storage/...", "public/..."
            $path = $logoSrc;
            try {
                if (strpos($path, '/') === 0) {
                    $logoSrcAbs = url($path);
                } else {
                    // Prefer Storage::url when it's a storage path
                    if (strpos($path, 'storage/') === 0) {
                        $logoSrcAbs = asset($path);
                    } else {
                        $logoSrcAbs = asset($path);
                    }
                }
            } catch (\Throwable $e) {
                $logoSrcAbs = $logoSrc; // fallback (may still not render if relative)
            }
        }
        }
    }

    // For outgoing email, prefer non-data URLs (many clients strip data URIs).
    $logoSrcEmail = null;
    if (!empty($logoSrcAbs) && stripos(trim((string)$logoSrcAbs), 'data:') !== 0) {
        $logoSrcEmail = $logoSrcAbs;
    } elseif (!empty($masterLogo) && stripos(trim((string)$masterLogo), 'data:') !== 0) {
        $logoSrcEmail = preg_match('/^https?:\/\//i', $masterLogo) ? $masterLogo : asset(ltrim($masterLogo, '/'));
    }
@endphp
<!-- Email background wrapper -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#F5F7FB; margin:0; padding:0; width:100%;">
    <tr>
        <td align="center" style="padding:14px 10px;">
            <!-- Container -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="760" style="width:760px; max-width:760px; background:#F5F7FB;">
                <tr>
                    <td style="padding:0 0 8px 0;">
                        <!-- Header -->
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px; width:100%;">
                            <tr>
                                <td style="padding:10px 12px 6px 12px;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
                                        <tr>
                                            <!-- Row 1: Logo (left) + Company details (right) -->
                                            <td valign="top" align="left" width="30%" style="font-family:Arial, sans-serif; vertical-align:top; padding-top:0; line-height:0;">
                                                @if(!empty($logoSrcEmail))
                                                    <img src="{{ $logoSrcEmail }}"
                                                         alt="{{ $companyName }} Logo"
                                                         width="120"
                                                         style="display:block; width:120px; max-width:120px; height:auto; border:0; outline:none; text-decoration:none; margin:0; line-height:0;" />
                                                @endif
                                            </td>
                                            <td valign="top" align="right" width="70%" style="font-family:Arial, sans-serif; color:#111827; vertical-align:top;">
                                                <div style="font-size:14px; font-weight:800; line-height:1.3; margin:0; padding:0;">{{ $companyName }}</div>
                                                <div style="font-size:12px; color:#6B7280; line-height:1.4; margin-top:3px;">
                                                    {{ $companyAddress }}<br/>
                                                    Tel: {{ $companyTel }}<br/>
Email: {{ $companyEmail }}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <!-- Row 2: Title (left) + Booking ID (right) -->
                                            <td colspan="2" style="padding-top:8px; border-top:1px solid #E5E7EB; font-family:Arial, sans-serif; vertical-align:top;">
                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
                                                    <tr>
                                                        <td valign="top" align="left" style="padding:0; font-family:Arial, sans-serif;">
                                                            <div style="font-size:13px; font-weight:900; color:#111827; line-height:1.3;">
                                                                Quotation &amp; Confirmation Voucher
                                                            </div>
                                                        </td>
                                                        <td valign="top" align="right" style="padding:0; font-family:Arial, sans-serif;">
                                                            <div style="font-size:12px; color:#6B7280; line-height:1.5;">Booking ID</div>
                                                            <div style="font-size:16px; font-weight:900; color:#111827; line-height:1.3;">{{ $bookingId }}</div>
                                                            <div style="margin-top:5px;">
                                                                <span style="display:inline-block; padding:6px 10px; background:#DCFCE7; color:#166534; border:1px solid #BBF7D0; border-radius:999px; font-size:12px; font-weight:900; line-height:1;">
                                                                    Confirmed
                                                                </span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 12px 10px 12px;">
                                    <div style="font-family:Arial, sans-serif; font-size:12px; color:#6B7280; line-height:1.6;">
                                        Proposal Date: <span style="color:#111827; font-weight:700;">{{ $proposalDate }}</span> &nbsp;&nbsp;•&nbsp;&nbsp;
                                        Validity: <span style="color:#111827; font-weight:700;">{{ $proposalValidity }}</span> &nbsp;&nbsp;•&nbsp;&nbsp;
                                        Sent By: <span style="color:#111827; font-weight:700;">{{ $proposalSentBy }}</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <!-- /Header -->
                    </td>
                </tr>

                <!-- Booking Summary -->
                <tr>
                    <td style="padding:0 0 8px 0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px; width:100%;">
                            <tr>
                                <td style="padding:10px 12px;">
                                    <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:900; letter-spacing:0.2px; color:#111827; margin:0 0 6px 0;">Booking Summary</div>
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
                                        <tr>
                                            <td width="50%" valign="top" style="padding-right:6px;">
                                                <div style="font-family:Arial, sans-serif; font-size:11px; color:#6B7280; margin:0 0 4px 0;">Destination</div>
                                                <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:800; color:#111827; margin:0 0 6px 0;">{{ $destination }}</div>

                                                <div style="font-family:Arial, sans-serif; font-size:11px; color:#6B7280; margin:0 0 4px 0;">Travel Dates</div>
                                                <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:800; color:#111827; margin:0;">{{ $travelDateFromFormatted }} – {{ $travelDateToFormatted }}</div>
                                            </td>
                                            <td width="50%" valign="top" style="padding-left:6px;">
                                                <div style="font-family:Arial, sans-serif; font-size:11px; color:#6B7280; margin:0 0 4px 0;">Guests</div>
                                                <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:800; color:#111827; margin:0 0 6px 0;">
                                                    Adults: {{ $noOfAdults }} &nbsp;&nbsp; Children: {{ $noOfChildren }} &nbsp;&nbsp; Infants: {{ $noOfInfants }}
                                                </div>

                                                <div style="font-family:Arial, sans-serif; font-size:11px; color:#6B7280; margin:0 0 4px 0;">Duration</div>
                                                <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:800; color:#111827; margin:0;">{{ $duration }}</div>
                                            </td>
                                        </tr>
                                    </table>

                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; margin-top:4px;">
                                        <tr>
                                            <td style="border-top:1px solid #E5E7EB; padding-top:8px;">
                                                <div style="font-family:Arial, sans-serif; font-size:11px; color:#6B7280; margin:0 0 6px 0;">Travel Company / Agent</div>
                                                <div style="font-family:Arial, sans-serif; font-size:12px; color:#111827; line-height:1.6;">
                                                    @if($agentName)<strong style="color:#111827;">{{ $agentName }}</strong><br/>@endif
                                                    @if($agentAddress){{ $agentAddress }}<br/>@endif
                                                    @if($contactPerson)Contact Person: {{ $contactPerson }}<br/>@endif
                                                    @if($agentPhone)Tel: {{ $agentPhone }}<br/>@endif
                                                    @if($agentEmail)Email: {{ $agentEmail }}@endif
                                                    @if(!$agentName && !$agentAddress && !$contactPerson && !$agentPhone && !$agentEmail)
                                                        —
@endif
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Passenger Details -->
                <tr>
                    <td style="padding:0 0 8px 0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px; width:100%;">
                            <tr>
                                <td style="padding:12px 14px;">
                                    <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:900; color:#111827; margin:0 0 10px 0;">Lead Passenger Details</div>
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
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border:1px solid #E5E7EB; border-radius:10px;">
                                        <tr>
                                            <td style="background:#F9FAFB; padding:10px 10px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:800; border-bottom:1px solid #E5E7EB;">Name</td>
                                            <td style="background:#F9FAFB; padding:10px 10px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:800; border-bottom:1px solid #E5E7EB;">Type</td>
                                            <td style="background:#F9FAFB; padding:10px 10px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:800; border-bottom:1px solid #E5E7EB;">Phone</td>
                                            <td style="background:#F9FAFB; padding:10px 10px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:800; border-bottom:1px solid #E5E7EB;">Email</td>
                                        </tr>
@if(!empty($passengers) && is_array($passengers))
    @foreach($passengers as $passenger)
                                        <tr>
                                            <td style="padding:10px 10px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                <strong>{{ ($passenger['salutation'] ?? 'Mr') . ' ' . ($passenger['first_name'] ?? '—') }}</strong>
                                            </td>
                                            <td style="padding:10px 10px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                {{ $passenger['passenger_type'] ?? 'Adult' }}
                                            </td>
                                            <td style="padding:10px 10px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                {{ $passenger['mobile_phone'] ?? ($passenger['phone'] ?? '—') }}
                                            </td>
                                            <td style="padding:10px 10px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                {{ $passenger['email'] ?? '—' }}
                                            </td>
                                        </tr>
    @endforeach
@else
                                        <tr>
                                            <td style="padding:10px 10px; font-family:Arial, sans-serif; font-size:12px; color:#111827;">
                                                <strong>{{ ($bookingDetails['salutation'] ?? 'Mr') . ' ' . ($leadGuestName ?? '—') }}</strong>
                                            </td>
                                            <td style="padding:10px 10px; font-family:Arial, sans-serif; font-size:12px; color:#111827;">
                                                {{ $bookingDetails['passenger_type'] ?? 'Adult' }}
                                            </td>
                                            <td style="padding:10px 10px; font-family:Arial, sans-serif; font-size:12px; color:#111827;">
                                                {{ $bookingDetails['phone'] ?? '—' }}
                                            </td>
                                            <td style="padding:10px 10px; font-family:Arial, sans-serif; font-size:12px; color:#111827;">
                                                {{ $bookingDetails['email'] ?? '—' }}
                                            </td>
                                        </tr>
@endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Hotel Options -->
                <tr>
                    <td style="padding:0 0 8px 0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px; width:100%;">
                            <tr>
                                <td style="padding:12px 14px;">
                                    <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:900; color:#111827; margin:0 0 10px 0;">Hotel</div>
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; margin-top:6px; border:1px solid #E5E7EB; border-radius:10px;">
                                        <tr>
                                            <td style="background:#111827; padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; font-weight:900; color:#FFFFFF;">
                                                HOTEL / ACCOMMODATION SERVICES
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:0;">
                                                @php
                                                    $allHotels = !empty($hotelOptions) && is_array($hotelOptions) ? $hotelOptions : [];
        $firstHotel = $allHotels[0] ?? null;
        $additionalHotels = array_slice($allHotels, 1);
    @endphp
                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse;">
                                                    <tr>
                                                        <td style="background:#F9FAFB; padding:10px 12px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Information</td>
                                                    </tr>
                                                    <tr>
                                                        <td valign="top" style="padding:12px 12px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
@if(empty($allHotels))
                                    <div style="font-family:Arial, sans-serif; font-size:12px; color:#111827;">No hotel services booked for this tour.</div>
@elseif(!empty($allHotels) && count($allHotels) > 0)
    @foreach($allHotels as $hotelIndex => $hotel)
        @php
            $totalRooms = 0;
            if (isset($hotel['no_of_rooms'])) {
                $totalRooms = (int)($hotel['no_of_rooms']['single'] ?? 0) +
                             (int)($hotel['no_of_rooms']['double'] ?? 0) +
                             (int)($hotel['no_of_rooms']['triple'] ?? 0);
            }
            $roomCategories = $hotel['room_categories'] ?? [];
            $hotelCategoryValue = trim((string)($hotel['hotel_category'] ?? ''));
            $hotelRemarks = $hotel['remarks'] ?? ($hotel['note'] ?? ($hotel['notes'] ?? ($hotel['specialRequests'] ?? ($hotel['special_requests'] ?? ''))));
            if (is_array($hotelRemarks)) {
                $hotelRemarks = json_encode($hotelRemarks);
            }
            $hotelRemarks = trim((string)$hotelRemarks);

            // Per-hotel guests: sum beds[*].head_count from nested rooms payload when available
            $hotelGuestsCount = null;
            if (isset($hotel['rooms']) && is_array($hotel['rooms'])) {
                $sum = 0;
                foreach ($hotel['rooms'] as $r) {
                    $beds = (is_array($r) && isset($r['beds']) && is_array($r['beds'])) ? $r['beds'] : [];
                    foreach ($beds as $b) {
                        if (is_array($b) && isset($b['head_count']) && is_numeric($b['head_count'])) {
                            $sum += (int)$b['head_count'];
                        }
                    }
                }
                if ($sum > 0) {
                    $hotelGuestsCount = $sum;
                }
            }
            @endphp
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border:1px solid #E5E7EB; border-radius:10px;">
                                        <tr>
                                            <td style="padding:12px 12px; font-family:Arial, sans-serif; font-size:12px; color:#111827;">
                                                <div style="font-size:12px; color:#6B7280; margin:0 0 4px 0;">Hotel {{ $hotelIndex + 1 }}</div>
                                                <div style="font-size:13px; font-weight:900; margin:0 0 4px 0;">{{ $hotel['hotel_name'] ?? 'N/A' }} <span style="font-weight:700; color:#6B7280;">({{ $totalRooms }} {{ $totalRooms == 1 ? 'room' : 'rooms' }})</span></div>
                                                @if($hotelCategoryValue !== '' && strtoupper($hotelCategoryValue) !== 'N/A')
                                                    <div style="font-size:12px; color:#6B7280; margin:0;">Category: <span style="color:#111827; font-weight:800;">{{ $hotelCategoryValue }}</span></div>
        @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:0 12px 12px 12px;">
                                                <div style="font-family:Arial, sans-serif; font-size:11px; font-weight:900; color:#111827; margin:0 0 8px 0;">Room Pricing</div>
                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border:1px solid #E5E7EB; border-radius:10px;">
                                                    <tr>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Room</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Single</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Double</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Triple</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Child</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Infant</td>
                                                    </tr>
        @foreach($roomCategories as $roomCategory)
                                                    <tr>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB; font-weight:800;">
                                                            {{ !empty($roomCategory['name']) ? $roomCategory['name'] : 'N/A' }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                            {{ is_numeric($roomCategory['single_price']) ? number_format($roomCategory['single_price'], 2) : '100.00' }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                            {{ is_numeric($roomCategory['double_price']) ? number_format($roomCategory['double_price'], 2) : '150.00' }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                            {{ (is_numeric($roomCategory['triple_price']) && floatval($roomCategory['triple_price']) > 0) ? number_format($roomCategory['triple_price'], 2) : 'N/A' }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                            {{ (isset($roomCategory['child_price']) && is_numeric($roomCategory['child_price'])) ? number_format($roomCategory['child_price'], 2) : '0.00' }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                            {{ number_format($infantPrice, 2) }}
                                                        </td>
                                                    </tr>
        @endforeach
                                                </table>

                                                <div style="height:10px; line-height:10px; font-size:10px;">&nbsp;</div>
                                                <div style="font-family:Arial, sans-serif; font-size:11px; font-weight:900; color:#111827; margin:0 0 8px 0;">Total</div>
                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border:1px solid #E5E7EB; border-radius:10px;">
                                                    <tr>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Single</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Double</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Triple</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Child</td>
                                                        <td style="background:#F9FAFB; padding:8px 8px; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; font-weight:900; border-bottom:1px solid #E5E7EB;">Infant</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900;">
                                                            {{ number_format(floatval($hotel['first_total']['single'] ?? 0), 2) }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900;">
                                                            {{ number_format(floatval($hotel['first_total']['double'] ?? 0), 2) }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900;">
                                                            {{ (floatval($hotel['first_total']['triple'] ?? 0) > 0) ? number_format(floatval($hotel['first_total']['triple'] ?? 0), 2) : 'N/A' }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900;">
                                                            {{ number_format(floatval($hotel['first_total']['child'] ?? 0), 2) }}
                                                        </td>
                                                        <td style="padding:8px 8px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900;">
                                                            {{ number_format($infantPrice, 2) }}
                                                        </td>
                                                    </tr>
                                                </table>
                                                <div style="font-family:Arial, sans-serif; font-size:12px; color:#6B7280; margin:6px 0 0 0;">
                                                    Guests: <span style="color:#111827; font-weight:800;">{{ $hotelGuestsCount !== null ? $hotelGuestsCount : 'N/A' }}</span>
                                                </div>
                                                <div style="font-family:Arial, sans-serif; font-size:12px; color:#6B7280; margin:6px 0 0 0;">
                                                    Remarks: <span style="color:#111827; font-weight:800;">{{ $hotelRemarks !== '' ? $hotelRemarks : 'N/A' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    @if($hotelIndex < count($allHotels) - 1)
                                        <div style="height:6px; line-height:6px; font-size:6px;">&nbsp;</div>
@endif
                @endforeach
        @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

@php
    // Define all possible service types
    $allServiceTypes = [
        'entry_port' => 'Arrival Services',
        'exit_port' => 'Departure Services',
        'attraction' => 'Attraction Services',
        'attraction_package' => 'Attraction Services',
        'restaurant' => 'Restaurant Services',
        'guide' => 'Guide Services',
        'travel_point' => 'Transfer Services',
        'travel_hourly' => 'Transfer Services',
        'local_transport' => 'Transfer Services',
        'local_transfer' => 'Transfer Services',
        'point_to_point' => 'Transfer Services',
        'hourly' => 'Transfer Services',
    ];
    
    // Normalize servicesByType keys
    $normalizedServicesByType = [];
    if (!empty($servicesByType) && is_array($servicesByType)) {
        foreach ($servicesByType as $type => $cards) {
            $normalizedType = str_replace(' ', '_', strtolower($type));
            if ($normalizedType !== 'hotel') {
                $normalizedServicesByType[$normalizedType] = $cards;
            }
        }
    }
    
    // Group by section label to avoid duplicates
    $servicesBySection = [];
    foreach ($normalizedServicesByType as $type => $cards) {
        if ($type === 'entry_port') {
            $sectionLabel = 'Arrival Services';
        } elseif ($type === 'exit_port') {
            $sectionLabel = 'Departure Services';
        } elseif ($type === 'attraction' || $type === 'attraction_package') {
            $sectionLabel = 'Attraction Services';
        } elseif ($type === 'restaurant') {
            $sectionLabel = 'Restaurant Services';
        } elseif ($type === 'guide') {
            $sectionLabel = 'Guide Services';
        } elseif (in_array($type, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly'])) {
            $sectionLabel = 'Transfer Services';
        } else {
            $sectionLabel = ucwords(str_replace('_', ' ', $type)) . ' Services';
        }
        
        if (!isset($servicesBySection[$sectionLabel])) {
            $servicesBySection[$sectionLabel] = [];
        }
        $servicesBySection[$sectionLabel] = array_merge($servicesBySection[$sectionLabel], $cards);
    }
    
    // Define sections to show
    $sectionsToShow = [
        'Arrival Services / Port of Entry' => 'entry_port',
        'Departure Services / Port of Exit' => 'exit_port',
        'Attraction Services' => ['attraction', 'attraction_package'],
        'Restaurant Services' => 'restaurant',
        'Guide Services' => 'guide',
        'Transfer Services' => ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly'],
    ];
@endphp
                <!-- Services -->
                <tr>
                    <td style="padding:0 0 8px 0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px; width:100%;">
                            <tr>
                                <td style="padding:12px 14px;">
                                    <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:900; color:#111827; margin:0 0 10px 0;">Services</div>
@foreach($sectionsToShow as $sectionLabel => $types)
@php
    $typesArray = is_array($types) ? $types : [$types];
    $hasServices = false;
    foreach ($typesArray as $type) {
        if (isset($normalizedServicesByType[$type]) && !empty($normalizedServicesByType[$type])) {
            $hasServices = true;
            break;
        }
    }
    $sectionCards = [];
    foreach ($typesArray as $type) {
        if (isset($normalizedServicesByType[$type]) && !empty($normalizedServicesByType[$type])) {
            $sectionCards = array_merge($sectionCards, $normalizedServicesByType[$type]);
        }
    }
@endphp

                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; margin-top:6px; border:1px solid #E5E7EB; border-radius:10px;">
                                        <tr>
                                            <td style="background:#111827; padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; font-weight:900; color:#FFFFFF;">
{{ strtoupper($sectionLabel) }}
                                            </td>
                                        </tr>
@if(!$hasServices || empty($sectionCards))
                                        <tr>
                                            <td style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#111827;">
No {{ strtolower($sectionLabel) }} booked for this tour.
                                            </td>
                                        </tr>
@else
                                        <tr>
                                            <td style="padding:0 0 0 0;">
                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse;">
    @foreach($sectionCards as $card)
        @php
            // Determine the type of this card
            $cardType = null;
            foreach ($typesArray as $type) {
                if (isset($normalizedServicesByType[$type]) && in_array($card, $normalizedServicesByType[$type], true)) {
                    $cardType = $type;
                    break;
                }
            }
            if (!$cardType) {
                // Try to infer from card data
                if (isset($card['vehicle']) || isset($card['entry_port_flight'])) {
                    $cardType = 'entry_port';
                } elseif (isset($card['exit_port_flight'])) {
                    $cardType = 'exit_port';
                } elseif (isset($card['attraction'])) {
                    $cardType = 'attraction';
                } elseif (isset($card['restaurant'])) {
                    $cardType = 'restaurant';
                } elseif (isset($card['guide'])) {
                    $cardType = 'guide';
                } elseif (isset($card['vehicle']) && !isset($card['entry_port_flight']) && !isset($card['exit_port_flight'])) {
                    $cardType = 'travel_point'; // Default to transfer
                }
            }
            $normalizedType = $cardType;
        @endphp
                                                    <tr>
                                                        <td valign="top" style="padding:12px 12px; font-family:Arial, sans-serif; font-size:12px; color:#111827; border-bottom:1px solid #E5E7EB;">
                                                            <div style="font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900; margin:0 0 8px 0;">
                                                                @if($normalizedType === 'entry_port')
                                                                    Port of Arrival Transfer
                                                                @elseif($normalizedType === 'exit_port')
                                                                    Port of Departure Transfer
                                                                @elseif($normalizedType === 'attraction' || $normalizedType === 'attraction_package')
                                                                    Attraction
                                                                @elseif($normalizedType === 'restaurant')
                                                                    Restaurant
                                                                @elseif($normalizedType === 'guide')
                                                                    Guide
                                                                @elseif(in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly']))
                                                                    Transfer
                                                                @else
                                                                    Service
                                                                @endif
                                                            </div>
@if($normalizedType === 'entry_port')
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
            $way = $vehicleData['way'] ?? 'N/A';
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

            $adultCount = $card['adult_count'] ?? null;
            $childCount = $card['child_count'] ?? null;
            $infantCount = $card['infant_count'] ?? null;
        @endphp
                                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; border:1px solid #E5E7EB;">
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Port</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Transfer Type</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Vehicle</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Max Pax</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $portName }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $transferType }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $vehicleTypeSeater }} / {{ $vehicleBrand }} / {{ $vehicleNumber }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $maxPassengerWithLuggage }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Way</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">{{ $way ?: 'N/A' }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Flight</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Origin</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Arrival</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Terminal</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $flightName }} ({{ $flightNo }})</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $originDepartureTime }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $destinationArrivalTime }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $originDepartureTerminal }} / {{ $destinationArrivalTerminal }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Guests</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">
                                                                            Adults: {{ $adultCount !== null ? $adultCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Children: {{ $childCount !== null ? $childCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Infants: {{ $infantCount !== null ? $infantCount : 'N/A' }}
                                                                        </td>
                                                                    </tr>
                                                                </table>

@elseif($normalizedType === 'exit_port')
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
            $way = $vehicleData['way'] ?? 'N/A';
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

            $adultCount = $card['adult_count'] ?? null;
            $childCount = $card['child_count'] ?? null;
            $infantCount = $card['infant_count'] ?? null;
        @endphp
                                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; border:1px solid #E5E7EB;">
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Port</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Transfer Type</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Vehicle</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Max Pax</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $portName }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $transferType }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $vehicleTypeSeater }} / {{ $vehicleBrand }} / {{ $vehicleNumber }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $maxPassengerWithLuggage }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Way</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">{{ $way ?: 'N/A' }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Flight</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Origin</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Arrival</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Terminal</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $flightName }} ({{ $flightNo }})</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $originDepartureTime }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $destinationArrivalTime }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $originDepartureTerminal }} / {{ $destinationArrivalTerminal }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Guests</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">
                                                                            Adults: {{ $adultCount !== null ? $adultCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Children: {{ $childCount !== null ? $childCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Infants: {{ $infantCount !== null ? $infantCount : 'N/A' }}
                                                                        </td>
                                                                    </tr>
                                                                </table>

@elseif($normalizedType === 'attraction' || $normalizedType === 'attraction_package')
        @php
            $attractionData = $card['attraction'] ?? [];
            $attractionTiming = $attractionData['visit_time'] ?? 'N/A';
            $transferRequired = $attractionData['transfer_required'] ?? 'N/A';
            $adultCount = $attractionData['adult_count'] ?? null;
            $childCount = $attractionData['child_count'] ?? null;
            $seniorCount = $attractionData['senior_count'] ?? null;
            $transferTypeRaw = $attractionData['transfer_type'] ?? 'N/A';
            if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
            } else {
                $transferType = $transferTypeRaw;
            }
        @endphp
                                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; border:1px solid #E5E7EB;">
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Attraction Name</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Timing</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Transfer</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Transfer Type</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $card['title'] ?? 'N/A' }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $attractionTiming }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $transferRequired }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $transferType }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Guests</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">
                                                                            Adults: {{ $adultCount !== null ? $adultCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Children: {{ $childCount !== null ? $childCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Infant/Senior: {{ $seniorCount !== null ? $seniorCount : 'N/A' }}
                                                                        </td>
                                                                    </tr>
                                                                </table>

@elseif($normalizedType === 'restaurant')
        @php
            $restaurantData = $card['restaurant'] ?? [];
            $mealPlan = $restaurantData['meal_plan'] ?? 'N/A';
            $mealType = $restaurantData['meal_type'] ?? 'N/A';
            $transferRequired = $restaurantData['transfer_required'] ?? 'N/A';
            $adultCount = $restaurantData['adult_count'] ?? null;
            $childCount = $restaurantData['child_count'] ?? null;
            $infantCount = $restaurantData['infant_count'] ?? ($restaurantData['senior_count'] ?? null);
            $transferTypeRaw = $restaurantData['transfer_type'] ?? 'N/A';
            if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
            } else {
                $transferType = $transferTypeRaw;
            }
        @endphp
                                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; border:1px solid #E5E7EB;">
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Restaurant</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Meal Plan</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Meal Type</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Transfer / Type</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $card['title'] ?? 'N/A' }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $mealPlan }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $mealType }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $transferRequired }} / {{ $transferType }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Guests</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">
                                                                            Adults: {{ $adultCount !== null ? $adultCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Children: {{ $childCount !== null ? $childCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Infants: {{ $infantCount !== null ? $infantCount : 'N/A' }}
                                                                        </td>
                                                                    </tr>
                                                                </table>

@elseif($normalizedType === 'guide')
        @php
            $guideData = $card['guide'] ?? [];
            $guideName = $guideData['guide_name'] ?? $card['title'] ?? 'N/A';
            $languageProficiency = $guideData['language_proficiency'] ?? 'N/A';
            $totalExperience = $guideData['total_experience'] ?? 'N/A';

            $adultCount = $card['adult_count'] ?? null;
            $childCount = $card['child_count'] ?? null;
            $infantCount = $card['infant_count'] ?? null;
        @endphp
                                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; border:1px solid #E5E7EB;">
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Guide</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Language</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Experience</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $guideName }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $languageProficiency }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $totalExperience }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Guests</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" style="padding:8px; font-weight:800; color:#111827;">
                                                                            Adults: {{ $adultCount !== null ? $adultCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Children: {{ $childCount !== null ? $childCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Infants: {{ $infantCount !== null ? $infantCount : 'N/A' }}
                                                                        </td>
                                                                    </tr>
                                                                </table>

@elseif(in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly']))
        @php
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
            $maxPassengerCapacity = $vehicleData['max_passenger_capacity'] ?? $vehicleData['seating_capacity'] ?? 'N/A';
            $way = $vehicleData['way'] ?? 'N/A';

            $adultCount = $card['adult_count'] ?? null;
            $childCount = $card['child_count'] ?? null;
            $infantCount = $card['infant_count'] ?? null;
        @endphp
                                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; border:1px solid #E5E7EB;">
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Transfer Type</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Vehicle</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Vehicle No</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Max Pax</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $transferType }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $vehicleTypeSeater }} / {{ $vehicleBrand }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $vehicleNumber }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827;">{{ $maxPassengerCapacity }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Way</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">{{ $way ?: 'N/A' }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-top:1px solid #E5E7EB; border-bottom:1px solid #E5E7EB;">Guests</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">
                                                                            Adults: {{ $adultCount !== null ? $adultCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Children: {{ $childCount !== null ? $childCount : 'N/A' }}
                                                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                                                            Infants: {{ $infantCount !== null ? $infantCount : 'N/A' }}
                                                                        </td>
                                                                    </tr>
                                                                </table>

@else
        @php
            $dateValue = '';
            $timeValue = '';
            foreach ($card['chips'] ?? [] as $chip) {
                if (strtolower($chip['label']) === 'date') $dateValue = $chip['value'];
                if (strtolower($chip['label']) === 'time') $timeValue = $chip['value'];
            }
        @endphp
                                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-collapse:collapse; border:1px solid #E5E7EB;">
                                                                    <tr>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Service</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Date</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Time</td>
                                                                        <td style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Location</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $card['title'] ?? 'N/A' }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $dateValue ?: 'N/A' }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $timeValue ?: 'N/A' }}</td>
                                                                        <td style="padding:8px; font-weight:800; color:#111827; border-bottom:1px solid #E5E7EB;">{{ $card['subtitle'] ?? 'N/A' }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-size:11px; font-weight:900; color:#6B7280; background:#F9FAFB; border-bottom:1px solid #E5E7EB;">Notes</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="4" style="padding:8px; font-weight:800; color:#111827;">{{ $card['notes'] ?? 'N/A' }}</td>
                                                                    </tr>
                                                                </table>
@endif
@php
    $serviceRemarks = $card['remarks'] ?? ($card['note'] ?? ($card['notes'] ?? ''));
    if ($serviceRemarks === '' || $serviceRemarks === null) {
        $serviceRemarks = $card['attraction']['remarks'] ?? ($card['restaurant']['remarks'] ?? ($card['guide']['remarks'] ?? ($card['vehicle']['remarks'] ?? ($card['entry_port_flight']['remarks'] ?? ($card['exit_port_flight']['remarks'] ?? '')))));
    }
    if ($serviceRemarks === '' || $serviceRemarks === null) {
        $serviceRemarks = $card['attraction']['notes'] ?? ($card['restaurant']['notes'] ?? ($card['guide']['notes'] ?? ($card['vehicle']['notes'] ?? '')));
    }
    if (is_array($serviceRemarks)) {
        $serviceRemarks = json_encode($serviceRemarks);
    }
    $serviceRemarks = trim((string)$serviceRemarks);
@endphp
                                                            <div style="font-family:Arial, sans-serif; font-size:12px; color:#6B7280; margin:6px 0 0 0;">
                                                                Remarks: <span style="color:#111827; font-weight:800;">{{ $serviceRemarks !== '' ? $serviceRemarks : 'N/A' }}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
    @endforeach
                                                </table>
                                            </td>
                                        </tr>
@endif
                                    </table>
@endforeach
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Pricing (if available) -->
                <tr>
                    <td style="padding:0 0 8px 0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px; width:100%;">
                            <tr>
                                <td style="padding:12px 14px;">
                                    <div style="font-family:Arial, sans-serif; font-size:13px; font-weight:900; color:#111827; margin:0 0 10px 0;">Pricing</div>
                                    @php
                                        $pricingCurrency = $bookingDetails['currency'] ?? ($tour->currency ?? '');
                                        $pricingTotal = $bookingDetails['total_price'] ?? null;
                                        $pricingTaxes = $bookingDetails['taxes'] ?? null;
                                        $pricingPaid = $bookingDetails['paid_amount'] ?? ($bookingDetails['amount_paid'] ?? null);
                                        $pricingBalance = $bookingDetails['balance_amount'] ?? ($bookingDetails['balance_due'] ?? null);
                                    @endphp
                                    @if(isset($pricingTotal) || isset($tourPrices) || isset($optionFinalTotalSingle) || isset($optionFinalTotalDouble) || isset($optionFinalTotalChild) || isset($optionFinalTotalInfant))
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border:1px solid #E5E7EB; border-radius:10px;">
                                            @if(isset($pricingTotal))
                                            <tr>
                                                <td style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#6B7280; border-bottom:1px solid #E5E7EB;">Package Cost</td>
                                                <td align="right" style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900; border-bottom:1px solid #E5E7EB;">
                                                    {{ $pricingCurrency ? ($pricingCurrency . ' ') : '' }}{{ is_numeric($pricingTotal) ? number_format((float)$pricingTotal, 2) : $pricingTotal }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if(isset($pricingTaxes))
                                            <tr>
                                                <td style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#6B7280; border-bottom:1px solid #E5E7EB;">Taxes</td>
                                                <td align="right" style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900; border-bottom:1px solid #E5E7EB;">
                                                    {{ is_numeric($pricingTaxes) ? (($pricingCurrency ? ($pricingCurrency . ' ') : '') . number_format((float)$pricingTaxes, 2)) : (is_array($pricingTaxes) ? json_encode($pricingTaxes) : $pricingTaxes) }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if(isset($pricingPaid))
                                            <tr>
                                                <td style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#6B7280; border-bottom:1px solid #E5E7EB;">Paid</td>
                                                <td align="right" style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900; border-bottom:1px solid #E5E7EB;">
                                                    {{ $pricingCurrency ? ($pricingCurrency . ' ') : '' }}{{ is_numeric($pricingPaid) ? number_format((float)$pricingPaid, 2) : $pricingPaid }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if(isset($pricingBalance))
                                            <tr>
                                                <td style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#6B7280;">Balance</td>
                                                <td align="right" style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; font-weight:900; color:#B91C1C;">
                                                    {{ $pricingCurrency ? ($pricingCurrency . ' ') : '' }}{{ is_numeric($pricingBalance) ? number_format((float)$pricingBalance, 2) : $pricingBalance }}
                                                </td>
                                            </tr>
                                            @else
                                            <tr>
                                                <td style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#6B7280;">Total (Hotel Option Final Totals)</td>
                                                <td align="right" style="padding:10px 12px; font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900;">
                                                    @if(isset($optionFinalTotalSingle) || isset($optionFinalTotalDouble) || isset($optionFinalTotalChild) || isset($optionFinalTotalInfant))
                                                        S: {{ isset($optionFinalTotalSingle) ? number_format($optionFinalTotalSingle, 2) : 'N/A' }} &nbsp;•&nbsp;
                                                        D: {{ isset($optionFinalTotalDouble) ? number_format($optionFinalTotalDouble, 2) : 'N/A' }} &nbsp;•&nbsp;
                                                        C: {{ isset($optionFinalTotalChild) ? number_format($optionFinalTotalChild, 2) : 'N/A' }} &nbsp;•&nbsp;
                                                        I: {{ isset($optionFinalTotalInfant) ? number_format($optionFinalTotalInfant, 2) : 'N/A' }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    @else
                                        <div style="font-family:Arial, sans-serif; font-size:12px; color:#111827;">—</div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:0;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px; width:100%;">
                            <tr>
                                <td style="padding:12px 14px;">
                                    <div style="font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900; margin:0 0 6px 0;">Support</div>
                                    <div style="font-family:Arial, sans-serif; font-size:12px; color:#6B7280; line-height:1.6;">
                                        If you have any questions, contact us at <span style="color:#111827; font-weight:800;">{{ $companyEmail }}</span> or <span style="color:#111827; font-weight:800;">{{ $companyTel }}</span>.
                                    </div>
                                    <div style="height:6px; line-height:6px; font-size:6px;">&nbsp;</div>
                                    <div style="font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900; margin:0 0 6px 0;">Important Notes</div>
                                    <div style="font-family:Arial, sans-serif; font-size:12px; color:#6B7280; line-height:1.6;">
                                        *Please note that this is not a tour itinerary / schedule, a confirmed tour itinerary / schedule is only generated post confirmation of the tour and payment is completed.<br/>
                                        *The above quotation only specifies the optionwise costs based on the tour requirements with standard exclusions &amp; Inclusions as mentioned above.
                                    </div>
                                    <div style="height:10px; line-height:10px; font-size:10px;">&nbsp;</div>
                                    <div style="font-family:Arial, sans-serif; font-size:12px; color:#111827; font-weight:900;">Thank you for booking with {{ $companyName }}.</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:14px 0 0 0; font-family:Arial, sans-serif; font-size:11px; color:#6B7280; text-align:center;">
                        © {{ date('Y') }} {{ $companyName }}. All rights reserved.
                    </td>
                </tr>

            </table>
            <!-- /Container -->
        </td>
    </tr>
</table>
    </div>
    <!-- End of Email Content Container -->

    <script>
        async function copyEmailContent() {
            const emailContent = document.getElementById('emailContent');
            const copyButton = document.getElementById('copyEmailButton');
            const copyButtonText = document.getElementById('copyButtonText');
            const successMessage = document.getElementById('copySuccessMessage');
            
            try {
                function showSuccess(message) {
                    if (copyButton) {
                        copyButton.style.background = '#10b981';
                        copyButton.style.boxShadow = '0 8px 20px rgba(16,185,129,0.25)';
                    }
                    if (copyButtonText) copyButtonText.textContent = 'Copied!';
                    if (successMessage) {
                        if (message) successMessage.textContent = '✓ ' + message;
                        successMessage.style.display = 'block';
                    }
                    
                    setTimeout(() => {
                        if (copyButton) {
                            copyButton.style.background = '#4f46e5';
                            copyButton.style.boxShadow = '0 8px 20px rgba(79,70,229,0.25)';
                        }
                        if (copyButtonText) copyButtonText.textContent = 'Copy Email Content';
                        if (successMessage) {
                            successMessage.textContent = '✓ Content copied! Paste into Gmail compose (Ctrl+V or Cmd+V)';
                            successMessage.style.display = 'none';
                        }
                    }, 4000);
                }

                // Copy HTML (for rich paste in Gmail/Outlook web compose)
                const htmlContent = emailContent.innerHTML || '';
                const textFallback = emailContent.innerText || emailContent.textContent || '';

                if (navigator.clipboard && navigator.clipboard.write && window.ClipboardItem) {
                    await navigator.clipboard.write([
                        new ClipboardItem({
                            "text/html": new Blob([htmlContent], { type: "text/html" }),
                            "text/plain": new Blob([textFallback], { type: "text/plain" })
                        })
                    ]);
                    showSuccess('Formatted email copied! Paste into Gmail compose using Ctrl+V (or Cmd+V on Mac)');
                    return;
                }

                // Fallback: plain text
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(textFallback);
                    showSuccess('Email copied (plain text fallback). Paste into Gmail compose using Ctrl+V');
                    return;
                }
                
                // Last fallback: execCommand (plain text)
                const textArea = document.createElement('textarea');
                textArea.value = textFallback;
                textArea.style.position = 'fixed';
                textArea.style.left = '-9999px';
                textArea.style.top = '0';
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    const success = document.execCommand('copy');
                    if (success) {
                        showSuccess('Email copied (plain text fallback). Paste into Gmail compose using Ctrl+V');
                    } else {
                        throw new Error('execCommand failed');
                    }
                } catch (e) {
                    alert('Copy failed. Please manually select the email (Ctrl+A) and copy (Ctrl+C), then paste into Gmail compose.');
                }
                document.body.removeChild(textArea);
            } catch (err) {
                console.error('Copy error:', err);
                alert('Copy failed. Please manually select the email (Ctrl+A) and copy (Ctrl+C), then paste into Gmail compose.');
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
