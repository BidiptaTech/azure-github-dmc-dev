@extends('layouts.layout')

@section('title', 'Agent Wallet & Tour — ' . ($agent->name ?? 'Agent'))

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('agents.index') }}">TA Contacts</a></li>
                        <li class="breadcrumb-item active">Wallet & Tour</li>
                    </ol>
                </nav>
                <h4 class="mb-1">{{ $agent->name }}</h4>
                <p class="text-muted mb-0">
                    @if($agency)
                        {{ $agency->agency_name ?? $agency->company_name ?? 'Agency' }}
                        &middot;
                    @endif
                    Agent wallet &mdash; finance microservice (Python)
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @if(!empty($availableCurrencies) && count($availableCurrencies) > 1)
                    <div class="btn-group btn-group-sm">
                        @foreach($availableCurrencies as $curr)
                            <a href="{{ route('finance.agent.dashboard', ['agent' => $agent->agent_id, 'currency' => $curr]) }}"
                               class="btn {{ $currency === $curr ? 'btn-primary' : 'btn-outline-primary' }}">{{ $curr }}</a>
                        @endforeach
                    </div>
                @endif
                <a href="{{ route('agents.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Back to Agents
                </a>
                <a href="{{ route('finance.agent.transactions', array_filter(['agent' => $agent->agent_id, 'currency' => $currency ?? null])) }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-pulse-line me-1"></i> Transactions
                </a>
                @if(!empty($canCreateTour) && $canProForm)
                    <a href="{{ route('finance.agent.initialize-tour', $agent->agent_id) }}" class="btn btn-primary btn-sm">
                        <i class="ri-file-list-3-line me-1"></i> Create Tour (Pro)
                    </a>
                @endif
                @if(!empty($canCreateTour) && $canLiteForm)
                    <a href="{{ route('single-tour-package.create', ['agent_id' => $agent->agent_id, 'agency_id' => $agent->agency_id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri-route-line me-1"></i> Create Tour (Lite)
                    </a>
                @endif
            </div>
        </div>

        @if(empty($walletExists))
            <div class="alert alert-warning mb-4">
                <strong>No wallet found for {{ $currency }}.</strong>
                A DMC user must allocate prepaid funds to this agent first. Creating a tour enquiry does not credit or debit the wallet until the booking is confirmed with <code>payment_mode: wallet</code>.
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                            <i class="ri-wallet-3-line ri-xl"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Available Balance</div>
                            <div class="fs-4 fw-bold">{{ $currency }} {{ number_format((float)($wallet->available_balance ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                            <i class="ri-lock-line ri-xl"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Reserved</div>
                            <div class="fs-4 fw-bold">{{ $currency }} {{ number_format((float)($wallet->reserved_balance ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                            <i class="ri-arrow-down-circle-line ri-xl"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Credited</div>
                            <div class="fs-5 fw-semibold">{{ number_format((float)($wallet->total_credited ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                            <i class="ri-arrow-up-circle-line ri-xl"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Debited</div>
                            <div class="fs-5 fw-semibold">{{ number_format((float)($wallet->total_debited ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @if($canAllocate)
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="ri-exchange-dollar-line me-1"></i> Allocate to Agent Wallet</h5>
                            <small class="text-muted">Calls Python finance service <code>/wallet/allocate</code></small>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('finance.agent.allocate', $agent->agent_id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Amount</label>
                                    <input type="number" name="topup_amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00" value="{{ old('topup_amount') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Currency</label>
                                    @php
                                        $currencyOptions = !empty($availableCurrencies) ? $availableCurrencies : array_filter([$defaultCurrency ?? $currency ?? 'SGD']);
                                        $selectedCurrency = old('wallet_currency_code', $defaultCurrency ?? $currency ?? ($currencyOptions[0] ?? 'SGD'));
                                    @endphp
                                    <select name="wallet_currency_code" class="form-select" required>
                                        @foreach($currencyOptions as $currCode)
                                            <option value="{{ $currCode }}" @selected($selectedCurrency === $currCode)>{{ $currCode }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small">Uses this DMC / agent configured currency</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Note (optional)</label>
                                    <input type="text" name="ledger_description" class="form-control" maxlength="255" placeholder="Allocation note" value="{{ old('ledger_description') }}">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-send-plane-line me-1"></i> Allocate Funds
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="ri-information-line me-1"></i> Wallet Info</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2">View-only access. DMC users can allocate prepaid funds to this agent wallet.</p>
                            <p class="mb-0 small">When a tour is confirmed with <strong>payment_mode: wallet</strong>, the booking flow debits this wallet via the Python <code>/wallet/booking</code> endpoint.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-lg-{{ $canAllocate ? '8' : '8' }} mb-4">
                @include('finance.partials.wallet-ledger', [
                    'ledger' => $ledger,
                    'ledgerFilters' => $ledgerFilters ?? null,
                    'ledgerBadge' => $currency,
                    'ledgerAjaxUrl' => route('finance.agent.ledger', $agent->agent_id),
                    'ledgerPersistParams' => [
                        'currency' => $currency ?? null,
                    ],
                    'showReferenceColumn' => false,
                ])
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                @include('finance.partials.agent-tours', [
                    'tours' => $tours,
                    'toursFilters' => $toursFilters ?? null,
                    'agentId' => $agent->agent_id,
                    'toursAjaxUrl' => route('finance.agent.tours', $agent->agent_id),
                    'toursPersistParams' => [
                        'currency' => $currency ?? null,
                    ],
                ])
            </div>
        </div>

    </div>
</div>
@endsection
