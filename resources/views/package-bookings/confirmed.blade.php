@php
    $toursUrl = route('bookings.confirmed');
    $packagesUrl = route('package-bookings.confirmed');
    $pageHeading = 'Package Confirmed';
    $tableTitle = 'Confirmed Bookings List';
    $showPackagePaymentColumn = true;
    $showNegotiationColumn = false;
@endphp

@include('package-bookings._layout', compact('toursUrl', 'packagesUrl', 'pageHeading', 'tableTitle', 'bookings', 'statusColumn', 'pageTitle', 'showPackagePaymentColumn', 'showNegotiationColumn'))

