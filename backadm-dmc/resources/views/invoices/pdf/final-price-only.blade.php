<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $invoice->invoice_number ?? 'DRAFT' }}</title>
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
        .payment-summary {
            width: 50%;
            float: right;
            margin-top: 20px;
        }
        .payment-summary table {
            width: 100%;
        }
        .payment-summary td {
            padding: 8px;
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
            margin-top: 10px;
        }
        .footer-note {
            margin-top: 20px;
            padding: 10px;
            background-color: #ffcccc;
            font-size: 10px;
            color: #cc0000;
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
        <h1>INVOICE</h1>
        <p><strong>{{ $dmcCompanyName }}</strong></p>
        <p>Invoice Number: <strong>{{ $invoice->invoice_number ?? 'DRAFT' }}</strong></p>
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

    <!-- Invoice Details -->
    <table class="invoice-info" style="width: 50%; float: right;">
        <tr>
            <td><strong>Invoice Details:</strong></td>
        </tr>
        <tr>
            <td>Postal / Pin: {{ $clientDetails['postal_code'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Invoice Date: {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('jS M Y') : '' }}</td>
        </tr>
        <tr>
            <td>Due Date: {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('jS M Y') : '' }}</td>
        </tr>
        <tr>
            <td>Invoice Sent by: {{ $invoice->sent_by ?? '' }}</td>
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

    <!-- Payment Summary -->
    @php
        $tour = $invoice->tour;
        $tourStatus = $tour->tour_status ?? '';
        $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
        $shouldShowTax = in_array($tourStatus, $statusesWithTax);
        
        $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
        $taxBreakdown = $notes['tax_breakdown'] ?? [];
        $gstAmount = $invoice->gst_amount ?? 0;
        $serviceCharge = $invoice->service_charge ?? 0;
        $touristTax = $invoice->tourist_tax ?? 0;
        $paymentReceived = $invoice->payment_received ?? 0;
        $outstandingBalance = $invoice->outstanding_balance ?? 0;
        $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
        $baseAmount = $notes['base_amount'] ?? ($invoice->getNegotiatedAmount() ?? ($invoice->total_amount ?? 0));
        $finalPrice = $baseAmount + $gstAmount;
    @endphp
    @if($shouldShowTax)
    <div class="payment-summary">
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td><strong>Payment Received</strong></td>
                <td>{{ $invoice->base_currency ?? 'SGD' }}</td>
                <td class="text-right">{{ number_format(round($paymentReceived), 2) }}</td>
            </tr>
            <tr>
                <td><strong>Outstanding Balance</strong></td>
                <td>{{ $invoice->base_currency ?? 'SGD' }}</td>
                <td class="text-right">{{ number_format(round($outstandingBalance), 2) }}</td>
            </tr>
            @if($gstAmount > 0)
            <tr>
                <td><strong>Total Vat / GST Tax</strong></td>
                <td>{{ $invoice->base_currency ?? 'SGD' }}</td>
                <td class="text-right">{{ number_format(round($gstAmount), 2) }}</td>
            </tr>
            @endif
            @if($serviceCharge > 0)
            <tr>
                <td><strong>Total Service Charge</strong></td>
                <td>{{ $invoice->base_currency ?? 'SGD' }}</td>
                <td class="text-right">{{ number_format(round($serviceCharge), 2) }}</td>
            </tr>
            @endif
            @if($touristTax > 0)
            <tr>
                <td><strong>Total Tourist Tax</strong></td>
                <td>{{ $invoice->base_currency ?? 'SGD' }}</td>
                <td class="text-right">{{ number_format(round($touristTax), 2) }}</td>
            </tr>
            @endif
            <tr style="border-top: 2px solid #000;">
                <td><strong>Total</strong></td>
                <td>{{ $invoice->base_currency ?? 'SGD' }}</td>
                <td class="text-right"><strong>{{ number_format(round($finalPrice), 2) }}</strong></td>
            </tr>
        </table>
    </div>
    @endif

    <div style="clear: both;"></div>

    <!-- Payment Terms -->
    @php
        $paymentTerms = $invoice->payment_terms ?? [];
    @endphp
    @if(!empty($paymentTerms))
    <div class="payment-terms">
        <strong>Payment Terms:</strong>
        <ol style="margin-left: 20px; margin-top: 5px;">
            @foreach($paymentTerms as $term)
            <li>{{ $term }}</li>
            @endforeach
        </ol>
    </div>
    @else
    <div class="payment-terms">
        <strong>Payment Terms:</strong>
        <ol style="margin-left: 20px; margin-top: 5px;">
            <li>Please pay the amount before the payment due date to avoid auto release of booking. Bank details are mentioned below.</li>
            <li>Payment/Remittance to be made in the currency as stated in the invoice.</li>
            <li>Bank charges to be borne by remitter. Please ensure that {{ $invoice->dmc->company_name ?? 'DMC' }} receive full payment as per Invoice.</li>
            <li>To ensure prompt credit, please send payment details along with remittance advice at dmc email.</li>
            <li>Interest @ 18% will be charged on all overdues.</li>
        </ol>
    </div>
    @endif

    <!-- Bank Details -->
    @php
        $bankDetails = $invoice->bank_details ?? [];
    @endphp
    @if(!empty($bankDetails))
    <div class="bank-details">
        <strong>Bank Details:</strong>
        <table style="margin-top: 10px; background-color: white;">
            <tr>
                <td>Account Name:</td>
                <td>{{ $bankDetails['account_name'] ?? '' }}</td>
            </tr>
            <tr>
                <td>Account Number:</td>
                <td>{{ $bankDetails['account_number'] ?? '' }}</td>
            </tr>
            <tr>
                <td>Bank Address:</td>
                <td>{{ $bankDetails['bank_address'] ?? '' }}</td>
            </tr>
            @if(isset($bankDetails['ifsc']))
            <tr>
                <td>IFSC (For India only):</td>
                <td>{{ $bankDetails['ifsc'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['swift_bic_iban']))
            <tr>
                <td>SWIFT/BIC/IBAN Code (as applicable for international, Europe transfers):</td>
                <td>{{ $bankDetails['swift_bic_iban'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['bank_code']))
            <tr>
                <td>Bank Code (For Singapore):</td>
                <td>{{ $bankDetails['bank_code'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['branch_code']))
            <tr>
                <td>Branch Code (For Singapore):</td>
                <td>{{ $bankDetails['branch_code'] }}</td>
            </tr>
            @endif
            @if(isset($bankDetails['aba_routing']))
            <tr>
                <td>ABA/Routing Number (For USA only):</td>
                <td>{{ $bankDetails['aba_routing'] }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <!-- Footer Note -->
    <div class="footer-note">
        <strong>*The base currency applied is the destination's local currency. Any alternate currency displayed is for reference purposes only and is subject to exchange rate fluctuations at the time of payment.</strong>
    </div>

</body>
</html>

