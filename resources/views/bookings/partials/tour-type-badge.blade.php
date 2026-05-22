{{-- Requires $tour->tour_type --}}
@php
    $tourTypeLower = strtolower($tour->tour_type ?? '');
    $tourTypeClass = $tourTypeLower === 'group' ? 'group' : 'fit';
@endphp
<span class="tour-type-badge-3d {{ $tourTypeClass }}" title="{{ $tour->tour_type }}">{{ $tour->tour_type }}</span>
