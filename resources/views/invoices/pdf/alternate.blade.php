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
        @page {
            margin: 8mm 14mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            margin: 2mm 6mm;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.25;
            color: #111;
            padding: 0;
        }
        table { width: 100%; border-collapse: collapse; border-spacing: 0; }
        th, td { border: none; padding: 1px 3px 2px; vertical-align: top; }
        .no-border td, .no-border th { border: none; padding: 0 2px 1px 0; }
        /* Lines table only — outer frame removed */
        table.inv-lines-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            table-layout: fixed;
        }
        table.inv-lines-table > thead > tr > th {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #444;
            background: transparent !important;
            border: none !important;
            border-bottom: 1px solid #999 !important;
            padding: 3px 4px 2px !important;
            vertical-align: bottom;
        }
        table.inv-lines-table > thead > tr > th.inv-col-service { text-align: left; width: 16%; }
        table.inv-lines-table > thead > tr > th.inv-col-details { text-align: left; width: 62%; }
        table.inv-lines-table > thead > tr > th.inv-col-amount {
            text-align: right;
            width: 22%;
            font-family: DejaVu Sans Mono, Courier New, Courier, monospace;
            padding-right: 4px !important;
            padding-left: 2px !important;
        }
        table.inv-lines-table > tbody > tr > td {
            border: none !important;
            vertical-align: top;
        }
        table.inv-lines-table > tbody > tr.inv-line-data > td {
            border-bottom: none !important;
            padding: 2px 4px !important;
        }
        table.inv-lines-table .inv-col-service { width: 16%; }
        table.inv-lines-table .inv-col-details { width: 62%; }
        table.inv-lines-table .inv-col-amount {
            width: 22%;
            text-align: right;
            font-family: DejaVu Sans Mono, Courier New, Courier, monospace;
            padding-right: 4px !important;
            padding-left: 2px !important;
            vertical-align: top;
        }
        .inv-svc-cat {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #666;
            line-height: 1.2;
        }
        .inv-svc-detail {
            font-size: 10px;
            color: #222;
            line-height: 1.3;
        }
        .inv-svc-amt {
            display: block;
            font-size: 10px;
            font-weight: bold;
            color: #111;
            text-align: right;
            white-space: nowrap;
            font-family: inherit;
        }
        table.inv-lines-table > tbody > tr.inv-total-row > td {
            border: none !important;
            padding: 2px 4px !important;
            font-size: 10px;
            color: #333;
            vertical-align: middle;
        }
        table.inv-lines-table > tbody > tr.inv-total-row > td.inv-total-label-cell {
            text-align: right;
            padding-right: 4px;
            font-weight: 600;
            color: #333;
        }
        table.inv-lines-table > tbody > tr.inv-total-row:not(.inv-grand-row) > td.inv-col-amount .inv-svc-amt {
            font-weight: normal;
            font-size: 10px;
        }
        table.inv-lines-table > tbody > tr.inv-grand-row > td.inv-col-amount .inv-svc-amt {
            font-weight: bold !important;
        }
        table.inv-lines-table > tbody > tr.inv-total-sep > td {
            border-top: none !important;
            padding-top: 4px !important;
        }
        table.inv-lines-table > tbody > tr.inv-grand-row > td {
            font-size: 11px !important;
            font-weight: bold !important;
            color: #111 !important;
            border-top: 1px solid #333 !important;
            padding-top: 4px !important;
            padding-bottom: 3px !important;
            vertical-align: middle !important;
        }
        table.inv-lines-table > tbody > tr.inv-grand-row > td.inv-total-label-cell {
            font-size: 11px !important;
        }
        table.inv-lines-table > tbody > tr.inv-grand-row > td.inv-col-amount .inv-svc-amt,
        table.inv-lines-table > tbody > tr.inv-grand-row > td.inv-col-amount {
            font-size: 11px !important;
            font-weight: bold !important;
        }
        table.meta-header-wrap { width: 100%; border-collapse: collapse; margin: 0 0 6px 0; }
        table.meta-header-wrap > tbody > tr > td {
            border: none !important;
            padding: 4px 6px 4px 0 !important;
            vertical-align: top;
        }
        table.meta-header-wrap td.meta-right-stack {
            vertical-align: top;
            padding: 4px 0 4px 8px !important;
            border-left: none !important;
        }
        table.inv-currency-bar { margin-top: 4px; border-collapse: collapse; width: 100%; table-layout: fixed; }
        table.inv-currency-bar tr.conv-row td {
            border: none !important;
            background-color: #fffde7 !important;
            font-weight: bold;
            font-size: 9px;
            padding: 4px 6px !important;
            border-bottom: 1px solid #e6e0b0 !important;
        }
        table.inv-currency-bar tr.conv-row td:last-child {
            font-family: DejaVu Sans Mono, Courier New, Courier, monospace;
            text-align: right;
        }
        @include('invoices.pdf.partials.header-css')
        .inv-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 4px 4px 2px;
            margin: 2px 0 4px;
            border: none;
        }
        .inv-lite-price-hint {
            text-align: center;
            font-size: 10px;
            font-weight: 600;
            color: #444;
            margin: 0 0 6px 0;
            letter-spacing: 0.02em;
        }
        .inv-inclusions-block {
            margin: 0 0 10px 0;
            page-break-inside: avoid;
        }
        .inv-inclusions-title {
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #333;
            margin: 0 0 4px 0;
            padding: 0 0 3px 0;
            border-bottom: 1px solid #bbb;
        }
        .inv-inclusions-list {
            margin: 5px 0 0 0;
            padding: 0;
        }
        table.inv-inclusions-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
        }
        table.inv-inclusions-table td {
            border: none !important;
            padding: 2px 8px 2px 0 !important;
            vertical-align: top;
            line-height: 1.35;
        }
        table.inv-inclusions-table td.inv-inclusion-type {
            width: 26%;
            max-width: 120px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #666;
            white-space: nowrap;
        }
        table.inv-inclusions-table td.inv-inclusion-desc {
            width: 74%;
            font-size: 10px;
            color: #222;
            word-wrap: break-word;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        /* Bank details — flat, no frame */
        .inv-bank-block { margin-top: 6px; }
        .inv-bank-section-title {
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #333;
            margin: 6px 0 2px 0;
            padding: 0;
            border-bottom: 1px solid #bbb;
        }
        table.inv-bank-grid { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 0 4px 0; }
        table.inv-bank-grid td {
            border: none !important;
            padding: 1px 4px 2px 0 !important;
            vertical-align: top;
        }
        table.inv-bank-grid td.inv-bank-label {
            width: 32%;
            font-weight: bold;
            font-size: 10px;
            color: #444;
            white-space: nowrap;
        }
        table.inv-bank-grid td.inv-bank-val {
            font-size: 10px;
            color: #111;
            line-height: 1.25;
        }
        .amount-col { text-align: right; white-space: nowrap; }
        .note-red {
            color: #c00;
            font-weight: bold;
            text-align: left;
            margin: 6px 0 3px;
            font-size: 9px;
            line-height: 1.25;
        }
        .payment-terms-block { margin-top: 5px; font-size: 10px; page-break-inside: avoid; }
        .payment-terms-block ol { margin: 2px 0 0 14px; padding: 0; }
        .payment-terms-block li { margin: 0 0 1px; line-height: 1.25; }
        .footer-disclaimer { color: #c00; text-align: center; margin-top: 6px; font-size: 8.5px; }
        .sign-off { text-align: right; margin-top: 10px; font-size: 9px; line-height: 1.2; }
        /* Compact shared header overrides (after partial CSS) */
        body.invoice-pdf-compact .header { margin-bottom: 5px !important; }
        body.invoice-pdf-compact .dmc-logo-wrapper {
            height: auto !important;
            min-height: 0 !important;
            max-height: 56px !important;
            margin-bottom: 0 !important;
        }
        body.invoice-pdf-compact .dmc-logo-wrapper img {
            max-width: 220px !important;
            max-height: 110px !important;
            margin-top: -30px !important;
            object-fit: contain;
        }
        body.invoice-pdf-compact .header-center .dmc-name {
            font-size: 14px !important;
            margin-bottom: 2px !important;
        }
        body.invoice-pdf-compact .header-center .dmc-address,
        body.invoice-pdf-compact .header-center .dmc-contact,
        body.invoice-pdf-compact .header-center .dmc-meta {
            font-size: 9.5px !important;
            line-height: 1.2 !important;
            margin-top: 1px !important;
        }
        body.invoice-pdf-compact .header-center .dmc-meta div { margin-top: 1px !important; }
        body.invoice-pdf-compact .header-doc-title {
            font-size: 13px !important;
            margin-top: 4px !important;
            margin-bottom: 0 !important;
        }
        body.invoice-pdf-compact .header-right .doc-number { font-size: 9px !important; }
        body.invoice-pdf-compact .header-table td.header-left {
            padding: 2px 6px 0 0 !important;
        }
    </style>
</head>
<body class="invoice-pdf-compact">
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

    // Price-only lite summary: must run in this scope (Blade @include is isolated).
    if (($mode ?? 'full') === 'price-only') {
        require resource_path('views/invoices/pdf/partials/alternate-lite-price-compute-inc.php');
    }
@endphp

@include('invoices.pdf.partials.header', ['invoice' => $invoice, 'logoType' => ($logoType ?? 'dmc'), 'showBlueTitle' => false])

{{-- M/s + booking summary (left); invoice meta (right) --}}
<table class="meta-header-wrap">
    <tr>
        <td style="width:70%; vertical-align:top;">
            <strong>M/s:</strong> {{ $msName !== '' ? $msName : '—' }}<br>
            <strong>Guest / Party:</strong> {{ $leadGuest !== '' ? $leadGuest : ($clientDetails['email'] ?? 'Guest') }}<br>
            <strong>Travellers:</strong> Adults {{ str_pad((string)($invoice->no_of_adults ?? 0), 2, '0', STR_PAD_LEFT) }} &nbsp;|&nbsp; Children {{ str_pad((string)($invoice->no_of_children ?? 0), 2, '0', STR_PAD_LEFT) }}<br>
            <strong>Destination:</strong> {{ $invoice->destination ?? '—' }}<br>
            <strong>Travel Dates:</strong> {{ $arrival !== '' ? $arrival : '—' }} &ndash; {{ $departure !== '' ? $departure : '—' }}
        </td>
        <td class="meta-right-stack" style="width:30%; vertical-align:top;">
            <strong>@if(($invoice->invoice_type ?? '') === 'proforma') Proforma No: @else Inv No.: @endif</strong> {{ $invNo ?: '—' }}<br>
            <strong>Display ID:</strong> {{ $displayIdTour ?? '—' }}<br>
            <strong>Date:</strong> {{ $invDate }}<br>
            <strong>Co. Reg. No:</strong> {{ $displayCompanyRegNo ?? '—' }}<br>
            <strong>Sales Ref No:</strong> {{ $salesRefNoMeta !== '' ? $salesRefNoMeta : '—' }}
        </td>
    </tr>
</table>

<div class="inv-title">{{ $docTitle }}</div>


@if($mode !== 'price-only')
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
            $detailText = $desc !== '' ? \Illuminate\Support\Str::limit($desc, 220) : $typeLabel;
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
@else
{{-- Lite + Price Breakup: Inclusions list, then aggregate summary table (same totals as standard price-only logic) --}}
<div class="inv-inclusions-block">
    <div class="inv-inclusions-title">Inclusions</div>
    <div class="inv-inclusions-list">
        <table class="inv-inclusions-table">
            <tbody>
                @foreach($invoice->items->sortBy('id') as $item)
                @php
                    $typeLabelPo = ucwords(str_replace('_', ' ', (string) ($item->item_type ?? 'Item')));
                    $descPo = trim((string) ($item->description ?? ''));
                    $detailTextPo = $descPo !== '' ? \Illuminate\Support\Str::limit($descPo, 220) : $typeLabelPo;
                @endphp
                <tr>
                    <td class="inv-inclusion-type">{{ strtoupper($typeLabelPo) }}:</td>
                    <td class="inv-inclusion-desc">{{ $detailTextPo }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<table class="inv-lines-table">
    <tbody>
        <tr class="inv-total-row inv-total-sep"><td colspan="3" style="padding-top:6px;"></td></tr>

        <tr class="inv-total-row inv-grand-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Total (Actual Amount)</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $litePdfFormatPrice($liteActualAmount) }}</span></td>
        </tr>
        @if($liteNegotiatedAmount !== null)
        <tr class="inv-total-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Last Negotiated Amount</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $litePdfFormatPrice($liteNegotiatedAmount) }}</span></td>
        </tr>
        @if($liteDiscountVsActual > 0)
        <tr class="inv-total-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Discount</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">-{{ $litePdfFormatPrice($liteDiscountVsActual) }}</span></td>
        </tr>
        @elseif($liteDiscountVsActual < 0)
        <tr class="inv-total-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Additional Charges</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $litePdfFormatPrice(abs($liteDiscountVsActual)) }}</span></td>
        </tr>
        @endif
        @endif

        @if($liteShouldShowTax && $liteGstAmount > 0)
            @if(!empty($taxBreakdownLite))
                @foreach($taxBreakdownLite as $taxName => $taxValue)
                <tr class="inv-total-row">
                    <td class="inv-col-service">&nbsp;</td>
                    <td class="inv-total-label-cell">{{ $taxName }}</td>
                    <td class="inv-col-amount"><span class="inv-svc-amt">{{ $litePdfFormatPrice($taxValue) }}</span></td>
                </tr>
                @endforeach
            @else
                <tr class="inv-total-row">
                    <td class="inv-col-service">&nbsp;</td>
                    <td class="inv-total-label-cell">Total Vat / GST Tax</td>
                    <td class="inv-col-amount"><span class="inv-svc-amt">{{ $litePdfFormatPrice($liteGstAmount) }}</span></td>
                </tr>
            @endif
        @endif

        <tr class="inv-total-row inv-grand-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Final Price</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $litePdfFormatPrice($liteFinalPrice) }}</span></td>
        </tr>

        @if($liteShouldShowTax)
        <tr class="inv-total-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Payment Received</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $litePdfFormatPrice($litePaymentReceived) }}</span></td>
        </tr>
        <tr class="inv-total-row">
            <td class="inv-col-service">&nbsp;</td>
            <td class="inv-total-label-cell">Outstanding Balance</td>
            <td class="inv-col-amount"><span class="inv-svc-amt">{{ $litePdfFormatPrice($liteOutstandingBalance) }}</span></td>
        </tr>
        @endif
    </tbody>
</table>
@endif

@if($showCurrencyConversion)
@php
    $convAmt = $currencyConversion[$selectedCurrency] ?? null;
@endphp
@if($convAmt !== null)
<table class="inv-currency-bar">
    <tr class="conv-row">
        <td style="width:78%; vertical-align:middle;">
            <strong>Total booking amount {{ strtolower($selectedCurrency) }}</strong>
        </td>
        <td style="width:22%; text-align:right; vertical-align:middle; font-family: DejaVu Sans Mono, Courier New, Courier, monospace;">
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
<div class="payment-terms-block">
    <strong>Payment Terms</strong>
    <ol>
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
        @endif

        @if($hasIndiaBankContent)
        <p class="note-red">Note:- If you pay in India then you can transfer your payment in our Indian collection agent account.</p>
        <div class="inv-bank-section-title">Bank Details ({{ $indiaBankDetails['bank_type'] ?? 'INR Accounts' }})</div>
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
        @endif
    </div>
    @endif
    @endforeach
@elseif(!empty($invoice->bank_details))
@php $bd = $invoice->bank_details ?? []; @endphp
<div class="inv-bank-block">
    <div class="inv-bank-section-title">Bank Details ({{ $bd['bank_type'] ?? 'SGD Accounts' }})</div>
    <table class="inv-bank-grid">
        <tr><td class="inv-bank-label">Account Name</td><td class="inv-bank-val">{{ $bd['account_name'] ?? '' }}</td></tr>
        <tr><td class="inv-bank-label">Account No</td><td class="inv-bank-val">{{ $bd['account_number'] ?? '' }}</td></tr>
        <tr><td class="inv-bank-label">Bank Address</td><td class="inv-bank-val">{{ $bd['bank_address'] ?? '' }}</td></tr>
    </table>
</div>
@endif

<div class="sign-off">
    <div><strong>Authorised Signatory</strong></div>
</div>
<div class="footer-disclaimer">Its computer generated invoice no need for signature</div>

</body>
</html>

