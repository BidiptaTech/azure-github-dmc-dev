@extends('layouts.layout')

@section('content')
@php
use Illuminate\Support\Facades\Crypt;
@endphp
<style>
    #currency {
        height: 38px;
        padding: 2px 8px !important;
        line-height: 1.2;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 18px;
        background: url("data:image/svg+xml;utf8,<svg fill='black' height='10' viewBox='0 0 20 20' width='10' xmlns='http://www.w3.org/2000/svg'><path d='M5 7l5 5 5-5z'/></svg>") no-repeat right 6px center;
        background-size: 10px;
    }
</style>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h4>
                @if($mode === 'price-only')
                    {{ $invoice->invoice_type === 'proforma' ? 'Proforma' : 'Invoice' }} Price Breakup Preview
                @else
                    {{ $invoice->invoice_type === 'proforma' ? 'Proforma' : 'Tax' }} Invoice Preview
                @endif
            </h4>
            <p class="text-muted mb-0">
                @if($invoice->invoice_type === 'proforma')
                    Proforma #{{ $invoice->proforma_number ?? 'N/A' }}
                @else
                    Invoice #{{ $invoice->invoice_number ?? 'N/A' }}
                @endif
                &mdash; Booking ID: {{ $invoice->tour->display_id ?? $invoice->tour_id }}
            </p>
            <div class="mt-2">
                <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => 'full', 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc']) }}" 
                   class="btn btn-sm {{ $mode === 'full' ? 'btn-primary' : 'btn-outline-secondary' }}">Full Invoice</a>
                <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => 'price-only', 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc']) }}" 
                   class="btn btn-sm {{ $mode === 'price-only' ? 'btn-info' : 'btn-outline-secondary' }}">Price Breakup</a>
                @if($hasAgency ?? false)
                <span class="ml-2 border-left pl-2" style="border-left: 1px solid #dee2e6;">
                    <span class="text-muted small mr-1">Logo:</span>
                    <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => 'dmc']) }}" 
                       class="btn btn-sm {{ ($logoType ?? 'dmc') === 'dmc' ? 'btn-success' : 'btn-outline-secondary' }}">DMC</a>
                    <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => 'agency']) }}" 
                       class="btn btn-sm {{ ($logoType ?? 'dmc') === 'agency' ? 'btn-success' : 'btn-outline-secondary' }}">Agency</a>
                </span>
                @endif
            </div>
        </div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            <form method="GET" action="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id)]) }}" class="d-flex flex-nowrap align-items-center justify-content-md-end">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="logo_type" value="{{ $logoType ?? 'dmc' }}">
                <label for="currency" class="mb-0 mr-2 font-weight-bold">Currency:</label>
                <select name="currency" id="currency" class="form-control form-control-sm mr-2" style="max-width: 120px; height: 38px; line-height:1.2;" onchange="this.form.submit()">
                    @foreach($availableCurrencies as $currency)
                        <option value="{{ $currency }}" {{ $currency === $selectedCurrency ? 'selected' : '' }}>{{ $currency }}</option>
                    @endforeach
                </select>
                <a href="{{ route('invoices.pdf', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc']) }}"
                   class="btn btn-primary flex-shrink-0">
                    <i class="ri-download-line me-1"></i> Download PDF
                </a>
                <a href="{{ route('invoices.show', Crypt::encrypt($invoice->invoice_id)) }}" class="btn btn-outline-secondary ml-2 flex-shrink-0">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>
            </form>
        </div>
    </div>

    @if(($selectedCurrency ?? 'SGD') !== 'SGD' && !empty($currencyConversion ?? []))
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="ri-exchange-dollar-line me-2"></i>Currency Conversion (SGD + {{ $selectedCurrency }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($currencyConversion ?? [] as $curr => $amount)
                        <div class="col-md-4">
                            <strong>{{ $curr }}:</strong> {{ number_format(round($amount)) }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body" style="padding: 0;">
                    <iframe
                        src="{{ route('invoices.pdf', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc', 'preview' => 1]) }}"
                        style="width: 100%; height: 900px; border: none;"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
