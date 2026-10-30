@extends('layouts.layout')

@section('title', 'Stripe Merchant Account')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Finance /</span> Stripe Merchant Account
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 col-xl-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $travclicksLogo ?? asset('assets/images/logo-dark.png') }}"
                             alt="Travclicks"
                             style="height: 36px; width: auto; object-fit: contain;">
                        <h5 class="mb-0">Connect your Stripe account</h5>
                    </div>
                    @if($ready)
                        <span class="badge bg-success">Charges enabled</span>
                    @elseif(!empty($account['stripe_account_id']))
                        <span class="badge bg-warning text-dark">Onboarding incomplete</span>
                    @else
                        <span class="badge bg-secondary">Not connected</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="mb-3 text-muted">
                        Link your Stripe merchant account to <strong>Travclicks</strong>.
                        Pick your real <strong>business country</strong> (not USA unless that is where you operate).
                        Currency follows the country you select.
                    </p>

                    <div class="border rounded p-3 mb-3">
                        <div class="row g-2 small">
                            <div class="col-sm-6"><span class="text-muted">Stripe mode</span><div class="fw-semibold">{{ strtoupper($settings['stripe_mode'] ?? 'test') }}</div></div>
                            <div class="col-sm-6"><span class="text-muted">Onboarding</span><div class="fw-semibold">{{ $account['onboarding_status'] ?? 'not_started' }}</div></div>
                            <div class="col-sm-6"><span class="text-muted">Business country</span><div class="fw-semibold">{{ !empty($account['country_code']) ? strtoupper($account['country_code']) : '—' }}</div></div>
                            <div class="col-sm-6"><span class="text-muted">Base currency</span><div class="fw-semibold">{{ !empty($account['default_currency']) ? strtoupper($account['default_currency']) : '—' }}</div></div>
                            <div class="col-sm-6"><span class="text-muted">Stripe account</span><div class="fw-semibold">{{ $account['stripe_account_id'] ?? '—' }}</div></div>
                            <div class="col-sm-6"><span class="text-muted">Last synced</span><div class="fw-semibold">{{ $account['last_synced_at'] ?? '—' }}</div></div>
                            <div class="col-sm-6"><span class="text-muted">Charges enabled</span><div class="fw-semibold">{{ !empty($account['charges_enabled']) ? 'Yes' : 'No' }}</div></div>
                            <div class="col-sm-6"><span class="text-muted">Payouts enabled</span><div class="fw-semibold">{{ !empty($account['payouts_enabled']) ? 'Yes' : 'No' }}</div></div>
                        </div>
                    </div>

                    @if(empty($settings['online_enabled']))
                        <div class="alert alert-info mb-3">
                            Platform <strong>Online card</strong> is currently disabled in Travclicks Payment Settings.
                            You can still complete Connect onboarding; card checkout stays hidden until Travclicks enables Online.
                        </div>
                    @endif

                    @php
                        $countryLocked = !empty($ready);
                        $selectedCountry = old('country_code', strtolower((string) ($account['country_code'] ?? '')));
                        // Do not default to US — leave blank so DMC must choose.
                        if ($selectedCountry === 'us' && empty($account['stripe_account_id']) && ! old('country_code')) {
                            $selectedCountry = '';
                        }
                    @endphp

                    <form method="POST" action="{{ route('finance.stripe.connect.start') }}" class="mb-0">
                        @csrf

                        <div class="mb-3">
                            <label for="country_code" class="form-label fw-semibold">Business country / address country <span class="text-danger">*</span></label>
                            <select name="country_code" id="country_code" class="form-select"
                                    {{ $countryLocked ? 'disabled' : 'required' }}>
                                <option value="">Select your business country</option>
                                @foreach(($countries ?? []) as $country)
                                    <option value="{{ strtolower($country->code) }}"
                                        data-currency="{{ $country->currency }}"
                                        {{ $selectedCountry === strtolower($country->code) ? 'selected' : '' }}>
                                        {{ $country->name }} ({{ $country->code }})
                                        @if(!empty($country->currency)) — {{ $country->currency }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($countryLocked)
                                <input type="hidden" name="country_code" value="{{ strtolower((string) $account['country_code']) }}">
                                <small class="text-muted">Country is locked after charges are enabled.</small>
                            @else
                                <small class="text-muted">
                                    Choose any country where your business is based. Changing country before charges are enabled
                                    creates a new Stripe onboarding for that country (address is not forced to USA).
                                </small>
                            @endif
                            <div class="small mt-1">
                                Selected currency: <strong id="selected-currency">{{ !empty($account['default_currency']) ? strtoupper($account['default_currency']) : '—' }}</strong>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button type="submit" class="btn btn-primary" {{ $countryLocked ? '' : '' }}>
                                <i class="ri-external-link-line me-1"></i>
                                {{ !empty($account['stripe_account_id']) ? 'Continue Stripe onboarding' : 'Connect Stripe merchant account' }}
                            </button>

                            @if(!empty($account['stripe_account_id']))
                                <a href="{{ route('finance.stripe.connect.return') }}" class="btn btn-outline-secondary">
                                    <i class="ri-refresh-line me-1"></i>Refresh status
                                </a>
                            @endif
                        </div>
                    </form>

                    @if(!empty($account['stripe_account_id']) && empty($ready))
                        <form method="POST" action="{{ route('finance.stripe.connect.reset-country') }}" class="mt-3"
                              onsubmit="return confirm('Clear the current Stripe link so you can choose a different business country?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="ri-map-pin-change-line me-1"></i>Change business country
                            </button>
                            <small class="text-muted d-block mt-1">
                                Use this if Stripe opened with the wrong country (e.g. USA). Then select the correct country and connect again.
                            </small>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body small text-muted">
                    <h6 class="text-dark">Notes</h6>
                    <ul class="mb-0">
                        <li>Business address/country is chosen by you — not fixed to USA.</li>
                        <li>Onboarding is branded with the Travclicks logo.</li>
                        <li>You never enter card numbers in Travclicks — Stripe hosts onboarding and bank details.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('country_code');
    var label = document.getElementById('selected-currency');
    if (!select || !label || select.disabled) return;

    function syncCurrency() {
        var opt = select.options[select.selectedIndex];
        var currency = opt && opt.getAttribute('data-currency') ? opt.getAttribute('data-currency') : '';
        label.textContent = currency ? currency.toUpperCase() : '—';
    }

    select.addEventListener('change', syncCurrency);
    syncCurrency();
});
</script>
@endsection
