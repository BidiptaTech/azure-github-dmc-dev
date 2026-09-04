@if(!empty($flowSummary))
    <div class="row g-3 mb-3 px-1">
        <div class="col-md-6">
            <div class="border rounded p-3 h-100 bg-label-danger">
                <div class="text-muted small mb-1">Total funded to Master DMC</div>
                @forelse(($flowSummary['funded'] ?? []) as $code => $total)
                    <div class="fw-bold text-danger">{{ $code }} {{ number_format((float) $total, 2) }}</div>
                @empty
                    <div class="fw-semibold text-muted">—</div>
                @endforelse
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded p-3 h-100 bg-label-success">
                <div class="text-muted small mb-1">Total returned after booking</div>
                @forelse(($flowSummary['returned'] ?? []) as $code => $total)
                    <div class="fw-bold text-success">{{ $code }} {{ number_format((float) $total, 2) }}</div>
                @empty
                    <div class="fw-semibold text-muted">—</div>
                @endforelse
            </div>
        </div>
    </div>
@endif

@if($transactions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $transactions->total() > 0)
    <div class="wallet-glance-feed">
        @foreach($transactions as $txn)
            @php
                $isIn = ($txn['direction'] ?? '') === 'in';
                $accent = $txn['accent'] ?? ($isIn ? 'success' : 'danger');
                $createdAt = \Carbon\Carbon::parse($txn['created_at']);
            @endphp
            <div class="wallet-glance-item">
                <div class="wallet-glance-icon bg-label-{{ $accent }} text-{{ $accent }}">
                    <i class="{{ $txn['icon'] ?? ($isIn ? 'ri-arrow-down-circle-line' : 'ri-arrow-up-circle-line') }}"></i>
                </div>
                <div class="wallet-glance-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ $txn['title'] ?? 'Transaction' }}</div>
                            <div class="text-muted small text-truncate">{{ $txn['subtitle'] ?: ($txn['type_label'] ?? '') }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold text-{{ $accent }}">
                                {{ $isIn ? '+' : '−' }}{{ $txn['currency'] ?? '' }} {{ number_format((float) ($txn['amount'] ?? 0), 2) }}
                            </div>
                            <div class="text-muted small">
                                {{ $createdAt->format('d M Y') }} · {{ $createdAt->format('H:i') }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        <span class="badge bg-label-{{ $accent }}">{{ $txn['type_label'] ?? 'Transaction' }}</span>
                        @if(!empty($txn['wallet_label']))
                            <span class="badge bg-label-primary">{{ $txn['wallet_label'] }}</span>
                        @endif
                        @if(!empty($txn['tour_id']))
                            <span class="badge bg-label-secondary">Tour #{{ $txn['tour_id'] }}</span>
                        @endif
                        <span class="text-muted small ms-auto">
                            Balance {{ number_format((float) ($txn['balance_after'] ?? 0), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center text-muted py-4">
        <i class="ri-history-line ri-2x d-block mb-2"></i>
        No fund or booking return transactions for the selected filters.
    </div>
@endif

@if($transactions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $transactions->total() > 0)
    <div class="card-footer bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 wallet-glance-pagination">
        <small class="text-muted">
            Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ number_format($transactions->total()) }} transactions
        </small>
        {{ $transactions->appends($glanceQueryParams ?? [])->links('pagination::bootstrap-4') }}
    </div>
@endif
