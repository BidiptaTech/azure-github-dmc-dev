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
        @include('invoices.pdf.partials.header-css')
        .inv-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 10px;
            border: 1px solid #000;
            margin-bottom: 0;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .meta-label { font-weight: bold; width: 120px; }
        .amount-col { text-align: right; white-space: nowrap; }
        .conv-row { background-color: #ffff00; font-weight: bold; }
        .note-red { color: #cc0000; font-weight: bold; text-align: center; margin: 12px 0; }
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

    $si = 0;
@endphp

@include('invoices.pdf.partials.header', ['invoice' => $invoice, 'logoType' => ($logoType ?? 'dmc'), 'showBlueTitle' => true])

{{-- M/s + invoice meta --}}
<table style="margin-top:0; border-top:0;">
    <tr>
        <td style="width:50%; border:1px solid #000;">
            <strong>M/s:</strong> {{ $msName !== '' ? $msName : '—' }}
        </td>
        <td style="width:50%; border:1px solid #000;">
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

<table style="margin-top:0;">
    <thead>
        <tr>
            <th style="width:8%;" class="text-center">SI NO</th>
            <th>Particulars</th>
            <th style="width:20%;" class="text-right">Amount ({{ $baseCc }})</th>
        </tr>
    </thead>
    <tbody>
        @php $si = 1; @endphp
        <tr>
            <td class="text-center">{{ $si++ }}</td>
            <td>
                <strong>Service Offered to:</strong>
                {{ $leadGuest !== '' ? $leadGuest : ($clientDetails['email'] ?? 'Guest') }}
                <br><strong>No of Adult:</strong> {{ str_pad((string)($invoice->no_of_adults ?? 0), 2, '0', STR_PAD_LEFT) }}
                &nbsp; <strong>No of Child:</strong> {{ str_pad((string)($invoice->no_of_children ?? 0), 2, '0', STR_PAD_LEFT) }}
                <br><strong>Destination:</strong> {{ $invoice->destination ?? '' }}
                <br><strong>Arrival:</strong> {{ $arrival }} &nbsp; <strong>Departure:</strong> {{ $departure }}
            </td>
            <td class="amount-col"></td>
        </tr>

        @foreach($invoice->items->sortBy('id') as $item)
        @php
            $typeLabel = ucwords(str_replace('_', ' ', (string) ($item->item_type ?? 'Item')));
            $desc = $item->description ?? '';
            $line = 'Rate: ' . $typeLabel . ($desc !== '' ? ' — ' . \Illuminate\Support\Str::limit($desc, 200) : '');
        @endphp
        <tr>
            <td class="text-center">{{ $si++ }}</td>
            <td>{{ $line }}</td>
            <td class="amount-col">{{ $fmtMoney($item->total_price ?? 0) }}</td>
        </tr>
        @endforeach

        @if($serviceCharge > 0)
        <tr>
            <td class="text-center">{{ $si++ }}</td>
            <td>TOTAL TT Bank charges per Transaction - {{ $baseCc }} (service charge)</td>
            <td class="amount-col">{{ $fmtMoney($serviceCharge) }}</td>
        </tr>
        @endif

        @if($touristTax > 0)
        <tr>
            <td class="text-center">{{ $si++ }}</td>
            <td>Tourist Tax</td>
            <td class="amount-col">{{ $fmtMoney($touristTax) }}</td>
        </tr>
        @endif

        @if($discount > 0.01)
        <tr>
            <td class="text-center">{{ $si++ }}</td>
            <td>Discount</td>
            <td class="amount-col">-{{ $fmtMoney($discount) }}</td>
        </tr>
        @elseif($discount < -0.01)
        <tr>
            <td class="text-center">{{ $si++ }}</td>
            <td>Additional Charges</td>
            <td class="amount-col">{{ $fmtMoney(abs($discount)) }}</td>
        </tr>
        @endif

        @if($shouldShowTax && $gstAmount > 0)
        <tr>
            <td class="text-center">{{ $si++ }}</td>
            <td>GST / Tax (included in total where applicable)</td>
            <td class="amount-col">{{ $fmtMoney($gstAmount) }}</td>
        </tr>
        @endif

        <tr style="font-weight:bold;">
            <td colspan="2" class="text-right">TOTAL {{ $baseCc }}</td>
            <td class="amount-col">{{ $fmtMoney($grandTotal) }}</td>
        </tr>

        @if($shouldShowTax)
        <tr>
            <td colspan="2" class="text-right">Payment Received</td>
            <td class="amount-col">{{ $fmtMoney($paymentReceived) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="text-right">Outstanding Balance</td>
            <td class="amount-col">{{ $fmtMoney($outstandingBalance) }}</td>
        </tr>
        @endif
    </tbody>
</table>

@if($showCurrencyConversion)
@php
    $convAmt = $currencyConversion[$selectedCurrency] ?? null;
@endphp
@if($convAmt !== null)
<table style="margin-top:10px;">
    <tr class="conv-row">
        <td colspan="2">
            <strong>Total booking amount {{ strtolower($selectedCurrency) }}</strong>
        </td>
        <td class="amount-col">
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
    <div style="margin-top:16px; page-break-inside: avoid;">
        @if(!empty($bankDetailsData['account_name']) || !empty($bankDetailsData['account_number']))
        <div class="text-center" style="font-weight:bold; margin-bottom:6px;">Bank Details ({{ $bankTypeLabel }})</div>
        <table>
            @if(!empty($bankDetailsData['account_name']))
            <tr><td style="width:40%;"><strong>Account Name</strong></td><td>{{ $bankDetailsData['account_name'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['account_number']))
            <tr><td><strong>Account No</strong></td><td>{{ $bankDetailsData['account_number'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['swift_bic_iban']))
            <tr><td><strong>Swift Code</strong></td><td>{{ $bankDetailsData['swift_bic_iban'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['bank_code']))
            <tr><td><strong>Bank No</strong></td><td>{{ $bankDetailsData['bank_code'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['branch_code']))
            <tr><td><strong>Branch No</strong></td><td>{{ $bankDetailsData['branch_code'] }}</td></tr>
            @endif
            @if(!empty($bankDetailsData['bank_address']))
            <tr><td><strong>Bank Address</strong></td><td>{{ $bankDetailsData['bank_address'] }}</td></tr>
            @endif
        </table>
        @endif

        @if($hasIndiaBankContent)
        <p class="note-red">Note:- If you pay in India then you can transfer your payment in our Indian collection agent account.</p>
        <div class="text-center" style="font-weight:bold; margin-bottom:6px;">Bank Details ({{ $indiaBankDetails['bank_type'] ?? 'INR Accounts' }})</div>
        <table>
            @if(!empty($indiaBankDetails['gst_number']))
            <tr><td style="width:40%;"><strong>GST Registration Number</strong></td><td>{{ $indiaBankDetails['gst_number'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['pan_number']))
            <tr><td><strong>PAN Number</strong></td><td>{{ $indiaBankDetails['pan_number'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['account_name']))
            <tr><td><strong>Account Name</strong></td><td>{{ $indiaBankDetails['account_name'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['bank_name']))
            <tr><td><strong>Bank</strong></td><td>{{ $indiaBankDetails['bank_name'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['account_number']))
            <tr><td><strong>Account No</strong></td><td>{{ $indiaBankDetails['account_number'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['ifsc']))
            <tr><td><strong>IFSC Code</strong></td><td>{{ $indiaBankDetails['ifsc'] }}</td></tr>
            @endif
            @if(!empty($indiaBankDetails['bank_address']))
            <tr><td><strong>Bank Address</strong></td><td>{{ $indiaBankDetails['bank_address'] }}</td></tr>
            @endif
        </table>
        @endif
    </div>
    @endif
    @endforeach
@elseif(!empty($invoice->bank_details))
@php $bd = $invoice->bank_details ?? []; @endphp
<div style="margin-top:16px;">
    <div class="text-center" style="font-weight:bold; margin-bottom:6px;">Bank Details ({{ $bd['bank_type'] ?? 'SGD Accounts' }})</div>
    <table>
        <tr><td style="width:40%;"><strong>Account Name</strong></td><td>{{ $bd['account_name'] ?? '' }}</td></tr>
        <tr><td><strong>Account No</strong></td><td>{{ $bd['account_number'] ?? '' }}</td></tr>
        <tr><td><strong>Bank Address</strong></td><td>{{ $bd['bank_address'] ?? '' }}</td></tr>
    </table>
</div>
@endif

<div class="sign-off">
    <div><strong>Authorised Signatory</strong></div>
</div>
<div class="footer-disclaimer">Its computer generated invoice no need for signature</div>

</body>
</html>
