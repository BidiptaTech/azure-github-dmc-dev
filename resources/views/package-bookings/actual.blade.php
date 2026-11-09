@php
    $toursUrl = route('bookings.actual');
    $packagesUrl = route('package-bookings.actual');
    $pageHeading = 'Package Actual';
    $tableTitle = 'Actual Bookings List';
    $showPackagePaymentColumn = true;
    $showNegotiationColumn = false;
@endphp

@include('package-bookings._layout', compact('toursUrl', 'packagesUrl', 'pageHeading', 'tableTitle', 'bookings', 'statusColumn', 'pageTitle', 'showPackagePaymentColumn', 'showNegotiationColumn'))

