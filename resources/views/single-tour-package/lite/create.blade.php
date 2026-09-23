{{-- === STP LITE: create.blade.php ===
     Create form — backups: create.blade.php / create.backup.blade.php (not routed)
     Depends: SingleTourPackageController@create + public/js|css/single-tour-package/lite/*
     === --}}
@extends('layouts.layout')

@section('title', 'Create Single Tour Package')

@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="{{ asset('css/single-tour-package/lite.css') }}?v={{ filemtime(public_path('css/single-tour-package/lite.css')) }}">
@endpush

@section('content')
<div class="content-wrapper stp-lite-page" id="stpLiteRoot">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="stp-lite-hero d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h1>Create Single Tour Package</h1>
                <p>Configure tour, city stays, and services — then save.</p>
            </div>
            <span class="stp-lite-badge"><i class="ri-flashlight-line"></i> Create</span>
        </div>

        <form id="singleTourPackageForm" method="POST" action="{{ route('single-tour-package.store') }}">
            @csrf
            <input type="hidden" name="dmc_id" value="{{ (int) ($userDmcId ?? 0) }}">
            <input type="hidden" id="hotel_data" name="hotel_data" value="[]">
            <input type="hidden" id="entry_port_data" name="entry_port_data" value="[]">
            <input type="hidden" id="exit_port_data" name="exit_port_data" value="[]">
            <input type="hidden" id="transport_data" name="transport_data" value="[]">
            <input type="hidden" id="attraction_data" name="attraction_data" value="[]">
            <input type="hidden" id="guide_data" name="guide_data" value="[]">
            <input type="hidden" id="restaurant_data" name="restaurant_data" value="[]">
            <input type="hidden" id="miscellaneous_data" name="miscellaneous_data" value="[]">

            @include('single-tour-package.lite.partials.01-config-toggles')
            @include('single-tour-package.lite.partials.02-tour-details')
            @include('single-tour-package.lite.partials.04-multi-country-planner')
            @include('single-tour-package.lite.partials.06-lead-guests')
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCLzISM9kkNCKKmQs7BcpSll4emFw1yicw&libraries=places"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

@include('single-tour-package.lite.scripts.bootstrap-config')

<script src="{{ asset('js/single-tour-package/lite/input-sanitize.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/input-sanitize.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/geo.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/geo.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/maps-autocomplete.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/maps-autocomplete.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/guest-caps.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/guest-caps.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/tour-type.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/tour-type.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/country-mode.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/country-mode.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/tour-details.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/tour-details.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/accordion-manager.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/accordion-manager.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/hotel.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/hotel.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/transport-shared.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/transport-shared.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/arrival.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/arrival.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/departure.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/departure.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/transport.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/transport.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/attraction.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/attraction.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/guide.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/guide.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/restaurant.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/restaurant.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/miscellaneous.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/miscellaneous.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/country-segments.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/country-segments.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/guests.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/guests.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/city-markup.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/city-markup.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/submit.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/submit.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/main.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/main.js')) }}"></script>
@endsection
{{-- === END create.blade.php === --}}
