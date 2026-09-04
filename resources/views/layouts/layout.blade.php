<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr"
  data-theme="theme-bordered" data-assets-path="{{ rtrim(url('/'), '/') . '/assets/' }}"
  data-template="vertical-menu-template-bordered"
  data-style="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
@include('layouts.header')  
@yield('css')
@stack('css')
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
@include('layouts.sidebar')
@include('layouts.topbar')

@yield('content')  
@include('layouts.footer') 
<!-- Close layout wrappers (opened in sidebar/topbar) -->
</div>
</div>
</div>
@yield('scripts')
@stack('scripts')


</body>
</html>
