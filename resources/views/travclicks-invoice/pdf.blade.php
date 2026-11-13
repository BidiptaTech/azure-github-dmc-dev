<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Travclicks Invoice - {{ $data['invoice_number'] }}</title>
    <style type="text/css">
        /* Premium Travclicks DMC Offer-style template — DomPDF safe */
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1a2b4b;
            margin: 0;
            padding: 14px 16px;
            background: #ffffff;
        }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }

        .brand-row td { border: none; padding: 0 0 8px 0; vertical-align: middle; }
        .brand-left { width: 22%; text-align: left; }
        .brand-center { width: 56%; text-align: center; }
        .brand-right { width: 22%; text-align: right; }
        .logo-tc { max-width: 120px; max-height: 42px; }
        .logo-dmc { max-width: 110px; max-height: 42px; }
        .brand-kicker {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1.2px;
            color: #1a2b4b;
            text-transform: uppercase;
            margin: 0 0 2px 0;
        }
        .brand-sub {
            font-size: 8px;
            color: #64748b;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin: 0;
        }

        .hero {
            width: 100%;
            background: #1a2b4b;
            color: #ffffff;
            margin: 0 0 10px 0;
        }
        .hero td {
            border: none;
            padding: 14px 16px;
            vertical-align: middle;
            color: #ffffff;
        }
        .hero-left { width: 58%; }
        .hero-right {
            width: 42%;
            border-left: 1px solid rgba(255,255,255,0.25);
            padding-left: 16px;
        }
        .hero-title {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
            color: #ffffff;
        }
        .hero-caption {
            font-size: 9px;
            color: #cbd5e1;
            margin: 0;
        }
        .hero-meta-label {
            font-size: 7.5px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #94a3b8;
            margin: 0 0 1px 0;
        }
        .hero-meta-value {
            font-size: 10px;
            font-weight: bold;
            color: #ffffff;
            margin: 0 0 7px 0;
        }

        .parties {
            width: 100%;
            margin: 0 0 12px 0;
            background: #f1f5f9;
            border: 1px solid #d1d5db;
        }
        .parties td {
            width: 50%;
            padding: 10px 12px;
            border: none;
            vertical-align: top;
        }
        .parties .divider {
            border-left: 1px solid #d1d5db;
        }
        .party-label {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #64748b;
            margin: 0 0 5px 0;
        }
        .party-name {
            font-size: 11px;
            font-weight: bold;
            color: #1a2b4b;
            margin: 0 0 3px 0;
        }
        .party-line {
            font-size: 9px;
            color: #334155;
            line-height: 1.45;
            margin: 0;
        }

        .section-head {
            margin: 2px 0 6px 0;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #1a2b4b;
            margin: 0 0 2px 0;
        }
        .section-note {
            font-size: 8px;
            color: #64748b;
            margin: 0 0 6px 0;
        }

        .breakdown {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 12px 0;
        }
        .breakdown th {
            background: #1a2b4b;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            text-align: center;
            padding: 7px 4px;
            border: 1px solid #1a2b4b;
        }
        .breakdown td {
            font-size: 8.5px;
            color: #1a2b4b;
            padding: 6px 4px;
            border: 1px solid #d1d5db;
            vertical-align: middle;
        }
        .breakdown .ctr { text-align: center; }
        .breakdown .num { text-align: right; white-space: nowrap; }
        .row-alt { background: #f8fafc; }
        .tour-id { font-weight: bold; font-size: 9px; }
        .tour-sub {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 2px;
        }
        .offer-tag {
            display: inline-block;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 7px;
            font-weight: bold;
            padding: 1px 4px;
            text-transform: uppercase;
            margin-left: 3px;
        }
        .saving { color: #10b981; font-weight: bold; }
        .strong { font-weight: bold; }

        .summary {
            width: 100%;
            border: 1px solid #d1d5db;
            background: #f1f5f9;
            margin: 0 0 12px 0;
        }
        .summary td {
            width: 33.33%;
            padding: 10px 12px;
            border: none;
            vertical-align: top;
        }
        .summary .mid {
            border-left: 1px solid #d1d5db;
            border-right: 1px solid #d1d5db;
            text-align: center;
        }
        .summary .right { border-left: none; }
        .sum-label {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #64748b;
            margin: 0 0 6px 0;
        }
        .sum-line {
            font-size: 9px;
            color: #334155;
            margin: 0 0 3px 0;
        }
        .sum-line strong { color: #1a2b4b; }
        .total-card {
            background: #ffffff;
            border: 1px solid #d1d5db;
            padding: 10px 8px;
        }
        .total-kicker {
            font-size: 8px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #64748b;
            margin: 0 0 4px 0;
        }
        .total-value {
            font-size: 22px;
            font-weight: bold;
            color: #10b981;
            margin: 0;
        }
        .total-sub {
            font-size: 8px;
            color: #64748b;
            margin: 4px 0 0 0;
        }
        .cond-text {
            font-size: 8px;
            color: #475569;
            line-height: 1.45;
            margin: 0;
        }

        .fx-box {
            width: 100%;
            border: 1px solid #d1d5db;
            margin: 0 0 12px 0;
        }
        .fx-box th {
            background: #1a2b4b;
            color: #ffffff;
            font-size: 8px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            text-align: left;
            padding: 6px 10px;
            border: none;
        }
        .fx-box td {
            padding: 5px 10px;
            font-size: 9px;
            border-top: 1px solid #e2e8f0;
            color: #1a2b4b;
        }
        .fx-box .amt { text-align: right; font-weight: bold; }

        .signoff {
            width: 100%;
            margin: 8px 0 10px 0;
        }
        .signoff td {
            border: none;
            padding: 0;
            width: 50%;
            font-size: 9px;
            color: #475569;
        }
        .sign-line {
            border-bottom: 1px solid #94a3b8;
            height: 22px;
            margin: 0 18px 4px 0;
        }
        .page-foot {
            width: 100%;
            border-top: 1px solid #d1d5db;
            margin-top: 6px;
            padding-top: 6px;
        }
        .page-foot td {
            border: none;
            font-size: 7.5px;
            color: #94a3b8;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .empty {
            text-align: center;
            padding: 16px;
            border: 1px dashed #d1d5db;
            color: #64748b;
            margin: 0 0 12px 0;
        }
    </style>
</head>
<body>
@php
    $dmc = $data['dmc'];
    $tc = $data['travclicks'];
    $totals = $data['totals'];
    $currency = $data['currency'];
    $base = $data['base_prices'];
    $fmt = fn ($n) => number_format((float) $n, 2);
    $dmcName = $dmc->company_name ?: $dmc->name;
    $dmcAddress = $dmc->company_address ?? $dmc->address ?? null;
    $dmcPhone = $dmc->company_phone ?? $dmc->phone ?? $dmc->mobile ?? null;
    $dmcEmail = $dmc->company_email ?? $dmc->email ?? null;
    $savingsPct = ($totals['gross'] ?? 0) > 0
        ? round((((float) $totals['discount']) / (float) $totals['gross']) * 100, 1)
        : 0;
    $offerRef = $data['invoice_number'];
@endphp

{{-- Top brand strip: Travclicks | proposal label | DMC --}}
<table class="brand-row">
    <tr>
        <td class="brand-left">
            @if(!empty($travclicksLogo))
                <img src="{{ $travclicksLogo }}" class="logo-tc" alt="Travclicks">
            @else
                <div class="brand-kicker">Travclicks</div>
            @endif
        </td>
        <td class="brand-center">
            <p class="brand-kicker">Business Pricing Proposal</p>
            <p class="brand-sub">DMC Partnership — Monthly Invoice</p>
        </td>
        <td class="brand-right">
            @if(!empty($dmcLogo))
                <img src="{{ $dmcLogo }}" class="logo-dmc" alt="DMC">
            @endif
        </td>
    </tr>
</table>

{{-- Navy hero --}}
<table class="hero">
    <tr>
        <td class="hero-left">
            <div class="hero-title">Travclicks Monthly Invoice</div>
            <p class="hero-caption">Exclusive tour charges prepared for your DMC business.</p>
        </td>
        <td class="hero-right">
            <p class="hero-meta-label">Invoice Reference</p>
            <p class="hero-meta-value">{{ $offerRef }}</p>
            <p class="hero-meta-label">Invoice Period</p>
            <p class="hero-meta-value">{{ $data['period_label'] }}</p>
            <p class="hero-meta-label">Currency</p>
            <p class="hero-meta-value" style="margin-bottom:0;">SGD</p>
        </td>
    </tr>
</table>

{{-- Prepared for / Issued by --}}
<table class="parties">
    <tr>
        <td>
            <p class="party-label">Invoice Prepared For</p>
            <p class="party-name">{{ $dmcName }}</p>
            @if(!empty($dmcAddress))
                <p class="party-line">{{ $dmcAddress }}</p>
            @endif
            @if(!empty($dmcPhone))
                <p class="party-line">Tel: {{ $dmcPhone }}</p>
            @endif
            @if(!empty($dmcEmail))
                <p class="party-line">Email: {{ $dmcEmail }}</p>
            @endif
        </td>
        <td class="divider">
            <p class="party-label">Invoice Issued By</p>
            <p class="party-name">{{ $tc['name'] }}</p>
            @if(!empty($tc['address']))
                <p class="party-line">{{ $tc['address'] }}</p>
            @endif
            @if(!empty($tc['phone']))
                <p class="party-line">Tel: {{ $tc['phone'] }}</p>
            @endif
            @if(!empty($tc['email']))
                <p class="party-line">Email: {{ $tc['email'] }}</p>
            @endif
            <p class="party-line" style="margin-top:4px;">
                Default rates: A {{ $fmt($base->adult_price) }} /
                C {{ $fmt($base->child_price) }} /
                I {{ $fmt($base->infant_price) }} SGD
            </p>
            <p class="party-line">Generated: {{ $data['generated_at'] }} · Tours: {{ $totals['tour_count'] }} Actual</p>
        </td>
    </tr>
</table>

<div class="section-head">
    <p class="section-title">Tour-Wise Invoice Breakdown</p>
    <p class="section-note">
        Standard Travclicks rates apply in SGD. Where a DMC Offer covers the tour start date, offer rates and DMC savings are shown.
    </p>
</div>

@if(empty($data['lines']))
    <div class="empty">No Actual tours were found for {{ $data['period_label'] }}.</div>
@else
    <table class="breakdown">
        <thead>
            <tr>
                <th style="width:4%;">No.</th>
                <th style="width:16%;">Tour / DMC Order</th>
                <th style="width:13%;">Tour Dates</th>
                <th style="width:8%;">Pax A/C/I</th>
                <th style="width:12%;">Standard Rate A/C/I</th>
                <th style="width:12%;">Offer Rate A/C/I</th>
                <th style="width:10%;">Standard Gross</th>
                <th style="width:10%;">Offer / Line Total</th>
                <th style="width:10%;">DMC Saving</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['lines'] as $i => $line)
                <tr @if($i % 2 === 1) class="row-alt" @endif>
                    <td class="ctr">{{ $i + 1 }}</td>
                    <td>
                        <div class="tour-id">
                            {{ $line['display_id'] }}
                            @if($line['has_offer'])
                                <span class="offer-tag">Offer</span>
                            @endif
                        </div>
                        @if(!empty($line['guest']) && is_string($line['guest']))
                            <div class="tour-sub">{{ $line['has_offer'] ? 'Special offer — ' : '' }}{{ \Illuminate\Support\Str::limit($line['guest'], 24) }}</div>
                        @elseif($line['has_offer'])
                            <div class="tour-sub">Special offer applied</div>
                        @endif
                    </td>
                    <td class="ctr">
                        {{ $line['start_date'] }}<br>
                        <span class="tour-sub">to {{ $line['end_date'] }}</span>
                    </td>
                    <td class="ctr">{{ $line['adult'] }} / {{ $line['child'] }} / {{ $line['infant'] }}</td>
                    <td class="ctr">
                        {{ $fmt($line['default_adult']) }} / {{ $fmt($line['default_child']) }} / {{ $fmt($line['default_infant']) }}
                    </td>
                    <td class="ctr strong">
                        @if($line['has_offer'])
                            {{ $fmt($line['rate_adult']) }} / {{ $fmt($line['rate_child']) }} / {{ $fmt($line['rate_infant']) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="num">{{ $fmt($line['gross']) }}</td>
                    <td class="num strong">{{ $fmt($line['net']) }}</td>
                    <td class="num">
                        @if($line['discount'] > 0)
                            <span class="saving">{{ $fmt($line['discount']) }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Summary strip --}}
<table class="summary">
    <tr>
        <td>
            <p class="sum-label">Price Summary</p>
            <p class="sum-line">Standard tour value: <strong>SGD {{ $fmt($totals['gross']) }}</strong></p>
            <p class="sum-line">Offer discount: <strong>SGD {{ $fmt($totals['discount']) }}</strong></p>
            <p class="sum-line">Savings %: <strong>{{ number_format($savingsPct, 1) }}%</strong></p>
            @if($totals['has_tax'])
                <p class="sum-line">Tax total: <strong>SGD {{ $fmt($totals['tax_total']) }}</strong></p>
            @endif
            <p class="sum-line">Net before tax: <strong>SGD {{ $fmt($totals['net']) }}</strong></p>
        </td>
        <td class="mid">
            <p class="sum-label">Total Invoice Value</p>
            <div class="total-card">
                <p class="total-kicker">Grand Total</p>
                <p class="total-value">SGD {{ $fmt($totals['grand_total']) }}</p>
                <p class="total-sub">{{ $totals['tour_count'] }} Actual tour(s) · {{ $data['period_label'] }}</p>
            </div>
        </td>
        <td>
            <p class="sum-label">Invoice Conditions</p>
            <p class="cond-text">
                Platform fees are billed in SGD. Offer rates apply only when the tour start date falls inside an active DMC Offer window.
                Amounts exclude third-party supplier costs. Please settle within the agreed commercial terms.
            </p>
        </td>
    </tr>
</table>

@if($currency['show_conversion'])
<table class="fx-box">
    <thead>
        <tr>
            <th colspan="2">Currency Conversion (SGD → {{ $currency['dmc'] }})</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total (SGD)</td>
            <td class="amt">{{ $fmt($totals['grand_total']) }}</td>
        </tr>
        <tr>
            <td>Base Rate</td>
            <td class="amt">{{ $currency['exchange_rate'] !== null ? $fmt($currency['exchange_rate']) : 'N/A' }}</td>
        </tr>
        <tr>
            <td>Commission ({{ $fmt($currency['commission_percent'] ?? 0) }}%)</td>
            <td class="amt">{{ $fmt($currency['commission_on_rate'] ?? 0) }}</td>
        </tr>
        <tr>
            <td>Rate + Commission</td>
            <td class="amt">{{ $currency['effective_rate'] !== null ? $fmt($currency['effective_rate']) : 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Amount ({{ $currency['dmc'] }})</strong></td>
            <td class="amt"><strong>{{ $currency['dmc_amount'] !== null ? $fmt($currency['dmc_amount']) : 'N/A' }}</strong></td>
        </tr>
    </tbody>
</table>
@endif

<table class="signoff">
    <tr>
        <td>
            <div class="sign-line"></div>
            DMC acceptance / authorized signatory
        </td>
        <td>
            <div class="sign-line"></div>
            Date
        </td>
    </tr>
</table>

<table class="page-foot">
    <tr>
        <td style="text-align:left;">Travclicks | Business Pricing Proposal</td>
        <td style="text-align:right;">Confidential — Prepared for DMC</td>
    </tr>
</table>
</body>
</html>
