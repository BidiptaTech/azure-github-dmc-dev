@php
    $toursUrl = route('bookings.refunds');
    $packagesUrl = route('package-bookings.refunds');
    $pageHeading = $pageHeading ?? 'Package Refunds';
    $tableTitle = $tableTitle ?? 'Refunds List';
    $showPackagePaymentColumn = false;
    $showNegotiationColumn = false;
    $showBookingStatusColumn = true;
    $hideEditAction = true;
@endphp

@include('package-bookings._layout', compact(
    'toursUrl',
    'packagesUrl',
    'pageHeading',
    'tableTitle',
    'bookings',
    'statusColumn',
    'pageTitle',
    'showPackagePaymentColumn',
    'showNegotiationColumn',
    'showBookingStatusColumn',
    'packageComments'
))

