@extends('layouts.layout')

@section('content')
@php
use Illuminate\Support\Facades\Crypt;
@endphp
<style>
    #currency {
        height: 38px;
        min-height: 38px;
        line-height: 1.2;
    }
    .invoice-preview-toolbar {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.75rem 1rem;
        padding: 0.5rem 0.875rem;
        background: var(--bs-gray-50, #f8f9fa);
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 0.375rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .invoice-preview-toolbar .toolbar-segment {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        flex-shrink: 0;
        white-space: nowrap;
    }
    .invoice-preview-toolbar .toolbar-divider {
        width: 1px;
        height: 1.375rem;
        background: var(--bs-border-color, #dee2e6);
        flex-shrink: 0;
        align-self: center;
    }
    .invoice-preview-toolbar .btn-group .btn {
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
        padding-left: 0.65rem;
        padding-right: 0.65rem;
        font-size: 0.8125rem;
    }
    .invoice-preview-toolbar .toolbar-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--bs-secondary-color, #697a8d);
        margin: 0;
        margin-right: 0.125rem;
    }
    .invoice-preview-actions .btn {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding-left: 1rem;
        padding-right: 1rem;
        font-weight: 500;
    }
</style>
<div class="container-fluid">
    <div class="row mb-2 align-items-start">
        <div class="col-md-6">
            <h4 class="mb-1">
                @if($mode === 'price-only')
                    {{ $invoice->invoice_type === 'proforma' ? 'Proforma' : 'Invoice' }} Price Breakup Preview
                @else
                    {{ $invoice->invoice_type === 'proforma' ? 'Proforma' : 'Tax' }} Invoice Preview
                @endif
            </h4>
            <p class="text-muted small mb-0">
                @if($invoice->invoice_type === 'proforma')
                    Proforma #{{ $invoice->proforma_number ?? 'N/A' }}
                @else
                    Invoice #{{ $invoice->invoice_number ?? 'N/A' }}
                @endif
                &mdash; Booking ID: {{ $invoice->tour->display_id ?? $invoice->tour_id }}
            </p>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <form method="GET" action="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id)]) }}" class="invoice-preview-actions d-flex flex-wrap align-items-center justify-content-md-end gap-2 gap-md-3">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="logo_type" value="{{ $logoType ?? 'dmc' }}">
                <input type="hidden" name="format" value="{{ $format ?? 'standard' }}">
                <div class="d-flex align-items-center gap-2">
                    <label for="currency" class="mb-0 fw-semibold text-nowrap">Currency</label>
                    <select name="currency" id="currency" class="form-select form-select-sm" style="max-width: 120px; min-width: 100px;" onchange="this.form.submit()">
                        @foreach($availableCurrencies as $currency)
                            <option value="{{ $currency }}" {{ $currency === $selectedCurrency ? 'selected' : '' }}>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('invoices.pdf', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc', 'format' => $format ?? 'standard']) }}"
                       class="btn btn-primary shadow-sm">
                        <i class="ri-download-line me-1"></i> Download PDF
                    </a>
                    <a href="{{ route('invoices.show', Crypt::encrypt($invoice->invoice_id)) }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="invoice-preview-toolbar" role="toolbar" aria-label="Invoice preview options">
                @if($hasAgency ?? false)
                <div class="toolbar-segment">
                    <span class="toolbar-label">Company</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Company branding">
                        <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => 'dmc', 'format' => $format ?? 'standard']) }}"
                           class="btn {{ ($logoType ?? 'dmc') === 'dmc' ? 'btn-success' : 'btn-outline-secondary' }}">DMC</a>
                        <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => 'agency', 'format' => $format ?? 'standard']) }}"
                           class="btn {{ ($logoType ?? 'dmc') === 'agency' ? 'btn-success' : 'btn-outline-secondary' }}">Agency</a>
                    </div>
                </div>
                <span class="toolbar-divider" aria-hidden="true"></span>
                @endif
                <div class="toolbar-segment">
                    <span class="toolbar-label">Layout</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Invoice layout">
                        <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc', 'format' => 'standard']) }}"
                           class="btn {{ ($format ?? 'standard') === 'standard' ? 'btn-dark' : 'btn-outline-secondary' }}">Standard</a>
                        <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc', 'format' => 'alternate']) }}"
                           class="btn {{ ($format ?? 'standard') === 'alternate' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}"
                           title="Travel-agent style (SI / Particulars / Amount)">Lite</a>
                    </div>
                </div>
                <span class="toolbar-divider" aria-hidden="true"></span>
                <div class="toolbar-segment">
                    <span class="toolbar-label">Invoice View</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Full invoice or price breakup">
                        <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => 'full', 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc', 'format' => $format ?? 'standard']) }}"
                           class="btn {{ $mode === 'full' ? 'btn-primary' : 'btn-outline-secondary' }}">Packaged</a>
                        <a href="{{ route('invoices.preview', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => 'price-only', 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc', 'format' => $format ?? 'standard']) }}"
                           class="btn {{ $mode === 'price-only' ? 'btn-info' : 'btn-outline-secondary' }}">Price breakup</a>
                    </div>
                </div>
            </div>
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
                        src="{{ route('invoices.pdf', ['invoiceId' => Crypt::encrypt($invoice->invoice_id), 'mode' => $mode, 'currency' => $selectedCurrency, 'logo_type' => $logoType ?? 'dmc', 'format' => $format ?? 'standard', 'preview' => 1]) }}"
                        style="width: 100%; height: 900px; border: none;"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
