@php
    $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
    $brandLogo = $logoSetting['master_value'] ?? asset('images/travos/travclicks.png');
    $coreImage = asset('images/travos/travclicks-core-reference.jpeg');
    $openLogin = $errors->any() || old('email');
@endphp
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
      $faviconSetting = \App\Helpers\CommonHelper::masterSettingsName('favicon');
  @endphp
  <link rel="icon" href="{{ $faviconSetting['master_value'] ?? '' }}" type="image/png">
  <title>Login | TravOS on Travclicks Cloud</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;650;700;750;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/travos-login.css') }}?v=1">
</head>
<body class="travos-login-page">
<svg class="symbols" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <symbol id="arrow" viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></symbol>
  <symbol id="external" viewBox="0 0 24 24"><path d="M6 18 18 6M9 6h9v9"/></symbol>
  <symbol id="mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/></symbol>
  <symbol id="lock" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v2"/></symbol>
  <symbol id="eye" viewBox="0 0 24 24"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></symbol>
  <symbol id="eye-off" viewBox="0 0 24 24"><path d="m3 3 18 18M10 6a11 11 0 0 1 2 0c6 0 10 6 10 6a19 19 0 0 1-4 4M6 7a20 20 0 0 0-4 5s4 6 10 6a11 11 0 0 0 3-.5"/></symbol>
  <symbol id="globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/></symbol>
  <symbol id="activity" viewBox="0 0 24 24"><path d="M2 12h5l3-7 4 14 3-7h5"/></symbol>
  <symbol id="phone" viewBox="0 0 24 24"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/></symbol>
</svg>

<header class="topbar">
  <div class="nav-shell">
    <a class="portal-home" href="{{ url('/') }}" aria-label="Travclicks homepage">
      <img class="header-brand" src="{{ $brandLogo }}" alt="Travclicks" width="240" height="96">
    </a>
    <span class="header-product">TravOS <span>DMC workspace</span></span>
    <button type="button" class="live-link portal-login" id="openLoginBtn" data-open-login aria-haspopup="dialog" aria-controls="workspace-login">
      DMC Log In
    </button>
  </div>
</header>

<main class="workspace portal-landing">
  <section class="intro" aria-labelledby="platform-title">
    <p class="eyebrow"><span></span> FOR DMCS, INBOUND TRAVEL AGENTS &amp; TOUR OPERATORS</p>
    <h1 id="platform-title">Run Every Moving Part <em>of a Traveler’s Journey.</em></h1>
    <p class="intro-lede">From the first enquiry to the final transfer, manage quotations, bookings, suppliers and service delivery in one DMC workspace.</p>
    <p class="cloud-byline"><span></span> Travclicks Cloud</p>
  </section>

  <figure class="operations" id="operations" aria-labelledby="operations-title">
    <div class="ops-header">
      <div><span class="ops-indicator"></span><span id="operations-title">TravOS Operations Network</span></div>
      <div class="ops-controls">
        <span class="ops-caption">ILLUSTRATIVE SYSTEM VIEW</span>
        <button id="motion-control" type="button" aria-controls="operations" hidden>Pause animation</button>
      </div>
    </div>
    <div class="system-view">
      <svg class="system-graphic" viewBox="0 0 720 306" preserveAspectRatio="none" aria-hidden="true">
        <defs>
          <pattern id="system-grid" width="24" height="24" patternUnits="userSpaceOnUse"><path d="M24 0H0V24" fill="none" stroke="#6f9bfd" stroke-opacity=".12"/></pattern>
          <radialGradient id="core-light"><stop stop-color="#4d6bff" stop-opacity=".56"/><stop offset="1" stop-color="#1c4bce" stop-opacity="0"/></radialGradient>
          <linearGradient id="trace-light"><stop stop-color="#9ba4ff"/><stop offset=".55" stop-color="#728bfc"/><stop offset="1" stop-color="#72efec"/></linearGradient>
          <path id="left-tracks" d="M136 64H217L267 115H305 M136 153H305 M136 242H217L267 191H305"/>
          <path id="right-tracks" d="M415 115H453L503 64H584 M415 153H584 M415 191H453L503 242H584"/>
        </defs>
        <rect width="720" height="306" fill="url(#system-grid)"/>
        <ellipse cx="360" cy="153" rx="222" ry="147" fill="url(#core-light)"/>
        <path class="minor-circuit" d="M40 26h183l39 35m-222 219h183l39-35M680 26H497l-39 35m222 219H497l-39-35M360 9v44m0 200v44"/>
        <g class="back-rings" fill="none" stroke="#789bfb">
          <ellipse cx="360" cy="153" rx="191" ry="75" transform="rotate(-16 360 153)"/>
          <ellipse cx="360" cy="153" rx="153" ry="116"/>
          <ellipse cx="360" cy="153" rx="125" ry="125" stroke-dasharray="2 9"/>
        </g>
        <g fill="none" stroke="url(#trace-light)">
          <use class="trace-base" href="#left-tracks"/><use class="trace-base" href="#right-tracks"/>
          <use class="trace-signal signal-in" href="#left-tracks"/><use class="trace-signal signal-out" href="#right-tracks"/>
        </g>
        <g class="reactor">
          <circle cx="360" cy="153" r="98" fill="none" stroke="#6f90f8" opacity=".31"/>
          <circle class="reactor-orbit" cx="360" cy="153" r="88" fill="none" stroke="#a5b7ff" stroke-dasharray="98 28 17 23 38 349" stroke-width="2"/>
          <circle class="reactor-orbit reverse" cx="360" cy="153" r="76" fill="none" stroke="#71ebe6" stroke-dasharray="28 13 46 12 16 12 36 314" stroke-width="2"/>
          <circle class="reactor-pulse" cx="360" cy="153" r="64" fill="none" stroke="#72e3f4" stroke-width="2"/>
        </g>
      </svg>
      <div class="system-core" aria-hidden="true">
        <svg class="brand-core" viewBox="0 0 460 470">
          <defs>
            <linearGradient id="core-pink" x1="28%" y1="5%" x2="75%" y2="100%">
              <stop stop-color="#ff95df"/><stop offset=".63" stop-color="#fc77cc"/><stop offset="1" stop-color="#ec58b5"/>
            </linearGradient>
            <linearGradient id="core-green" x1="80%" y1="9%" x2="28%" y2="100%">
              <stop stop-color="#e3ff9c"/><stop offset=".54" stop-color="#b9f691"/><stop offset="1" stop-color="#83de8b"/>
            </linearGradient>
            <filter id="blue-network-detail" color-interpolation-filters="sRGB">
              <feGaussianBlur in="SourceGraphic" stdDeviation="2.2" result="softened"/>
              <feComposite in="SourceGraphic" in2="softened" operator="arithmetic" k2="1.75" k3="-.75"/>
            </filter>
            <clipPath id="blue-edge"><circle cx="165" cy="226" r="155.4"/></clipPath>
          </defs>
          <g class="disc-beat traveller-disc">
            <g class="disc-rotation"><circle cx="315" cy="144" r="139" fill="url(#core-pink)"/></g>
          </g>
          <g class="disc-beat provider-disc">
            <g class="disc-rotation"><circle cx="312" cy="326" r="137" fill="url(#core-green)"/></g>
          </g>
          <g class="disc-beat dmc-disc">
            <g class="disc-rotation" clip-path="url(#blue-edge)">
              <image filter="url(#blue-network-detail)" href="{{ $coreImage }}" x="-309" y="-280" width="1024" height="1024"/>
              <g class="blue-pulses">
                <circle cx="111" cy="186" r="8"/><circle cx="169" cy="215" r="8"/>
                <circle cx="238" cy="210" r="8"/><circle cx="211" cy="255" r="5"/>
                <circle cx="168" cy="271" r="5"/>
              </g>
            </g>
          </g>
        </svg>
        <strong>TravOS</strong>
      </div>
      <ul class="system-modules" aria-label="Connected TravOS modules">
        <li class="module left upper"><span class="module-dot"></span>Enquiries</li>
        <li class="module left middle"><span class="module-dot"></span>Quotations</li>
        <li class="module left lower"><span class="module-dot"></span>Bookings</li>
        <li class="module right upper"><span class="module-dot"></span>Suppliers</li>
        <li class="module right middle"><span class="module-dot"></span>Dispatch</li>
        <li class="module right lower"><span class="module-dot"></span>Trip updates</li>
      </ul>
    </div>
    <ul class="core-key" aria-label="Travclicks circle colours">
      <li><span class="key-dot key-dmc"></span>DMC operators</li>
      <li><span class="key-dot key-traveller"></span>Travellers</li>
      <li><span class="key-dot key-provider"></span>On-ground service providers</li>
    </ul>
    <figcaption class="app-strip">
      <div class="connected-app">
        <span class="app-icon guest"><svg aria-hidden="true"><use href="#phone"/></svg></span>
        <div>
          <strong>TravHorse <span>GUEST APP · iOS &amp; Android</span></strong>
          <p>Itineraries, pickup alerts and on-trip support</p>
        </div>
      </div>
      <div class="connected-app">
        <span class="app-icon biz"><svg aria-hidden="true"><use href="#phone"/></svg></span>
        <div>
          <strong>TravBiz <span>SERVICE DELIVERY APP · iOS &amp; Android</span></strong>
          <p>Assigned jobs and updates for drivers, guides and service partners</p>
        </div>
      </div>
    </figcaption>
  </figure>

  <section class="cloud-platform" aria-labelledby="cloud-title">
    <div class="cloud-trust" aria-label="Travclicks Cloud platform">
      <p><svg aria-hidden="true"><use href="#lock"/></svg> Enterprise-grade security</p>
      <p><svg aria-hidden="true"><use href="#globe"/></svg> Global infrastructure</p>
      <p><svg aria-hidden="true"><use href="#activity"/></svg> Real-time operations platform</p>
    </div>

    <div class="cloud-heading">
      <p class="eyebrow"><span></span> TRAVCLICKS CLOUD</p>
      <h2 id="cloud-title">The Smart Way to <em>Run Your DMC</em></h2>
      <p>Travclicks Cloud brings suppliers, reservations, finance and operations into one secure platform so your teams move faster and serve better.</p>
    </div>
    <div class="cloud-grid">
      <article class="cloud-feature">
        <span class="cloud-number">01 / CENTRALISE</span>
        <h3>Bring your operation together</h3>
        <p>Connect the original enquiry, its quote and every confirmed service in one booking record.</p>
        <ul>
          <li>Centralize bookings, suppliers &amp; inventory</li>
          <li>Automate approvals, vouchers &amp; confirmations</li>
          <li>Real-time dashboards for ops and finance</li>
        </ul>
      </article>
      <article class="cloud-feature">
        <span class="cloud-number">02 / COORDINATE</span>
        <h3>One Platform for Faster Operations</h3>
        <p>Standardize workflows across teams, reduce manual follow-ups, and give each booking a clear owner and status.</p>
        <ul>
          <li>Role-based task flows &amp; approvals</li>
          <li>Instant vouchers and supplier communication</li>
          <li>Less rework with centralized data</li>
        </ul>
      </article>
      <article class="cloud-feature">
        <span class="cloud-number">03 / GROW</span>
        <h3>Built for Profitable Growth</h3>
        <p>Track costs and margins, respond to quotes faster, and make decisions from the same booking and finance records.</p>
        <ul>
          <li>Margin visibility from quote to invoice</li>
          <li>Accurate reporting for management</li>
          <li>Scale without increasing overhead</li>
        </ul>
      </article>
    </div>

    <section class="cloud-desk" aria-labelledby="desk-title">
      <div class="cloud-heading">
        <p class="eyebrow"><span></span> INSIDE TRAVOS</p>
        <h2 id="desk-title">One booking connects <em>every team.</em></h2>
        <p>Sales, product, operations and finance work from the same trip record, with the details each team needs to do its job.</p>
      </div>
      <div class="cloud-desk-grid">
        <article class="cloud-feature">
          <span class="cloud-number">01 / SALES</span>
          <h3>Quote and convert</h3>
          <p>Capture FIT and group enquiries, build priced itineraries, negotiate revisions with agents and track proposals through confirmation.</p>
          <ul>
            <li>AI-assisted enquiry capture and draft replies</li>
            <li>Quote revisions and approvals</li>
          </ul>
        </article>
        <article class="cloud-feature">
          <span class="cloud-number">02 / PRODUCTS</span>
          <h3>Manage rates and inventory</h3>
          <p>Maintain contracted hotels, attractions and on-ground suppliers. Build packages and manage rates, room blocks and service availability.</p>
          <ul>
            <li>Hotels, activities, vehicles and guides</li>
            <li>Supplier rates and group allotments</li>
          </ul>
        </article>
        <article class="cloud-feature">
          <span class="cloud-number">03 / OPERATIONS</span>
          <h3>Coordinate every service</h3>
          <p>Manage rooming lists and vouchers, assign jobs to drivers and guides in TravBiz, and share itinerary and pickup updates in TravHorse.</p>
          <ul>
            <li>Role-based tasks, dispatch and live updates</li>
            <li>Connected apps for guests and local teams</li>
          </ul>
        </article>
        <article class="cloud-feature">
          <span class="cloud-number">04 / FINANCE</span>
          <h3>See costs and margins</h3>
          <p>Track supplier costs, selling prices and margins across currencies. Give finance the booking and supplier details needed for invoicing and reporting.</p>
          <ul>
            <li>Quote-to-invoice margin visibility</li>
            <li>Multi-currency costing and reporting</li>
          </ul>
        </article>
      </div>
    </section>

    <div class="cloud-audience">
      <div>
        <p class="eyebrow">POWERING INBOUND TRAVEL</p>
        <h2>Built for DMCs, inbound travel agents and tour operators.</h2>
        <p>Give reservations, operations and finance a shared view of FIT and group bookings, from proposal to final service.</p>
      </div>
    </div>
  </section>
</main>

<footer class="page-footer">
  <span>TravOS <span class="footer-dot">·</span> DMC operations workspace</span>
  <span>Travclicks Cloud</span>
</footer>

<dialog id="workspace-login" class="signin-card portal-dialog" aria-labelledby="signin-title">
  <button type="button" class="portal-close" id="closeLoginBtn" aria-label="Close sign-in">&times;</button>
  <div class="card-label">
    <span class="lock-badge"><svg aria-hidden="true"><use href="#lock"/></svg></span>
    <span>TRAVCLICKS CLOUD</span>
  </div>
  <h2 id="signin-title">Sign in to Travclicks Cloud</h2>
  <p class="signin-intro">Access your organisation’s TravOS DMC workspace.</p>

  <form method="POST" action="{{ url('/login') }}" id="travosLoginForm">
    @csrf
    <label for="inputEmailAddress">Email</label>
    <div class="field @error('email') is-invalid @enderror">
      <svg aria-hidden="true"><use href="#mail"/></svg>
      <input
        id="inputEmailAddress"
        type="email"
        name="email"
        value="{{ old('email') }}"
        placeholder="Enter email"
        autocomplete="username"
        required
        autofocus
      >
    </div>
    @error('email')
      <div class="field-error" role="alert">{{ $message }}</div>
    @enderror

    <label for="inputChoosePassword">Password</label>
    <div class="field @error('password') is-invalid @enderror" id="show_hide_password">
      <svg aria-hidden="true"><use href="#lock"/></svg>
      <input
        id="inputChoosePassword"
        type="password"
        name="password"
        placeholder="Enter password"
        autocomplete="current-password"
        required
      >
      <button type="button" class="show-password" aria-label="Show password" aria-controls="inputChoosePassword">
        <svg aria-hidden="true"><use href="#eye" id="passwordEyeIcon"/></svg>
      </button>
    </div>
    @error('password')
      <div class="field-error" role="alert">{{ $message }}</div>
    @enderror

    <div class="form-meta">
      <label class="remember">
        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
        Remember me
      </label>
      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}">Forgot password?</a>
      @endif
    </div>

    <button class="submit" type="submit" id="secureLoginBtn">
      Sign in
      <svg aria-hidden="true"><use href="#arrow"/></svg>
    </button>
  </form>

  <div class="card-footer">
    <span>
      <svg aria-hidden="true"><use href="#lock"/></svg>
      Protected by enterprise-grade encryption &amp; secure authentication.
    </span>
  </div>
</dialog>

<script>
  (function () {
    var dialog = document.getElementById('workspace-login');
    var openBtn = document.getElementById('openLoginBtn');
    var closeBtn = document.getElementById('closeLoginBtn');
    var shouldOpen = @json($openLogin);
    var ops = document.getElementById('operations');
    var motionBtn = document.getElementById('motion-control');

    function openLogin() {
      if (!dialog) return;
      if (typeof dialog.showModal === 'function') {
        if (!dialog.open) dialog.showModal();
      } else {
        dialog.setAttribute('open', '');
      }
      var email = document.getElementById('inputEmailAddress');
      if (email) setTimeout(function () { email.focus(); }, 40);
    }

    function closeLogin() {
      if (!dialog) return;
      if (typeof dialog.close === 'function') dialog.close();
      else dialog.removeAttribute('open');
    }

    if (openBtn) openBtn.addEventListener('click', function (e) {
      e.preventDefault();
      openLogin();
    });
    if (closeBtn) closeBtn.addEventListener('click', closeLogin);
    if (dialog) {
      dialog.addEventListener('click', function (e) {
        if (e.target === dialog) closeLogin();
      });
    }
    if (shouldOpen) openLogin();

    var eyeBtn = document.querySelector('#show_hide_password .show-password');
    if (eyeBtn) {
      eyeBtn.addEventListener('click', function () {
        var input = document.getElementById('inputChoosePassword');
        var icon = document.getElementById('passwordEyeIcon');
        if (!input) return;
        var showing = input.getAttribute('type') === 'text';
        input.setAttribute('type', showing ? 'password' : 'text');
        if (icon) icon.setAttribute('href', showing ? '#eye' : '#eye-off');
        eyeBtn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
      });
    }

    var form = document.getElementById('travosLoginForm');
    var submitBtn = document.getElementById('secureLoginBtn');
    if (form && submitBtn) {
      form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in…';
      });
    }

    if (ops && motionBtn && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      motionBtn.hidden = false;
      motionBtn.addEventListener('click', function () {
        var paused = ops.hasAttribute('data-paused');
        if (paused) {
          ops.removeAttribute('data-paused');
          motionBtn.textContent = 'Pause animation';
        } else {
          ops.setAttribute('data-paused', '');
          motionBtn.textContent = 'Play animation';
        }
      });
    }
  })();
</script>
</body>
</html>
