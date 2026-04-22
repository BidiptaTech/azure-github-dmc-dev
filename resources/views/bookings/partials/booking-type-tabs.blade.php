@php
    /** @var string $type 'tours'|'packages' */
    $type = $type ?? 'tours';
    /** @var string $toursUrl */
    /** @var string $packagesUrl */
@endphp

<div class="mb-3">
    <ul class="nav nav-pills nav-fill">
        <li class="nav-item">
            <a class="nav-link {{ $type === 'tours' ? 'active' : '' }}" href="{{ $toursUrl }}">
                <i class="ri-map-pin-line me-1"></i> Tours Booking
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'packages' ? 'active' : '' }}" href="{{ $packagesUrl }}">
                <i class="ri-box-3-line me-1"></i> Package Booking
            </a>
        </li>
    </ul>
</div>

