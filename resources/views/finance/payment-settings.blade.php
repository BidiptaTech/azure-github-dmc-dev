@extends('layouts.layout')

@section('title', 'Payment Settings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Finance /</span> Payment Settings
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 col-xl-7">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="ri-bank-card-line me-2 text-primary"></i>Travclicks Payment Methods
                    </h5>
                    <span class="badge bg-label-secondary">Platform admin</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payment-service.settings.save') }}">
                        @csrf

                        <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3 bg-light">
                            <div class="me-3">
                                <div class="fw-semibold">Offline payments</div>
                                <small class="text-muted">Cash, Cheque, Bank Transfer — always available. Cannot be disabled.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <input type="hidden" name="offline_enabled" value="1">
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3">
                            <div class="me-3">
                                <div class="fw-semibold">Wallet payments</div>
                                <small class="text-muted">Agent DMC-scoped wallet debit on booking payment. History stays intact if turned off.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="wallet_enabled" name="wallet_enabled" value="1"
                                       {{ !empty($settings['wallet_enabled']) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3">
                            <div class="me-3">
                                <div class="fw-semibold">Online card (Stripe)</div>
                                <small class="text-muted">
                                    Disabled by default. Requires Stripe keys from <code>.env</code>.
                                    @if(empty($settings['stripe_configured']))
                                        <span class="text-danger d-block mt-1">Stripe is not configured — set <code>STRIPE_PUBLIC_KEY</code>, <code>STRIPE_SECRET_KEY</code>, and <code>STRIPE_MODE</code> in <code>.env</code>.</span>
                                    @endif
                                </small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="online_enabled" name="online_enabled" value="1"
                                       {{ !empty($settings['online_enabled']) ? 'checked' : '' }}
                                       {{ empty($settings['stripe_configured']) && empty($settings['online_enabled']) ? 'disabled' : '' }}>
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-4 bg-light">
                            <div class="fw-semibold mb-2">Stripe credentials <span class="badge bg-label-secondary">from .env</span></div>
                            <div class="row g-2 small">
                                <div class="col-sm-4"><span class="text-muted">STRIPE_MODE</span><div class="fw-semibold font-monospace">{{ strtoupper($settings['stripe_mode'] ?? 'test') }}</div></div>
                                <div class="col-sm-8"><span class="text-muted">STRIPE_PUBLIC_KEY</span><div class="fw-semibold font-monospace text-break">{{ $settings['stripe_publishable_key_masked'] ?? '(not set)' }}</div></div>
                                <div class="col-sm-12"><span class="text-muted">STRIPE_SECRET_KEY</span><div class="fw-semibold font-monospace text-break">{{ $settings['stripe_secret_key_masked'] ?? '(not set)' }}</div></div>
                            </div>
                            <small class="text-muted d-block mt-2">Change keys only in server <code>.env</code>, then run <code>php artisan config:clear</code>. They are not stored or editable here.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>Save settings
                            </button>
                            <a href="{{ route('finance.wallet.index') }}" class="btn btn-outline-secondary">Wallet management</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2">Current status</h6>
                    <ul class="mb-0 small text-muted">
                        <li>Offline: <strong class="text-success">Always on</strong></li>
                        <li>Wallet: <strong>{{ !empty($settings['wallet_enabled']) ? 'Enabled' : 'Disabled' }}</strong></li>
                        <li>Online: <strong>{{ !empty($settings['online_enabled']) ? 'Enabled' : 'Disabled' }}</strong></li>
                        <li>Stripe (.env): <strong>{{ strtoupper($settings['stripe_mode'] ?? 'test') }}</strong>
                            — {{ !empty($settings['stripe_configured']) ? 'keys OK' : 'keys missing / mode mismatch' }}</li>
                        @if(!empty($settings['updated_at']))
                            <li>Last updated: {{ $settings['updated_at'] }}
                                @if(!empty($settings['updated_by'])) by {{ $settings['updated_by'] }} @endif
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
