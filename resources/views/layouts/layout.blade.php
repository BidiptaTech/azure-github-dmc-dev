<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr"
  data-theme="theme-bordered" data-assets-path="{{ rtrim(config('app.url'), '/') . '/assets/' }}"
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
<!-- Close layout-page + layout-container (opened in sidebar/topbar) -->
</div>
</div>
<!-- Mobile sidebar backdrop (click to close) -->
<div class="layout-overlay layout-menu-toggle"></div>
<div class="drag-target"></div>
<!-- Close layout-wrapper -->
</div>
@yield('scripts')
@stack('scripts')


</body>
</html>
