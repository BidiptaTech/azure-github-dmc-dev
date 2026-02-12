@extends('layouts.layout')

@section('content')
<style>
    #currency {
    height: 38px;
    padding: 2px 8px !important;
    line-height: 1.2;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    padding-right: 18px; /* space for arrow */
    background: url("data:image/svg+xml;utf8,<svg fill='black' height='10' viewBox='0 0 20 20' width='10' xmlns='http://www.w3.org/2000/svg'><path d='M5 7l5 5 5-5z'/></svg>")
        no-repeat right 6px center;
    background-size: 10px;
 }
</style>
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-6">
                <h4>Tour Quotation Preview</h4>
                <p class="text-muted mb-0">
                    Tour ID: {{ $tour->display_id ?? $tour->tour_id }} &mdash;
                    Destination: {{ $tour->destination ?? $tour->tour_destination ?? 'N/A' }}
                </p>
            </div>
            <div class="col-md-6 text-md-right mt-3 mt-md-0">
                <form method="GET" action="{{ route('tour.itinerary.preview', ['encryptedTourId' => Crypt::encrypt($tour->tour_id)]) }}" class="d-flex flex-nowrap align-items-center justify-content-md-end">
                    <label for="currency" class="mb-0 mr-2 font-weight-bold">Currency:</label>
                    <select name="currency" id="currency" class="form-control form-control-sm mr-2" style="max-width: 120px; height: 38px; line-height:1.2;" onchange="this.form.submit()">
                        @foreach($availableCurrencies as $currency)
                            <option value="{{ $currency }}" {{ $currency === $selectedCurrency ? 'selected' : '' }}>
                                {{ $currency }}
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('tour.itinerary.pdf', ['tourId' => $tour->tour_id, 'currency' => $selectedCurrency]) }}"
                       class="btn btn-primary flex-shrink-0">
                        Download Quotation
                    </a>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="padding: 0;">
                        <iframe
                            src="{{ route('tour.itinerary.pdf', ['tourId' => $tour->tour_id, 'currency' => $selectedCurrency, 'preview' => 1]) }}"
                            style="width: 100%; height: 900px; border: none;"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

