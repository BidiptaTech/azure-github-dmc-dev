@extends('layouts.layout')
@section('title', 'Vendor Credit')

@section('content')
<style>
    .vc-card { border: 1px solid #e5e7eb; border-radius: 10px; }
    .vc-card.is-low { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245, 158, 11, .15); }
    .vc-balance { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
    .vc-balance.neg { color: #dc2626; }
    .vc-mini { font-size: .78rem; color: #64748b; }
    .vc-mini b { color: #1e293b; font-weight: 600; }
    .vc-amt-debit { color: #dc2626; font-weight: 600; white-space: nowrap; }
    .vc-amt-credit { color: #16a34a; font-weight: 600; white-space: nowrap; }
    .vc-table td, .vc-table th { font-size: .8rem; vertical-align: middle; }
    .vc-fx { font-size: .72rem; color: #64748b; white-space: nowrap; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-3">
        <span class="text-muted fw-light">Settings /</span> Vendor Credit
    </h4>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="alert alert-info small mb-4">
        <strong>How this works:</strong> each API vendor gives TravClicks a credit balance in the vendor's own currency,
        separately for <b>Sandbox</b> and <b>Live</b>.
        When an online hotel or attraction is <b>booked</b> with the vendor, its cost is <b>deducted</b> here.
        When it is <b>cancelled</b>, the refund (cost minus any vendor cancellation charge) is <b>added back</b>.
        If the vendor charges in another currency, the line shows the vendor amount and the exchange rate used.
        With <b>Limit enforced</b> on, bookings are blocked once the balance can't cover them.
    </div>

    {{-- Accounts --}}
    <div class="row g-3 mb-4">
        @forelse($accounts as $account)
            @php $neg = (float) $account->balance < 0; @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card vc-card h-100 {{ $account->isLow() || $neg ? 'is-low' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold">{{ $supplierCodes[$account->supplier_code] ?? $account->supplier_code }}</div>
                                <span class="badge {{ $account->environment === 'live' ? 'bg-danger' : 'bg-secondary' }}">{{ $account->environmentLabel() }}</span>
                                @if($account->enforce_limit)
                                    <span class="badge bg-label-primary">Limit enforced</span>
                                @else
                                    <span class="badge bg-label-warning">Not enforced</span>
                                @endif
                                @if(! $account->isActive())<span class="badge bg-label-dark">Inactive</span>@endif
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#vcEdit{{ $account->id }}">
                                <i class="ri-settings-3-line"></i>
                            </button>
                        </div>
                        <div class="vc-mini">Available balance</div>
                        <div class="vc-balance {{ $neg ? 'neg' : '' }}">{{ $account->currency_code }} {{ number_format((float) $account->balance, 2) }}</div>
                        @if($account->isLow())
                            <div class="small text-warning mt-1"><i class="ri-alert-line"></i> At or below the low-balance alert ({{ $account->currency_code }} {{ number_format((float) $account->low_balance_threshold, 2) }})</div>
                        @endif
                        @if($account->auto_created)
                            <div class="small text-warning mt-1"><i class="ri-information-line"></i> Created automatically on the first booking. Add the real credit and turn on the limit.</div>
                        @endif
                        <div class="vc-mini mt-2">
                            Credit added <b>{{ number_format((float) $account->total_credit_added, 2) }}</b> ·
                            Used for bookings <b>{{ number_format((float) $account->total_debited, 2) }}</b> ·
                            Refunded <b>{{ number_format((float) $account->total_refunded, 2) }}</b>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#vcPost{{ $account->id }}"><i class="ri-add-line"></i> Add credit / adjust</button>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('supplier-credits.index', ['account_id' => $account->id]) }}">Statement</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Post credit / adjustment --}}
            <div class="modal fade" id="vcPost{{ $account->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form method="POST" action="{{ route('supplier-credits.post', $account) }}" class="modal-content">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">{{ $supplierCodes[$account->supplier_code] ?? $account->supplier_code }} · {{ $account->environmentLabel() }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select mb-3" required>
                                <option value="topup">Credit added (vendor top-up)</option>
                                <option value="adjustment_credit">Manual adjustment (+) increase balance</option>
                                <option value="adjustment_debit">Manual adjustment (−) decrease balance</option>
                            </select>
                            <label class="form-label">Amount ({{ $account->currency_code }})</label>
                            <input type="number" name="amount" class="form-control mb-3" step="0.01" min="0.01" required>
                            <label class="form-label">Note / reference <span class="text-danger">*</span></label>
                            <input type="text" name="note" class="form-control" maxlength="500" required placeholder="e.g. Vendor invoice / bank transfer ref">
                            <div class="form-text">Current balance: {{ $account->currency_code }} {{ number_format((float) $account->balance, 2) }}</div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary">Post</button></div>
                    </form>
                </div>
            </div>

            {{-- Edit settings --}}
            <div class="modal fade" id="vcEdit{{ $account->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form method="POST" action="{{ route('supplier-credits.update', $account) }}" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header"><h5 class="modal-title">Settings · {{ $supplierCodes[$account->supplier_code] ?? $account->supplier_code }} ({{ $account->environmentLabel() }})</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body">
                            <label class="form-label">Vendor currency</label>
                            <select name="currency_code" class="form-select mb-1">
                                @foreach($currencies as $cur)
                                    <option value="{{ $cur }}" @selected($cur === $account->currency_code)>{{ $cur }}</option>
                                @endforeach
                            </select>
                            <div class="form-text mb-3">Locked once transactions exist.</div>
                            <label class="form-label">Low-balance alert ({{ $account->currency_code }})</label>
                            <input type="number" name="low_balance_threshold" class="form-control mb-3" step="0.01" min="0" value="{{ $account->low_balance_threshold }}">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select mb-3">
                                <option value="active" @selected($account->status === 'active')>Active</option>
                                <option value="inactive" @selected($account->status === 'inactive')>Inactive (no blocking)</option>
                            </select>
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="enforce_limit" value="0">
                                <input class="form-check-input" type="checkbox" name="enforce_limit" value="1" id="vcEnf{{ $account->id }}" @checked($account->enforce_limit)>
                                <label class="form-check-label" for="vcEnf{{ $account->id }}">Block bookings when balance is not enough</label>
                            </div>
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="1000">{{ $account->notes }}</textarea>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-warning mb-0">No vendor credit accounts yet. Create one below.</div></div>
        @endforelse
    </div>

    {{-- New account --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Add vendor credit account</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('supplier-credits.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select name="supplier_code" class="form-select" required>
                        @foreach($supplierCodes as $code => $label)
                            <option value="{{ $code }}" @selected(old('supplier_code') === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Environment</label>
                    <select name="environment" class="form-select" required>
                        <option value="demo">Sandbox</option>
                        <option value="live">Live</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Vendor currency</label>
                    <select name="currency_code" class="form-select" required>
                        @foreach($currencies as $cur)
                            <option value="{{ $cur }}" @selected(old('currency_code', 'SGD') === $cur)>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Opening credit</label>
                    <input type="number" name="opening_credit" class="form-control" step="0.01" min="0" value="{{ old('opening_credit') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Low-balance alert</label>
                    <input type="number" name="low_balance_threshold" class="form-control" step="0.01" min="0" value="{{ old('low_balance_threshold') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" maxlength="1000" value="{{ old('notes') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input type="hidden" name="enforce_limit" value="0">
                        <input class="form-check-input" type="checkbox" name="enforce_limit" value="1" id="vcEnfNew" checked>
                        <label class="form-check-label" for="vcEnfNew">Block bookings when balance is not enough</label>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Create</button></div>
            </form>
        </div>
    </div>

    {{-- Statement --}}
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0">Statement</h5>
            <a class="btn btn-sm btn-outline-success" href="{{ route('supplier-credits.export', request()->query()) }}"><i class="ri-download-2-line"></i> Export CSV</a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="account_id" class="form-select form-select-sm">
                        <option value="">All vendors</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}" @selected((string) ($filters['account_id'] ?? '') === (string) $a->id)>{{ $supplierCodes[$a->supplier_code] ?? $a->supplier_code }} · {{ $a->environmentLabel() }} ({{ $a->currency_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="txn_type" class="form-select form-select-sm">
                        <option value="">All types</option>
                        @foreach($typeLabels as $k => $label)
                            <option value="{{ $k }}" @selected(($filters['txn_type'] ?? '') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="reconciliation" class="form-select form-select-sm">
                        <option value="">Any reconciliation</option>
                        <option value="unreconciled" @selected(($filters['reconciliation'] ?? '') === 'unreconciled')>Unreconciled</option>
                        <option value="needs_review" @selected(($filters['reconciliation'] ?? '') === 'needs_review')>Needs review</option>
                        <option value="reconciled" @selected(($filters['reconciliation'] ?? '') === 'reconciled')>Reconciled</option>
                    </select>
                </div>
                <div class="col-md-1"><input type="number" name="tour_id" class="form-control form-control-sm" placeholder="Tour #" value="{{ $filters['tour_id'] ?? '' }}"></div>
                <div class="col-md-1"><input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}"></div>
                <div class="col-md-1"><input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}"></div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('supplier-credits.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover vc-table">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th><th>Vendor</th><th>What happened</th><th>Tour / Order</th>
                            <th class="text-end">Vendor amount</th><th class="text-end">Change</th><th class="text-end">Balance after</th>
                            <th>Reference</th><th>Reconciliation</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $t)
                        <tr>
                            <td class="text-nowrap">{{ optional($t->created_at)->format('d M Y H:i') }}</td>
                            <td>{{ $supplierCodes[$t->supplier_code] ?? $t->supplier_code }}<br><span class="badge {{ $t->environment === 'live' ? 'bg-label-danger' : 'bg-label-secondary' }}">{{ $t->environment === 'live' ? 'Live' : 'Sandbox' }}</span></td>
                            <td>
                                <b>{{ $t->typeLabel() }}</b>
                                @if($t->service_type)<span class="text-muted">· {{ ucfirst($t->service_type) }}</span>@endif
                                @if($t->description)<div class="text-muted">{{ $t->description }}</div>@endif
                            </td>
                            <td class="text-nowrap">
                                @if($t->tour_id)Tour #{{ $t->tour_id }}@endif
                                @if($t->order_id)<div class="text-muted">Order {{ $t->order_id }}</div>@endif
                            </td>
                            <td class="text-end">
                                @if($t->supplier_amount !== null){{ $t->supplier_currency }} {{ number_format((float) $t->supplier_amount, 2) }}@endif
                                @if($t->fx_rate !== null && $t->supplier_currency !== $t->currency_code)
                                    <div class="vc-fx">1 {{ $t->supplier_currency }} = {{ rtrim(rtrim(number_format((float) $t->fx_rate, 6), '0'), '.') }} {{ $t->currency_code }}</div>
                                @endif
                                @if((float) $t->cancellation_charge > 0)
                                    <div class="vc-fx">less charge {{ $t->currency_code }} {{ number_format((float) $t->cancellation_charge, 2) }}</div>
                                @endif
                            </td>
                            <td class="text-end {{ $t->direction === 'debit' ? 'vc-amt-debit' : 'vc-amt-credit' }}">
                                {{ $t->direction === 'debit' ? '−' : '+' }}{{ $t->currency_code }} {{ number_format((float) $t->amount, 2) }}
                            </td>
                            <td class="text-end text-nowrap">{{ $t->currency_code }} {{ number_format((float) $t->balance_after, 2) }}</td>
                            <td class="small">
                                @if($t->supplier_booking_reference)<div>Vendor: {{ $t->supplier_booking_reference }}</div>@endif
                                @if($t->agency_reference)<div class="text-muted">Ours: {{ $t->agency_reference }}</div>@endif
                            </td>
                            <td>
                                @php $rc = $t->reconciliation_status; @endphp
                                <span class="badge {{ $rc === 'reconciled' ? 'bg-label-success' : ($rc === 'needs_review' ? 'bg-label-warning' : 'bg-label-secondary') }}">
                                    {{ $rc === 'needs_review' ? 'Needs review' : ucfirst($rc) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No transactions yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
