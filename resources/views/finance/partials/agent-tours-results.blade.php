<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>Tour</th>
                <th>Status</th>
                <th>Finance Status</th>
                <th>Payment Mode</th>
                <th class="text-end">Selling Price</th>
                <th>Created</th>
                <th>Finance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tours as $tour)
                @php
                    $canSettle = in_array($tour->tour_status, ['Confirmed', 'Definite', 'Actual'], true)
                        && ($tour->finance_status ?? 'pending') !== 'settled';
                @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">#{{ $tour->tour_id }}</div>
                        <small class="text-muted">{{ $tour->display_id ?? '—' }}</small>
                    </td>
                    <td><span class="badge bg-label-info">{{ $tour->tour_status ?? '—' }}</span></td>
                    <td>
                        <span class="badge bg-label-{{ ($tour->finance_status ?? 'pending') === 'settled' ? 'success' : 'warning' }}">
                            {{ $tour->finance_status ?? 'pending' }}
                        </span>
                    </td>
                    <td>{{ $tour->payment_mode ?? '—' }}</td>
                    <td class="text-end">{{ $tour->selling_price ? number_format((float) $tour->selling_price, 2) : '—' }}</td>
                    <td class="text-nowrap small">
                        <div>{{ $tour->created_at?->format('M d, Y') }}</div>
                        <span class="text-muted">{{ $tour->created_at?->format('h:i A') }}</span>
                    </td>
                    <td>
                        @if($canSettle)
                            <form method="POST"
                                  action="{{ route('finance.agent.settle-tour', ['agent' => $agentId, 'tour' => $tour->tour_id]) }}"
                                  class="d-inline">
                                @csrf
                                <button type="submit"
                                        class="btn btn-sm btn-primary"
                                        onclick="return confirm('Debit agent wallet for tour #{{ $tour->tour_id }}?')">
                                    Settle Wallet
                                </button>
                            </form>
                        @elseif(($tour->finance_status ?? '') === 'settled')
                            <span class="badge bg-label-success">Settled</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="ri-map-pin-line ri-2x d-block mb-2"></i>
                        No tours found for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tours instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $tours->total() > 0)
    <div class="card-footer bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 agent-tours-pagination">
        <small class="text-muted agent-tours-summary">
            Showing {{ $tours->firstItem() }}–{{ $tours->lastItem() }} of {{ number_format($tours->total()) }} tours
        </small>
        {{ $tours->appends($toursQueryParams ?? [])->links('pagination::bootstrap-4') }}
    </div>
@endif
