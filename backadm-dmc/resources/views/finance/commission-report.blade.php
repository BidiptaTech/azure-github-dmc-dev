@extends('layouts.layout')

@section('title', 'Commission statement')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Commission statement</h4>
            <p class="text-muted mb-0">
                @if($viewer === 'travclicks')
                    Travclicks online markup commission, API cost, DMC commission, and conversion charges — by tour id (not wallet).
                @elseif($viewer === 'agent')
                    Amount charged on your wallet or card, the order id, and any currency conversion charge.
                @else
                    Your DMC commission (including online services) and the amount credited to wallet, by tour id.
                @endif
            </p>
        </div>
    </div>

    <form method="get" class="card mb-3">
        <div class="card-body d-flex gap-2 align-items-end">
            <div>
                <label class="form-label" for="tour_id">Order / tour id</label>
                <input class="form-control" id="tour_id" name="tour_id" value="{{ $tourId }}" placeholder="Tour id">
            </div>
            <button class="btn btn-primary" type="submit">Open order</button>
        </div>
    </form>

    <div class="card mb-3">
        <div class="card-header">Orders</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
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
                        <th class="text-end">Conversion charge</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tours as $row)
                        <tr @if(!empty($row->has_online_commission)) class="table-primary" @endif>
                            <td>
                                <a href="{{ route('finance.commission.index', ['tour_id' => $row->tour_id]) }}">#{{ $row->tour_id }}</a>
                                @if(!empty($row->has_online_commission) || ($row->markup_label ?? '') === 'Online')
                                    <span class="badge bg-label-primary ms-1">Online</span>
                                @endif
                            </td>
                            <td>{{ $row->currency_code }}</td>
                            <td class="text-end">{{ number_format((float) $row->charged_amount, 2) }}</td>
                            @if($viewer !== 'agent')
                                <td class="text-end text-success">{{ number_format((float) $row->dmc_commission, 2) }}</td>
                            @endif
                            @if($viewer === 'dmc')
                                <td class="text-end">
                                    @if((float) ($row->wallet_credited_amount ?? 0) > 0)
                                        {{ number_format((float) $row->wallet_credited_amount, 2) }}
                                    @elseif((float) ($row->dmc_commission ?? 0) > 0 && !empty($row->has_online_commission))
                                        {{ number_format((float) $row->dmc_commission, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                            @if($viewer === 'travclicks')
                                <td class="text-end fw-semibold">{{ number_format((float) $row->travclicks_commission, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $row->api_cost, 2) }}</td>
                            @endif
                            <td class="text-end">{{ number_format((float) $row->conversion_charge, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">
                            @if($viewer === 'travclicks')
                                No commission yet. Online service markup appears here when the service is approved.
                            @elseif($viewer === 'dmc')
                                No commission yet. Online service commission appears here when the service is approved; fully paid tours also appear after payment.
                            @else
                                No commission yet. It appears here after a tour is fully paid.
                            @endif
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tourId)
        @php $opened = $tours->firstWhere('tour_id', (int) $tourId); @endphp
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Order #{{ $tourId }} {{ $selectedTour?->display_id ? '· '.$selectedTour->display_id : '' }}</span>
                <span class="text-muted">
                    @if($opened)
                        All amounts in {{ $opened->currency_code }}
                        · Commission {{ number_format((float) $opened->dmc_commission, 2) }}
                        · Total {{ number_format((float) $opened->charged_amount, 2) }}
                        @if($viewer === 'dmc' && (float) ($opened->wallet_credited_amount ?? 0) > 0)
                            · Wallet {{ number_format((float) $opened->wallet_credited_amount, 2) }}
                        @endif
                    @else
                        Total amount {{ number_format($totals['charged_amount'], 2) }} {{ $totals['currency_code'] ?? $totals['charged_currency'] ?? '' }}
                    @endif
                </span>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
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
                                    <td class="text-end">{{ $line->api_cost === null ? '—' : number_format((float) $line->api_cost, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $line->travclicks_commission, 2) }}</td>
                                @endif
                                @if($viewer !== 'agent')
                                    <td class="text-end">{{ number_format((float) $line->dmc_commission, 2) }}</td>
                                @endif
                                @if($viewer === 'dmc')
                                    <td class="text-end">
                                        @if((float) ($line->wallet_credited_amount ?? 0) > 0)
                                            {{ number_format((float) $line->wallet_credited_amount, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif
                                <td class="text-end">{{ number_format((float) $line->agent_price, 2) }}</td>
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
                                        All amounts in {{ $opened->currency_code }}. Commission {{ number_format((float) $opened->dmc_commission, 2) }}. Total {{ number_format((float) $opened->charged_amount, 2) }}.
                                    @else
                                        No commission lines for this tour yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($lines->isNotEmpty())
                        <tfoot>
                            <tr class="fw-semibold">
                                <td colspan="{{ $viewer === 'travclicks' ? 3 : 2 }}">Total</td>
                                @if($viewer === 'travclicks')
                                    <td class="text-end">{{ number_format($totals['api_cost'], 2) }}</td>
                                    <td class="text-end">{{ number_format($totals['travclicks_commission'], 2) }}</td>
                                @endif
                                @if($viewer !== 'agent')
                                    <td class="text-end">{{ number_format($totals['dmc_commission'], 2) }}</td>
                                @endif
                                @if($viewer === 'dmc')
                                    <td class="text-end">{{ number_format($totals['wallet_credited_amount'], 2) }}</td>
                                @endif
                                <td class="text-end">{{ number_format($totals['agent_price'], 2) }}</td>
                                <td colspan="3">
                                    @if($totals['conversion_charge'] > 0)
                                        Conversion charge {{ number_format($totals['conversion_charge'], 2) }}
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @php
            $curColors = ['SGD' => 'primary', 'IDR' => 'danger', 'USD' => 'success', 'INR' => 'warning', 'EUR' => 'info', 'GBP' => 'dark', 'MYR' => 'secondary', 'THB' => 'info', 'AUD' => 'primary'];
            $curBadge = fn ($c) => $c === '' ? '' : '<span class="badge bg-label-'.($curColors[$c] ?? 'secondary').' ms-1 align-middle">'.e($c).'</span>';
            $money = fn ($v) => number_format((float) $v, 2);
            $verifiedTotal = 0.0;
            foreach ($payments as $pp) {
                if ((int) ($pp['status'] ?? 0) === 1) { $verifiedTotal += (float) ($pp['amount'] ?? 0); }
            }
            $orderCur = strtoupper((string) ($payments[0]['currency'] ?? ($opened->currency_code ?? '')));
        @endphp
        <div class="card mt-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-0">Transactions</h5>
                    <small class="text-muted">Order #{{ $tourId }} · {{ count($payments) }} {{ \Illuminate\Support\Str::plural('payment', count($payments)) }}</small>
                </div>
                <div class="text-end small">
                    <span class="text-muted">From</span> <strong>{{ $selectedTour?->agent?->name ?? ('Agent #'.($selectedTour->agent_id ?? '—')) }}</strong>
                    <i class="ri-arrow-right-line mx-1 text-muted"></i>
                    <span class="text-muted">To</span> <strong>{{ $selectedTour?->dmc?->name ?? ('DMC #'.($selectedTour->dmc_id ?? '—')) }}</strong>
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
                            <th class="text-end">Conv. charge</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $i => $p)
                            @php
                                $cur = strtoupper((string) ($p['currency'] ?? ''));
                                $walletAmt = $p['wallet_amount'] ?? null;
                                $walletCur = strtoupper((string) ($p['wallet_currency'] ?? ''));
                                $converted = $walletAmt !== null && $walletCur !== '' && $walletCur !== $cur;
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
                                    @if(!empty($p['payment_date']))<div class="small text-muted">Paid on {{ $p['payment_date'] }}</div>@endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', (string) ($p['payment_type'] ?? '—'))) }}</div>
                                    @if(!empty($p['payment_for']))<span class="badge bg-label-{{ $p['payment_for'] === 'online' ? 'info' : 'secondary' }}">{{ ucfirst($p['payment_for']) }}</span>@endif
                                    @if(!empty($p['auto_verified']))<span class="badge bg-label-success">Auto</span>@endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-break">{{ $p['transaction_id'] ?? '—' }}</div>
                                    @if(!empty($p['payment_attempt_uuid']))
                                        <small class="text-muted" title="{{ $p['payment_attempt_uuid'] }}">{{ \Illuminate\Support\Str::limit($p['payment_attempt_uuid'], 13, '…') }}</small>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap fw-semibold">{{ isset($p['amount']) ? $money($p['amount']) : '—' }}{!! $curBadge($cur) !!}</td>
                                <td class="text-end text-nowrap">
                                    @if($converted)
                                        {{ $money($walletAmt) }}{!! $curBadge($walletCur) !!}
                                    @elseif(isset($p['original_amount']))
                                        {{ $money($p['original_amount']) }}{!! $curBadge($cur) !!}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($converted && !empty($p['exchange_rate']))
                                        <small class="text-muted">1 {{ $cur }} =</small> {{ $money($p['exchange_rate']) }}{!! $curBadge($walletCur) !!}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if(!empty($p['conversion_charge']))
                                        {{ $money($p['conversion_charge']) }}{!! $curBadge($cur) !!}
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
                            <tr><td colspan="10" class="text-center text-muted py-4">No payment transactions recorded on this order.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($payments))
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <td colspan="4" class="text-end">Verified total</td>
                                <td class="text-end text-nowrap">{{ $money($verifiedTotal) }}{!! $curBadge($orderCur) !!}</td>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
