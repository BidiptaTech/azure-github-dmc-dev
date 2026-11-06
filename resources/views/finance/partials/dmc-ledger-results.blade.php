@php
    $dmcLedgerTotals = $dmcLedgerTotals ?? [];
@endphp

@if(!empty($dmcLedgerTotals))
    <div class="d-flex flex-wrap gap-2 px-3 pt-3">
        @foreach($dmcLedgerTotals as $total)
            <div class="border rounded px-3 py-2 small">
                <div class="fw-semibold mb-1">{{ $total['currency'] }} · {{ number_format($total['count']) }} entries</div>
                <span class="text-success">Credit {{ number_format($total['credit'], 2) }}</span>
                <span class="text-muted mx-1">|</span>
                <span class="text-danger">Debit {{ number_format($total['debit'], 2) }}</span>
                <span class="text-muted mx-1">|</span>
                <span class="fw-semibold">Net {{ number_format($total['credit'] - $total['debit'], 2) }}</span>
            </div>
        @endforeach
    </div>
@endif

<div class="table-responsive mt-2">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Transaction ID</th>
                <th>Type</th>
                <th>Details</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dmcLedger as $entry)
                @php
                    $entryCurrency = strtoupper((string) ($entry->wallet_currency_code ?? ''));
                    $isFunding = ($entry->ledger_entry_type ?? '') === 'allocation_credit';
                @endphp
                <tr>
                    <td class="text-nowrap small">
                        <div>{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y') }}</div>
                        <span class="text-muted">{{ \Carbon\Carbon::parse($entry->created_at)->format('H:i') }}</span>
                    </td>
                    <td class="small font-monospace">{{ $entry->finance_reference }}</td>
                    <td class="small font-monospace">{{ $entry->reference_id ?: '—' }}</td>
                    <td>
                        <span class="badge {{ $isFunding ? 'bg-label-success' : 'bg-label-info' }}">{{ $entry->entry_type_label }}</span>
                    </td>
                    <td class="small">
                        <div class="fw-medium">{{ $entry->transfer_summary ?? ($entry->ledger_description ?? '—') }}</div>
                        @if(!empty($entry->tour_id))
                            <span class="text-muted">Tour #{{ $entry->tour_id }}</span>
                        @endif
                    </td>
                    <td class="text-end text-danger text-nowrap">
                        {{ $entry->debit_amount > 0 ? $entryCurrency.' '.number_format((float) $entry->debit_amount, 2) : '—' }}
                    </td>
                    <td class="text-end text-success">
                        {{ $entry->credit_amount > 0 ? $entryCurrency.' '.number_format((float) $entry->credit_amount, 2) : '—' }}
                        @if(!empty($entry->fx_summary))
                            <span class="text-muted small ms-1" title="{{ $entry->fx_summary['estimated'] ? 'Estimated at current rate, original SGD not recorded' : ($entry->fx_summary['rate'] ? '1 SGD = '.number_format($entry->fx_summary['rate'], 2).' '.$entry->fx_summary['to'] : 'Sent by Travclicks') }}">
                                {{ $entry->fx_summary['estimated'] ? '≈' : '=' }} SGD {{ number_format($entry->fx_summary['sgd'], 2) }}@if($entry->fx_summary['estimated']) (est.)@endif
                            </span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold text-nowrap">
                        {{ $entryCurrency }} {{ number_format((float) ($entry->balance_after ?? 0), 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="ri-wallet-line ri-2x d-block mb-2"></i>
                        No DMC ledger entries found for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($dmcLedger instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $dmcLedger->total() > 0)
    <div class="card-footer bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 dmc-ledger-pagination">
        <small class="text-muted">
            Showing {{ $dmcLedger->firstItem() }}–{{ $dmcLedger->lastItem() }} of {{ number_format($dmcLedger->total()) }} entries
        </small>
        {{ $dmcLedger->appends($dmcLedgerQueryParams ?? [])->links('pagination::bootstrap-4') }}
    </div>
@endif
