<!-- Tab View -->
<ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ Request::routeIs('hotels.edit') ? 'active' : '' }}" 
        id="pills-hotel-tab" href="{{ route('hotels.edit', $hotel->hotel_unique_id) }}" role="tab">
            Hotel
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link {{ empty($hotel->name) ? 'disabled' : (Request::routeIs('hotels.contact') ? 'active' : '') }}" 
        id="pills-contact-tab" 
        href="{{ empty($hotel->name) ? '#' : route('hotels.contact', $hotel->hotel_unique_id) }}" 
        role="tab" 
        aria-selected="{{ empty($hotel->name) ? 'false' : 'true' }}" 
        tabindex="{{ empty($hotel->name) ? '-1' : '0' }}">
            Contacts
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link {{ empty($hotel->hotel_owner_company_name) ? 'disabled' : (Request::routeIs('hotelp') ? 'active' : '') }}" 
        id="pills-ports-tab" 
        href="{{ empty($hotel->hotel_owner_company_name) ? '#' : route('hotelp', $hotel->hotel_unique_id) }}" 
        role="tab" 
        aria-selected="{{ empty($hotel->hotel_owner_company_name) ? 'false' : 'true' }}" 
        tabindex="{{ empty($hotel->hotel_owner_company_name) ? '-1' : '0' }}">
            Ports & NearBy
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link 
            {{ (!$hotel->port_of_entry && !$hotel->port_of_exit && !$hotel->others) ? 'disabled' : (Request::routeIs('hotels.facility') ? 'active' : '') }}" 
            id="pills-facility-tab" 
            href="{{ route('hotels.facility', $hotel->hotel_unique_id) }}" 
            role="tab" 
            aria-selected="{{ Request::routeIs('hotels.facility') ? 'true' : 'false' }}">
            Facilities
        </a>
    </li>

     <li class="nav-item" role="presentation">
        <a class="nav-link {{ empty($hotel->facilities) ? 'disabled' : (Request::routeIs('hotels.createroom') ? 'active' : '') }}" 
        id="rooms-type-tab" 
        href="{{ empty($hotel->facilities) ? 'javascript:void(0);' : route('hotels.createroom', ['id' => $hotel->hotel_unique_id]) }}"
        style="cursor: {{ empty($hotel->facilities) ? 'not-allowed' : 'pointer' }}">
            Rooms Type
        </a>
    </li> 
     @php
        $roomCount = App\Models\Room::where('hotel_id', $hotel->hotel_unique_id)->count();
    @endphp
    @if(in_array(Auth::user()->role_id, [1, 20]))
    <li class="nav-item" role="presentation">
        <a 
            class="nav-link {{ $roomCount > 0 ? (Request::routeIs('beds.create') ? 'active' : '') : 'disabled' }}" 
            id="pills-bedtype-tab" 
            href="{{ $roomCount > 0 ? route('beds.create', $hotel->hotel_unique_id) : 'javascript:void(0);' }}" 
            role="tab"
            aria-disabled="{{ $roomCount > 0 ? 'false' : 'true' }}">
            Bed Type
        </a>
    </li> 
    @endif
    @php
        $bedCount = App\Models\BedMaster::where('hotel_id', $hotel->hotel_unique_id)->count();
    @endphp
    <li class="nav-item" role="presentation">
        <a 
            class="nav-link {{ $bedCount > 0 ? (Request::routeIs('hotels.beds') ? 'active' : '') : 'disabled' }}" 
            id="pills-room-tab" 
            href="{{ $bedCount > 0 ? route('hotels.beds', $hotel->hotel_unique_id) : 'javascript:void(0);' }}" 
            role="tab"
            aria-disabled="{{ $bedCount > 0 ? 'false' : 'true' }}">
            Rooms
        </a>
    </li>
    @php
    $hote_id = $hotel->hotel_unique_id;
        $roomCount = App\Models\Bed::whereHas('room', function ($query) use ($hote_id) {
            $query->where('hotel_id', $hote_id);
        })->count();
    @endphp

   

    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $roomCount > 0 ? (Request::routeIs('hotel-restaurant-create')|| Request::routeIs('hotel-meals-create') ? 'active' : '') : 'disabled' }}" 
        id="pills-event-tab" 
        href="{{ $roomCount > 0 ? env('APP_URL') . route('hotel-restaurant-create', $hotel->hotel_unique_id, false) : '#' }}"
        role="tab" 
        aria-selected="{{ $roomCount > 0 ? 'true' : 'false' }}" 
        tabindex="{{ $roomCount > 0 ? '0' : '-1' }}">
            Hotel Restaurants
        </a>
    </li>

     @php 
        $hotelRestaurantCount = App\Models\Restaurant::where('owned_by', $hotel->hotel_unique_id)->count();
    @endphp

    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $hotelRestaurantCount > 0 ? (Request::routeIs('hotels.season') ? 'active' : '') : 'disabled' }}" 
        id="pills-season-tab" 
        href="{{ $hotelRestaurantCount > 0 ? route('hotels.season', $hotel->hotel_unique_id) : '#' }}" 
        role="tab" 
        aria-selected="{{ $hotelRestaurantCount > 0 ? 'true' : 'false' }}" 
        tabindex="{{ $hotelRestaurantCount > 0 ? '0' : '-1' }}">
            Seasons
        </a>
    </li>
    @php 
        $rateCount = App\Models\Rate::where('hotel_id', $hotel->hotel_unique_id)->where('event_type','Season')->count();
    @endphp

    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $rateCount > 0 ? (Request::routeIs('hotels.rates') ? 'active' : '') : 'disabled' }}" 
        id="pills-event-tab" 
        href="{{ $rateCount > 0 ? route('hotels.rates', $hotel->hotel_unique_id) : '#' }}" 
        role="tab" 
        aria-selected="{{ $rateCount > 0 ? 'true' : 'false' }}" 
        tabindex="{{ $rateCount > 0 ? '0' : '-1' }}">
            Blackout/Fair Dates
        </a>
    </li>
    @php 
    $eventCount = App\Models\Rate::where('hotel_id', $hotel->hotel_unique_id)
                    ->whereIn('event_type', ['Blackout Date', 'Fair Date'])
                    ->count();
    @endphp

    {{-- <li class="nav-item" role="presentation">
        <a class="nav-link {{ $eventCount > 0 ? (Request::routeIs('policy') ? 'active' : '') : 'disabled' }}" 
        id="pills-policy-tab" 
        href="{{ $eventCount > 0 ? route('policy', $hotel->hotel_unique_id) : '#' }}" 
        role="tab" 
        aria-selected="{{ $eventCount > 0 ? 'true' : 'false' }}" 
        tabindex="{{ $eventCount > 0 ? '0' : '-1' }}">
            Property Policy
        </a>
    </li>
    @php
        $policyCount = App\Models\HotelPolicy::where('hotel_id', $hotel->hotel_unique_id)->count();
    @endphp --}}

    {{-- <li class="nav-item" role="presentation">
        <a class="nav-link {{ $policyCount > 0 ? (Request::routeIs('cancellation.policy') ? 'active' : '') : 'disabled' }}" 
        id="pills-cancellation-tab" 
        href="{{ $policyCount > 0 ? route('cancellation.policy', $hotel->hotel_unique_id) : '#' }}" 
        role="tab" 
        aria-selected="{{ $policyCount > 0 ? 'true' : 'false' }}" 
        tabindex="{{ $policyCount > 0 ? '0' : '-1' }}">
            Cancellation Policy
        </a>
    </li>
    @php
        $refundPolicyCount = App\Models\HotelPolicy::where('hotel_id', $hotel->hotel_unique_id)->count();
    @endphp --}}

    {{-- <li class="nav-item" role="presentation">
        <a class="nav-link {{ $refundPolicyCount > 0 ? (Request::routeIs('refund.policy') ? 'active' : '') : 'disabled' }}" 
            id="pills-refund-tab" 
            href="{{ $refundPolicyCount > 0 ? route('refund.policy', $hotel->hotel_unique_id) : '#' }}" 
            role="tab" 
            aria-selected="{{ $refundPolicyCount > 0 ? 'true' : 'false' }}" 
            tabindex="{{ $refundPolicyCount > 0 ? '0' : '-1' }}">
            Refund Policy
        </a>
    </li> --}}

    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $eventCount > 0 ? (Request::routeIs('policy') || Request::routeIs('cancellation.policy') || Request::routeIs('refund.policy') ? 'active' : '') : 'disabled' }}" 
        id="pills-policy-tab" 
        href="{{ $eventCount > 0 ? route('policy', $hotel->hotel_unique_id) : '#' }}" 
        role="tab" 
        aria-selected="{{ $eventCount > 0 ? 'true' : 'false' }}" 
        tabindex="{{ $eventCount > 0 ? '0' : '-1' }}">
            Policy
        </a>
    </li>

    @php
        $hotelPolicyCount = App\Models\HotelPolicy::where('hotel_id', $hotel->hotel_unique_id)->count();
    @endphp
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ !empty($hotelPolicyCount) ? (Request::routeIs('hotel.conference') ? 'active' : '') : 'disabled' }}" 
        id="pills-calendar-tab" 
        href="{{ !empty($hotelPolicyCount) ? route('hotel.conference', $hotel->hotel_unique_id) : '#' }}" 
        role="tab"
        aria-selected="{{ !empty($hotelPolicyCount) ? 'true' : 'false' }}" 
        tabindex="{{ !empty($hotelPolicyCount) ? '0' : '-1' }}">
            Meeting & Conference
        </a>
    </li>


    <li class="nav-item" role="presentation">
        <a class="nav-link {{ Request::routeIs('hotels.calender') ? 'active' : '' }}" 
        id="pills-calendar-tab" href="{{ route('hotels.calender', $hotel->hotel_unique_id) }}" role="tab">
            Calendar
        </a>
    </li>

</ul>
<!-- End of Tab View -->
