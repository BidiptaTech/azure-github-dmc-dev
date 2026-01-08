<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr"
  data-theme="theme-bordered" data-assets-path="{{ env('APP_URL') . '/assets/'}}"
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
</head>
@include('layouts.sidebar')
@include('layouts.topbar')

@yield('content')  
@include('layouts.footer') 
@yield('scripts')
@stack('scripts')


</body>
</html>
