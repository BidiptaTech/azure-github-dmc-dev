{{-- === STP LITE: bootstrap-config ===
     Depends: controller compact vars (countries, UserDmc, dmcGroupPax, …)
     Owns: window.STP_LITE_CONFIG — single source for routes + DMC flags + geo maps
     === --}}
@php
    $mdmcCountries = ($countries ?? collect())->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'currency' => strtoupper(trim((string) ($c->currency ?? ''))),
        ];
    })->values();
    $countryIdByName = [];
    foreach ($mdmcCountries as $c) {
        $countryIdByName[$c['name']] = $c['id'];
    }

    // Same maps as classic create.blade.php — Flat suffixes / country currency must match DB
    $countryCurrencyMap = \App\Models\Country::query()
        ->whereNotNull('currency')
        ->where('currency', '!=', '')
        ->get(['name', 'currency'])
        ->mapWithKeys(static function ($c) {
            return [trim((string) $c->name) => strtoupper(trim((string) $c->currency))];
        })
        ->all();
    $cityCountryMap = \App\Models\City::query()
        ->whereNotNull('country')
        ->where('country', '!=', '')
        ->get(['name', 'country'])
        ->mapWithKeys(static function ($c) {
            return [trim((string) $c->name) => trim((string) $c->country)];
        })
        ->all();

    $liteCurrencyMarkups = [];
    if (!empty($tour)) {
        $rawMarkups = $tour->currency_markups ?? null;
        if (is_string($rawMarkups)) {
            $decodedMarkups = json_decode($rawMarkups, true);
            $rawMarkups = (json_last_error() === JSON_ERROR_NONE) ? $decodedMarkups : [];
        }
        if (is_array($rawMarkups)) {
            $liteCurrencyMarkups = array_values($rawMarkups);
        }
    }
@endphp
<script>
window.TOUR_PACKAGE_CURRENCY = @json($dmcCurrency ?? 'SGD');
window.COUNTRY_CURRENCY_MAP = @json($countryCurrencyMap);
window.CITY_COUNTRY_MAP = @json($cityCountryMap);
window.LITE_CITY_GEO = window.LITE_CITY_GEO || {};

window.STP_LITE_CONFIG = {
    mode: 'create',
    csrfToken: @json(csrf_token()),
    dmcId: @json((int) ($userDmcId ?? 0)),
    dmcGroupPax: @json((int) ($dmcGroupPax ?? 0)),
    dmcCurrency: @json($dmcCurrency ?? 'SGD'),
    isThirdPartyDmc: @json(!empty($isThirdPartyDmc)),
    isRestrictedThirdParty: @json(!empty($isRestrictedThirdParty)),
    ownDmcCountries: @json(array_values($ownDmcCountries ?? [])),
    zoneOn: @json((int) (optional($UserDmc)->zone_on ?? 0)),
    mdmcCountries: @json($mdmcCountries),
    countryIdByName: @json($countryIdByName),
    siblingDmcCountryMap: @json($siblingDmcCountryMap ?? new \stdClass()),
    siblingDmcCityMap: @json($siblingDmcCityMap ?? new \stdClass()),
    enquiryLocked: @json(!empty($enquiry) && empty($tour->tour_id ?? null)),
    routes: {
        store: @json(route('single-tour-package.store')),
        ajaxCities: @json(route('ajax.cities')),
        fetchAgentsByAgency: @json(route('fetch-agents-by-agency')),
        fetchRoomsByHotel: @json(route('fetch-rooms-by-hotel')),
        fetchBedsByRoom: @json(route('fetch-beds-by-room')),
        getHotelPrice: @json(route('get-hotel-price')),
        fetchHotelsByDmc: @json(route('fetch-hotels-by-dmc')),
        fetchZonesByDmc: @json(route('fetch-zones-by-dmc')),
        fetchZoneAssignedLocations: @json(route('fetch-zone-assigned-locations')),
        fetchVehiclesByZones: @json(route('fetch-vehicles-by-zones')),
        fetchVehiclesByCityAndDmc: @json(route('fetch-vehicles-by-city-dmc')),
        fetchPortsByCountry: @json(route('fetch-ports-by-country-single-tour')),
        fetchAttractionsByDmc: @json(route('fetch-attractions-by-dmc')),
        fetchTicketsByAttraction: @json(route('fetch-tickets-by-attraction')),
        fetchGuidesByDmc: @json(route('fetch-guides-by-dmc')),
        fetchRestaurantsByDmc: @json(route('fetch-restaurants-by-dmc')),
        fetchMealsByRestaurant: @json(route('fetch-meals-by-restaurant')),
        fetchAttractionTransferPricing: @json(route('fetch-attraction-transfer-pricing')),
        fetchRestaurantTransferPricing: @json(route('fetch-restaurant-transfer-pricing')),
        storeOrders: @json(route('single-tour-package.store-orders')),
        thankYou: @json(route('single-tour-package.thank-you')),
        thankYouUpdated: @json(route('single-tour-package.thank-you-updated')),
        editPackage: @json(url('/single-tour-package/tour/edit/__ID__')),
        // Edit routes use __ID__ placeholder — replaced at save time with tour_id
        updateInfo: @json(url('/single-tour-package/__ID__/info')),
        updateCityPlans: @json(url('/single-tour-package/__ID__/city-plans')),
        updateGuests: @json(url('/single-tour-package/__ID__/guests')),
        clearServices: @json(url('/single-tour-package/__ID__/services/clear')),
        getMiscellaneous: @json(route('enquiry-form-pro.get-miscellaneous'))
    },
    dateFormats: {
        display: 'MMM D, YYYY',
        store: 'YYYY-MM-DD'
    },
    serviceOrder: ['hotel', 'arrival', 'attraction', 'guide', 'restaurant', 'transport', 'departure', 'miscellaneous'],
    currencyMarkups: @json($liteCurrencyMarkups),
    bundleAttractionIcon: @json(asset('assets/images/bundle-attraction-icon.png'))
};
</script>
{{-- === END bootstrap-config === --}}
