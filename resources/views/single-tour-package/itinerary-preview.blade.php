@extends('layouts.layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-8">
                <h4>Tour Quotation Preview</h4>
                <p class="text-muted mb-0">
                    Tour ID: {{ $tour->display_id ?? $tour->tour_id }} &mdash;
                    Destination: {{ $tour->destination ?? $tour->tour_destination ?? 'N/A' }}
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <form method="GET" action="{{ route('tour.itinerary.preview', ['tourId' => $tour->tour_id]) }}" class="form-inline justify-content-md-end">
                    <label for="currency" class="mr-2 font-weight-bold">Currency:</label>
                    <select name="currency" id="currency" class="form-control mr-2" onchange="this.form.submit()">
                        @foreach($availableCurrencies as $currency)
                            <option value="{{ $currency }}" {{ $currency === $selectedCurrency ? 'selected' : '' }}>
                                {{ $currency }}
                            </option>
                        @endforeach
                    </select>

                    <a href="{{ route('tour.itinerary.pdf', ['tourId' => $tour->tour_id, 'currency' => $selectedCurrency]) }}"
                       class="btn btn-primary">
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

