<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr"
  data-theme="theme-bordered" data-assets-path="{{ subdirectory_asset('assets/') }}"
  data-template="vertical-menu-template-bordered"
  data-style="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
@include('layouts.header')  
@yield('css')  
</head>
@include('layouts.sidebar')
@include('layouts.topbar')

@yield('content')  
@include('layouts.footer') 
@yield('scripts')


</body>
</html>
