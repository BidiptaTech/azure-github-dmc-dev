<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        @if(($invoice->invoice_type ?? '') === 'proforma')
            Proforma — {{ $invoice->proforma_number ?? 'DRAFT' }}
        @else
            Invoice — {{ $invoice->invoice_number ?? 'DRAFT' }}
        @endif
    </title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; padding: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        .no-border td, .no-border th { border: none; }
        /* Invoice items: outer bordered box + inner line table (PDF-safe) */
        table.inv-lines-outer-wrap { width: 100%; border-collapse: collapse; margin: 0 0 8px 0; }
        table.inv-lines-outer-wrap td.inv-lines-box-cell {
            border: 1px solid #000;
            padding: 0;
            vertical-align: top;
        }
        /* Premium line-items section (PDF-safe: tables only, no flex/grid) */
        table.inv-lines-table { width: 100%; border-collapse: collapse; margin-top: 0; table-layout: fixed; }
        table.inv-lines-table > thead > tr > th {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #374151;
            background-color: #F3F4F6;
            border: none !important;
            border-bottom: 1px solid #D1D5DB !important;
            padding: 10px 8px;
            vertical-align: bottom;
        }
        table.inv-lines-table > thead > tr > th.inv-col-service { text-align: left; width: 18%; }
        table.inv-lines-table > thead > tr > th.inv-col-details { text-align: left; width: 60%; }
        table.inv-lines-table > thead > tr > th.inv-col-amount {
            text-align: right;
            width: 22%;
            font-family: DejaVu Sans Mono, Courier New, Courier, monospace;
            padding-right: 10px;
            padding-left: 6px;
        }
        table.inv-lines-table > tbody > tr > td {
            border: none !important;
            vertical-align: top;
        }
        table.inv-lines-table > tbody > tr.inv-line-data > td {
            border-bottom: none !important;
            padding: 11px 8px;
        }
        table.inv-lines-table .inv-col-service { width: 18%; }
        table.inv-lines-table .inv-col-details { width: 60%; }
        table.inv-lines-table .inv-col-amount {
            width: 22%;
            text-align: right;
            font-family: DejaVu Sans Mono, Courier New, Courier, monospace;
            padding-right: 10px;
            padding-left: 6px;
            vertical-align: top;
        }
        .inv-svc-cat {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6B7280;
            line-height: 1.35;
        }
        .inv-svc-detail {
            font-size: 11px;
            color: #1F2937;
            line-height: 1.5;
        }
        .inv-svc-amt {
            display: block;
            font-size: 11px;
            font-weight: bold;
            color: #111827;
            text-align: right;
            white-space: nowrap;
            font-family: inherit;
        }
        table.inv-lines-table > tbody > tr.inv-total-row > td {
            border: none !important;
            padding: 7px 8px;
            font-size: 11px;
            color: #374151;
            vertical-align: middle;
        }
        table.inv-lines-table > tbody > tr.inv-total-row > td.inv-col-service {
            border: none !important;
        }
        table.inv-lines-table > tbody > tr.inv-total-row > td.inv-total-label-cell {
            text-align: right;
            padding-right: 10px;
            font-weight: 600;
            color: #374151;
        }
        table.inv-lines-table > tbody > tr.inv-total-row > td.inv-col-amount {
            color: #111827;
        }
        table.inv-lines-table > tbody > tr.inv-total-row:not(.inv-grand-row) > td.inv-col-amount .inv-svc-amt {
            font-weight: normal;
            font-size: 11px;
        }
        table.inv-lines-table > tbody > tr.inv-grand-row > td.inv-col-amount .inv-svc-amt {
            font-weight: bold !important;
        }
        table.inv-lines-table > tbody > tr.inv-total-sep > td {
            border-top: none !important;
            padding-top: 14px !important;
        }
        table.inv-lines-table > tbody > tr.inv-grand-row > td {
            font-size: 12px !important;
            font-weight: bold !important;
            color: #111827 !important;
            padding-top: 10px !important;
            padding-bottom: 8px !important;
            vertical-align: middle !important;
        }
        table.inv-lines-table > tbody > tr.inv-grand-row > td.inv-total-label-cell {
            font-size: 12px !important;
        }
        table.inv-lines-table > tbody > tr.inv-grand-row > td.inv-col-amount .inv-svc-amt,
        table.inv-lines-table > tbody > tr.inv-grand-row > td.inv-col-amount {
            font-size: 13px !important;
            font-weight: bold !important;
        }
        /* Top meta row: M/s + booking (left), invoice refs (right) */
        table.meta-header-wrap { width: 100%; border-collapse: collapse; }
        table.meta-header-wrap td.meta-right-stack { vertical-align: top; padding: 0; }
        table.inv-currency-bar { margin-top: 10px; border-collapse: collapse; width: 100%; table-layout: fixed; }
        table.inv-currency-bar tr.conv-row td {
            border: 1px solid #CA8A04 !important;
            background-color: #ffff00;
            font-weight: bold;
        }
        table.inv-currency-bar tr.conv-row td:last-child {
            font-family: DejaVu Sans Mono, Courier New, Courier, monospace;
            text-align: right;
            padding-right: 10px !important;
            padding-left: 6px !important;
        }
        @include('invoices.pdf.partials.header-css')
        .inv-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            padding: 12px 8px 10px;
            margin-top: 18px;
            margin-bottom: 12px;
            border: none;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .meta-label { font-weight: bold; width: 120px; }
        /* Bank details — section heading outside box; outer frame only (no inner row dividers) */
        .inv-bank-block { margin-top: 16px; page-break-inside: avoid; }
        .inv-bank-section-title {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #374151;
            margin: 0 0 8px 0;
            padding: 0 6px;
        }
        table.inv-bank-outer { width: 100%; border-collapse: collapse; margin: 0 0 2px 0; }
        table.inv-bank-outer td.inv-bank-box-cell {
            border: 1px solid #000;
            padding: 0;
            vertical-align: top;
        }
        table.inv-bank-grid { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0; }
        table.inv-bank-grid td {
            border: none !important;
            padding: 9px 10px;
            vertical-align: top;
        }
        table.inv-bank-grid td.inv-bank-label {
            width: 38%;
            font-weight: bold;
            font-size: 10px;
            color: #4B5563;
        }
        table.inv-bank-grid td.inv-bank-val {
            font-size: 10px;
            color: #111827;
            line-height: 1.45;
        }
        .amount-col { text-align: right; white-space: nowrap; }
        .conv-row { background-color: #ffff00; font-weight: bold; }
        .note-red { color: #cc0000; font-weight: bold; text-align: center; margin: 14px 0 10px; font-size: 10px; line-height: 1.4; }
        .footer-disclaimer { color: #cc0000; text-align: center; margin-top: 16px; font-size: 10px; }
        .sign-off { text-align: right; margin-top: 24px; font-size: 11px; }
    </style>
</head>
<body>
@php
    $mode = $mode ?? 'full';
    $logoType = $logoType ?? 'dmc';
    $displayLogoSrc = null;
    $displayCompanyName = 'Company';
    $rootDmc = null;
    $dmcUser = $invoice->dmc;

    if ($logoType === 'agency' && $invoice->agent && $invoice->agent->agency) {
        $agency = $invoice->agent->agency;
        $displayCompanyName = $agency->agency_name ?? ($invoice->travel_company_details['company_name'] ?? 'Agency');
        $agencyLogo = $agency->logo ?? null;
        if ($agencyLogo) {
            try {
                if (preg_match('/^data:image\//i', $agencyLogo)) {
                    $displayLogoSrc = $agencyLogo;
                } else {
                    $logoContent = preg_match('/^https?:\/\//i', $agencyLogo) ? @file_get_contents($agencyLogo) : @file_get_contents(public_path(ltrim($agencyLogo, '/')));
                    if ($logoContent) {
                        $displayLogoSrc = 'data:image/png;base64,' . base64_encode($logoContent);
                    }
                }
            } catch (\Exception $e) {
                $displayLogoSrc = null;
            }
        }
    }

    if ($logoType === 'dmc' && $dmcUser) {
        $rootDmc = $dmcUser;
        $visited = [];
        while ($rootDmc && (int) $rootDmc->role_id !== 11 && $rootDmc->created_by && !in_array($rootDmc->created_by, $visited, true)) {
            $visited[] = $rootDmc->created_by;
            $rootDmc = \App\Models\User::where('userId', $rootDmc->created_by)->first();
        }
        if (!$rootDmc) {
            $rootDmc = $dmcUser;
        }
        $displayCompanyName = $rootDmc->company_name ?? $dmcUser->company_name ?? 'Company';
        $dmcLogo = $rootDmc->logo ?? $dmcUser->logo ?? null;
        if ($dmcLogo) {
            try {
                if (preg_match('/^data:image\//i', $dmcLogo)) {
                    $displayLogoSrc = $dmcLogo;
                } else {
                    $logoContent = preg_match('/^https?:\/\//i', $dmcLogo) ? @file_get_contents($dmcLogo) : @file_get_contents(public_path(ltrim($dmcLogo, '/')));
                    if ($logoContent) {
                        $displayLogoSrc = 'data:image/png;base64,' . base64_encode($logoContent);
                    }
                }
            } catch (\Exception $e) {
                $displayLogoSrc = null;
            }
        }
    }

    $contactRoot = $rootDmc ?? $dmcUser;
    $addr = $contactRoot ? ($contactRoot->address ?? '') : '';
    $tel = $contactRoot ? ($contactRoot->tel ?? $contactRoot->telephone ?? $contactRoot->phone ?? '') : '';
    $fax = $contactRoot ? ($contactRoot->fax ?? '') : '';
    $em = $contactRoot ? ($contactRoot->email ?? $contactRoot->company_email ?? '') : '';
    $web = $contactRoot ? ($contactRoot->website ?? '') : '';
    $emails = array_filter(array_map('trim', preg_split('/[\/|,]+/', (string) $em)));

    $travelCompany = $invoice->travel_company_details ?? [];
    $msName = $travelCompany['company_name'] ?? ($travelCompany['name'] ?? '');
    if ($msName === '' && $invoice->agent && $invoice->agent->agency) {
        $msName = $invoice->agent->agency->agency_name ?? '';
    }

    // Co. Reg / TA licence from root DMC (same logic as proforma.blade.php header)
    $rootDmcForMeta = null;
    if ($invoice->dmc) {
        $rootDmcForMeta = $invoice->dmc;
        $visitedMeta = [];
        while ($rootDmcForMeta && (int) $rootDmcForMeta->role_id !== 11 && $rootDmcForMeta->created_by && !in_array($rootDmcForMeta->created_by, $visitedMeta, true)) {
            $visitedMeta[] = $rootDmcForMeta->created_by;
            $rootDmcForMeta = \App\Models\User::where('userId', $rootDmcForMeta->created_by)->first();
        }
        if (!$rootDmcForMeta) {
            $rootDmcForMeta = $invoice->dmc;
        }
    }
    $displayCompanyRegNo = null;
    $displayLicenceNo = null;
    if ($rootDmcForMeta) {
        $reg = trim((string) ($rootDmcForMeta->company_reg_no ?? ''));
        if ($reg === '') {
            $reg = trim((string) (($invoice->dmc->company_reg_no ?? '')));
        }
        $displayCompanyRegNo = $reg !== '' ? $reg : null;
        $lic = $rootDmcForMeta->ta_licence_no ?? $rootDmcForMeta->licence_no ?? $invoice->dmc->ta_licence_no ?? $invoice->dmc->licence_no ?? null;
        $displayLicenceNo = ($lic !== null && trim((string) $lic) !== '') ? trim((string) $lic) : null;
    }

    $displayAddress = null;
    $displayPhone = null;
    $displayEmail = null;
    if ($contactRoot) {
        $displayAddress = $contactRoot->company_address ?? $contactRoot->address ?? null;
        $displayPhone = $contactRoot->company_phone
            ?? $contactRoot->phone
            ?? $contactRoot->mobile
            ?? $contactRoot->tel
            ?? $contactRoot->telephone
            ?? null;
        $displayEmail = $contactRoot->company_email ?? $contactRoot->email ?? null;
        if (($displayEmail === null || trim((string) $displayEmail) === '') && !empty($emails)) {
            $displayEmail = implode(' / ', $emails);
        }
    }

    $clientDetails = $invoice->client_details ?? [];
    $leadGuest = $clientDetails['lead_guest_name'] ?? '';
    $tour = $invoice->tour;
    $displayIdTour = $tour ? ($tour->display_id ?? '') : '';
    $salesRefNoMeta = '';
    if ($tour) {
        $createdByUserCode = '';
        try {
            if (!empty($tour->created_by)) {
                $createdByUser = \App\Models\User::where('userId', $tour->created_by)->first();
                $createdByUserCode = trim((string) ($createdByUser->user_code ?? ''));
            }
        } catch (\Throwable $e) {
            $createdByUserCode = '';
        }

        // Sales Ref No should be users.user_code (requested).
        // If not available, fall back to tour sales_ref_no/sales_reference, else to display_id.
        if ($createdByUserCode !== '') {
            $salesRefNoMeta = $createdByUserCode ?? '';
        }
    }
    $invNo = ($invoice->invoice_type ?? '') === 'proforma' ? ($invoice->proforma_number ?? '') : ($invoice->invoice_number ?? '');
    $invDate = $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d-M-Y') : '';
    $docTitle = ($invoice->invoice_type ?? '') === 'proforma' ? 'PROFORMA INVOICE' : 'INVOICE';

    $baseCc = strtoupper($invoice->base_currency ?? 'SGD');
    $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
    $baseAmount = $notes['base_amount'] ?? ($invoice->getNegotiatedAmount() ?? ($invoice->total_amount ?? 0));
    $gstAmount = (float) ($invoice->gst_amount ?? 0);
    $serviceCharge = (float) ($invoice->service_charge ?? 0);
    $touristTax = (float) ($invoice->tourist_tax ?? 0);
    $finalPrice = (float) $baseAmount + $gstAmount;
    $grandTotal = (float) ($invoice->total_amount ?? ($finalPrice + $serviceCharge + $touristTax));
    $tourStatus = $tour ? ($tour->tour_status ?? '') : '';
    $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
    $shouldShowTax = in_array($tourStatus, $statusesWithTax, true);
    $paymentReceived = (float) ($invoice->payment_received ?? 0);
    $outstandingBalance = (float) ($invoice->outstanding_balance ?? $grandTotal);

    $actualFromItems = (float) $invoice->items->sum('total_price');
    $discount = $actualFromItems - (float) $baseAmount;

    $selectedCurrency = strtoupper($selectedCurrency ?? 'SGD');
    $currencyConversion = $currencyConversion ?? [];
    $showCurrencyConversion = ($selectedCurrency !== 'SGD' && count($currencyConversion) > 1);

    // Use currency code prefix to avoid missing-glyph issues in PDF engines.
    $selectedCurrencyPrefix = $selectedCurrency . ' ';

    $fmtMoney = function ($n) use ($baseCc) {
        return number_format(round((float) $n, 2), 2);
    };

    $arrival = $invoice->travel_from_date ? \Carbon\Carbon::parse($invoice->travel_from_date)->format('d M Y') : '';
    $departure = $invoice->travel_to_date ? \Carbon\Carbon::parse($invoice->travel_to_date)->format('d M Y') : '';
@endphp

@include('invoices.pdf.partials.header', ['invoice' => $invoice, 'logoType' => ($logoType ?? 'dmc'), 'showBlueTitle' => true])

{{-- M/s + booking summary (left); invoice meta (right) --}}
<table class="meta-header-wrap" style="margin-top:0; border-top:0;">
    <tr>
        <td style="width:50%; border:1px solid #000; vertical-align:top;">
            <strong>M/s:</strong> {{ $msName !== '' ? $msName : '—' }}<br><br>
            <strong>Guest / Party:</strong> {{ $leadGuest !== '' ? $leadGuest : ($clientDetails['email'] ?? 'Guest') }}<br>
            <strong>Travellers:</strong> Adults {{ str_pad((string)($invoice->no_of_adults ?? 0), 2, '0', STR_PAD_LEFT) }} &nbsp;|&nbsp; Children {{ str_pad((string)($invoice->no_of_children ?? 0), 2, '0', STR_PAD_LEFT) }}<br>
            <strong>Destination:</strong> {{ $invoice->destination ?? '—' }}<br>
            <strong>Travel Dates:</strong> {{ $arrival !== '' ? $arrival : '—' }} &ndash; {{ $departure !== '' ? $departure : '—' }}
        </td>
        <td class="meta-right-stack" style="width:50%; border:1px solid #000;">
            <table class="no-border" style="width:100%;">
                <tr><td class="meta-label">@if(($invoice->invoice_type ?? '') === 'proforma') Proforma No: @else Inv No.: @endif</td><td>{{ $invNo ?: '—' }}</td></tr>
                <tr><td class="meta-label">Display ID:</td><td>{{ $displayIdTour ?? '—' }}</td></tr>
                <tr><td class="meta-label">Date:</td><td>{{ $invDate }}</td></tr>
                <tr><td class="meta-label">Co. Reg. No:</td><td>{{ $displayCompanyRegNo ?? '—' }}</td></tr>
                <tr><td class="meta-label">Sales Ref No:</td><td>{{ $salesRefNoMeta !== '' ? $salesRefNoMeta : '—' }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="inv-title">{{ $docTitle }}</div>

<table class="inv-lines-outer-wrap">
<tbody>
<tr>
<td class="inv-lines-box-cell">
<table class="inv-lines-table">
    <thead>
        <tr>
            <th class="inv-col-service">Service</th>
            <th class="inv-col-details">Details</th>
            <th class="inv-col-amount">Amount ({{ $baseCc }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items->sortBy('id') as $item)
        @php
            $typeLabel = ucwords(str_replace('_', ' ', (string) ($item->item_type ?? 'Item')));
            $desc = trim((string) ($item->description ?? ''));
            $detailText = $desc !== '' ? \Illuminate\Support\Str::limit($desc, 200) : $typeLabel;
        @endphp
        <tr class="inv-line-data">
            <td class="inv-col-service"><span class="inv-svc-cat">{{ strtoupper($typeLabel) }}</span></td>
            <td class="inv-col-details"><span class="inv-svc-detail">{{ $detailText }}</span></td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $fmtMoney($item->total_price ?? 0) }}</span></td>
        </tr>
        @endforeach

        @if($serviceCharge > 0)
        <tr class="inv-line-data">
            <td class="inv-col-service"><span class="inv-svc-cat">Fees</span></td>
            <td class="inv-col-details"><span class="inv-svc-detail">TOTAL TT Bank charges per Transaction – {{ $baseCc }} (service charge)</span></td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $fmtMoney($serviceCharge) }}</span></td>
        </tr>
        @endif

        @if($touristTax > 0)
        <tr class="inv-line-data">
            <td class="inv-col-service"><span class="inv-svc-cat">Tax</span></td>
            <td class="inv-col-details"><span class="inv-svc-detail">Tourist Tax</span></td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $fmtMoney($touristTax) }}</span></td>
        </tr>
        @endif

        @if($discount > 0.01)
        <tr class="inv-line-data">
            <td class="inv-col-service"><span class="inv-svc-cat">Discount</span></td>
            <td class="inv-col-details"><span class="inv-svc-detail">Discount</span></td>
            <td class="inv-col-amount"><span class="inv-svc-amt">-{{ $fmtMoney($discount) }}</span></td>
        </tr>
        @elseif($discount < -0.01)
        <tr class="inv-line-data">
            <td class="inv-col-service"><span class="inv-svc-cat">Adjustment</span></td>
            <td class="inv-col-details"><span class="inv-svc-detail">Additional Charges</span></td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $fmtMoney(abs($discount)) }}</span></td>
        </tr>
        @endif

        @if($shouldShowTax && $gstAmount > 0)
        <tr class="inv-line-data">
            <td class="inv-col-service"><span class="inv-svc-cat">GST / Tax</span></td>
            <td class="inv-col-details"><span class="inv-svc-detail">GST / Tax (included in total where applicable)</span></td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $fmtMoney($gstAmount) }}</span></td>
        </tr>
        @endif

        <tr class="inv-total-row inv-total-sep inv-grand-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">TOTAL {{ $baseCc }}</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $fmtMoney($grandTotal) }}</span></td>
        </tr>

        @if($shouldShowTax)
        <tr class="inv-total-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Payment Received</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $fmtMoney($paymentReceived) }}</span></td>
        </tr>
        <tr class="inv-total-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Outstanding Balance</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $fmtMoney($outstandingBalance) }}</span></td>
        </tr>
        @endif
    </tbody>
</table>
</td>
</tr>
</tbody>
</table>

@if($showCurrencyConversion)
@php
    $convAmt = $currencyConversion[$selectedCurrency] ?? null;
@endphp
@if($convAmt !== null)
<table class="inv-currency-bar" style="margin-top:10px; width:100%; table-layout:fixed; border-collapse:collapse;">
    <tr class="conv-row">
        <td style="width:78%; padding:8px 10px; vertical-align:middle;">
            <strong>Total booking amount {{ strtolower($selectedCurrency) }}</strong>
        </td>
        <td style="width:22%; padding:8px 10px; text-align:right; vertical-align:middle; font-family: DejaVu Sans Mono, Courier New, Courier, monospace;">
            {{ $selectedCurrencyPrefix }}{{ number_format(round((float) $convAmt)) }}
        </td>
    </tr>
</table>
@endif
@endif

@php
    $tourForBank = $invoice->tour;
    $dmcIdForBank = $tourForBank?->dmc_id ?? $invoice->dmc_id ?? null;
    $bankDetails = collect();
    $paymentTerms = [];
    if ($dmcIdForBank) {
        $bankDetails = \App\Models\BankDetail::where('dmc_id', $dmcIdForBank)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();
    }
    if ($bankDetails->isNotEmpty()) {
        $paymentTerms = $bankDetails->first()->payment_terms ?? [];
    }
    if (empty($paymentTerms)) {
        $paymentTerms = $invoice->payment_terms ?? [];
    }
@endphp

@if(!empty($paymentTerms))
<div style="margin-top:14px; font-size:10px;">
    <strong>Payment Terms</strong>
    <ol style="margin-left:18px; margin-top:4px;">
        @foreach($paymentTerms as $term)
        <li>{{ $term }}</li>
        @endforeach
    </ol>
</div>
@endif

@if($bankDetails->isNotEmpty())
    @foreach($bankDetails as $bankDetail)
    @php
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
        $bankTypeLabel = $bankDetail->bank_type ?? 'SGD Accounts';
        $indiaBankDetails = is_array($bankDetail->india_bank_details ?? null) ? $bankDetail->india_bank_details : [];
        $hasIndiaBankContent = !empty($indiaBankDetails) && (
            !empty($indiaBankDetails['gst_number']) || !empty($indiaBankDetails['pan_number']) ||
            !empty($indiaBankDetails['account_name']) || !empty($indiaBankDetails['account_number']) ||
            !empty($indiaBankDetails['bank_name']) || !empty($indiaBankDetails['ifsc']) ||
            !empty($indiaBankDetails['bank_address'])
        );
    @endphp
    @if(!empty($bankDetailsData['account_name']) || !empty($bankDetailsData['account_number']) || $hasIndiaBankContent)
    <div class="inv-bank-block">
        @if(!empty($bankDetailsData['account_name']) || !empty($bankDetailsData['account_number']))
        <div class="inv-bank-section-title">Bank Details ({{ $bankTypeLabel }})</div>
        <table class="inv-bank-outer"><tbody><tr><td class="inv-bank-box-cell">
        <table class="inv-bank-grid">
            @if(!empty($bankDetailsData['account_name']))
            <tr><td class="inv-bank-label">Account Name</td><td class="inv-bank-val">{{ $bankDetailsData['account_name'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['account_number']))
            <tr><td class="inv-bank-label">Account No</td><td class="inv-bank-val">{{ $bankDetailsData['account_number'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['swift_bic_iban']))
            <tr><td class="inv-bank-label">Swift Code</td><td class="inv-bank-val">{{ $bankDetailsData['swift_bic_iban'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['bank_code']))
            <tr><td class="inv-bank-label">Bank No</td><td class="inv-bank-val">{{ $bankDetailsData['bank_code'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['branch_code']))
            <tr><td class="inv-bank-label">Branch No</td><td class="inv-bank-val">{{ $bankDetailsData['branch_code'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['bank_address']))
            <tr><td class="inv-bank-label">Bank Address</td><td class="inv-bank-val">{{ $bankDetailsData['bank_address'] }}</td></tr>
            @endif
        </table>
        </td></tr></tbody></table>
        @endif

        @if($hasIndiaBankContent)
        <p class="note-red">Note:- If you pay in India then you can transfer your payment in our Indian collection agent account.</p>
        <div class="inv-bank-section-title">Bank Details ({{ $indiaBankDetails['bank_type'] ?? 'INR Accounts' }})</div>
        <table class="inv-bank-outer"><tbody><tr><td class="inv-bank-box-cell">
        <table class="inv-bank-grid">
            @if(!empty($indiaBankDetails['gst_number']))
            <tr><td class="inv-bank-label">GST Registration Number</td><td class="inv-bank-val">{{ $indiaBankDetails['gst_number'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['pan_number']))
            <tr><td class="inv-bank-label">PAN Number</td><td class="inv-bank-val">{{ $indiaBankDetails['pan_number'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['account_name']))
            <tr><td class="inv-bank-label">Account Name</td><td class="inv-bank-val">{{ $indiaBankDetails['account_name'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['bank_name']))
            <tr><td class="inv-bank-label">Bank</td><td class="inv-bank-val">{{ $indiaBankDetails['bank_name'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['account_number']))
            <tr><td class="inv-bank-label">Account No</td><td class="inv-bank-val">{{ $indiaBankDetails['account_number'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['ifsc']))
            <tr><td class="inv-bank-label">IFSC Code</td><td class="inv-bank-val">{{ $indiaBankDetails['ifsc'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['bank_address']))
            <tr><td class="inv-bank-label">Bank Address</td><td class="inv-bank-val">{{ $indiaBankDetails['bank_address'] }}</td></tr>
            @endif
        </table>
        </td></tr></tbody></table>
        @endif
    </div>
    @endif
    @endforeach
@elseif(!empty($invoice->bank_details))
@php $bd = $invoice->bank_details ?? []; @endphp
<div class="inv-bank-block">
    <div class="inv-bank-section-title">Bank Details ({{ $bd['bank_type'] ?? 'SGD Accounts' }})</div>
    <table class="inv-bank-outer"><tbody><tr><td class="inv-bank-box-cell">
    <table class="inv-bank-grid">
        <tr><td class="inv-bank-label">Account Name</td><td class="inv-bank-val">{{ $bd['account_name'] ?? '' }}</td></tr>
        <tr><td class="inv-bank-label">Account No</td><td class="inv-bank-val">{{ $bd['account_number'] ?? '' }}</td></tr>
        <tr><td class="inv-bank-label">Bank Address</td><td class="inv-bank-val">{{ $bd['bank_address'] ?? '' }}</td></tr>
    </table>
    </td></tr></tbody></table>
</div>
@endif

<div class="sign-off">
    <div><strong>Authorised Signatory</strong></div>
</div>
<div class="footer-disclaimer">Its computer generated invoice no need for signature</div>

</body>
</html>
