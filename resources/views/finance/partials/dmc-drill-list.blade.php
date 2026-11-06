@php
    $money = fn ($rows, $key) => collect($rows)->map(fn ($r) => $r['currency'].' '.number_format($r[$key], 2));
@endphp

<div id="dmc-drill-list" data-view="{{ $view }}" data-master-id="{{ $masterId ?? '' }}">
    <div class="table-responsive">
        @if($view === 'masters')
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Master DMC</th>
                        <th class="text-center">DMCs</th>
                        <th class="text-end">Received from Travclicks</th>
                        <th class="text-end">Available</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $master)
                        <tr style="cursor:pointer" data-nav="dmcs" data-master-id="{{ $master->id }}">
                            <td class="fw-medium">{{ $master->name }}</td>
                            <td class="text-center">{{ $master->dmc_count }}</td>
                            <td class="text-end text-success text-nowrap small">@forelse($money($master->totals, 'received') as $line)<div>{{ $line }}</div>@empty — @endforelse</td>
                            <td class="text-end fw-semibold text-nowrap small">@forelse($money($master->totals, 'available') as $line)<div>{{ $line }}</div>@empty — @endforelse</td>
                            <td class="text-end"><i class="ri-arrow-right-s-line"></i></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">No Master DMCs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>DMC</th>
                        <th>Currency</th>
                        <th class="text-end">Received from Travclicks</th>
                        <th class="text-end">Used</th>
                        <th class="text-end">Reserved</th>
                        <th class="text-end">Available</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $dmc)
                        @forelse($dmc->wallets as $w)
                            <tr style="cursor:pointer" data-nav="ledger" data-master-id="{{ $masterId }}" data-dmc-id="{{ $dmc->id }}">
                                @if($loop->first)<td class="fw-medium" rowspan="{{ $dmc->wallets->count() }}">{{ $dmc->name }}</td>@endif
                                <td><span class="badge bg-label-secondary">{{ $w->wallet_currency_code }}</span></td>
                                <td class="text-end text-success text-nowrap">{{ number_format((float) $w->funded_by_travclicks, 2) }}</td>
                                <td class="text-end text-danger text-nowrap">{{ number_format((float) $w->total_debit, 2) }}</td>
                                <td class="text-end text-warning text-nowrap">{{ number_format((float) $w->reserved_balance, 2) }}</td>
                                <td class="text-end fw-semibold text-nowrap">{{ number_format((float) $w->available_balance, 2) }}</td>
                                @if($loop->first)<td class="text-end" rowspan="{{ $dmc->wallets->count() }}"><i class="ri-arrow-right-s-line"></i></td>@endif
                            </tr>
                        @empty
                            <tr style="cursor:pointer" data-nav="ledger" data-master-id="{{ $masterId }}" data-dmc-id="{{ $dmc->id }}">
                                <td class="fw-medium">{{ $dmc->name }}</td>
                                <td colspan="5" class="text-muted small">No wallet yet</td>
                                <td class="text-end"><i class="ri-arrow-right-s-line"></i></td>
                            </tr>
                        @endforelse
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No DMCs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    @if($items->total() > 0)
        <div class="card-footer bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 dmc-ledger-pagination">
            <small class="text-muted">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ number_format($items->total()) }}</small>
            {{ $items->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
