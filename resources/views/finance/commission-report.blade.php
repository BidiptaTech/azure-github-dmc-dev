@extends('layouts.layout')

@section('title', 'Commission statement')

@section('content')
@php
    $curColors = [
        'SGD' => 'primary', 'IDR' => 'danger', 'USD' => 'success', 'INR' => 'warning',
        'EUR' => 'info', 'GBP' => 'dark', 'MYR' => 'secondary', 'THB' => 'info', 'AUD' => 'primary',
    ];
    $curBadge = function ($c) use ($curColors) {
        $c = strtoupper(trim((string) $c));
        if ($c === '') {
            return '';
        }

        return '<span class="badge bg-label-'.($curColors[$c] ?? 'secondary').' ms-1 align-middle">'.e($c).'</span>';
    };
    $money = fn ($v, $d = 2) => number_format((float) $v, $d);
    $fxPct = (float) ($fxChargePercent ?? 0);
    $sgdRates = is_array($sgdRates ?? null) ? $sgdRates : [];
    $fxCharge = app(\App\Services\Payment\FxConversionCharge::class);
@endphp
<style>
    .cs-page .cs-hero {
        border: 0;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0ea5e9 140%);
        color: #f8fafc;
    }
    .cs-page .cs-hero .text-muted-hero { color: rgba(248,250,252,.72) !important; }
    .cs-page .cs-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        overflow: hidden;
    }
    .cs-page .cs-card .card-header {
        background: #fff;
        border-bottom: 1px solid #eef2f7;
        padding: 1rem 1.25rem;
    }
    .cs-page .table thead th {
        font-size: .72rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
        border-bottom-width: 1px;
        white-space: nowrap;
    }
    .cs-page .cs-amount {
        font-variant-numeric: tabular-nums;
        font-weight: 600;
    }
    .cs-page .cs-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .7rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #0f172a;
        font-size: .8rem;
        font-weight: 600;
    }
    .cs-page .cs-pill.is-fx {
        background: #fff7ed;
        color: #9a3412;
    }
    .cs-page .cs-fx-banner {
        border-radius: .85rem;
        border: 1px solid #fed7aa;
        background: #fffbeb;
        color: #9a3412;
        padding: .85rem 1rem;
        font-size: .875rem;
        line-height: 1.45;
    }
    .cs-page .cs-stat {
        border-radius: .85rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: .75rem 1rem;
        min-width: 140px;
    }
    .cs-page .cs-stat .label { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
    .cs-page .cs-stat .value { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
    .cs-page tr.cs-online td { background: rgba(14, 165, 233, .04); }
</style>

<div class="container-xxl flex-grow-1 container-p-y cs-page">
    <div class="card cs-hero mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="mb-1 text-white">Commission statement</h4>
                <p class="mb-0 text-muted-hero">
                    @if($viewer === 'travclicks')
                        Travclicks online markup commission, API cost, DMC commission, and FX conversion charges — by tour id.
                    @elseif($viewer === 'agent')
                        Amount charged on your wallet or card, the order id, and any currency conversion charge.
                    @else
                        Your DMC commission (including online services) and wallet credits, by tour id.
                    @endif
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="cs-pill">
                    <i class="ri-funds-line"></i>
                    Rate display: 1 SGD = …
                </span>
            </div>
        </div>
    </div>

    <form method="get" class="card cs-card mb-3">
        <div class="card-body d-flex flex-wrap gap-2 align-items-end">
            <div class="flex-grow-1" style="min-width: 200px;">
                <label class="form-label" for="tour_id">Order / tour id</label>
                <input class="form-control" id="tour_id" name="tour_id" value="{{ $tourId }}" placeholder="Tour id">
            </div>
            <button class="btn btn-primary" type="submit">
                <i class="ri-search-line me-1"></i> Open order
            </button>
        </div>
    </form>

    <div class="card cs-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Orders</h5>
                <small class="text-muted">Commission roll-up by tour</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Currency</th>
                        <th class="text-end">Total amount</th>
                        @if($viewer !== 'agent')
                            <th class="text-end">DMC commission</th>
                        @endif
                        @if($viewer === 'dmc')
                            <th class="text-end">Wallet credited</th>
                        @endif
                        @if($viewer === 'travclicks')
                            <th class="text-end">Travclicks commission</th>
                            <th class="text-end">API cost</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($tours as $row)
                        <tr class="{{ !empty($row->has_online_commission) ? 'cs-online' : '' }}">
                            <td>
                                <a class="fw-semibold" href="{{ route('finance.commission.index', ['tour_id' => $row->tour_id]) }}">#{{ $row->tour_id }}</a>
                                @if(!empty($row->has_online_commission) || ($row->markup_label ?? '') === 'Online')
                                    <span class="badge bg-label-info ms-1">Online</span>
                                @endif
                            </td>
                            <td>{!! $curBadge($row->currency_code) !!}</td>
                            <td class="text-end cs-amount">{{ $money($row->charged_amount) }}</td>
                            @if($viewer !== 'agent')
                                <td class="text-end cs-amount text-success">{{ $money($row->dmc_commission) }}</td>
                            @endif
                            @if($viewer === 'dmc')
                                <td class="text-end cs-amount">
                                    @if((float) ($row->wallet_credited_amount ?? 0) > 0)
                                        {{ $money($row->wallet_credited_amount) }}
                                    @elseif((float) ($row->dmc_commission ?? 0) > 0 && !empty($row->has_online_commission))
                                        {{ $money($row->dmc_commission) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                            @if($viewer === 'travclicks')
                                <td class="text-end cs-amount">{{ $money($row->travclicks_commission) }}</td>
                                <td class="text-end cs-amount">{{ $money($row->api_cost) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                @if($viewer === 'travclicks')
                                    No commission yet. Online service markup appears here when the service is approved.
                                @elseif($viewer === 'dmc')
                                    No commission yet. Online service commission appears here when the service is approved; fully paid tours also appear after payment.
                                @else
                                    No commission yet. It appears here after a tour is fully paid.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tourId)
        @php
            $opened = $tours->firstWhere('tour_id', (int) $tourId);
            $headerCur = strtoupper((string) ($opened->currency_code ?? $displayCurrency ?? 'SGD'));
        @endphp
        <div class="card cs-card mb-3">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">Order #{{ $tourId }} {{ $selectedTour?->display_id ? '· '.$selectedTour->display_id : '' }}</h5>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="cs-pill">
                                Amounts in {!! $curBadge($headerCur) !!}
                            </span>
                        </div>
                    </div>
                    @if($opened)
                        <div class="d-flex flex-wrap gap-2">
                            <div class="cs-stat">
                                <div class="label">Total</div>
                                <div class="value">{{ $money($opened->charged_amount) }}{!! $curBadge($headerCur) !!}</div>
                            </div>
                            <div class="cs-stat">
                                <div class="label">Commission</div>
                                <div class="value">{{ $money($opened->dmc_commission) }}{!! $curBadge($headerCur) !!}</div>
                            </div>
                            @if($viewer === 'dmc' && (float) ($opened->wallet_credited_amount ?? 0) > 0)
                                <div class="cs-stat">
                                    <div class="label">Wallet</div>
                                    <div class="value">{{ $money($opened->wallet_credited_amount) }}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Channel</th>
                            @if($viewer === 'travclicks')
                                <th>API</th>
                                <th class="text-end">API cost</th>
                                <th class="text-end">Travclicks commission</th>
                            @endif
                            @if($viewer !== 'agent')
                                <th class="text-end">DMC commission</th>
                            @endif
                            @if($viewer === 'dmc')
                                <th class="text-end">Wallet credited</th>
                            @endif
                            <th class="text-end">Agent price</th>
                            <th>Agent</th>
                            <th>DMC</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lines as $line)
                            <tr>
                                <td>{{ $line->item_label }}</td>
                                <td>{{ $line->channel }}{{ ($line->payment_method ?? '') === 'online_approve' ? ' · online approve' : '' }}</td>
                                @if($viewer === 'travclicks')
                                    <td>{{ $line->supplier_api ?: '—' }}</td>
                                    <td class="text-end cs-amount">{{ $line->api_cost === null ? '—' : $money($line->api_cost) }}</td>
                                    <td class="text-end cs-amount">{{ $money($line->travclicks_commission) }}</td>
                                @endif
                                @if($viewer !== 'agent')
                                    <td class="text-end cs-amount">{{ $money($line->dmc_commission) }}</td>
                                @endif
                                @if($viewer === 'dmc')
                                    <td class="text-end cs-amount">
                                        @if((float) ($line->wallet_credited_amount ?? 0) > 0)
                                            {{ $money($line->wallet_credited_amount) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif
                                <td class="text-end cs-amount">{{ $money($line->agent_price) }}</td>
                                <td>{{ $line->agent_entry === 'debit' ? 'Debit' : '—' }}</td>
                                <td>{{ $line->dmc_entry === 'credit' ? 'Credit' : '—' }}</td>
                                <td class="small">
                                    Tour #{{ $line->tour_id }}
                                    @if($line->stripe_transfer_id)
                                        <div class="text-muted">Stripe {{ $line->stripe_transfer_id }}</div>
                                    @elseif($line->transfer_status)
                                        <div class="text-muted">{{ str_replace('_', ' ', $line->transfer_status) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    @if($opened)
                                        All amounts in {{ $opened->currency_code }}. Commission {{ $money($opened->dmc_commission) }}. Total {{ $money($opened->charged_amount) }}.
                                    @else
                                        No commission lines for this tour yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($lines->isNotEmpty())
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <td colspan="{{ $viewer === 'travclicks' ? 3 : 2 }}">Total</td>
                                @if($viewer === 'travclicks')
                                    <td class="text-end">{{ $money($totals['api_cost']) }}</td>
                                    <td class="text-end">{{ $money($totals['travclicks_commission']) }}</td>
                                @endif
                                @if($viewer !== 'agent')
                                    <td class="text-end">{{ $money($totals['dmc_commission']) }}</td>
                                @endif
                                @if($viewer === 'dmc')
                                    <td class="text-end">{{ $money($totals['wallet_credited_amount']) }}</td>
                                @endif
                                <td class="text-end">{{ $money($totals['agent_price']) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @php
            $verifiedTotal = 0.0;
            $verifiedWalletTotal = 0.0;
            $verifiedWalletCur = '';
            foreach ($payments as $pp) {
                if ((int) ($pp['status'] ?? 0) !== 1) {
                    continue;
                }
                $verifiedTotal += (float) ($pp['amount'] ?? 0);
                $fxVerified = $fxCharge->paymentDisplay($pp, strtoupper((string) ($opened->currency_code ?? $headerCur ?? 'SGD')));
                if ($fxVerified['converted'] && $fxVerified['paid_in'] !== null && (float) $fxVerified['paid_in'] > 0) {
                    $verifiedWalletTotal += (float) $fxVerified['paid_in'];
                    $verifiedWalletCur = strtoupper((string) ($fxVerified['paid_in_currency'] ?? $verifiedWalletCur));
                }
            }
            $orderCur = strtoupper((string) ($payments[0]['order_currency'] ?? $payments[0]['currency'] ?? ($opened->currency_code ?? $headerCur)));
        @endphp

        <div class="card cs-card">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">Transactions</h5>
                        <small class="text-muted">
                            Order #{{ $tourId }} · {{ count($payments) }} {{ \Illuminate\Support\Str::plural('payment', count($payments)) }}
                        </small>
                        <div class="mt-2 small text-muted">
                            From <strong>{{ $selectedTour?->agent?->name ?? ('Agent #'.($selectedTour->agent_id ?? '—')) }}</strong>
                            <i class="ri-arrow-right-line mx-1"></i>
                            To <strong>{{ $selectedTour?->dmc?->name ?? ('DMC #'.($selectedTour->dmc_id ?? '—')) }}</strong>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="cs-stat">
                            <div class="label">Verified (order)</div>
                            <div class="value">{{ $money($verifiedTotal) }}{!! $curBadge($orderCur) !!}</div>
                        </div>
                        @if($verifiedWalletTotal > 0 && $verifiedWalletCur !== '' && $verifiedWalletCur !== $orderCur)
                            <div class="cs-stat">
                                <div class="label">Verified (wallet)</div>
                                <div class="value">{{ $money($verifiedWalletTotal) }}{!! $curBadge($verifiedWalletCur) !!}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date &amp; time</th>
                            <th>Method</th>
                            <th>Transaction</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid in</th>
                            <th class="text-end">Rate</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $i => $p)
                            @php
                                $orderCurRow = strtoupper((string) ($p['order_currency'] ?? $p['currency'] ?? ($opened->currency_code ?? 'SGD')));
                                $fxRow = $fxCharge->paymentDisplay($p, $orderCurRow);
                                $cur = $orderCurRow;
                                $walletAmt = $fxRow['paid_in'];
                                $walletCur = strtoupper((string) ($fxRow['paid_in_currency'] ?? ''));
                                $converted = (bool) ($fxRow['converted'] ?? false);
                                $ratePerSgd = (float) ($fxRow['rate_per_sgd'] ?? 0);
                                $rateCur = strtoupper((string) ($fxRow['rate_currency'] ?? ''));
                                $ts = !empty($p['date']) ? strtotime((string) $p['date']) : false;
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td class="text-nowrap">
                                    @if($ts)
                                        <div class="fw-semibold">{{ date('d M Y', $ts) }}</div>
                                        <small class="text-muted">{{ date('H:i:s', $ts) }}</small>
                                    @else
                                        —
                                    @endif
                                    @if(!empty($p['payment_date']))
                                        <div class="small text-muted">Paid on {{ $p['payment_date'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', (string) ($p['payment_type'] ?? '—'))) }}</div>
                                    @if(!empty($p['payment_for']))
                                        <span class="badge bg-label-{{ $p['payment_for'] === 'online' ? 'info' : 'secondary' }}">{{ ucfirst($p['payment_for']) }}</span>
                                    @endif
                                    @if(!empty($p['auto_verified']))
                                        <span class="badge bg-label-success">Auto</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-break">{{ $p['transaction_id'] ?? '—' }}</div>
                                    @if(!empty($p['payment_attempt_uuid']))
                                        <small class="text-muted" title="{{ $p['payment_attempt_uuid'] }}">{{ \Illuminate\Support\Str::limit($p['payment_attempt_uuid'], 13, '…') }}</small>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap cs-amount">
                                    {{ isset($p['amount']) ? $money($p['amount']) : '—' }}{!! $curBadge($cur) !!}
                                </td>
                                <td class="text-end text-nowrap cs-amount">
                                    @if($converted && $walletAmt !== null)
                                        {{ $money($walletAmt, $walletCur === 'IDR' ? 0 : 2) }}{!! $curBadge($walletCur) !!}
                                    @elseif(isset($p['original_amount']))
                                        {{ $money($p['original_amount']) }}{!! $curBadge($cur) !!}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end text-nowrap cs-amount">
                                    @if(!empty($fxRow['show_rate']) && $ratePerSgd > 0 && $rateCur !== '')
                                        1 SGD = {{ $money($ratePerSgd, 2) }}{!! $curBadge($rateCur) !!}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if((int) ($p['status'] ?? 0) === 1)
                                        <span class="badge bg-label-success"><i class="ri-checkbox-circle-line me-1"></i>Verified</span>
                                    @else
                                        <span class="badge bg-label-warning"><i class="ri-time-line me-1"></i>Unverified</span>
                                    @endif
                                </td>
                                <td class="small text-muted" style="min-width:160px">{{ $p['remarks'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No payment transactions recorded on this order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($payments))
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <td colspan="4" class="text-end">Verified total</td>
                                <td class="text-end text-nowrap">{{ $money($verifiedTotal) }}{!! $curBadge($orderCur) !!}</td>
                                <td class="text-end text-nowrap">
                                    @if($verifiedWalletTotal > 0 && $verifiedWalletCur !== '' && $verifiedWalletCur !== $orderCur)
                                        {{ $money($verifiedWalletTotal) }}{!! $curBadge($verifiedWalletCur) !!}
                                    @endif
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
