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

    $isFxLine = function ($line) {
        $channel = strtolower(trim((string) ($line->channel ?? '')));
        return $channel === 'fx' || (float) ($line->conversion_charge ?? 0) > 0.0001;
    };
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
    .cs-page .cs-stat {
        border-radius: .85rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: .75rem 1rem;
        min-width: 148px;
    }
    .cs-page .cs-stat .label { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
    .cs-page .cs-stat .value { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
    .cs-page .cs-stat.is-muted { background: #fff7ed; border-color: #fed7aa; }
    .cs-page .cs-stat.is-muted .value { color: #9a3412; }
    .cs-page tr.cs-online td { background: rgba(14, 165, 233, .04); }
    .cs-page .cs-section-title {
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
        margin: 0 0 .65rem;
    }
    .cs-page .cs-fx-note {
        border-radius: .85rem;
        border: 1px dashed #fdba74;
        background: #fffbeb;
        color: #9a3412;
        padding: .9rem 1rem;
        display: flex;
        gap: .85rem;
        align-items: flex-start;
    }
    .cs-page .cs-fx-note .cs-fx-amount {
        font-size: 1.1rem;
        font-weight: 700;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .cs-page .cs-fx-note p { margin: 0; font-size: .875rem; line-height: 1.45; }
    .cs-page .cs-legend {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem 1rem;
        font-size: .8rem;
        color: #64748b;
    }
    .cs-page .cs-legend span { display: inline-flex; align-items: center; gap: .35rem; }
    .cs-page .cs-dot {
        width: .55rem; height: .55rem; border-radius: 50%; display: inline-block;
    }
    .cs-page .cs-dot.is-service { background: #0ea5e9; }
    .cs-page .cs-dot.is-fx { background: #f97316; }
    .cs-page .cs-row-meta { font-size: .78rem; color: #64748b; }
    .cs-page .cs-empty {
        text-align: center;
        color: #64748b;
        padding: 2rem 1rem;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y cs-page">
    <div class="card cs-hero mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h4 class="mb-1 text-white">Commission statement</h4>
                <p class="mb-0 text-muted-hero">
                    @if($viewer === 'travclicks')
                        Service commissions, API cost, and FX notes by order. FX is shown for reference — not added into totals.
                    @elseif($viewer === 'agent')
                        What you paid per order. Currency conversion (if any) is already inside the paid amount.
                    @else
                        Your DMC commission and wallet credits by order. FX is informational only.
                    @endif
                </p>
            </div>
            <div class="cs-legend text-white-50">
                <span><i class="cs-dot is-service"></i> Services in total</span>
                <span><i class="cs-dot is-fx"></i> FX shown, not added</span>
            </div>
        </div>
    </div>

    <form method="get" class="card cs-card mb-3">
        <div class="card-body d-flex flex-wrap gap-2 align-items-end">
            <div class="flex-grow-1" style="min-width: 200px;">
                <label class="form-label" for="tour_id">Order / tour id</label>
                <input class="form-control" id="tour_id" name="tour_id" value="{{ $tourId }}" placeholder="e.g. 4475">
            </div>
            <button class="btn btn-primary" type="submit">
                <i class="ri-search-line me-1"></i> Open order
            </button>
            @if($tourId)
                <a class="btn btn-outline-secondary" href="{{ route('finance.commission.index') }}">Clear</a>
            @endif
        </div>
    </form>

    <div class="card cs-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Orders</h5>
                <small class="text-muted">Totals are service amounts only</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Currency</th>
                        <th class="text-end">Services total</th>
                        @if($viewer !== 'agent')
                            <th class="text-end">DMC commission</th>
                        @endif
                        @if($viewer === 'dmc')
                            <th class="text-end">Wallet credited</th>
                        @endif
                        @if($viewer === 'travclicks')
                            <th class="text-end">Travclicks commission</th>
                            <th class="text-end">API cost</th>
                            <th class="text-end">FX (info)</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($tours as $row)
                        <tr class="{{ !empty($row->has_online_commission) ? 'cs-online' : '' }} {{ (int) $tourId === (int) $row->tour_id ? 'table-active' : '' }}">
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
                                <td class="text-end cs-amount text-muted">
                                    @if((float) ($row->conversion_charge ?? 0) > 0)
                                        {{ $money($row->conversion_charge) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="cs-empty">
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

            $approveLines = collect();
            $walletLines = collect();
            $fxLines = collect();
            $otherLines = collect();
            foreach ($lines as $line) {
                if ($isFxLine($line)) {
                    $fxLines->push($line);
                    continue;
                }
                $method = (string) ($line->payment_method ?? '');
                if ($method === 'online_approve') {
                    $approveLines->push($line);
                } elseif ($method === 'wallet') {
                    $walletLines->push($line);
                } else {
                    $otherLines->push($line);
                }
            }
            // Prefer approve rows for the main breakdown (avoids duplicate hotel/wallet rows).
            $serviceLines = $approveLines->isNotEmpty()
                ? $approveLines
                : ($walletLines->isNotEmpty() ? $walletLines : $otherLines);

            $fxTotal = (float) ($totals['conversion_charge'] ?? ($opened->conversion_charge ?? 0));
            if ($fxTotal <= 0 && $fxLines->isNotEmpty()) {
                $fxTotal = (float) $fxLines->sum(fn ($l) => (float) ($l->conversion_charge ?? $l->agent_price ?? 0));
            }
            $serviceTotal = (float) ($totals['agent_price'] ?? ($opened->charged_amount ?? 0));
        @endphp

        <div class="card cs-card mb-3">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">Order #{{ $tourId }} {{ $selectedTour?->display_id ? '· '.$selectedTour->display_id : '' }}</h5>
                        <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                            <span class="cs-pill">Amounts in {!! $curBadge($headerCur) !!}</span>
                            @if($selectedTour)
                                <span class="cs-row-meta">
                                    {{ $selectedTour->agent->name ?? ('Agent #'.($selectedTour->agent_id ?? '—')) }}
                                    <i class="ri-arrow-right-s-line"></i>
                                    {{ $selectedTour->dmc->name ?? ('DMC #'.($selectedTour->dmc_id ?? '—')) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($opened)
                        <div class="d-flex flex-wrap gap-2">
                            <div class="cs-stat">
                                <div class="label">Services total</div>
                                <div class="value">{{ $money($serviceTotal) }}{!! $curBadge($headerCur) !!}</div>
                            </div>
                            <div class="cs-stat">
                                <div class="label">DMC commission</div>
                                <div class="value">{{ $money($opened->dmc_commission) }}{!! $curBadge($headerCur) !!}</div>
                            </div>
                            @if($viewer === 'travclicks')
                                <div class="cs-stat">
                                    <div class="label">Travclicks</div>
                                    <div class="value">{{ $money($opened->travclicks_commission) }}{!! $curBadge($headerCur) !!}</div>
                                </div>
                                <div class="cs-stat">
                                    <div class="label">API cost</div>
                                    <div class="value">{{ $money($opened->api_cost) }}{!! $curBadge($headerCur) !!}</div>
                                </div>
                            @endif
                            @if($viewer === 'dmc' && (float) ($opened->wallet_credited_amount ?? 0) > 0)
                                <div class="cs-stat">
                                    <div class="label">Wallet credited</div>
                                    <div class="value">{{ $money($opened->wallet_credited_amount) }}</div>
                                </div>
                            @endif
                            @if($fxTotal > 0)
                                <div class="cs-stat is-muted">
                                    <div class="label">FX (not in total)</div>
                                    <div class="value">{{ $money($fxTotal) }}{!! $curBadge($headerCur) !!}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-body pb-0">
                <p class="cs-section-title">1 · Services</p>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Service</th>
                            <th>Source</th>
                            @if($viewer === 'travclicks')
                                <th>API</th>
                                <th class="text-end">API cost</th>
                                <th class="text-end">Travclicks</th>
                            @endif
                            @if($viewer !== 'agent')
                                <th class="text-end">DMC commission</th>
                            @endif
                            @if($viewer === 'dmc')
                                <th class="text-end">Wallet credited</th>
                            @endif
                            <th class="text-end">Agent price</th>
                            <th>Flow</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceLines as $line)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $line->item_label ?: 'Service' }}</div>
                                    <div class="cs-row-meta">Tour #{{ $line->tour_id }}</div>
                                </td>
                                <td>
                                    @if(($line->payment_method ?? '') === 'online_approve')
                                        <span class="badge bg-label-info">Online approve</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ ucfirst((string) ($line->channel ?: $line->payment_method ?: '—')) }}</span>
                                    @endif
                                </td>
                                @if($viewer === 'travclicks')
                                    <td>{{ $line->supplier_api ?: '—' }}</td>
                                    <td class="text-end cs-amount">{{ $line->api_cost === null ? '—' : $money($line->api_cost) }}</td>
                                    <td class="text-end cs-amount">{{ $money($line->travclicks_commission) }}</td>
                                @endif
                                @if($viewer !== 'agent')
                                    <td class="text-end cs-amount text-success">{{ $money($line->dmc_commission) }}</td>
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
                                <td class="cs-row-meta">
                                    @if(($line->agent_entry ?? '') === 'debit') Agent debit @endif
                                    @if(($line->dmc_entry ?? '') === 'credit')
                                        @if(($line->agent_entry ?? '') === 'debit') · @endif
                                        DMC credit
                                    @endif
                                    @if(($line->agent_entry ?? '') !== 'debit' && ($line->dmc_entry ?? '') !== 'credit')
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="cs-empty">
                                    @if($opened)
                                        No service commission lines yet for this order.
                                    @else
                                        No commission lines for this tour yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($serviceLines->isNotEmpty())
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <td colspan="{{ $viewer === 'travclicks' ? 3 : 2 }}">Services total</td>
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
                                <td class="text-end">{{ $money($serviceTotal) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($fxTotal > 0)
                <div class="card-body">
                    <p class="cs-section-title">2 · Currency conversion</p>
                    <div class="cs-fx-note">
                        <div>
                            <div class="cs-fx-amount">{{ $money($fxTotal) }}{!! $curBadge($headerCur) !!}</div>
                            @if($fxPct > 0)
                                <div class="small mt-1 opacity-75">{{ rtrim(rtrim(number_format($fxPct, 2), '0'), '.') }}% conversion fee</div>
                            @endif
                        </div>
                        <div>
                            <p class="fw-semibold mb-1">Already included in the payment</p>
                            <p>
                                This conversion fee was charged with the wallet payment.
                                It is shown here for your information only — it is <strong>not added again</strong> to the Services total above.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
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
                        <h5 class="mb-1">3 · Payments</h5>
                        <small class="text-muted">
                            {{ count($payments) }} {{ \Illuminate\Support\Str::plural('transaction', count($payments)) }} on this order
                        </small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="cs-stat">
                            <div class="label">Verified (order)</div>
                            <div class="value">{{ $money($verifiedTotal) }}{!! $curBadge($orderCur) !!}</div>
                        </div>
                        @if($verifiedWalletTotal > 0 && $verifiedWalletCur !== '' && $verifiedWalletCur !== $orderCur)
                            <div class="cs-stat">
                                <div class="label">Verified (wallet paid-in)</div>
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
                            <th class="text-end">Order amount</th>
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
                                <td colspan="9" class="cs-empty">No payment transactions recorded on this order.</td>
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
