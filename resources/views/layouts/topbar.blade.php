<!-- Layout container -->
<div class="layout-page">
<!-- Navbar -->
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0   d-xl-none ">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
          <i class="ri-menu-fill ri-22px"></i>
        </a>
      </div>
      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        {{-- <div class="navbar-nav align-items-center">
          <div class="nav-item navbar-search-wrapper mb-0">
            <a class="nav-item nav-link search-toggler fw-normal px-0" href="javascript:void(0);">
              <i class="ri-search-line ri-22px scaleX-n1-rtl me-3"></i>
              <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
            </a>
          </div>
        </div> --}}
        <!-- /Search -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">
          <div class="topbar-item nav-user">
              <div class="dropdown">
                  <a class="topbar-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ env('APP_URL') . '/assets/images/users/avatar-1.jpg' }}" width="32" class="rounded-circle me-2" alt="user-image">
                      <span class="d-lg-flex flex-column d-none">
                          <span class="fw-bold text-dark">{{ Auth::user()->name }}</span>
                      </span>
                      <i data-lucide="chevron-down" class="ms-1 d-none d-sm-flex" height="12"></i>
                  </a>

                  <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-3">
                      <!-- Welcome Header -->
                      <div class="dropdown-header text-center mb-3">
                          <h6 class="text-muted m-0">Welcome, {{ Auth::user()->name }}!</h6>
                      </div>

                      <!-- Account Link -->
                      <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
                          <i class="mdi mdi-account-circle-outline me-2 font-18"></i>
                          <span style="margin-left: 32px">My Account</span>
                      </a>

                      @php
                          $credits = \App\Models\Transaction::where('user_id', Auth::id())->sum('amount');
                          $debits = \App\Models\Transaction::where('credited_from', Auth::id())->sum('amount');
                          $walletBalance = $credits - $debits;
                      @endphp

                      <!-- Wallet Balance Display (Non-Admin Users) -->
                      @if(Auth::id() != 1)
                      <div class="text-center mb-2">
                          <h6 class="text-muted mb-0 d-inline-flex align-items-center gap-2">
                              <span>Balance: ₹{{ number_format($walletBalance, 2) }}</span>
                          </h6>
                      </div>
                      @endif

                      <hr class="dropdown-divider my-2">

                      <!-- Back to Admin Button (if session 'from_admin' is active) -->
                      @if(session()->has('login_stack') && count(session('login_stack')) > 0)
                          <a href="{{ route('admin.revertPreviousUser') }}" class="dropdown-item d-flex align-items-center justify-content-center gap-2 mb-2 btn btn-sm btn-outline-secondary">
                              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-primary">
                                  <path d="M19 12H5M12 5l-7 7 7 7"></path>
                              </svg>
                              Back to Previous User
                          </a>
                      @endif

                      <!-- Sign Out Button -->
                      <form method="POST" action="{{ route('logout') }}" class="d-block">
                          @csrf
                          <button type="submit" class="dropdown-item btn btn-sm btn-danger d-flex align-items-center justify-content-center gap-1 w-100">
                              <i class="material-icons-outlined" style="font-size: 13px;">Sign Out</i>
                          </button>
                      </form>
                  </div>
              </div>
          </div>
        </ul>

      </div>
      <!-- Search Small Screens -->
      <div class="navbar-search-wrapper search-input-wrapper  d-none">
        <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search..." aria-label="Search...">
        <i class="ri-close-fill search-toggler cursor-pointer"></i>
      </div>
</nav>
<!-- / Navbar -->