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
                  <a class="topbar-link dropdown-toggle d-flex align-items-center position-relative" 
                     data-bs-toggle="dropdown" 
                     aria-expanded="false"
                     style="padding: 8px 12px; border-radius: 12px; transition: all 0.3s ease; border: 1px solid transparent;">
                      
                      <!-- User Avatar with Status Indicator -->
                      <div class="position-relative me-3">
                          <img src="{{ env('APP_URL') . '/assets/images/users/avatar-1.jpg' }}" 
                               width="42" height="42" 
                               class="rounded-circle shadow-sm border border-2 border-primary" 
                               alt="user-image"
                               style="object-fit: cover;">
                          <!-- Online Status Indicator -->
                          <div class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" 
                               style="width: 12px; height: 12px;"></div>
                      </div>
                      
                      <!-- User Info -->
                      <div class="d-lg-flex flex-column d-none me-2">
                          <div class="d-flex align-items-center gap-1">
                              <span class="fw-bold text-dark" style="font-size: 0.95rem; line-height: 1.2;">
                                  {{ Auth::user()->name }}
                              </span>
                              <!-- Role Badge -->
                              <span class="badge bg-primary-subtle text-primary" style="font-size: 0.7rem; padding: 2px 6px;">
                                  {{ Auth::user()->role->name ?? 'User' }}
                              </span>
                          </div>
                          <div class="d-flex align-items-center gap-1">
                              <i class="ri-building-line text-muted" style="font-size: 0.75rem;"></i>
                              <span class="text-muted" style="font-size: 0.8rem; line-height: 1.2;">
                                  {{ Auth::user()->company_name ?? 'No Company' }}
                              </span>
                          </div>
                      </div>
                      
                      <!-- Dropdown Arrow with Animation -->
                      {{-- <i class="ri-arrow-down-s-line ms-1 d-none d-sm-flex text-muted" 
                         style="font-size: 1.1rem; transition: transform 0.3s ease;"></i> --}}
                  </a>

                  <!-- Enhanced Dropdown Menu -->
                  <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-0" 
                       style="min-width: 300px; border-radius: 16px; overflow: hidden;">
                      
                      <!-- User Profile Header -->
                      <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                          <div class="d-flex align-items-center">
                              <img src="{{ env('APP_URL') . '/assets/images/users/avatar-1.jpg' }}" 
                                   width="60" height="60" 
                                   class="rounded-circle shadow border border-3 border-white me-3" 
                                   alt="user-image"
                                   style="object-fit: cover;">
                              <div class="flex-grow-1">
                                  <h6 class="mb-1 fw-bold">{{ Auth::user()->name }}</h6>
                                  <p class="mb-1 opacity-75" style="font-size: 0.9rem;">
                                      {{ Auth::user()->company_name ?? 'No Company' }}
                                  </p>
                                  <small class="opacity-75">
                                      <i class="ri-mail-line me-1"></i>
                                      {{ Auth::user()->email }}
                                  </small>
                              </div>
                          </div>
                      </div>

                      <!-- Wallet Balance Card -->
                      @php
                          $credits = \App\Models\Transaction::where('user_id', Auth::id())->sum('amount');
                          $debits = \App\Models\Transaction::where('credited_from', Auth::id())->sum('amount');
                          $walletBalance = $credits - $debits;
                      @endphp
                      @if(Auth::id() != 1)
                      <div class="p-3 border-bottom">
                          <!-- <div class="d-flex align-items-center justify-content-between p-3 rounded-3" 
                               style="background: linear-gradient(135deg, #777dd4 0%, #5ad888 100%); color: white;">
                              <div class="d-flex align-items-center">
                                  <div>
                                      <div class="fw-bold">Wallet Balance</div>
                                      <small class="opacity-75">Available Funds</small>
                                  </div>
                              </div>
                              <div class="text-end">
                                  <div class="fw-bold fs-5">₹{{ number_format($walletBalance, 2) }}</div>
                                  <small class="opacity-75">{{ $walletBalance >= 0 ? 'Positive' : 'Negative' }}</small>
                              </div>
                          </div> -->
                      </div>
                      @endif

                      <!-- Quick Actions -->
                      <div class="p-3">
                          <!-- Account Management -->
                          {{-- <div class="row g-2 mb-3">
                              <div class="col-6">
                                  <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center p-3 rounded-3 border" 
                                     style="transition: all 0.3s ease;">
                                      <i class="ri-user-settings-line text-primary me-2" style="font-size: 1.1rem;"></i>
                                      <div>
                                          <div class="fw-semibold">Profile</div>
                                          <small class="text-muted">Edit Account</small>
                                      </div>
                                  </a>
                              </div>
                              <div class="col-6">
                                  <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center p-3 rounded-3 border" 
                                     style="transition: all 0.3s ease;">
                                      <i class="ri-shield-keyhole-line text-warning me-2" style="font-size: 1.1rem;"></i>
                                      <div>
                                          <div class="fw-semibold">Security</div>
                                          <small class="text-muted">Change Password</small>
                                      </div>
                                  </a>
                              </div>
                          </div> --}}

                          <!-- Back to Admin Button -->
                          @if(session()->has('login_stack') && count(session('login_stack')) > 0)
                          <div class="mb-3">
                              <a href="{{ route('admin.revertPreviousUser') }}" 
                                 class="dropdown-item d-flex align-items-center justify-content-center gap-2 p-3 rounded-3 border border-primary text-primary" 
                                 style="transition: all 0.3s ease;">
                                  <i class="ri-arrow-go-back-line"></i>
                                  <span class="fw-semibold">Back to Previous User</span>
                              </a>
                          </div>
                          @endif

                          <!-- Sign Out Button -->
                          <form method="POST" action="{{ route('logout') }}" class="d-block">
                              @csrf
                              <button type="submit" 
                                      class="dropdown-item btn btn-danger d-flex align-items-center justify-content-center gap-2 w-100 p-3 rounded-3" 
                                      style="transition: all 0.3s ease;">
                                  <i class="ri-logout-box-r-line"></i>
                                  <span class="fw-semibold">Sign Out</span>
                              </button>
                          </form>
                      </div>
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

<style>
.topbar-link:hover {
    background-color: rgba(0,0,0,0.05) !important;
    border-color: rgba(0,0,0,0.1) !important;
}

.dropdown-item:hover {
    background-color: rgba(0,0,0,0.05) !important;
    transform: translateY(-1px);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.dropdown-menu {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>