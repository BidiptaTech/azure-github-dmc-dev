@extends('layouts.layout')

@section('title', 'DMC Offer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings /</span> DMC Offer
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

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Add DMC Offer</h5>
            <small class="text-muted">Only one offer per DMC. Choose a DMC without an offer yet, set date range and prices. Currency is always SGD (Travclicks).</small>
        </div>
        <div class="card-body">
            @if($dmcs->isEmpty())
                <div class="alert alert-info mb-0">
                    Every available DMC already has an offer. Edit an existing row in the list below, or delete one to add again.
                </div>
            @else
            <form action="{{ route('dmc-offer.store') }}" method="POST" class="row g-3">
                @csrf
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
                            @endphp
                            <option value="{{ $dmc->userId }}" @selected(old('dmc_id') == $dmc->userId)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                </div>
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
                        <i class="ri-add-line me-1"></i> Save Offer
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Offers List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Offer ID</th>
                            <th>DMC</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Adult</th>
                            <th>Child</th>
                            <th>Infant</th>
                            <th>Currency</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offers as $idx => $offer)
                            @php
                                $dmc = $offer->dmc;
                                $dmcLabel = $dmc
                                    ? (($dmc->company_name ?: $dmc->name) . (!empty($dmc->country) ? ' (' . $dmc->country . ')' : ''))
                                    : ('#' . $offer->dmc_id);
                            @endphp
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $offer->dmc_offer_id ?? $offer->id }}</td>
                                <td>{{ $dmcLabel }}</td>
                                <td>{{ optional($offer->start_date)->format('Y-m-d') }}</td>
                                <td>{{ optional($offer->end_date)->format('Y-m-d') }}</td>
                                <td>{{ number_format((float) $offer->adult_price, 2) }}</td>
                                <td>{{ number_format((float) $offer->child_price, 2) }}</td>
                                <td>{{ number_format((float) $offer->infant_price, 2) }}</td>
                                <td>{{ $offer->currency }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('dmc-offer.edit', $offer) }}"
                                           class="btn btn-primary btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                           style="width: 28px; height: 28px; padding: 0;" title="Edit">
                                            <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                        </a>
                                        <form action="{{ route('dmc-offer.destroy', $offer) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                                    style="width: 28px; height: 28px; padding: 0;"
                                                    title="Delete"
                                                    onclick="return confirm('Delete this offer?')">
                                                <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No offers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
