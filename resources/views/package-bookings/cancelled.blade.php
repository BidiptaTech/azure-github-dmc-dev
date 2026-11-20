@php
    $toursUrl = route('bookings.cancelled');
    $packagesUrl = route('package-bookings.cancelled');
    $pageHeading = 'Package Cancelled';
    $tableTitle = 'Cancelled Bookings List';
    $showNegotiationColumn = false;
    $hideEditAction = true;
@endphp

@include('package-bookings._layout', compact('toursUrl', 'packagesUrl', 'pageHeading', 'tableTitle', 'bookings', 'statusColumn', 'pageTitle', 'showNegotiationColumn'))

