@extends('layouts.layout')

@section('title', $pageTitle ?? 'Wallet Transactions')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ $backUrl }}">{{ $backLabel ?? 'Wallet Management' }}</a></li>
                        <li class="breadcrumb-item active">Transactions</li>
                    </ol>
                </nav>
                <h4 class="mb-1">{{ ($glanceScope ?? '') === 'master_dmc_flow' ? 'Master DMC Fund Flow' : 'Transactions at a Glance' }}</h4>
                <p class="text-muted mb-0">
                    @if(($glanceScope ?? '') === 'master_dmc_flow')
                        Funds admin gave to Master DMC and booking markup returns to Travclicks
                        @if(!empty($ownerDisplayName))
                            &middot; {{ $ownerDisplayName }}
                        @endif
                    @else
                        @if(!empty($ownerDisplayName))
                            {{ $ownerDisplayName }} &middot;
                        @endif
                        Recent wallet activity in plain language
                    @endif
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> {{ $backButtonLabel ?? 'Back to Wallet' }}
                </a>
                @if(!empty($ledgerUrl))
                    <a href="{{ $ledgerUrl }}" class="btn btn-outline-primary btn-sm">
                        <i class="ri-file-list-3-line me-1"></i> Full Ledger
                    </a>
                @endif
            </div>
        </div>

        @include('finance.partials.wallet-transactions-glance', [
            'transactions' => $transactions,
            'glanceFilters' => $glanceFilters ?? null,
            'glanceBadge' => ($glanceScope ?? '') === 'master_dmc_flow' ? 'Fund Flow' : ($currency ?? 'USD'),
            'glanceCurrency' => $currency ?? 'USD',
            'glanceAjaxUrl' => $glanceAjaxUrl,
            'glancePersistParams' => $glancePersistParams ?? [],
            'glanceScope' => $glanceScope ?? 'wallet',
            'flowSummary' => $flowSummary ?? null,
            'masterDmcOptions' => $masterDmcOptions ?? collect(),
            'selectedMasterDmcId' => $selectedMasterDmcId ?? null,
            'walletOptions' => $walletOptions ?? collect(),
            'selectedWalletKey' => $selectedWalletKey ?? '',
        ])

    </div>
</div>
@endsection
