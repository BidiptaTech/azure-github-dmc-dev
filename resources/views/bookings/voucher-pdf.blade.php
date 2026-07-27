<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirmation Voucher - {{ $referenceId }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 15px 25px;
        }
        @include('invoices.pdf.partials.header-css')
        table { width: 100%; border-collapse: collapse; }

        .info-table { border: 1px solid #000; }
        .info-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 11px;
            vertical-align: top;
        }
        .info-table .label-cell {
            width: 28%;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
        }
        .info-table .value-cell {
            width: 72%;
            text-align: center;
            font-weight: bold;
        }
        .info-table .value-cell-left {
            width: 72%;
            text-align: left;
            padding: 6px 10px;
            font-weight: bold;
        }

        .na-highlight {
            background-color: #ffff00;
            color: #cc0000;
            font-weight: bold;
            padding: 1px 6px;
        }
        .deadline-date {
            color: #cc0000;
            font-weight: bold;
            font-size: 14px;
            display: block;
            margin-bottom: 6px;
            padding-bottom: 4px;
            letter-spacing: 0.5px;
            line-height: 1.6;
        }
        .deadline-note {
            font-size: 10px;
            color: #cc0000;
            line-height: 1.5;
            display: block;
            word-wrap: break-word;
            text-align: left;
            padding-top: 2px;
        }
        .deadline-note strong {
            font-size: 10px;
        }
        .inclusion-item {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 10px;
            padding-bottom: 4px;
            display: block;
            border-bottom: 1px dotted #ccc;
        }
        .inclusion-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    @php
        $rootDmc = $voucherRootDmc ?? $dmcUser ?? null;
    @endphp

    @include('invoices.pdf.partials.header', [
        'logoType' => $logoType ?? 'dmc',
        'showBlueTitle' => true,
        'docTitle' => 'CONFIRMATION VOUCHER',
        'docNumber' => $referenceId,
        'user_dmc' => $user_dmc ?? $dmcUser ?? null,
        'user_agency' => $user_agency ?? null,
    ])

    {{-- MAIN INFO TABLE --}}
    <table class="info-table">
        <tr>
            <td class="label-cell">GROUP / PAX NAME</td>
            <td class="value-cell">{{ strtoupper($paxName) }}</td>
        </tr>
        <tr>
            <td class="label-cell">TRAVEL DATES</td>
            <td class="value-cell">{{ $travelDates ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">NO OF PAX</td>
            <td class="value-cell">{{ $noOfPax }}</td>
        </tr>

        @if(count($hotels) > 0)
        <tr>
            <td class="label-cell">HOTEL NAME</td>
            <td class="value-cell">
                @foreach($hotels as $i => $hotel)
                    {{ $hotel['name'] }}
                    @if(!empty($hotel['due_date']))
                        <span style="color: #cc0000; font-weight: bold;"> ({{ $hotel['due_date'] }})</span>
                    @endif
                    @if($hotel['check_in'] && $hotel['check_out'])
                        <br><small>{{ \Carbon\Carbon::parse($hotel['check_in'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($hotel['check_out'])->format('d/m/Y') }}</small>
                    @endif
                    @if($i < count($hotels) - 1)<br><br>@endif
                @endforeach
            </td>
        </tr>
        @endif

        <tr>
            <td class="label-cell">NO OF ROOMS</td>
            <td class="value-cell-left">
                @if(count($hotels) > 0)
                    @foreach($hotels as $i => $hotel)
                        <strong>{{ $hotel['name'] }}:</strong> {{ $hotel['rooms'] }}
                        @if($i < count($hotels) - 1)<br>@endif
                    @endforeach
                @elseif($totalRooms === 'na')
                    <span class="na-highlight">na</span>
                @else
                    {{ $totalRooms }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label-cell">CONFIRMATION NO.</td>
            <td class="value-cell-left">
                @if(count($hotels) > 0)
                    @foreach($hotels as $i => $hotel)
                        <strong>{{ $hotel['name'] }}:</strong> {{ !empty($hotel['confirmation_no']) ? $hotel['confirmation_no'] : 'na' }}
                        @if($i < count($hotels) - 1)<br>@endif
                    @endforeach
                @elseif($confirmationNo === 'na')
                    <span class="na-highlight">na</span>
                @else
                    {{ $confirmationNo }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label-cell">BOOKING REFF NO</td>
            <td class="value-cell">{{ $referenceId }}</td>
        </tr>
        <tr>
            <td class="label-cell">MEAL PLAN AND PREFRENCE</td>
            <td class="value-cell-left">
                @if(count($hotels) > 0)
                    @foreach($hotels as $i => $hotel)
                        <strong>{{ $hotel['name'] }}:</strong> {{ !empty($hotel['meal_plan']) ? $hotel['meal_plan'] : '-' }}
                        @if($i < count($hotels) - 1)<br>@endif
                    @endforeach
                @else
                    {{ $mealPlanSummary }}
                @endif
            </td>
        </tr>

        @if(count($inclusions) > 0)
        <tr>
            <td class="label-cell">INCLUSION</td>
            <td class="value-cell-left">
                <strong style="text-decoration: underline;">INCLUSIONS;</strong>
                <br><br>
                @foreach($inclusions as $item)
                    <span class="inclusion-item">{!! nl2br(e($item)) !!}</span>
                @endforeach
            </td>
        </tr>
        @endif

        @if($lowestDueDate)
        <tr>
            <td class="label-cell">PAYMENT CUT-OFF DATE</td>
            <td class="value-cell-left" style="padding: 8px 12px;">
                <div class="deadline-date">{{ $lowestDueDate->format('d/m/Y') }}</div>
                <div class="deadline-note">
                    <strong>Note:</strong> Dateline mentioned above is strictly given by hotels, if you failed to reconfirm booking by the above date, auto-cancellation and re-booking will be done subject to availability at the time of booking, category and rate may change.
                </div>
            </td>
        </tr>
        @endif
    </table>

    {{-- FOOTER: DMC Contact Details --}}
    @php
        $footerParts = [];
        $dmcAddr = optional($rootDmc)->address ?? optional($dmcUser)->address ?? null;
        if (is_string($dmcAddr) && !empty(trim($dmcAddr))) $footerParts[] = trim($dmcAddr);

        $phones = [];
        $tel = optional($rootDmc)->tel ?? optional($rootDmc)->telephone ?? optional($rootDmc)->phone
            ?? optional($dmcUser)->tel ?? optional($dmcUser)->telephone ?? optional($dmcUser)->phone ?? null;
        $countryCode = optional($rootDmc)->country_code ?? optional($dmcUser)->country_code ?? null;
        if (is_string($tel) && !empty(trim($tel))) {
            $formattedPhone = ($countryCode ? '+' . $countryCode . ' ' : '') . trim($tel);
            $phones[] = $formattedPhone;
        }
        $phone2 = optional($rootDmc)->phone_number ?? optional($dmcUser)->phone_number ?? null;
        if (is_string($phone2) && !empty(trim($phone2)) && $phone2 !== ($tel ?? '')) {
            $phones[] = ($countryCode ? '+' . $countryCode . ' ' : '') . trim($phone2);
        }
        if (!empty($phones)) $footerParts[] = implode(' , ', $phones);

        $fax = optional($rootDmc)->fax ?? optional($dmcUser)->fax ?? null;
        if (is_string($fax) && !empty(trim($fax))) $footerParts[] = 'Fax: ' . trim($fax);

        $footerLine1 = implode(',  ', $footerParts);

        $footerLine2Parts = [];
        $dmcEmail = optional($rootDmc)->email ?? optional($rootDmc)->company_email
            ?? optional($dmcUser)->email ?? optional($dmcUser)->company_email ?? null;
        if (is_string($dmcEmail) && !empty(trim($dmcEmail))) $footerLine2Parts[] = 'Email : ' . trim($dmcEmail);

        $dmcWebsite = optional($rootDmc)->website ?? optional($dmcUser)->website ?? null;
        if (is_string($dmcWebsite) && !empty(trim($dmcWebsite))) $footerLine2Parts[] = 'visit us: ' . trim($dmcWebsite);

        $footerLine2 = implode(' : ', $footerLine2Parts);
    @endphp

    @if(!empty($footerLine1) || !empty($footerLine2))
    <div style="margin-top: 20px; text-align: center; border-top: 1px solid #000; padding-top: 6px; font-size: 11px; color: #000;">
        @if(!empty($footerLine1))
            <div style="font-weight: bold;">{{ $footerLine1 }}</div>
        @endif
        @if(!empty($footerLine2))
            <div style="font-weight: bold;">{{ $footerLine2 }}</div>
        @endif
    </div>
    @endif

</body>
</html>
