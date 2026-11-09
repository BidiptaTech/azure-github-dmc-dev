@php
    $toursUrl = route('bookings.definite');
    $packagesUrl = route('package-bookings.definite');
    $pageHeading = 'Package Definite';
    $tableTitle = 'Definite Bookings List';
    $showPackagePaymentColumn = true;
    $showNegotiationColumn = false;
@endphp

@include('package-bookings._layout', compact('toursUrl', 'packagesUrl', 'pageHeading', 'tableTitle', 'bookings', 'statusColumn', 'pageTitle', 'showPackagePaymentColumn', 'showNegotiationColumn'))

