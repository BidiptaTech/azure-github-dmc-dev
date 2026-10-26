@extends('layouts.layout')

@section('title', 'Commission statement')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Commission statement</h4>
            <p class="text-muted mb-0">
                @if($viewer === 'travclicks')
                    Online API cost, Travclicks commission, DMC commission, amount charged, and conversion charges for each order.
                @elseif($viewer === 'agent')
                    Amount charged on your wallet or card, the order id, and any currency conversion charge.
                @else
                    Your commission, the total amount, and conversion charges. Commission appears after the tour is fully paid.
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
                        @if($viewer === 'travclicks')
                            <th class="text-end">Travclicks commission</th>
                            <th class="text-end">API cost</th>
                        @endif
                        <th class="text-end">Conversion charge</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tours as $row)
                        <tr>
                            <td><a href="{{ route('finance.commission.index', ['tour_id' => $row->tour_id]) }}">#{{ $row->tour_id }}</a></td>
                            <td>{{ $row->currency_code }}</td>
                            <td class="text-end">{{ number_format((float) $row->charged_amount, 2) }}</td>
                            @if($viewer !== 'agent')
                                <td class="text-end text-success">{{ number_format((float) $row->dmc_commission, 2) }}</td>
                            @endif
                            @if($viewer === 'travclicks')
                                <td class="text-end">{{ number_format((float) $row->travclicks_commission, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $row->api_cost, 2) }}</td>
                            @endif
                            <td class="text-end">{{ number_format((float) $row->conversion_charge, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No commission yet. It appears here after a tour is fully paid.</td></tr>
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
                        Commission {{ number_format((float) $opened->dmc_commission, 2) }} · Total amount {{ number_format((float) $opened->charged_amount, 2) }}
                    @else
                        Total amount {{ number_format($totals['charged_amount'], 2) }}
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
                                <td>{{ $line->channel }}</td>
                                @if($viewer === 'travclicks')
                                    <td>{{ $line->supplier_api ?: '—' }}</td>
                                    <td class="text-end">{{ $line->api_cost === null ? '—' : number_format((float) $line->api_cost, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $line->travclicks_commission, 2) }}</td>
                                @endif
                                @if($viewer !== 'agent')
                                    <td class="text-end">{{ number_format((float) $line->dmc_commission, 2) }}</td>
                                @endif
                                <td class="text-end">{{ number_format((float) $line->agent_price, 2) }} {{ $line->currency_code }}</td>
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
                                <td colspan="10" class="text-center text-muted py-4">
                                    @if($opened)
                                        Commission {{ number_format((float) $opened->dmc_commission, 2) }} {{ $opened->currency_code }}. Total amount {{ number_format((float) $opened->charged_amount, 2) }} {{ $opened->currency_code }}.
                                    @else
                                        This tour is not fully paid yet, so its markup commission is not shown.
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
    @endif
</div>
@endsection
