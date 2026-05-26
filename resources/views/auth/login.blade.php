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
  /* Match left/right visual rhythm */
  .auth-hero{
    border-radius: 1rem;
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
     Hero panel — static branded panel (no animation)
     ═══════════════════════════════════════════ */
  .auth-hero{
    background: radial-gradient(1200px 700px at 20% 0%, rgba(37,99,235,.25) 0%, rgba(37,99,235,0) 60%),
                radial-gradient(900px 600px at 90% 20%, rgba(79,70,229,.18) 0%, rgba(79,70,229,0) 62%),
                linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
    box-shadow: inset 0 0 0 1px rgba(0,0,0,.06), 0 18px 45px rgba(0,0,0,.08);
  }
  .auth-hero-bg{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    pointer-events: none;
  }
  .auth-hero-bg svg{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: .92;
    filter: saturate(.95) contrast(1.12);
  }

  /* Subtle vignette (light theme) */
  .auth-hero-gradient{
    position: absolute; inset: 0; z-index: 2; pointer-events: none;
    background:
      linear-gradient(180deg,
        rgba(255,255,255,.55) 0%, rgba(255,255,255,0) 18%,
        rgba(255,255,255,0) 78%, rgba(255,255,255,.65) 100%),
      radial-gradient(900px 520px at 50% 30%, rgba(255,255,255,.35) 0%, rgba(255,255,255,0) 70%);
  }

  /* Hero right-panel copy (carousel, static motion) */
  .hero-copy{
    position: relative;
    z-index: 3;
    padding: 2.75rem 2.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    color: #fff;
  }
  .hero-copy::before{
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1;
    /* Match brand colors (same family as .auth-btn) */
    background: linear-gradient(90deg,
      rgba(29,78,216,.88) 0%,
      rgba(37,99,235,.58) 54%,
      rgba(79,70,229,0) 100%);
  }
  .hero-kicker{
    font-size: 1.9rem;
    font-weight: 300;
    letter-spacing: .2px;
    opacity: .95;
    margin: 0 0 .35rem 0;
  }
  .hero-headline{
    font-size: 3.1rem;
    font-weight: 800;
    letter-spacing: .2px;
    line-height: 1.05;
    margin: 0 0 1.1rem 0;
  }
  .hero-desc{
    max-width: 520px;
    font-size: .98rem;
    line-height: 1.55;
    opacity: .92;
    margin: 0 0 1.6rem 0;
  }
  .hero-pills{
    display: flex;
    flex-direction: column;
    gap: .85rem;
    margin-top: .25rem;
  }
  .hero-pill{
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    padding: .65rem 1.05rem;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.14);
    backdrop-filter: blur(8px);
    box-shadow: 0 10px 28px rgba(0,0,0,.18);
    font-weight: 700;
    letter-spacing: .2px;
    color: #fff;
    max-width: 560px;
  }
  .hero-pill-dot{
    width: 10px; height: 10px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,.85);
    background: rgba(255,255,255,.10);
    flex: 0 0 auto;
  }
  .hero-dots{
    display: flex;
    gap: .45rem;
    margin-top: 2.3rem;
    align-items: center;
  }
  .hero-dots span{
    height: 4px;
    width: 18px;
    border-radius: 999px;
    background: rgba(255,255,255,.28);
  }
  .hero-dots span.is-active{
    width: 72px;
    background: rgba(255,255,255,.78);
  }

  /* Bootstrap carousel tweaks */
  .hero-carousel{ width: 100%; }
  .hero-carousel .carousel-inner{
    width: 100%;
    position: relative;
    overflow: hidden;
    /* Let the hero height be driven by the card, not the slide content */
    min-height: 0;
  }
  .hero-carousel .carousel-item{
    padding-right: 1rem;
  }
  /* Prevent text overlap during transitions (show only active + transition targets) */
  .hero-carousel .carousel-item{ display: none; }
  .hero-carousel .carousel-item.active,
  .hero-carousel .carousel-item-next,
  .hero-carousel .carousel-item-prev{ display: block; }
  .hero-carousel .carousel-indicators{
    position: static;
    margin: 2.3rem 0 0 0;
    justify-content: flex-start;
    gap: .45rem;
  }
  .hero-carousel .carousel-indicators [data-bs-target]{
    height: 4px;
    width: 18px;
    border-radius: 999px;
    background-color: rgba(255,255,255,.28);
    opacity: 1;
    border: 0;
    margin: 0;
  }
  .hero-carousel .carousel-indicators .active{
    width: 72px;
    background-color: rgba(255,255,255,.78);
  }

  /* Bottom overlay text (Powering DMC...) */
  .hero-bottom-text{
    background: linear-gradient(0deg, rgba(15,23,42,.62) 0%, rgba(15,23,42,.25) 55%, transparent 100%);
  }
  .hero-tagline{
    font-size: .80rem;
    font-weight: 700;
    color: rgba(255,255,255,.95);
    letter-spacing: .5px;
  }
  .hero-subtitle{
    font-size: .68rem;
    font-weight: 700;
    color: rgba(255,255,255,.92);
    letter-spacing: .9px;
    text-transform: uppercase;
    margin-top: 2px;
  }

  @media (max-width: 1200px){
    .hero-copy{ padding: 2.75rem 2.4rem; }
    .hero-kicker{ font-size: 1.65rem; }
    .hero-headline{ font-size: 2.6rem; }
  }
  @media (max-width: 992px){
    /* Right panel is hidden on <lg, but keep sane defaults */
    .hero-copy{ padding: 2.25rem 2rem; }
  }
  .hero-map-attr{
    position: absolute; bottom: 3px; right: 6px; font-size: 7px;
    color: rgba(0,0,0,.18); z-index: 4; pointer-events: none;
  }

  /* ═══════════════════════════════════════════
     Secure Login glow card
     ═══════════════════════════════════════════ */
  .auth-login-card-wrap{
    margin-top: 1.75rem;
  }
  @media (min-width: 992px){
    .auth-login-card-wrap{
      margin-top: 2.35rem;
    }
  }
  .auth-benefits-list{
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    font-size: .78rem;
    line-height: 1.35;
    color: rgba(15,23,42,.62);
    letter-spacing: .15px;
  }
  .auth-benefits-list i{ font-size: .72rem; }
  .auth-protected-note{
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    font-size: .74rem;
    line-height: 1.35;
    color: rgba(15,23,42,.48);
    letter-spacing: .15px;
    font-style: italic;
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

          <div class="border rounded-3 p-2 auth-login-card-wrap auth-login-glow-card">
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

      {{-- ═══ RIGHT PANEL: Static branded hero (no animation) ═══ --}}
      <div class="col-lg-7 d-lg-flex d-none">
        <div class="p-2 w-100 d-flex flex-column text-white position-relative overflow-hidden">
          <div class="p-0 rounded-4 w-100 d-flex flex-column text-white position-relative overflow-hidden auth-hero">
          <div class="auth-hero-bg" aria-hidden="true">
            <svg viewBox="0 0 1200 700" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
                  <stop offset="0" stop-color="#1d4ed8" stop-opacity=".34"/>
                  <stop offset=".55" stop-color="#2563eb" stop-opacity=".22"/>
                  <stop offset="1" stop-color="#4f46e5" stop-opacity=".26"/>
                </linearGradient>
                <radialGradient id="g2" cx=".5" cy=".35" r=".75">
                  <stop offset="0" stop-color="#dbeafe" stop-opacity=".75"/>
                  <stop offset="1" stop-color="#eef2ff" stop-opacity="0"/>
                </radialGradient>
              </defs>

              <rect width="1200" height="700" fill="url(#g2)"/>

              <g fill="none" stroke="url(#g1)" stroke-width="2" opacity=".9">
                <path d="M40,520 C260,360 420,360 620,500 S980,650 1160,420" />
                <path d="M90,300 C260,120 460,120 630,250 S940,420 1130,210" opacity=".8"/>
                <path d="M140,620 C340,520 520,520 710,610 S980,690 1120,560" opacity=".65"/>
                <path d="M120,420 C330,310 520,310 690,410 S980,560 1100,390" opacity=".55"/>
              </g>

              <g fill="#2563eb" opacity=".35">
                <circle cx="240" cy="390" r="5"/>
                <circle cx="360" cy="270" r="4.5"/>
                <circle cx="520" cy="320" r="4.5"/>
                <circle cx="650" cy="410" r="5"/>
                <circle cx="820" cy="460" r="4.5"/>
                <circle cx="980" cy="310" r="4.5"/>
              </g>
            </svg>
          </div>
          <div class="auth-hero-gradient"></div>

          <div class="hero-copy">
            <div id="loginHeroCarousel" class="carousel slide carousel-fade hero-carousel" data-bs-ride="carousel" data-bs-interval="5500" data-bs-pause="false">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <div class="hero-kicker">The Smart Way to</div>
                  <div class="hero-headline">Run Your DMC</div>
                  <p class="hero-desc">
                    Travclicks Cloud brings suppliers, reservations, finance, and operations into one secure platform—so your teams move faster and serve better.
                  </p>
                  <div class="hero-pills" role="list" aria-label="Key platform benefits">
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Centralize bookings, suppliers &amp; inventory</div>
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Automate approvals, vouchers &amp; confirmations</div>
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Real-time dashboards for ops and finance</div>
                  </div>
                </div>

                <div class="carousel-item">
                  <div class="hero-kicker">One Platform for</div>
                  <div class="hero-headline">Faster Operations</div>
                  <p class="hero-desc">
                    Standardize workflows across teams, reduce manual follow-ups, and keep every booking moving with clear accountability and visibility.
                  </p>
                  <div class="hero-pills" role="list" aria-label="Operational value">
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Role-based task flows &amp; approvals</div>
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Instant vouchers and supplier communication</div>
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Less rework with centralized data</div>
                  </div>
                </div>

                <div class="carousel-item">
                  <div class="hero-kicker">Built for</div>
                  <div class="hero-headline">Profitable Growth</div>
                  <p class="hero-desc">
                    Track costs and margins, improve conversion with faster turnaround, and make better decisions using one source of truth.
                  </p>
                  <div class="hero-pills" role="list" aria-label="Growth outcomes">
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Margin visibility from quote to invoice</div>
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Accurate reporting for management</div>
                    <div class="hero-pill" role="listitem"><span class="hero-pill-dot"></span>Scale without increasing overhead</div>
                  </div>
                </div>
              </div>

              <div class="carousel-indicators" aria-hidden="true">
                <button type="button" data-bs-target="#loginHeroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#loginHeroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#loginHeroCarousel" data-bs-slide-to="2"></button>
              </div>
            </div>
          </div>

          {{-- Bottom overlay text --}}
          <div class="position-absolute w-100 px-3 pb-3 hero-bottom-text" style="bottom: 0; z-index: 4;">
            <p class="mb-0 text-center hero-tagline">Powering DMC's &nbsp;&middot;&nbsp; Travel Agents &nbsp;&middot;&nbsp; Global Travel Operations</p>
            <p class="mb-0 text-center hero-subtitle">One Unified Operating System</p>
          </div>

          <div class="hero-map-attr">Built for global travel operations</div>
        </div>
        </div>
      </div>

    </div>
  </div>
</div>

@endsection
