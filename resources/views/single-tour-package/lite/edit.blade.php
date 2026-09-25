{{-- === STP LITE: edit.blade.php ===
     Same UI as lite create — hydrate from tour + order JSON
     Access: /single-tour-package/{id}/edit
     Backups: editform.blade.php / editform.backup.blade.php (not routed)
     === --}}
@extends('layouts.layout')

@section('title', 'Edit Single Tour Package')

@php
    $tour = $tour ?? null;
    $startYmd = $tour && $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('Y-m-d') : '';
    $endYmd = $tour && $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('Y-m-d') : '';
    $datesDisplay = ($startYmd && $endYmd)
        ? \Carbon\Carbon::parse($startYmd)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($endYmd)->format('M d, Y')
        : '';
    $liteServices = $liteServices ?? [
        'hotel_data' => [],
        'entry_port_data' => [],
        'exit_port_data' => [],
        'transport_data' => [],
        'attraction_data' => [],
        'guide_data' => [],
        'restaurant_data' => [],
    ];
    $customer_info = $customer_info ?? [];

    $mainGuest = $tour->mainguest ?? null;
    if (is_string($mainGuest)) {
        $decoded = json_decode($mainGuest, true);
        $mainGuest = is_array($decoded) ? $decoded : [];
    }
    if (!is_array($mainGuest)) {
        $mainGuest = [];
    }
    // Prefer order/customer_info when mainguest empty
    if (empty(array_filter($mainGuest)) && !empty($customer_info)) {
        $mainGuest = [
            'salutation' => $customer_info['salutation'] ?? '',
            'full_name' => $customer_info['fullName'] ?? ($customer_info['full_name'] ?? ''),
            'email' => $customer_info['email'] ?? '',
            'phone' => $customer_info['phone'] ?? '',
            'country_code' => $customer_info['countryCode'] ?? ($customer_info['country_code'] ?? ''),
            'address1' => $customer_info['address1'] ?? '',
            'address2' => $customer_info['address2'] ?? '',
            'state' => $customer_info['state'] ?? '',
            'zip' => $customer_info['zip'] ?? '',
            'special_requests' => $customer_info['specialRequests'] ?? ($customer_info['special_requests'] ?? ''),
            'passport' => $customer_info['passport'] ?? ($customer_info['passport_no'] ?? ''),
            'passport_exp' => $customer_info['passport_exp'] ?? ($customer_info['passport_expiry'] ?? ''),
        ];
    }

    $additionalGuests = $tour->additionalguest ?? null;
    if (is_string($additionalGuests)) {
        $decodedAdd = json_decode($additionalGuests, true);
        $additionalGuests = is_array($decodedAdd) ? $decodedAdd : [];
    }
    if (!is_array($additionalGuests)) {
        $additionalGuests = [];
    }

    $editMale = (int) ($tour->male_count ?? $tour->male ?? 0);
    $editFemale = (int) ($tour->female_count ?? $tour->female ?? 0);
    $editAdults = (int) ($tour->adult ?? 0);
    if ($editAdults < 1) {
        $editAdults = max(1, $editMale + $editFemale);
    }
    $editAgencyId = (string) ($tour->agency_id ?? '');
    if ($editAgencyId === '' && !empty($tour->agent_id)) {
        $editAgencyId = (string) (optional(\App\Models\Agent::where('agent_id', $tour->agent_id)->first())->agency_id ?? '');
    }
    $tourStatusNorm = strtolower(trim((string) ($tour->tour_status ?? '')));
    $showAppPassword = in_array($tourStatusNorm, ['definite', 'actual'], true);
@endphp

@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="{{ asset('css/single-tour-package/lite.css') }}?v={{ filemtime(public_path('css/single-tour-package/lite.css')) }}">
@endpush

@section('content')
<div class="content-wrapper stp-lite-page" id="stpLiteRoot" data-mode="edit">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="stp-lite-hero d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h1 class="text-white">Edit Tour Package</h1>
                <p>
                    {{ $tour->display_id ?? ('#' . ($tour->tour_id ?? '')) }}
                    @if(!empty($tour->destination))
                        · {{ $tour->destination }}
                    @endif
                </p>
            </div>
            <span class="stp-lite-badge"><i class="ri-pencil-line"></i> Edit</span>
        </div>

        <form id="singleTourPackageForm" method="POST" action="{{ route('single-tour-package.update-info', $tour->tour_id ?? 0) }}">
            @csrf
            <input type="hidden" name="tour_id" id="tour_id" value="{{ (int) ($tour->tour_id ?? 0) }}">
            <input type="hidden" name="dmc_id" value="{{ (int) ($userDmcId ?? 0) }}">
            <input type="hidden" id="hotel_data" name="hotel_data" value="{{ e(json_encode($liteServices['hotel_data'] ?? [])) }}">
            <input type="hidden" id="entry_port_data" name="entry_port_data" value="{{ e(json_encode($liteServices['entry_port_data'] ?? [])) }}">
            <input type="hidden" id="exit_port_data" name="exit_port_data" value="{{ e(json_encode($liteServices['exit_port_data'] ?? [])) }}">
            <input type="hidden" id="transport_data" name="transport_data" value="{{ e(json_encode($liteServices['transport_data'] ?? [])) }}">
            <input type="hidden" id="attraction_data" name="attraction_data" value="{{ e(json_encode($liteServices['attraction_data'] ?? [])) }}">
            <input type="hidden" id="guide_data" name="guide_data" value="{{ e(json_encode($liteServices['guide_data'] ?? [])) }}">
            <input type="hidden" id="restaurant_data" name="restaurant_data" value="{{ e(json_encode($liteServices['restaurant_data'] ?? [])) }}">
            <input type="hidden" id="miscellaneous_data" name="miscellaneous_data" value="{{ e(json_encode($liteServices['miscellaneous_data'] ?? [])) }}">

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

<script>
window.STP_LITE_EDIT = {
    tourId: @json((int) ($tour->tour_id ?? 0)),
    displayId: @json($tour->display_id ?? ''),
    tourStatus: @json($tour->tour_status ?? ''),
    showAppPassword: @json($showAppPassword),
    startDate: @json($startYmd),
    endDate: @json($endYmd),
    datesDisplay: @json($datesDisplay),
    destination: @json($tour->destination ?? ''),
    city: @json($tour->city ?? ''),
    cityType: @json($tour->city_type ?? 'single'),
    tourType: @json($tour->tour_type ?? 'FIT'),
    adults: @json($editAdults),
    male: @json($editMale),
    female: @json($editFemale),
    children: @json((int) ($tour->child ?? 0)),
    infants: @json((int) ($tour->infant ?? 0)),
    childAges: @json($tour->child_ages ?? []),
    agentId: @json((string) ($tour->agent_id ?? '')),
    agencyId: @json($editAgencyId),
    referenceNumber: @json($tour->reference_number ?? ''),
    customer: @json($customer_info),
    mainGuest: @json($mainGuest),
    additionalGuests: @json($additionalGuests),
    services: @json($liteServices)
};
if (window.STP_LITE_CONFIG) {
    window.STP_LITE_CONFIG.mode = 'edit';
    window.STP_LITE_CONFIG.tourId = window.STP_LITE_EDIT.tourId;
    window.STP_LITE_CONFIG.tourStatus = window.STP_LITE_EDIT.tourStatus;
    window.STP_LITE_CONFIG.edit = window.STP_LITE_EDIT;
}
</script>

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
<script src="{{ asset('js/single-tour-package/lite/hydrate-edit.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/hydrate-edit.js')) }}"></script>
<script src="{{ asset('js/single-tour-package/lite/main.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/main.js')) }}"></script>
@endsection
{{-- === END edit.blade.php === --}}
