@extends('layouts.auth')

@section('title', 'Login')

@section('css')
{{-- Static map background video --}}
<style>
  /* ═══════════════════════════════════════════
     Page-level polish
     ═══════════════════════════════════════════ */
  body{
    background: radial-gradient(1200px 600px at 20% 10%, rgba(13,110,253,.10) 0%, rgba(13,110,253,0) 55%),
                radial-gradient(900px 500px at 90% 30%, rgba(111,66,193,.08) 0%, rgba(111,66,193,0) 60%),
                #f6f8ff;
  }
  /* Make the login view fill the viewport height (prevents large bottom gap). */
  .auth-page-wrapper{
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    padding: .75rem 0;
  }
  .auth-shell-card{
    max-width: 1280px;
    width: 100%;
    flex: 1 1 auto;
    display: flex;
    min-height: 0;
  }
  .auth-shell-card > .row{
    flex: 1 1 auto;
    min-height: 0;
  }
  .auth-shell-card .col-lg-5,
  .auth-shell-card .col-lg-7{
    display: flex;
    flex: 1 1 auto;
    min-height: 0;
  }
  /* Left column content should be allowed to stretch when card height grows. */
  .auth-shell-card .col-lg-5 .card-body{
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
  }
  .auth-hero{
    flex: 1 1 auto;
    min-height: 320px;
    height: 100%;
  }
  .auth-shell-card{
    background: rgba(255,255,255,.85);
    border: 1px solid rgba(15,23,42,.08);
    box-shadow: 0 18px 50px rgba(15, 23, 42, .10);
    backdrop-filter: blur(10px);
  }
  .auth-shell-card .card-body{ color: #0f172a; }
  .auth-watermark{
    font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
    font-size: .70rem; color: rgba(15,23,42,.35);
  }
  .auth-watermark span{ color: rgba(37,99,235,.55); }
  .auth-watermark--overlay{
    position: absolute; top: 4px; left: 58%; scale: 1.8;
    transform: translateX(-50%); font-size: .62rem; opacity: .55;
    pointer-events: none; white-space: nowrap;
  }
  .auth-logo{
    display: block; margin: 0 auto;
    width: 150px; height: 70px; object-fit: contain;
    scale: 1.2;
  }
  .auth-section-title{ font-weight: 800; letter-spacing: .2px; }
  .auth-divider{
    height: 1px; opacity: 100px;
    background: linear-gradient(90deg, rgba(15,23,42,0) 0%, rgba(15,23,42,.12) 20%, rgba(15,23,42,.12) 80%, rgba(15,23,42,0) 100%);
  }
  .auth-field .form-control,
  .auth-field .input-group-text{ border-color: rgba(15,23,42,.12); }
  .auth-field .form-control:focus{
    border-color: rgba(13,110,253,.45);
    box-shadow: 0 0 0 .20rem rgba(13,110,253,.12);
  }
  .auth-btn{
    border: 0;
    background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 35%, #4f46e5 100%);
    box-shadow: 0 10px 22px rgba(37,99,235,.25);
  }
  .auth-btn:hover{ filter: brightness(1.03); box-shadow: 0 12px 26px rgba(37,99,235,.30); }
  .auth-btn:focus{ box-shadow: 0 0 0 .20rem rgba(37,99,235,.20), 0 12px 26px rgba(37,99,235,.28); }

  /* ═══════════════════════════════════════════
     Hero panel — Leaflet world map
     ═══════════════════════════════════════════ */
  .auth-hero{
    background: #f0f4f8;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,.06), 0 18px 45px rgba(0,0,0,.08);
  }
  .auth-hero-video{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
    background: #f0f4f8;
    opacity: .95;
    filter: saturate(.65) contrast(1.05);
    pointer-events: none;
  }
  #heroLeafletMap{
    position: absolute; inset: 0; width: 100%; height: 100%;
    z-index: 1; background: transparent;
  }
  /* Light map — subtle muted tint */
  #heroLeafletMap .leaflet-tile-pane{
    opacity: .92;
    filter: saturate(.6) contrast(1.05);
  }
  #heroLeafletMap .leaflet-control-container{ display: none !important; }

  /* Subtle vignette (light theme) */
  .auth-hero-gradient{
    position: absolute; inset: 0; z-index: 2; pointer-events: none;
    background:
      linear-gradient(180deg,
        rgba(240,244,248,.50) 0%, rgba(240,244,248,0) 14%,
        rgba(240,244,248,0) 80%, rgba(240,244,248,.55) 100%);
  }
  /* Bottom overlay text */
  .hero-bottom-text{
    background: linear-gradient(0deg, rgba(255,255,255,.88) 0%, rgba(255,255,255,.50) 55%, transparent 100%);
  }
  .hero-tagline{
    font-size: .78rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: .5px;
  }
  .hero-subtitle{
    font-size: .68rem;
    font-weight: 600;
    color: #2563eb;
    letter-spacing: .8px;
    text-transform: uppercase;
    margin-top: 2px;
  }
  .hero-map-attr{
    position: absolute; bottom: 3px; right: 6px; font-size: 7px;
    color: rgba(0,0,0,.18); z-index: 4; pointer-events: none;
  }

  /* ─── City marker: dot + label (no pin icon) ─── */
  .hero-city{
    position: relative;
    width: 0; height: 0;
    opacity: 0;
    animation: cityFadeIn .45s ease forwards;
    animation-delay: calc(var(--i, 0) * 60ms + 350ms);
  }
  .hero-city-dot{
    position: absolute;
    width: 7px; height: 7px;
    top: -3.5px; left: -3.5px;
    background: #2563eb;
    border: 1.5px solid #fff;
    border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(37,99,235,.25);
    z-index: 3;
  }
  .hero-city-label{
    position: absolute;
    white-space: nowrap;
    font-size: 8.5px;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: .3px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    z-index: 4;
    /* default: centered above dot */
    left: 50%; bottom: 8px;
    transform: translateX(-50%);
  }
  /* Position variants */
  .hero-city-label--right{ left: 10px; bottom: -3px; transform: none; }
  .hero-city-label--left{ right: 10px; left: auto; bottom: -3px; transform: none; }
  .hero-city-label--below{ left: 50%; bottom: auto; top: 8px; transform: translateX(-50%); }

  @keyframes cityFadeIn{
    0%  { opacity: 0; transform: scale(.5); }
    100%{ opacity: 1; transform: scale(1); }
  }

  /* ─── Flight route lines ─── */
  .hero-route{
    animation: heroRouteDash 3s linear infinite;
  }
  .hero-route-2{ animation-duration: 2.4s; }
  .hero-route-3{ animation-duration: 4s; }
  @keyframes heroRouteDash{
    to{ stroke-dashoffset: -48; }
  }

  /* ─── Airplane icon flying along route ─── */
  .hero-plane-wrapper{
    background: transparent !important;
    border: none !important;
    will-change: transform;
  }
  .hero-plane-svg{
    display: block;
    filter: drop-shadow(0 1px 3px rgba(37,99,235,.35));
    will-change: transform;
  }

  /* Reduced motion */
  @media (prefers-reduced-motion: reduce){
    .hero-city{ animation: none !important; opacity: 1 !important; }
    .hero-route{ animation: none !important; }
  }

  /* ═══════════════════════════════════════════
     Secure Login glow card
     ═══════════════════════════════════════════ */
  .auth-benefits-list{
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: .68rem; color: rgba(15,23,42,.55); letter-spacing: .2px;
  }
  .auth-benefits-list i{ font-size: .62rem; }
  .auth-protected-note{
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: .65rem; color: rgba(15,23,42,.40); letter-spacing: .3px; font-style: italic;
  }
  .auth-login-glow-card{
    position: relative; overflow: hidden;
    background: linear-gradient(135deg, rgba(13,110,253,.10) 0%, rgba(13,110,253,.04) 35%, rgba(255,255,255,.92) 100%);
    border: 1px solid rgba(13,110,253,.12) !important;
    box-shadow: 0 10px 30px rgba(13,110,253,.08);
  }
  .auth-login-glow-card::before{
    content: ""; position: absolute; inset: -60%;
    background:
      radial-gradient(circle at 30% 30%, rgba(13,110,253,.18), transparent 55%),
      radial-gradient(circle at 70% 60%, rgba(111,66,193,.10), transparent 55%),
      radial-gradient(circle at 50% 80%, rgba(13,110,253,.10), transparent 60%);
    filter: blur(12px); animation: authGlowMove 10s ease-in-out infinite; pointer-events: none;
  }
  .auth-login-glow-card::after{
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(115deg, transparent 0%, rgba(255,255,255,.55) 45%, transparent 60%);
    transform: translateX(-130%) skewX(-15deg);
    animation: authShimmer 7.5s ease-in-out infinite; pointer-events: none; opacity: .55;
  }
  .auth-login-glow-card > *{ position: relative; z-index: 1; }
  @keyframes authGlowMove{
    0%   { transform: translate3d(-3%,-2%,0) scale(1); }
    50%  { transform: translate3d(3%,2%,0) scale(1.05); }
    100% { transform: translate3d(-3%,-2%,0) scale(1); }
  }
  @keyframes authShimmer{
    0%   { transform: translateX(-130%) skewX(-15deg); }
    55%  { transform: translateX(130%) skewX(-15deg); }
    100% { transform: translateX(130%) skewX(-15deg); }
  }
  @media (prefers-reduced-motion: reduce){
    .auth-login-glow-card::before,
    .auth-login-glow-card::after{ animation: none !important; }
  }
</style>
@endsection

@section('content')

<div class="mx-3 mx-lg-0 auth-page-wrapper">
  @php
      $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
      $fileStorage = \App\Helpers\CommonHelper::masterSettingsName('file_storage')['master_value'] ?? 'local';
  @endphp
  <div class="card my-0 mx-auto rounded-4 overflow-hidden p-2 auth-shell-card">
    <div class="row g-1">
      <div class="col-lg-5 d-flex">
        <div class="card-body p-2">
          <div class="text-center mb-1 position-relative" style="padding-top: 14px;">
            <div class="auth-watermark auth-watermark--overlay">Travclicks <span>Cloud</span></div>
            <img src="{{ $logoSetting['master_value'] }}" class="auth-logo" alt="Logo">
          </div>

          <div class="border rounded-3 p-2 mt-1 auth-login-glow-card">
            <div class="mb-2">
              <h4 class="auth-section-title fs-5 mb-1">Secure Login</h4>
            </div>
            <div class="form-body mt-1">
              <form class="row g-2" method="POST" action="{{ route('login') }}">
              @csrf
                <div class="col-12 auth-field">
                  <label for="inputEmailAddress" class="form-label mb-1">Email</label>
                  <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" id="inputEmailAddress" name="email" value="{{ old('email') }}" placeholder="Enter Email" autocomplete="username">
                  @error('email')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>
                <div class="col-12 auth-field">
                  <label for="inputChoosePassword" class="form-label mb-1">Password</label>
                  <div class="input-group input-group-sm" id="show_hide_password">
                    <input type="password" class="form-control border-end-0 form-control-sm @error('password') is-invalid @enderror" id="inputChoosePassword" name="password"
                      placeholder="Enter Password" autocomplete="current-password">
                    <a href="javascript:;" class="input-group-text bg-transparent"><i class="bi bi-eye-slash-fill"></i></a>
                      @error('password')
                          <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                      @enderror
                  </div>
                </div>
                <div class="col-12 d-flex justify-content-between align-items-center">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
                  </div>
                  @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-decoration-none">Forgot Password?</a>
                  @endif
                </div>
                <div class="col-12">
                  <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-1 auth-btn">Secure Login</button>
                  </div>
                </div>
              </form>
            </div>

            <div class="auth-divider my-3"></div>

            <div>
              <ul class="list-unstyled mb-1 auth-benefits-list">
                <li class="d-flex align-items-center mb-1">
                  <i class="bi bi-shield-lock-fill text-primary me-2"></i>
                  <span>Enterprise-grade security</span>
                </li>
                <li class="d-flex align-items-center mb-1">
                  <i class="bi bi-globe2 text-primary me-2"></i>
                  <span>Global infrastructure</span>
                </li>
                <li class="d-flex align-items-center">
                  <i class="bi bi-speedometer2 text-primary me-2"></i>
                  <span>Real-time operations platform</span>
                </li>
              </ul>
              <div class="auth-divider mt-2 mb-1"></div>
              <small class="d-block auth-protected-note">Protected by enterprise-grade encryption &amp; secure authentication</small>
            </div>
          </div>
        </div>
      </div>

      {{-- ═══ RIGHT PANEL: Map Background Video ═══ --}}
      <div class="col-lg-7 d-lg-flex d-none">
        <div class="p-0 rounded-4 w-100 d-flex flex-column text-white position-relative overflow-hidden auth-hero">
          <video class="auth-hero-video" autoplay loop muted playsinline preload="metadata">
            <source src="{{ asset('assets/images/map.mp4') }}" type="video/mp4">
          </video>
          <div class="auth-hero-gradient"></div>

          {{-- Bottom overlay text --}}
          <div class="position-absolute w-100 px-3 pb-3 hero-bottom-text" style="bottom: 0; z-index: 3;">
            <p class="mb-0 text-center hero-tagline">Powering DMC's &nbsp;&middot;&nbsp; Travel Agents &nbsp;&middot;&nbsp; Global Travel Operations</p>
            <p class="mb-0 text-center hero-subtitle">One Unified Operating System</p>
          </div>

          <div class="hero-map-attr">&copy; OpenStreetMap &copy; CARTO</div>
        </div>
      </div>

    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
(function(){

  /* ════════════════════════════════
     ALL 24 CITIES
     ════════════════════════════════ */
  /* One city per country/region — well-spaced, no overlap
     lbl: '' = above (default), 'right', 'left', 'below' */
  var cities = [
    { name: 'New York',    lat:  40.71, lng: -74.01, lbl: ''      },
    { name: 'London',      lat:  51.51, lng:  -0.13, lbl: 'left'  },
    { name: 'Paris',       lat:  48.86, lng:   2.35, lbl: 'right' },
    { name: 'Istanbul',    lat:  41.01, lng:  28.98, lbl: ''       },
    { name: 'Dubai',       lat:  25.20, lng:  55.27, lbl: ''       },
    { name: 'Maldives',    lat:   3.20, lng:  73.22, lbl: 'below' },
    { name: 'Mumbai',      lat:  19.08, lng:  72.88, lbl: ''       },
    { name: 'Bangkok',     lat:  13.76, lng: 100.50, lbl: ''       },
    { name: 'Singapore',   lat:   1.35, lng: 103.82, lbl: 'right' },
    { name: 'Bali',        lat:  -8.41, lng: 115.19, lbl: 'right' },
    { name: 'Seoul',       lat:  37.57, lng: 126.98, lbl: 'left'  },
    { name: 'Tokyo',       lat:  35.68, lng: 139.69, lbl: 'right' },
    { name: 'Shanghai',    lat:  31.23, lng: 121.47, lbl: 'left'  },
    { name: 'Sydney',      lat: -33.87, lng: 151.21, lbl: ''       }
  ];

  /* ════════════════════════════════
     CITY MARKER (dot + label, no pin)
     ════════════════════════════════ */
  function makeCityIcon(name, index, lblPos) {
    var lblCls = 'hero-city-label';
    if (lblPos === 'right') lblCls += ' hero-city-label--right';
    else if (lblPos === 'left') lblCls += ' hero-city-label--left';
    else if (lblPos === 'below') lblCls += ' hero-city-label--below';

    return L.divIcon({
      className: '',
      html:
        '<div class="hero-city" style="--i:' + index + '">' +
          '<span class="hero-city-dot"></span>' +
          '<span class="' + lblCls + '">' + name + '</span>' +
        '</div>',
      iconSize: [0, 0],
      iconAnchor: [0, 0]
    });
  }

  /* ════════════════════════════════
     INIT MAP
     ════════════════════════════════ */
  $(document).ready(function(){
    if (!document.getElementById('heroLeafletMap')) return;

    var worldBounds = L.latLngBounds(
      L.latLng(-45, -100),
      L.latLng(62,  165)
    );

    var map = L.map('heroLeafletMap', {
      zoomControl: false,
      attributionControl: false,
      dragging: false,
      scrollWheelZoom: false,
      doubleClickZoom: false,
      boxZoom: false,
      keyboard: false,
      touchZoom: false,
      tap: false,
      fadeAnimation: true,
      zoomSnap: 0.1,
      maxBounds: worldBounds.pad(0.1),
      maxBoundsViscosity: 1
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
      subdomains: 'abcd',
      noWrap: true,
      bounds: [[-90, -180], [90, 180]],
      maxZoom: 19
    }).addTo(map);

    function fitMap() {
      map.invalidateSize();
      map.fitBounds(worldBounds, { padding: [5, 5], animate: false });
    }
    setTimeout(fitMap, 200);
    window.addEventListener('resize', function() {
      clearTimeout(window._hrt);
      window._hrt = setTimeout(fitMap, 150);
    });

    /* ── Place city dots + labels ── */
    cities.forEach(function(c, i) {
      L.marker([c.lat, c.lng], {
        icon: makeCityIcon(c.name, i, c.lbl || ''),
        interactive: false
      }).addTo(map);
    });

    /* ════════════════════════════════════════════
       FLIGHT ROUTES + AIRPLANES
       ════════════════════════════════════════════ */

    /* Helper: find city by name */
    function city(n) {
      for (var i = 0; i < cities.length; i++) {
        if (cities[i].name === n) return cities[i];
      }
      return cities[0];
    }

    /* Flight connections (from → to, arc offset, CSS class, duration ms) */
    var flights = [
      { a: 'Dubai',     b: 'Mumbai',    off:  8, cls: 'hero-route',               dur: 4000 },
      { a: 'Dubai',     b: 'London',    off: 14, cls: 'hero-route hero-route-2',  dur: 6500 },
      { a: 'Mumbai',    b: 'Singapore', off: 10, cls: 'hero-route hero-route-3',  dur: 5200 },
      { a: 'Singapore', b: 'Bangkok',   off:  5, cls: 'hero-route',               dur: 3200 },
      { a: 'Singapore', b: 'Bali',      off: -6, cls: 'hero-route hero-route-2',  dur: 3600 },
      { a: 'Tokyo',     b: 'Sydney',    off:-14, cls: 'hero-route hero-route-3',  dur: 5800 },
      { a: 'London',    b: 'New York',  off: 12, cls: 'hero-route',               dur: 6000 },
      { a: 'Seoul',     b: 'Shanghai',  off:  5, cls: 'hero-route hero-route-2',  dur: 3000 }
    ];

    /* Quadratic Bezier curve between two lat/lng points */
    function bezier(start, end, northOff, segs) {
      var ctrl = [
        (start[0] + end[0]) / 2 + northOff,
        (start[1] + end[1]) / 2
      ];
      var pts = [];
      for (var i = 0; i <= segs; i++) {
        var t = i / segs, u = 1 - t;
        pts.push([
          u*u*start[0] + 2*u*t*ctrl[0] + t*t*end[0],
          u*u*start[1] + 2*u*t*ctrl[1] + t*t*end[1]
        ]);
      }
      return pts;
    }

    /* Airplane SVG — realistic silhouette (nose points RIGHT at 0°) */
    var planeSvg =
      '<svg class="hero-plane-svg" width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' +
        '<path transform="rotate(90 12 12)" d="M21 16v-2l-8-5V3.5A1.5 1.5 0 0 0 11.5 2 1.5 1.5 0 0 0 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z" fill="#2563eb"/>' +
      '</svg>';

    /* Draw routes + animated planes */
    var allCurves = [];
    flights.forEach(function(f) {
      var ca = city(f.a), cb = city(f.b);
      // More segments = smoother path (200 instead of 80)
      var pts = bezier([ca.lat, ca.lng], [cb.lat, cb.lng], f.off, 200);
      allCurves.push({ pts: pts, dur: f.dur });

      /* Dashed curved route line */
      L.polyline(pts, {
        color: '#2563eb',
        weight: 1.2,
        opacity: .30,
        dashArray: '8 5',
        className: f.cls,
        interactive: false
      }).addTo(map);
    });

    /* ── Smooth interpolation helpers ── */

    /* Lerp between two lat/lng pairs */
    function lerpLL(a, b, t) {
      return [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t];
    }

    /* Smooth angle — avoid jumps across ±180° */
    function lerpAngle(prev, target, factor) {
      var diff = target - prev;
      // Normalize to -180..180
      while (diff > 180) diff -= 360;
      while (diff < -180) diff += 360;
      return prev + diff * factor;
    }

    /* Animate airplanes along routes (delayed until fitBounds settles) */
    setTimeout(function() {
      allCurves.forEach(function(curve) {
        var pts = curve.pts, dur = curve.dur, total = pts.length;

        var icon = L.divIcon({
          className: 'hero-plane-wrapper',
          html: planeSvg,
          iconSize: [18, 18],
          iconAnchor: [9, 9]
        });
        var marker = L.marker(pts[0], { icon: icon, interactive: false }).addTo(map);

        var smoothAngle = 0;
        var started = false;

        (function animate() {
          var progress = (Date.now() % dur) / dur;

          /* Fractional index for smooth interpolation between points */
          var rawIdx = progress * (total - 1);
          var ci = Math.floor(rawIdx);
          var frac = rawIdx - ci;
          var ni = Math.min(ci + 1, total - 1);

          /* Interpolated position (no snapping to discrete points) */
          var pos = lerpLL(pts[ci], pts[ni], frac);
          marker.setLatLng(pos);

          /* Heading: use points a few steps apart for stable angle */
          var lookAhead = Math.min(ni + 4, total - 1);
          var p1 = map.latLngToContainerPoint(L.latLng(pos));
          var p2 = map.latLngToContainerPoint(L.latLng(pts[lookAhead]));
          var dx = p2.x - p1.x, dy = p2.y - p1.y;

          if (Math.abs(dx) > 0.1 || Math.abs(dy) > 0.1) {
            var targetAngle = Math.atan2(dy, dx) * 180 / Math.PI;
            /* Smooth the angle — blend 15% per frame to avoid jitter */
            smoothAngle = started ? lerpAngle(smoothAngle, targetAngle, 0.15) : targetAngle;
            started = true;
          }

          var el = marker.getElement();
          if (el) {
            var svg = el.querySelector('.hero-plane-svg');
            if (svg) svg.style.transform = 'rotate(' + smoothAngle.toFixed(1) + 'deg)';
          }

          requestAnimationFrame(animate);
        })();
      });
    }, 600);

  });
})();
</script>
@endsection
