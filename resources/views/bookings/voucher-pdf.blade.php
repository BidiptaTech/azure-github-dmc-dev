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
        table { width: 100%; border-collapse: collapse; }
        .header-table { margin-bottom: 8px; }
        .header-table td { border: none; vertical-align: middle; padding: 5px 0; }
        .header-left { width: 22%; text-align: left; vertical-align: middle; }
        .header-left img { max-width: 165px; max-height: 165px; }
        .header-center { width: 56%; text-align: center; }
        .header-center h2 { font-size: 20px; margin-bottom: 4px; color: #222; font-weight: bold; }
        .header-center p { font-size: 11px; color: #333; margin: 2px 0; font-weight: bold; }
        .header-right { width: 22%; }

        .voucher-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 8px 0 10px 0;
            color: #000;
        }

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
        $displayLogoSrc = null;
        $displayCompanyName = 'Company';
        $rootDmc = $dmcUser;
        if ($dmcUser) {
            $visited = [];
            while ($rootDmc && $rootDmc->role_id != 11 && $rootDmc->created_by && !in_array($rootDmc->created_by, $visited)) {
                $visited[] = $rootDmc->created_by;
                $rootDmc = \App\Models\User::where('userId', $rootDmc->created_by)->first();
            }
            if (!$rootDmc) $rootDmc = $dmcUser;
            $cn = $rootDmc->company_name ?? $dmcUser->company_name ?? 'Company';
            $displayCompanyName = is_array($cn) ? implode(' ', $cn) : (string) $cn;
            $dmcLogo = $rootDmc->logo ?? $dmcUser->logo ?? null;
            if ($dmcLogo) {
                try {
                    if (preg_match('/^data:image\\//i', $dmcLogo)) {
                        $displayLogoSrc = $dmcLogo;
                    } else {
                        if (preg_match('/^https?:\\/\\//i', $dmcLogo)) {
                            $logoContent = @file_get_contents($dmcLogo);
                        } else {
                            $logoPath = public_path(ltrim($dmcLogo, '/'));
                            $logoContent = @file_get_contents($logoPath);
                        }
                        if (!empty($logoContent)) {
                            $displayLogoSrc = 'data:image/png;base64,' . base64_encode($logoContent);
                        }
                    }
                } catch (\Exception $e) {
                    $displayLogoSrc = null;
                }
            }
        }
    @endphp

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                @if($displayLogoSrc)
                    <img src="{{ $displayLogoSrc }}" alt="Logo">
                @endif
            </td>
            <td class="header-center">
                <h2>{{ $displayCompanyName }}</h2>
                @php
                    $companyRegNo = optional($rootDmc)->company_reg_no ?: optional($dmcUser)->company_reg_no;
                    $licenceNo = optional($rootDmc)->ta_licence_no ?: optional($rootDmc)->licence_no ?: optional($dmcUser)->ta_licence_no ?: optional($dmcUser)->licence_no;
                @endphp
                @if(!empty($companyRegNo) && is_string($companyRegNo))
                    <p><strong>Company Reg No: {{ $companyRegNo }}</strong></p>
                @endif
                @if(!empty($licenceNo) && is_string($licenceNo))
                    <p><strong>TA Licence No: {{ $licenceNo }}</strong></p>
                @endif
            </td>
            <td class="header-right"></td>
        </tr>
    </table>

    <div class="voucher-title">CONFIRMATION VOUCHER</div>

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
            <td class="label-cell">PAYMENT DATELINE</td>
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
        $dmcAddr = $rootDmc->address ?? $dmcUser->address ?? null;
        if (is_string($dmcAddr) && !empty(trim($dmcAddr))) $footerParts[] = trim($dmcAddr);

        $phones = [];
        $tel = $rootDmc->tel ?? $rootDmc->telephone ?? $rootDmc->phone ?? $dmcUser->tel ?? $dmcUser->telephone ?? $dmcUser->phone ?? null;
        $countryCode = $rootDmc->country_code ?? $dmcUser->country_code ?? null;
        if (is_string($tel) && !empty(trim($tel))) {
            $formattedPhone = ($countryCode ? '+' . $countryCode . ' ' : '') . trim($tel);
            $phones[] = $formattedPhone;
        }
        $phone2 = $rootDmc->phone_number ?? $dmcUser->phone_number ?? null;
        if (is_string($phone2) && !empty(trim($phone2)) && $phone2 !== ($tel ?? '')) {
            $phones[] = ($countryCode ? '+' . $countryCode . ' ' : '') . trim($phone2);
        }
        if (!empty($phones)) $footerParts[] = implode(' , ', $phones);

        $fax = $rootDmc->fax ?? $dmcUser->fax ?? null;
        if (is_string($fax) && !empty(trim($fax))) $footerParts[] = 'Fax: ' . trim($fax);

        $footerLine1 = implode(',  ', $footerParts);

        $footerLine2Parts = [];
        $dmcEmail = $rootDmc->email ?? $rootDmc->company_email ?? $dmcUser->email ?? $dmcUser->company_email ?? null;
        if (is_string($dmcEmail) && !empty(trim($dmcEmail))) $footerLine2Parts[] = 'Email : ' . trim($dmcEmail);

        $dmcWebsite = $rootDmc->website ?? $dmcUser->website ?? null;
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
