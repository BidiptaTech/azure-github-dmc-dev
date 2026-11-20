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

{{-- Create Tour Pro: keep Bootstrap 5 modal API after any page script (BS4 etc.) --}}
<script>
(function () {
    function ctpHasModalApi(bs) {
        return !!(bs && bs.Modal && (
            typeof bs.Modal.getOrCreateInstance === 'function'
            || typeof bs.Modal.getInstance === 'function'
            || typeof bs.Modal === 'function'
        ));
    }
    // Prefer original layout BS5; only adopt window.bootstrap if it still looks like BS5
    if (!ctpHasModalApi(window.__ctpBootstrap) && ctpHasModalApi(window.bootstrap)
        && typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
        window.__ctpBootstrap = window.bootstrap;
    }
    window.ctpBootstrap = function () {
        return window.__ctpBootstrap || window.bootstrap || null;
    };
    window.ctpGetOrCreateModal = function (el, opts) {
        var bs = window.ctpBootstrap();
        if (!el || !bs || !bs.Modal) return null;
        if (typeof bs.Modal.getOrCreateInstance === 'function') {
            return bs.Modal.getOrCreateInstance(el, opts || {});
        }
        if (typeof bs.Modal.getInstance === 'function') {
            var existing = bs.Modal.getInstance(el);
            if (existing) return existing;
        }
        try { return new bs.Modal(el, opts || {}); } catch (e) { return null; }
    };
    window.ctpGetModalInstance = function (el) {
        var bs = window.ctpBootstrap();
        if (!el || !bs || !bs.Modal || typeof bs.Modal.getInstance !== 'function') return null;
        return bs.Modal.getInstance(el);
    };
    // Ensure CTP modals sit on <body> so nested stacking works on every template
    ['createTourProModal', 'tourProGuestModal'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
    });
})();
</script>

</body>
</html>
