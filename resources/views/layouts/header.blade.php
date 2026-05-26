<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<title>CoActive | DMC</title>
<meta name="description" content="Materialize – is the most developer friendly &amp; highly customizable Admin Dashboard Template." />
<meta name="keywords" content="dashboard, material, material design, bootstrap 5 dashboard, bootstrap 5 design, bootstrap 5">

<!-- Canonical SEO -->
<link rel="canonical" href="https://dmcdemo.coactivehub.com" />

<!-- Favicon -->
@php
    $faviconSetting = \App\Helpers\CommonHelper::masterSettingsName('favicon');
    $fileStorage = \App\Helpers\CommonHelper::masterSettingsName('file_storage')['master_value'] ?? 'local'; // Default to local if not set
@endphp
<link rel="icon" type="image/x-icon" href="{{ $faviconSetting['master_value'] }}" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com/">
<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;ampdisplay=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/fonts/remixicon/remixicon.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/fonts/flag-icons.css' }}" />
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<!-- Menu waves for no-customizer fix -->
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/node-waves/node-waves.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/fonts/remixicon/remixicon.css' }}" />
<!-- Core CSS -->
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/css/rtl/core.css' }}" class="template-customizer-core-css" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/css/rtl/theme-bordered.css' }}" class="template-customizer-theme-css" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/css/demo.css' }}" />
<!-- Custom local styles -->
<link rel="stylesheet" href="{{ asset('css/style.css') }}" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/typeahead-js/typeahead.css' }}" /> 
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/apex-charts/apex-charts.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/libs/swiper/swiper.css' }}" />

<!-- Page CSS -->
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/css/pages/cards-statistics.css' }}" />
<link rel="stylesheet" href="{{ env('APP_URL') . '/assets/vendor/css/pages/cards-analytics.css' }}" />

<!-- Helpers -->
<script src="{{ env('APP_URL') . '/assets/vendor/js/helpers.js' }}"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/js/template-customizer.js' }}"></script>
<script src="{{ env('APP_URL') . '/assets/js/config.js' }}"></script>

