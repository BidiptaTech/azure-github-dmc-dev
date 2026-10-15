@php
    $toursUrl = route('bookings.follow-ups');
    $packagesUrl = route('package-bookings.follow-ups');
    $pageHeading = 'Package Follow Ups';
    $tableTitle = 'Follow Ups (Prospect & Tentative)';
    $showBookingStatusColumn = true;
@endphp

@include('package-bookings._layout', compact('toursUrl', 'packagesUrl', 'pageHeading', 'tableTitle', 'bookings', 'statusColumn', 'pageTitle', 'showBookingStatusColumn'))

