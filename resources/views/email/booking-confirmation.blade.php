@php
    use App\Models\Setting;

    /*
    |--------------------------------------------------------------------------
    | Same data contract as mails/tour_auto_booked_dmc.blade.php
    | Populated via CommonHelper::normalizeTourAutoBookedEmailData()
    | Sent automatically on tour auto-book via sendTourAutoBookedDmcEmail()
    |--------------------------------------------------------------------------
    */

    // Guest summary (same logic as tour_auto_booked_dmc)
    $guestParts = [];
    if (($adults ?? 0) > 0) {
        $guestParts[] = $adults . ' adult' . ($adults > 1 ? 's' : '');
    }
    if (($children ?? 0) > 0) {
        $guestParts[] = $children . ' child' . ($children > 1 ? 'ren' : '');
    }
    if (($infants ?? 0) > 0) {
        $guestParts[] = $infants . ' infant' . ($infants > 1 ? 's' : '');
    }
    $guestsText = count($guestParts)
        ? implode(', ', $guestParts)
        : (($total_guests ?? 0) . ' guest(s)');

    // Branding
    $masterLogo = Setting::where('name', 'logo')->first();
    $masterName = Setting::where('name', 'name')->first();
    $settingLogo = $masterLogo ? $masterLogo->value : '';
    $settingName = $masterName ? $masterName->value : (config('app.name') ?: 'travclicks');

    $logo        = !empty($dmc_logo) ? $dmc_logo : $settingLogo;
    $companyName = !empty($dmc_label) ? $dmc_label : (!empty($dmc_name) ? $dmc_name : $settingName);
    $tagline     = $tagline ?? 'Travel Designed Around You';

    $supportEmail = !empty($dmc_contact_email) ? $dmc_contact_email : ($supportEmail ?? 'reservations.travclicks@gmail.com');
    $supportPhone = $supportPhone ?? '+65 6201 2366';

    // Booking summary
    $statusLabel   = !empty($is_partial_package) ? 'PARTIAL PACKAGE BOOKED' : ($statusLabel ?? 'BOOKING REQUEST UPDATED');
    $bookingNumber = $tour_display_id ?? ($bookingNumber ?? 'N/A');

    $destinationDisplay = trim(
        ($country ?? '') .
        (!empty($cities_label) ? ' — ' . $cities_label : (!empty($city ?? null) ? ' — ' . $city : ''))
    );
    if ($destinationDisplay === '') {
        $destinationDisplay = $destination ?? 'N/A';
    }

    $packageName = $packageName ?? (
        ($destination ?? 'Travel') . ' Experience Package'
    );

    $heroText = !empty($is_partial_package) && !empty($partial_package_message)
        ? $partial_package_message
        : ($heroText ?? "We've created a personalized itinerary based on your request.");

    $heroImage = $heroImage ?? 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1200&q=80';

    $tripDates = trim(($check_in_date ?? 'N/A') . ' – ' . ($check_out_date ?? 'N/A'), ' –');

    $bookedVia = !empty($dmc_label)
        ? $dmc_label
        : (
            !empty($agent_name)
                ? $agent_name . (!empty($agency_name) ? ' (' . $agency_name . ')' : '')
                : ($bookedVia ?? 'Travclicks')
        );

    $currencyCode = strtoupper(trim((string) ($currency_code ?? 'SGD'))) ?: 'SGD';
    $totalEstimation = round((float) ($total_estimation ?? 0), 2);

    if (!empty($total_estimation_formatted)) {
        $packageValue = $total_estimation_formatted;
        $totalPrice   = $total_estimation_formatted;
    } elseif ($totalEstimation > 0) {
        $formatted = $currencyCode . ' ' . number_format($totalEstimation, 2);
        $packageValue = $formatted;
        $totalPrice   = $formatted;
    } else {
        $packageValue = $packageValue ?? '—';
        $totalPrice   = $totalPrice ?? '—';
    }

    $detailsUrl   = $dashboard_link ?? ($detailsUrl ?? '#');
    $downloadUrl  = $downloadUrl ?? ($dashboard_link ?? '#');
    $itineraryUrl = $dashboard_link ?? ($itineraryUrl ?? '#');
    $chatUrl      = $chatUrl ?? ('mailto:' . $supportEmail);

    $bookedServices = is_array($booked_services ?? null) ? $booked_services : [];

    // Build "What's included" from booked service types
    $includedCounts = [
        'hotel'      => 0,
        'attraction' => 0,
        'restaurant' => 0,
        'transfer'   => 0,
        'guide'      => 0,
    ];
    foreach ($bookedServices as $svc) {
        $typeKey = strtolower((string) ($svc['order_type'] ?? $svc['badge'] ?? ''));
        if ($typeKey === 'hotel') {
            $includedCounts['hotel']++;
        } elseif ($typeKey === 'attraction') {
            $includedCounts['attraction']++;
        } elseif ($typeKey === 'restaurant') {
            $includedCounts['restaurant']++;
        } elseif ($typeKey === 'guide') {
            $includedCounts['guide']++;
        } elseif (in_array($typeKey, ['entry_port', 'exit_port', 'vehicle', 'transfer', 'travel_point', 'travel_hourly', 'local_transport', 'transfer'], true)) {
            $includedCounts['transfer']++;
        }
    }

    $included = $included ?? [];
    if (empty($included)) {
        if ($includedCounts['hotel'] > 0) {
            $included[] = 'Accommodation (' . $includedCounts['hotel'] . ')';
        }
        if ($includedCounts['attraction'] > 0) {
            $included[] = 'Attractions (' . $includedCounts['attraction'] . ')';
        }
        if ($includedCounts['restaurant'] > 0) {
            $included[] = 'Dining Experience (' . $includedCounts['restaurant'] . ')';
        }
        if ($includedCounts['guide'] > 0) {
            $included[] = 'Guide Services (' . $includedCounts['guide'] . ')';
        }
        if ($includedCounts['transfer'] > 0) {
            $included[] = 'Private Transfers (' . $includedCounts['transfer'] . ')';
        }
    }

    $features = $features ?? [
        ['icon' => '⚡', 'title' => 'Real-time', 'subtitle' => 'itinerary updates'],
        ['icon' => '🛡️', 'title' => 'Instant', 'subtitle' => 'supplier confirmations'],
        ['icon' => '🎧', 'title' => '24/7', 'subtitle' => 'traveller support'],
        ['icon' => '🔒', 'title' => 'Secure', 'subtitle' => 'booking management'],
    ];

    $brandBlue = '#2563eb';
    $textDark  = '#1f2a44';
    $textMuted = '#6b7280';
    $border    = '#e9edf5';
    $bgSoft    = '#f5f7fb';

    // Helper: extract hotel meta row from service lines
    $extractHotelMeta = function (array $service) use ($guestsText) {
        $meta = [];
        $map = [
            'Room'      => 'Room',
            'Bed'       => 'Bed',
            'Meal plan' => 'Meal Plan',
        ];
        foreach ($service['lines'] ?? [] as $line) {
            if (!is_array($line)) {
                continue;
            }
            $label = $line['label'] ?? '';
            if (isset($map[$label])) {
                $meta[] = ['label' => $map[$label], 'value' => $line['value'] ?? ''];
            }
        }
        if (!empty($service['pax'])) {
            $meta[] = ['label' => 'Guests', 'value' => $service['pax']];
        } elseif ($guestsText) {
            $meta[] = ['label' => 'Guests', 'value' => $guestsText];
        }
        return $meta;
    };

    // Helper: find nights badge for hotel services
    $extractNightBadge = function (array $service) {
        foreach ($service['lines'] ?? [] as $line) {
            if (is_array($line) && ($line['label'] ?? '') === 'Nights') {
                $n = (int) ($line['value'] ?? 0);
                return $n . ' Night' . ($n > 1 ? 's' : '');
            }
        }
        return null;
    };

    // Helper: format service price
    $formatServicePrice = function (array $service) use ($currencyCode) {
        $value = (float) ($service['price_value'] ?? 0);
        if ($value > 0) {
            return $currencyCode . ' ' . number_format($value, 2);
        }
        if (!empty($service['price'])) {
            return $currencyCode . ' ' . $service['price'];
        }
        return null;
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Booking #{{ $bookingNumber }}</title>
</head>
<body style="margin:0; padding:0; background-color:{{ $bgSoft }}; font-family:'Segoe UI', Arial, Helvetica, sans-serif; color:{{ $textDark }}; -webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:{{ $bgSoft }}; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="width:680px; max-width:680px; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 8px 30px rgba(31,42,68,0.08);">

                    <!-- HEADER -->
                    <tr>
                        <td style="padding:22px 28px; border-bottom:1px solid {{ $border }};">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;" width="55%">
                                        @if($logo)
                                            <img src="{{ $logo }}" alt="{{ $companyName }}" style="max-height:34px; display:block;">
                                        @else
                                            <span style="font-size:22px; font-weight:700; color:{{ $brandBlue }};">{{ $companyName }}</span>
                                        @endif
                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-top:4px;">{{ $tagline }}</div>
                                    </td>
                                    <td style="vertical-align:middle; text-align:right;" width="45%">
                                        <div style="font-size:11px; color:{{ $textMuted }};">Need help?</div>
                                        <a href="mailto:{{ $supportEmail }}" style="font-size:13px; color:{{ $brandBlue }}; text-decoration:none; font-weight:600;">{{ $supportEmail }}</a>
                                        @if(!empty($supportPhone))
                                            <div style="font-size:12px; color:{{ $textDark }}; margin-top:2px;">{{ $supportPhone }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- HERO -->
                    <tr>
                        <td style="padding:18px 28px 0 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-radius:12px; overflow:hidden; background-color:#0f2a4a; background-image:linear-gradient(120deg, rgba(15,42,74,0.92) 0%, rgba(15,42,74,0.45) 60%, rgba(15,42,74,0.15) 100%), url('{{ $heroImage }}'); background-size:cover; background-position:center;">
                                <tr>
                                    <td style="padding:30px 28px;">
                                        <span style="display:inline-block; background-color:rgba(255,255,255,0.16); color:#dbe7ff; font-size:11px; font-weight:700; letter-spacing:.5px; padding:6px 12px; border-radius:20px;">✔ {{ $statusLabel }}</span>
                                        <div style="font-size:28px; font-weight:700; color:#ffffff; margin-top:16px;">Booking #{{ $bookingNumber }}</div>
                                        <div style="font-size:16px; color:#eaf1ff; margin-top:6px;">{{ $packageName }}</div>
                                        <div style="font-size:13px; color:#cdddf7; margin-top:14px; max-width:420px; line-height:1.5;">{{ $heroText }}</div>
                                        @if(!empty($booked_at))
                                            <div style="font-size:12px; color:#b8cff5; margin-top:10px;">Booked: {{ $booked_at }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- TRIP SUMMARY -->
                    <tr>
                        <td style="padding:22px 28px 4px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid {{ $border }}; border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px 4px 18px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:14px; font-weight:700; color:{{ $textDark }};">📋 Trip summary</td>
                                                {{-- <td style="text-align:right;"><a href="{{ $detailsUrl }}" style="font-size:12px; color:{{ $brandBlue }}; text-decoration:none; font-weight:600;">View details ›</a></td> --}}
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 18px 16px 18px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                @php
                                                    $summaryCells = [
                                                        ['Destination', $destinationDisplay],
                                                        ['Dates', $tripDates],
                                                        ['Guests', $guestsText],
                                                        ['Booked via', $bookedVia],
                                                        ['Est. Package Value', $packageValue],
                                                    ];
                                                @endphp
                                                @foreach($summaryCells as $cell)
                                                    <td style="vertical-align:top; padding-right:10px; width:20%;">
                                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-bottom:4px;">{{ $cell[0] }}</div>
                                                        <div style="font-size:13px; font-weight:700; color:{{ $loop->last ? $brandBlue : $textDark }};">{{ $cell[1] }}</div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                            @if(!empty($requested_days) || !empty($available_days))
                                                <tr>
                                                    <td colspan="5" style="padding-top:10px;">
                                                        @if(!empty($requested_days))
                                                            <span style="font-size:11px; color:{{ $textMuted }};">Requested: </span>
                                                            <span style="font-size:12px; font-weight:600; color:{{ $textDark }};">{{ $requested_nights ?? max(0, (int) $requested_days - 1) }} night{{ (($requested_nights ?? max(0, (int) $requested_days - 1)) !== 1) ? 's' : '' }}</span>
                                                        @endif
                                                        @if(!empty($available_days))
                                                            <span style="font-size:11px; color:{{ $textMuted }}; margin-left:12px;">Package available: </span>
                                                            <span style="font-size:12px; font-weight:600; color:{{ $textDark }};">{{ $available_nights ?? max(0, (int) $available_days - 1) }} night{{ (($available_nights ?? max(0, (int) $available_days - 1)) !== 1) ? 's' : '' }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ITINERARY -->
                    @if(count($bookedServices) > 0)
                        <tr>
                            <td style="padding:18px 28px 4px 28px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:15px; font-weight:700; color:{{ $textDark }};">📖 Your itinerary</td>
                                        {{-- <td style="text-align:right;">
                                            <a href="{{ $downloadUrl }}" style="display:inline-block; font-size:12px; color:{{ $brandBlue }}; text-decoration:none; font-weight:600; border:1px solid {{ $border }}; border-radius:8px; padding:8px 14px;">⬇ Download itinerary</a>
                                        </td> --}}
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        @php $lastDayLabel = null; @endphp
                        @foreach($bookedServices as $service)
                            @php
                                $dayLabel = $service['day'] ?? null;
                                $accent   = $service['accent'] ?? $brandBlue;
                                $typeLabel = strtoupper($service['badge'] ?? ($service['type'] ?? 'SERVICE'));
                                $priceDisplay = $formatServicePrice($service);
                                $nightBadge = $extractNightBadge($service);
                                $hotelMeta = (strtolower($service['order_type'] ?? '') === 'hotel') ? $extractHotelMeta($service) : [];
                            @endphp

                            @if($dayLabel && $dayLabel !== $lastDayLabel)
                                @php $lastDayLabel = $dayLabel; @endphp
                                <tr>
                                    <td style="padding:10px 28px 0 28px;">
                                        <span style="display:inline-block; background-color:{{ $brandBlue }}; color:#ffffff; font-size:11px; font-weight:700; padding:5px 12px; border-radius:6px;">{{ strtoupper($dayLabel) }}</span>
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td style="padding:10px 28px 0 28px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid {{ $border }}; border-left:4px solid {{ $accent }}; border-radius:12px;">
                                        <tr>
                                            @if(!empty($service['time']))
                                                <td style="vertical-align:top; padding:16px 6px 16px 16px; width:62px;">
                                                    <div style="font-size:11px; font-weight:700; color:{{ $textMuted }};">{{ $service['time'] }}</div>
                                                </td>
                                            @endif
                                            <td style="vertical-align:top; padding:14px 16px 14px {{ !empty($service['time']) ? '0' : '16px' }};">
                                                <div style="font-size:10px; font-weight:700; letter-spacing:.6px; color:{{ $accent }};">{{ $typeLabel }}</div>
                                                <div style="font-size:15px; font-weight:700; color:{{ $textDark }}; margin-top:3px;">{{ $service['title'] ?? ($service['name'] ?? '—') }}</div>
                                                @if(!empty($service['subtitle']))
                                                    <div style="font-size:12px; color:{{ $textMuted }}; margin-top:2px;">{{ $service['subtitle'] }}</div>
                                                @endif
                                                @if(!empty($service['date']))
                                                    <div style="font-size:12px; color:{{ $textMuted }}; margin-top:4px;">{{ $service['date'] }}</div>
                                                @endif
                                                @if(!empty($service['pax']))
                                                    <div style="font-size:12px; color:{{ $textMuted }}; margin-top:2px;">{{ $service['pax'] }}</div>
                                                @endif

                                                @if(!empty($service['lines']) && is_array($service['lines']))
                                                    @foreach($service['lines'] as $line)
                                                        @if(is_array($line))
                                                            <div style="font-size:12px; color:{{ $textDark }}; margin-top:4px;">
                                                                <span style="color:{{ $textMuted }};">{{ $line['label'] ?? '' }}:</span>
                                                                {{ $line['value'] ?? '' }}
                                                            </div>
                                                        @else
                                                            <div style="font-size:12px; color:{{ $textDark }}; margin-top:4px;">{{ $line }}</div>
                                                        @endif
                                                    @endforeach
                                                @elseif(!empty($service['details']))
                                                    <div style="font-size:12px; color:{{ $textDark }}; margin-top:4px;">{{ $service['details'] }}</div>
                                                @endif
                                            </td>
                                            <td style="vertical-align:top; text-align:right; padding:14px 16px 14px 0; width:110px;">
                                                @if($nightBadge)
                                                    <span style="display:inline-block; background-color:#e7f7ee; color:#16a34a; font-size:11px; font-weight:700; padding:5px 10px; border-radius:14px;">{{ $nightBadge }}</span>
                                                @elseif($priceDisplay)
                                                    <span style="font-size:13px; font-weight:700; color:{{ $accent }};">{{ $priceDisplay }}</span>
                                                @elseif(!empty($service['time']))
                                                    <span style="font-size:12px; font-weight:600; color:{{ $accent }};">{{ $service['time'] }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if(!empty($hotelMeta))
                                            <tr>
                                                <td colspan="3" style="padding:0 16px 16px 16px;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid {{ $border }};">
                                                        <tr>
                                                            @foreach($hotelMeta as $m)
                                                                <td style="vertical-align:top; padding:12px 8px 0 0; width:25%;">
                                                                    <div style="font-size:10px; color:{{ $textMuted }};">{{ $m['label'] }}</div>
                                                                    <div style="font-size:12px; font-weight:700; color:{{ $textDark }}; margin-top:3px;">{{ $m['value'] }}</div>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <!-- TOTAL / WHAT'S INCLUDED -->
                    <tr>
                        <td style="padding:18px 28px 4px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:{{ $bgSoft }}; border:1px solid {{ $border }}; border-radius:12px;">
                                <tr>
                                    <td style="vertical-align:top; padding:20px; width:50%;">
                                        <div style="font-size:13px; font-weight:700; color:{{ $textDark }};">Total estimated price</div>
                                        @if($totalEstimation > 0)
                                            <div style="font-size:28px; font-weight:800; color:{{ $brandBlue }}; margin-top:6px;">{{ $totalPrice }}</div>
                                        @else
                                            <div style="font-size:16px; font-weight:600; color:{{ $textMuted }}; margin-top:6px;">Price on request</div>
                                        @endif
                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-top:6px; line-height:1.5;">Package total for all booked services above. Final amount may vary.</div>
                                    </td>
                                    <td style="vertical-align:top; padding:20px; width:50%;">
                                        <div style="font-size:13px; font-weight:700; color:{{ $textDark }}; margin-bottom:10px;">What's included</div>
                                        @if(count($included) > 0)
                                            @foreach($included as $inc)
                                                <div style="font-size:12px; color:{{ $textDark }}; margin-bottom:7px;">
                                                    <span style="color:{{ $brandBlue }}; font-weight:700;">✓</span>&nbsp; {{ $inc }}
                                                </div>
                                            @endforeach
                                        @else
                                            <div style="font-size:12px; color:{{ $textMuted }};">Services as listed in your itinerary.</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CTA BUTTONS (disabled — not needed in email)
                    <tr>
                        <td style="padding:16px 28px 4px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:50%; padding-right:7px;">
                                        <a href="{{ $itineraryUrl }}" style="display:block; text-align:center; background-color:{{ $brandBlue }}; color:#ffffff; font-size:14px; font-weight:700; text-decoration:none; padding:14px 0; border-radius:10px;">View full itinerary →</a>
                                    </td>
                                    <td style="width:50%; padding-left:7px;">
                                        <a href="{{ $chatUrl }}" style="display:block; text-align:center; background-color:#ffffff; color:{{ $textDark }}; font-size:14px; font-weight:700; text-decoration:none; padding:13px 0; border-radius:10px; border:1px solid {{ $border }};">💬 Chat with travel specialist</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    --}}

                    <!-- FEATURES -->
                    <tr>
                        <td style="padding:18px 28px 6px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid {{ $border }};">
                                <tr>
                                    @foreach($features as $feature)
                                        <td style="vertical-align:top; text-align:center; padding:16px 6px 4px 6px; width:25%;">
                                            <div style="font-size:18px;">{{ $feature['icon'] }}</div>
                                            <div style="font-size:12px; font-weight:700; color:{{ $textDark }}; margin-top:6px;">{{ $feature['title'] }}</div>
                                            <div style="font-size:11px; color:{{ $textMuted }}; margin-top:2px;">{{ $feature['subtitle'] }}</div>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="padding:22px 28px 28px 28px; border-top:1px solid {{ $border }};">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle; width:33%;">
                                        @if($logo)
                                            <img src="{{ $logo }}" alt="{{ $companyName }}" style="max-height:26px; display:block;">
                                        @else
                                            <span style="font-size:17px; font-weight:700; color:{{ $brandBlue }};">{{ $companyName }}</span>
                                        @endif
                                        <div style="font-size:10px; color:{{ $textMuted }}; margin-top:4px;">{{ $tagline }}</div>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center; width:33%;">
                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-bottom:6px;">Connect with us</div>
                                        <a href="#" style="text-decoration:none; color:{{ $brandBlue }}; font-size:13px; font-weight:700; padding:0 5px;">f</a>
                                        <a href="#" style="text-decoration:none; color:{{ $brandBlue }}; font-size:13px; font-weight:700; padding:0 5px;">◎</a>
                                        <a href="#" style="text-decoration:none; color:{{ $brandBlue }}; font-size:13px; font-weight:700; padding:0 5px;">in</a>
                                    </td>
                                    <td style="vertical-align:middle; text-align:right; width:34%;">
                                        <div style="font-size:11px; color:{{ $textMuted }}; line-height:1.5;">Thank you for choosing {{ $companyName }}.<br>We look forward to making your trip memorable.</div>
                                    </td>
                                </tr>
                            </table>
                            <div style="font-size:10px; color:{{ $textMuted }}; text-align:center; margin-top:18px;">&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
