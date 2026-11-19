@extends('layouts.layout')

@section('title', 'Edit DMC Invoice')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings / DMC Invoice /</span> Edit
    </h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Edit Invoice Prices</h5>
                <small class="text-muted">Invoice ID: {{ $invoice->dmc_invoice_id ?? $invoice->id }} · Currency always SGD</small>
            </div>
            <a href="{{ route('dmc-invoice.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
        <div class="card-body">
            <form action="{{ route('dmc-invoice.update', $invoice) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-3">
                    <label class="form-label">Adult Price</label>
                    <input type="number" step="0.01" min="0" name="adult_price" class="form-control"
                           value="{{ old('adult_price', $invoice->adult_price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Child Price</label>
                    <input type="number" step="0.01" min="0" name="child_price" class="form-control"
                           value="{{ old('child_price', $invoice->child_price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Infant Price</label>
                    <input type="number" step="0.01" min="0" name="infant_price" class="form-control"
                           value="{{ old('infant_price', $invoice->infant_price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <input type="text" class="form-control" value="SGD" readonly>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
