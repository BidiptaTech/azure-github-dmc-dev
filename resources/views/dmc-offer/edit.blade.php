@extends('layouts.layout')

@section('title', 'Edit DMC Offer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings / DMC Offer /</span> Edit
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
                <h5 class="mb-0">Edit Offer</h5>
                <small class="text-muted">Offer ID: {{ $offer->dmc_offer_id ?? $offer->id }} · Currency always SGD</small>
            </div>
            <a href="{{ route('dmc-offer.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
        <div class="card-body">
            <form action="{{ route('dmc-offer.update', $offer) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">DMC</label>
                    <select name="dmc_id" class="form-select" required>
                        <option value="">Select DMC</option>
                        @foreach($dmcs as $dmc)
                            @php
                                $label = $dmc->company_name ?: $dmc->name ?: ('DMC #' . $dmc->userId);
                                if (!empty($dmc->country)) {
                                    $label .= ' (' . $dmc->country . ')';
                                }
                                $selectedId = old('dmc_id', $offer->dmc_id);
                            @endphp
                            <option value="{{ $dmc->userId }}" @selected((string) $selectedId === (string) $dmc->userId)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ old('start_date', optional($offer->start_date)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control"
                           value="{{ old('end_date', optional($offer->end_date)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Adult Price</label>
                    <input type="number" step="0.01" min="0" name="adult_price" class="form-control"
                           value="{{ old('adult_price', $offer->adult_price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Child Price</label>
                    <input type="number" step="0.01" min="0" name="child_price" class="form-control"
                           value="{{ old('child_price', $offer->child_price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Infant Price</label>
                    <input type="number" step="0.01" min="0" name="infant_price" class="form-control"
                           value="{{ old('infant_price', $offer->infant_price) }}" required>
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
