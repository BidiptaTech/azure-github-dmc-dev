{{-- Online / mix acceptance: pay online services before negotiation --}}
@php
    $flow = $acceptanceFlow ?? [];
    $classification = (string) ($flow['classification'] ?? '');
    $nextAction = (string) ($flow['next_action'] ?? '');
    $currency = strtoupper((string) ($flow['currency'] ?? 'SGD'));
    $offlineAmount = (float) ($flow['negotiable_amount'] ?? 0);
    $onlineDue = (float) ($flow['payable_now'] ?? $flow['online_amount'] ?? 0);
    $hint = (string) ($flow['hint'] ?? '');
    $tourId = (int) ($tourId ?? 0);
    $isFullOnline = $classification === 'online' || $nextAction === 'full_payment';
    $title = $isFullOnline ? 'Online booking' : 'Pay online first';
    $subtitle = $isFullOnline
        ? 'All services are online — pay in full to confirm.'
        : 'Online services must be paid before offline negotiation.';
@endphp
<div class="online-pay-first-card {{ $isFullOnline ? 'is-full-online' : 'is-mix' }}">
    <div class="online-pay-first-card__top">
        <span class="online-pay-first-card__title">{{ $title }}</span>
    </div>

    <p class="online-pay-first-card__subtitle">{{ $subtitle }}</p>

    @if($onlineDue > 0)
        <div class="online-pay-first-card__offline mb-2">
            <i class="ri-secure-payment-line" aria-hidden="true"></i>
            {{ $isFullOnline ? 'Amount due:' : 'Online due now:' }}
            <strong>{{ $currency }} {{ number_format($onlineDue, 2) }}</strong>
        </div>
    @endif

    <button
        type="button"
        class="btn online-pay-first-card__btn"
        onclick="window.TravclicksPaymentService && window.TravclicksPaymentService.openAcceptancePay({{ $tourId }})"
    >
        <i class="ri-secure-payment-line" aria-hidden="true"></i>
        <span>Pay Now</span>
    </button>

    @if(!$isFullOnline && $offlineAmount > 0)
        <div class="online-pay-first-card__offline">
            <i class="ri-handshake-line" aria-hidden="true"></i>
            Offline to negotiate later:
            <strong>{{ $currency }} {{ number_format($offlineAmount, 2) }}</strong>
        </div>
    @elseif($isFullOnline && $hint !== '')
        <p class="online-pay-first-card__hint">{{ $hint }}</p>
    @endif

    @if(!$isFullOnline)
        <ol class="online-pay-first-card__steps" aria-label="Payment steps">
            <li class="is-active"><span>1</span> Pay online</li>
            <li><span>2</span> Negotiate offline</li>
        </ol>
    @endif
</div>
