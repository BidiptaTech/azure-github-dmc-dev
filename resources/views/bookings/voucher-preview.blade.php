@extends('layouts.layout')

@section('content')
@php
    $voucherPreviewBase = route('bookings.confirmation-voucher.preview', ['tourId' => $encryptedTourId]);
    $voucherQuery = [
        'logo_type' => $logoType ?? 'dmc',
    ];
    $voucherPdfBase = route('bookings.confirmation-voucher', ['tourId' => $encryptedTourId]);
@endphp
<style>
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
            <h4 class="mb-1">Confirmation Voucher Preview</h4>
            <p class="text-muted small mb-0">
                Tour ID: {{ $tour->display_id ?? $tour->tour_id }} &mdash;
                Destination: {{ $tour->destination ?? $tour->tour_destination ?? 'N/A' }}
            </p>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <div class="invoice-preview-actions d-flex flex-wrap align-items-center justify-content-md-end gap-2 gap-md-3">
                <a
                    href="{{ $voucherPdfBase }}?logo_type={{ urlencode($logoType ?? 'dmc') }}"
                    class="btn btn-primary shadow-sm"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="ri-download-line me-1"></i> Download PDF
                </a>
                
            </div>
        </div>
    </div>

    @if($hasAgency ?? false)
    <div class="row mb-3">
        <div class="col-12">
            <div class="invoice-preview-toolbar" role="toolbar" aria-label="Voucher branding">
                <div class="toolbar-segment">
                    <span class="toolbar-label">Company</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Company branding">
                        <a href="{{ $voucherPreviewBase }}?{{ http_build_query(array_merge($voucherQuery, ['logo_type' => 'dmc'])) }}"
                           class="btn {{ ($logoType ?? 'dmc') === 'dmc' ? 'btn-success' : 'btn-outline-secondary' }}">DMC</a>
                        <a href="{{ $voucherPreviewBase }}?{{ http_build_query(array_merge($voucherQuery, ['logo_type' => 'agency'])) }}"
                           class="btn {{ ($logoType ?? 'dmc') === 'agency' ? 'btn-success' : 'btn-outline-secondary' }}">Agency</a>
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
                        id="confirmationVoucherIframe"
                        src="{{ $voucherPdfBase }}?preview=1&logo_type={{ urlencode($logoType ?? 'dmc') }}"
                        style="width: 100%; height: 900px; border: none;"
                        title="Confirmation voucher PDF preview"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
