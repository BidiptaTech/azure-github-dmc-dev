{{-- Wider Add Payment modal: pricing left, form right, live wallet FX. --}}
@php
    $apTourCurrency = strtoupper(trim((string) ($tourCurrency ?? 'SGD')));
    $apWalletCurrency = strtoupper(trim((string) ($tour->dmc_wallet_currency ?? '')));
    $apWalletRate = $tour->dmc_wallet_sgd_rate ?? null;
    $apShowWalletFx = $apWalletCurrency !== '' && $apWalletCurrency !== $apTourCurrency;
    $apAcceptance = is_array($tour->acceptance_flow ?? null)
        ? $tour->acceptance_flow
        : app(\App\Services\Payment\AcceptanceFlowService::class)->forTour($tour);
    $apOnlinePaid = ! empty($apAcceptance['online_paid']);
    $apOnlinePaidAmount = (float) ($apAcceptance['online_paid_amount'] ?? $apAcceptance['online_amount'] ?? 0);
    $apOnlineCurrency = strtoupper(trim((string) ($apAcceptance['currency'] ?? $apTourCurrency)));
    $apOnlineHint = (string) ($apAcceptance['hint'] ?? '');
    $apShowOnlinePaidBanner = $apOnlinePaid && $apOnlinePaidAmount > 0;
@endphp

@once
<style>
    .add-payment-modal .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .18);
    }
    .add-payment-modal .modal-header {
        background: #38bdf8;
        border: 0;
        border-bottom: 1px solid #0ea5e9;
        padding: .85rem 1.1rem;
    }
    .add-payment-modal .modal-header .modal-title,
    .add-payment-modal .modal-header .modal-title * {
        color: #fff !important;
    }
    .add-payment-modal .modal-title {
        font-size: .95rem;
        font-weight: 650;
        letter-spacing: .01em;
        color: #fff !important;
    }
    .add-payment-modal .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    .add-payment-modal .modal-body {
        background: #f8fafc;
        padding: 1rem;
    }
    .add-payment-modal .modal-footer {
        background: #fff;
        border-top: 1px solid #e2e8f0;
        padding: .75rem 1.1rem;
    }
    .add-payment-modal .ap-panel {
        height: 100%;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: .9rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .add-payment-modal .ap-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .75rem;
        padding-bottom: .55rem;
        border-bottom: 1px solid #eef2f7;
    }
    .add-payment-modal .ap-panel-title {
        margin: 0;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
    }
    .add-payment-modal .ap-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .2rem .55rem;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
        font-size: .72rem;
        font-weight: 700;
    }
    .add-payment-modal .ap-field-label {
        font-size: .78rem;
        font-weight: 650;
        color: #334155;
        margin-bottom: .35rem;
    }
    .add-payment-modal .ap-field-label .text-muted {
        font-weight: 500;
        font-size: .72rem;
    }
    .add-payment-modal .form-control,
    .add-payment-modal .form-select,
    .add-payment-modal .input-group-text {
        border-color: #dbe3ee;
        border-radius: 10px;
        min-height: 40px;
        font-size: .9rem;
    }
    .add-payment-modal .input-group > .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .add-payment-modal .input-group > .input-group-text {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: .78rem;
    }
    .add-payment-modal .form-control:focus,
    .add-payment-modal .form-select:focus {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .15);
    }
    .add-payment-modal .ap-help {
        font-size: .72rem;
        color: #64748b;
        margin-top: .3rem;
    }
    .add-payment-modal .ap-alert-soft {
        display: none;
        margin-top: .45rem;
        padding: .45rem .65rem;
        border-radius: 10px;
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #be123c;
        font-size: .74rem;
        line-height: 1.35;
    }
    .add-payment-modal .ap-alert-soft.is-visible {
        display: block;
    }
    .add-payment-modal .btn-success {
        border: 0;
        background: linear-gradient(135deg, #059669, #10b981);
        box-shadow: 0 6px 16px rgba(16, 185, 129, .25);
    }
    .add-payment-modal .btn-outline-secondary {
        border-color: #cbd5e1;
        color: #475569;
    }
    /* Same deep-green banner as Negotiate by Agent */
    .add-payment-modal .nego-online-paid-banner {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-bottom: 0.85rem;
        padding: 0.75rem 0.95rem;
        border-radius: 0.65rem;
        border: 1px solid #14532d;
        background: linear-gradient(135deg, #14532d 0%, #166534 55%, #15803d 100%);
        color: #ecfdf5;
        box-shadow: 0 1px 2px rgba(20, 83, 45, 0.28);
    }
    .add-payment-modal .nego-online-paid-banner.d-none {
        display: none !important;
    }
    .add-payment-modal .nego-online-paid-banner__icon {
        flex-shrink: 0;
        width: 1.55rem;
        height: 1.55rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.16);
        color: #bbf7d0;
        font-size: 1rem;
        line-height: 1;
    }
    .add-payment-modal .nego-online-paid-banner__title {
        display: block;
        margin-bottom: 0.15rem;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #ffffff;
    }
    .add-payment-modal .nego-online-paid-banner__text {
        display: block;
        font-size: 0.78rem;
        line-height: 1.35;
        color: #d1fae5;
    }
    .add-payment-modal .nego-online-paid-banner__text strong {
        color: #ffffff;
        font-weight: 700;
    }
</style>
@endonce

<div
    class="modal fade add-payment-modal"
    id="addPaymentModal{{ $tour->tour_id }}"
    tabindex="-1"
    aria-labelledby="addPaymentModalLabel{{ $tour->tour_id }}"
    aria-hidden="true"
    data-dmc-rate="{{ $tour->dmc_exchange_rate_value ?? '' }}"
    data-previous-rate="{{ $tour->previous_exchange_rate ?? '' }}"
    data-previous-currency="{{ $tour->previous_payment_currency ?? '' }}"
    data-tour-currency="{{ $apTourCurrency }}"
    data-wallet-currency="{{ $apWalletCurrency }}"
    data-wallet-sgd-rate="{{ $apWalletRate !== null && $apWalletRate !== '' ? $apWalletRate : '' }}"
>
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center mb-0 text-white" id="addPaymentModalLabel{{ $tour->tour_id }}">
                    <i class="fas fa-money-bill-wave me-2 text-white"></i>
                    Add Payment for Tour #{{ $tour->tour_id }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm{{ $tour->tour_id }}" action="{{ route('tour.add-payment', $tour->tour_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="tour_id" value="{{ $tour->tour_id }}">
                    <input type="hidden" id="amount{{ $tour->tour_id }}" value="{{ $remainingAmount }}">
                    {{-- currency = order/tour currency for booking amount; dmc_* = what cashier typed (debit). --}}
                    <input type="hidden" name="currency" id="order_currency{{ $tour->tour_id }}" value="{{ $apTourCurrency }}">
                    <input type="hidden" name="dmc_currency" id="dmc_currency{{ $tour->tour_id }}" value="{{ $apWalletCurrency ?: $apTourCurrency }}">
                    <input type="hidden" name="exchange_rate" id="exchange_rate{{ $tour->tour_id }}" value="1">
                    <input type="hidden" name="fx_exchange_rate" id="fx_exchange_rate{{ $tour->tour_id }}" value="">

                    <div class="row g-3 align-items-stretch">
                        <div class="col-lg-6">
                            <div class="ap-panel">
                                <div class="ap-panel-head">
                                    <p class="ap-panel-title">Payment information</p>
                                    <span class="ap-chip">{{ $apTourCurrency }}</span>
                                </div>
                                @include('bookings.partials.add-payment-pricing-breakdown', ['tourCurrency' => $apTourCurrency])
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="ap-panel">
                                <div class="ap-panel-head">
                                    <p class="ap-panel-title">Collect payment</p>
                                    <span class="ap-chip">Due {{ number_format(ceil($remainingAmount), 2) }} {{ $apTourCurrency }}</span>
                                </div>

                                <div class="nego-online-paid-banner{{ $apShowOnlinePaidBanner ? '' : ' d-none' }}"
                                    id="addPaymentOnlinePaidBanner{{ $tour->tour_id }}"
                                    role="status"
                                    data-online-paid="{{ $apOnlinePaid ? '1' : '0' }}"
                                    data-online-paid-amount="{{ $apOnlinePaidAmount }}"
                                    data-online-currency="{{ e($apOnlineCurrency) }}">
                                    <span class="nego-online-paid-banner__icon" aria-hidden="true">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <div>
                                        <strong class="nego-online-paid-banner__title">Online payment complete</strong>
                                        <span class="nego-online-paid-banner__text" id="addPaymentOnlinePaidText{{ $tour->tour_id }}">
                                            @if($apShowOnlinePaidBanner)
                                                @if($apOnlineHint !== '')
                                                    {{ $apOnlineHint }}
                                                @else
                                                    Online payment complete:
                                                    <strong>{{ $apOnlineCurrency }} {{ number_format($apOnlinePaidAmount, 2) }}</strong>.
                                                    Collect the offline remaining amount below.
                                                @endif
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    {{-- Order-currency amount submitted to backend (booking remaining math). --}}
                                    <input type="hidden"
                                        id="payment_amount{{ $tour->tour_id }}"
                                        name="payment_amount"
                                        value="{{ $remainingAmount }}"
                                        data-max-order="{{ $remainingAmount }}">

                                    <div class="col-12" id="walletFxBox{{ $tour->tour_id }}">
                                        <label for="dmc_pay_amount{{ $tour->tour_id }}" class="ap-field-label">Payment amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="dmcCurrencyCode{{ $tour->tour_id }}">{{ $apWalletCurrency ?: $apTourCurrency }}</span>
                                            <input type="number"
                                                class="form-control"
                                                id="dmc_pay_amount{{ $tour->tour_id }}"
                                                name="dmc_pay_amount"
                                                placeholder="Enter amount to pay"
                                                required
                                                min="{{ ($apWalletCurrency ?: $apTourCurrency) === 'IDR' ? '1' : '0.01' }}"
                                                step="{{ ($apWalletCurrency ?: $apTourCurrency) === 'IDR' ? '1' : '0.01' }}"
                                                value=""
                                                data-tour-currency="{{ $apTourCurrency }}"
                                                data-dmc-currency="{{ $apWalletCurrency ?: $apTourCurrency }}"
                                                oninput="validatePaymentAmountInput({{ $tour->tour_id }})"
                                                onblur="validatePaymentAmountInput({{ $tour->tour_id }})">
                                        </div>
                                        <div class="d-flex flex-wrap justify-content-between gap-1">
                                            <div class="ap-help" id="dmcPayMaxHelp{{ $tour->tour_id }}">
                                                Pay in DMC currency{{ $apWalletCurrency ? ' (' . $apWalletCurrency . ')' : '' }}.
                                            </div>
                                            <small class="text-danger" id="paymentValidationError{{ $tour->tour_id }}" style="display: none;">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <span id="validationMessage{{ $tour->tour_id }}"></span>
                                            </small>
                                        </div>
                                        <div class="ap-alert-soft" id="walletFxBlock{{ $tour->tour_id }}"></div>
                                        <span id="walletFxAmount{{ $tour->tour_id }}" class="d-none" aria-hidden="true"></span>
                                    </div>

                                    <div class="col-12{{ $apShowWalletFx ? '' : ' d-none' }}" id="orderAmountBox{{ $tour->tour_id }}">
                                        <label for="order_currency_amount{{ $tour->tour_id }}" class="ap-field-label">Order currency amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="orderCurrencyCode{{ $tour->tour_id }}">{{ $apTourCurrency }}</span>
                                            <input type="text"
                                                class="form-control bg-light"
                                                id="order_currency_amount{{ $tour->tour_id }}"
                                                value="{{ number_format((float) $remainingAmount, 2, '.', ',') }}"
                                                readonly
                                                tabindex="-1">
                                        </div>
                                        <div class="ap-help" id="walletFxDetail{{ $tour->tour_id }}">
                                            Display only — booking remaining stays in {{ $apTourCurrency }}.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="payment_date{{ $tour->tour_id }}" class="ap-field-label">Payment date</label>
                                        <input type="date"
                                            class="form-control"
                                            id="payment_date{{ $tour->tour_id }}"
                                            name="payment_date"
                                            value="{{ date('Y-m-d') }}"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="payment_type{{ $tour->tour_id }}" class="ap-field-label d-flex align-items-center gap-1">
                                            <span>Payment type</span>
                                            <button type="button"
                                                class="btn btn-link p-0 border-0 text-muted lh-1"
                                                id="paymentEligibilityInfo{{ $tour->tour_id }}"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="left"
                                                data-bs-custom-class="payment-eligibility-tooltip"
                                                data-bs-title="Payment options load when this modal opens."
                                                aria-label="Payment method info">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        </label>
                                        <select class="form-select"
                                            id="payment_type{{ $tour->tour_id }}"
                                            name="payment_type"
                                            required>
                                            <option value="">Select payment type</option>
                                            <option value="cash">Cash</option>
                                            <option value="card">Card</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="online">Bank Transfer</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="transaction_id{{ $tour->tour_id }}" class="ap-field-label">
                                            Transaction ID <span class="text-muted">(optional)</span>
                                        </label>
                                        <input type="text" class="form-control" id="transaction_id{{ $tour->tour_id }}" name="transaction_id">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="remarks{{ $tour->tour_id }}" class="ap-field-label">
                                            Remarks <span class="text-muted">(optional)</span>
                                        </label>
                                        <textarea class="form-control" id="remarks{{ $tour->tour_id }}" name="remarks" rows="2" placeholder="Payment remarks..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success px-3" id="savePaymentBtn{{ $tour->tour_id }}" onclick="submitPaymentForm({{ $tour->tour_id }})">
                    <i class="fas fa-save me-1"></i>Verify Payment
                </button>
            </div>
        </div>
    </div>
</div>
