<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                @if($showReferenceColumn)
                    <th>Reference</th>
                @endif
                <th>Type</th>
                <th>Transfer</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledger as $entry)
                <tr>
                    <td class="text-nowrap small">
                        <div>{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y') }}</div>
                        <span class="text-muted">{{ \Carbon\Carbon::parse($entry->created_at)->format('H:i') }}</span>
                    </td>
                    @if($showReferenceColumn)
                        <td class="small font-monospace">{{ $entry->finance_reference }}</td>
                    @endif
                    <td>
                        <span class="badge bg-label-info">{{ str_replace('_', ' ', $entry->ledger_entry_type) }}</span>
                    </td>
                    <td class="small fw-medium">{{ $entry->transfer_summary ?? ($entry->ledger_description ?? '—') }}</td>
                    <td class="text-end text-danger">{{ $entry->debit_amount > 0 ? number_format((float) $entry->debit_amount, 2) : '—' }}</td>
                    <td class="text-end text-success">{{ $entry->credit_amount > 0 ? number_format((float) $entry->credit_amount, 2) : '—' }}</td>
                    <td class="text-end fw-semibold">{{ number_format((float) ($entry->balance_after ?? $entry->running_balance ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showReferenceColumn ? 7 : 6 }}" class="text-center text-muted py-5">
                        <i class="ri-wallet-line ri-2x d-block mb-2"></i>
                        No ledger entries found for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@php
    $rangeSummary = $ledgerFilteredSummary ?? $ledgerSummary ?? null;
    $ledgerDateFiltered = ! empty($ledgerFilters['date_from'] ?? null) || ! empty($ledgerFilters['date_to'] ?? null);
@endphp
@if($ledger instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $ledger->total() > 0)
    <div class="card-footer bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 wallet-ledger-pagination">
        <small class="text-muted wallet-ledger-summary">
            Showing {{ $ledger->firstItem() }}–{{ $ledger->lastItem() }} of {{ number_format($ledger->total()) }} entries
            @if($rangeSummary)
                · {{ $ledgerDateFiltered ? 'This date range' : 'Ledger total' }}:
                credited {{ number_format((float) $rangeSummary['credit'], 2) }},
                debited {{ number_format((float) $rangeSummary['debit'], 2) }}
            @endif
        </small>
        {{ $ledger->appends($ledgerQueryParams ?? [])->links('pagination::bootstrap-4') }}
    </div>
@elseif($ledgerDateFiltered)
    <div class="card-footer bg-transparent">
        <small class="text-muted">
            This date range: credited {{ number_format((float) ($rangeSummary['credit'] ?? 0), 2) }},
            debited {{ number_format((float) ($rangeSummary['debit'] ?? 0), 2) }}
        </small>
    </div>
@endif
