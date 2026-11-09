{{-- FIT/GROUP + pro/lite badges for booking list tour details column --}}
@include('bookings.partials.tour-detail-badges-styles')
<div class="tour-detail-badges-row">
    @if($tour->tour_type)
        @include('bookings.partials.tour-type-badge', ['tour' => $tour])
    @endif
    @include('bookings.partials.tour-pro-lite-badge', ['tour' => $tour])
</div>
