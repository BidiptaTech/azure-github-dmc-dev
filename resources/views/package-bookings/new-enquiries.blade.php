@php
    $toursUrl = route('bookings.new-enquiries');
    $packagesUrl = route('package-bookings.new-enquiries');
    $pageHeading = 'Package New Enquiries';
    $tableTitle = 'New Enquiries List';
@endphp

@include('package-bookings._layout', compact('toursUrl', 'packagesUrl', 'pageHeading', 'tableTitle', 'bookings', 'statusColumn', 'pageTitle'))

