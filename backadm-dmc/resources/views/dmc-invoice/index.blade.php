@extends('layouts.layout')

@section('title', 'DMC Invoice')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings /</span> DMC Invoice
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$invoice)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Add Travclicks Invoice Prices</h5>
            <small class="text-muted">Only one price set is allowed. Currency is always SGD (Travclicks level).</small>
        </div>
        <div class="card-body">
            <form action="{{ route('dmc-invoice.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Adult Price</label>
                    <input type="number" step="0.01" min="0" name="adult_price" class="form-control" value="{{ old('adult_price') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Child Price</label>
                    <input type="number" step="0.01" min="0" name="child_price" class="form-control" value="{{ old('child_price') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Infant Price</label>
                    <input type="number" step="0.01" min="0" name="infant_price" class="form-control" value="{{ old('infant_price') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <input type="text" class="form-control" value="SGD" readonly>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Invoice Prices</h5>
            <small class="text-muted">Travclicks stores a single adult / child / infant price set in SGD.</small>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Adult</th>
                            <th>Child</th>
                            <th>Infant</th>
                            <th>Currency</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($invoice)
                            <tr>
                                <td>{{ $invoice->dmc_invoice_id ?? $invoice->id }}</td>
                                <td>{{ number_format((float) $invoice->adult_price, 2) }}</td>
                                <td>{{ number_format((float) $invoice->child_price, 2) }}</td>
                                <td>{{ number_format((float) $invoice->infant_price, 2) }}</td>
                                <td>{{ $invoice->currency }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('dmc-invoice.edit', $invoice) }}"
                                           class="btn btn-primary btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                           style="width: 28px; height: 28px; padding: 0;" title="Edit">
                                            <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                        </a>
                                        <form action="{{ route('dmc-invoice.destroy', $invoice) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                                    style="width: 28px; height: 28px; padding: 0;"
                                                    title="Delete"
                                                    onclick="return confirm('Delete invoice prices? You can add them again after delete.')">
                                                <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="6" class="text-center">No invoice prices found. Use the form above to add once.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
