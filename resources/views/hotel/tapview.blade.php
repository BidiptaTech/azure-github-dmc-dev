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
        <a class="nav-link {{ Request::routeIs('hotelp') ? 'active' : '' }}" 
        id="pills-ports-tab" 
        href="{{ route('hotelp', $hotel->hotel_unique_id) }}" 
        role="tab" 
        aria-selected="true" 
        tabindex="0">
            Ports & Nearby
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link 
            {{ (Request::routeIs('hotels.facility') ? 'active' : '') }}" 
            id="pills-facility-tab" 
            href="{{ route('hotels.facility', $hotel->hotel_unique_id) }}" 
            role="tab" 
            aria-selected="{{ Request::routeIs('hotels.facility') ? 'true' : 'false' }}">
            Facilities
        </a>
    </li>

     <li class="nav-item" role="presentation">
        <a class="nav-link {{ Request::routeIs('hotels.createroom') ? 'active' : '' }}" 
        id="rooms-type-tab" 
        href="{{ route('hotels.createroom', ['id' => $hotel->hotel_unique_id]) }}">
            Room Pricing
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
            Bed Types
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
            Bed Configuration
        </a>
    </li>
        <li class="nav-item" role="presentation">
        <a class="nav-link {{ Request::routeIs('hotels.season') ? 'active' : '' }}" 
        id="pills-season-tab" 
        href="{{ route('hotels.season', $hotel->hotel_unique_id) }}" 
        role="tab" 
        aria-selected="true" 
        tabindex="0">
            Seasons
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ Request::routeIs('hotels.rates') ? 'active' : '' }}" 
        id="pills-event-tab" 
        href="{{ route('hotels.rates', $hotel->hotel_unique_id) }}" 
        role="tab" 
        aria-selected="true" 
        tabindex="0">
            Blackout/Fair Dates
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ Request::routeIs('policy') || Request::routeIs('cancellation.policy') || Request::routeIs('refund.policy') ? 'active' : '' }}" 
        id="pills-policy-tab" 
        href="{{ route('policy', $hotel->hotel_unique_id) }}" 
        role="tab" 
        aria-selected="true" 
        tabindex="0">
            Policy
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link {{ Request::routeIs('hotel.conference') ? 'active' : '' }}" 
        id="pills-calendar-tab" 
        href="{{ route('hotel.conference', $hotel->hotel_unique_id) }}" 
        role="tab"
        aria-selected="true" 
        tabindex="0">
            Meeting & Conference
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a class="nav-link {{ Request::routeIs('hotel-restaurant-create') || Request::routeIs('hotel-meals-create') ? 'active' : '' }}" 
        id="pills-restaurant-tab" 
        href="{{ route('hotel-restaurant-create', $hotel->hotel_unique_id) }}"
        role="tab" 
        aria-selected="true" 
        tabindex="0">
            Hotel Restaurants
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
