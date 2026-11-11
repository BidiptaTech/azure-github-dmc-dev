{{-- Payment Details: compact Online + Offline panels. --}}
@php
    $spdCurrency = strtoupper(trim((string) ($tourCurrency ?? 'SGD')));
    $spdExcludeOnline = strtolower(trim((string) ($tour->order_classification ?? ''))) !== 'online';
    $spdOnlinePaid = 0.0;
    $spdOfflinePaid = 0.0;
    $spdRows = is_array($paymentData ?? null) ? $paymentData : [];
    $spdOnlineRows = [];
    $spdOfflineRows = [];

    foreach ($spdRows as $spdIndex => $spdPay) {
        if (! is_array($spdPay)) {
            continue;
        }
        $isOnlineRow = strtolower(trim((string) ($spdPay['payment_for'] ?? ''))) === 'online';
        if ($isOnlineRow) {
            $spdOnlineRows[$spdIndex] = $spdPay;
        } else {
            $spdOfflineRows[$spdIndex] = $spdPay;
        }
        if ((int) ($spdPay['status'] ?? 0) !== 1) {
            continue;
        }
        $amt = (float) ($spdPay['amount'] ?? 0);
        if ($isOnlineRow) {
            $spdOnlinePaid += $amt;
        } else {
            $spdOfflinePaid += $amt;
        }
    }

    $spdInvoiceBase = round((float) ($baseAmount ?? 0), 2);
    $spdInvoiceTax = round((float) ($taxAmount ?? 0), 2);
    $spdInvoiceTotal = round((float) ($finalAmount ?? 0), 2);
    $spdTaxIncluded = ! empty($taxIncluded);
    $spdOnlineTaxIncluded = ! empty($onlineTaxIncluded);

    $spdOfflineBase = $spdInvoiceBase;
    $spdOfflineTax = $spdInvoiceTax;
    $spdOfflineTotal = $spdInvoiceTotal;
    if ($spdExcludeOnline && $spdOnlinePaid > 0.009) {
        $spdOfflineTotal = max(0, round($spdOfflineTotal - $spdOnlinePaid, 2));
        if ($spdTaxIncluded) {
            $spdOfflineBase = $spdOfflineTotal;
            $spdOfflineTax = 0.0;
        } else {
            $spdOfflineBase = max(0, round($spdOfflineTotal - $spdOfflineTax, 2));
        }
    }

    $spdOfflineRemaining = max(0, round($spdOfflineTotal - $spdOfflinePaid, 2));
    $spdOnlineTotal = $spdExcludeOnline ? round($spdOnlinePaid, 2) : $spdInvoiceTotal;
    $spdOnlineBase = $spdExcludeOnline ? round($spdOnlinePaid, 2) : $spdInvoiceBase;
    $spdOnlineTax = $spdExcludeOnline ? 0.0 : $spdInvoiceTax;
    $spdOnlineRemaining = $spdExcludeOnline
        ? 0.0
        : max(0, round($spdOnlineTotal - $spdOnlinePaid, 2));

    $financeRoles = [36, 33, 37, 38, 126, 127, 128, 129, 130, 131, 133, 134, 135, 136, 137, 138];
    $canManagePayments = in_array((int) (auth()->user()->role_id ?? 0), $financeRoles, true);
    $spdHasAnyRows = ! empty($spdOnlineRows) || ! empty($spdOfflineRows);
    $spdShowOfflinePanel = $spdExcludeOnline || ! empty($spdOfflineRows);
    $spdShowOnlinePanel = ! $spdExcludeOnline || ! empty($spdOnlineRows) || $spdOnlinePaid > 0.009;

    $spdFmt = static function (float $n, string $cur): string {
        return number_format($n, $cur === 'IDR' && abs($n - round($n)) < 0.001 ? 0 : 2) . ' ' . $cur;
    };
@endphp
<style>
    #showPaymentModal{{ $tour->tour_id }} .modal-dialog {
        max-width: min(1080px, 94vw);
        width: 94vw;
    }
    #showPaymentModal{{ $tour->tour_id }} .modal-content { max-height: 88vh; }
    #showPaymentModal{{ $tour->tour_id }} .modal-header { padding: 0.4rem 0.75rem; }
    #showPaymentModal{{ $tour->tour_id }} .modal-title { font-size: 0.9rem; }
    #showPaymentModal{{ $tour->tour_id }} .modal-body {
        overflow: auto;
        max-height: calc(88vh - 96px);
        padding: 0.6rem 0.75rem !important;
    }
    #showPaymentModal{{ $tour->tour_id }} .modal-footer { padding: 0.35rem 0.75rem; }
    #showPaymentModal{{ $tour->tour_id }} .spd-stack { display: flex; flex-direction: column; gap: 0.5rem; }
    #showPaymentModal{{ $tour->tour_id }} .spd-panel {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-panel-online { border-color: #fdba74; }
    #showPaymentModal{{ $tour->tour_id }} .spd-panel-offline { border-color: #93c5fd; }
    #showPaymentModal{{ $tour->tour_id }} .spd-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.3rem 0.55rem;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.2;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-panel-online .spd-panel-head {
        background: #fff7ed;
        color: #9a3412;
        border-bottom: 1px solid #fed7aa;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-panel-offline .spd-panel-head {
        background: #eff6ff;
        color: #1e40af;
        border-bottom: 1px solid #bfdbfe;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-panel-head .badge {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.2em 0.45em;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-panel-body { padding: 0.4rem 0.5rem 0.45rem; }
    #showPaymentModal{{ $tour->tour_id }} .payment-table-wrap {
        overflow: auto;
        max-height: 22vh;
        margin-bottom: 0.35rem;
    }
    #showPaymentModal{{ $tour->tour_id }} .payment-table-wrap .table {
        width: 100%;
        margin-bottom: 0;
        font-size: 0.7rem;
    }
    #showPaymentModal{{ $tour->tour_id }} .table th,
    #showPaymentModal{{ $tour->tour_id }} .table td {
        font-size: 0.7rem;
        padding: 0.28rem 0.4rem;
        white-space: nowrap;
        vertical-align: middle;
        line-height: 1.25;
    }
    #showPaymentModal{{ $tour->tour_id }} .table td.status-cell,
    #showPaymentModal{{ $tour->tour_id }} .table td.actions-cell {
        overflow: visible;
        white-space: normal;
    }
    #showPaymentModal{{ $tour->tour_id }} .table td[title] { cursor: help; }
    #showPaymentModal{{ $tour->tour_id }} tr.is-online-payment { background: #fff7ed; }
    #showPaymentModal{{ $tour->tour_id }} .spd-badge {
        font-size: 0.65rem;
        padding: 0.15em 0.4em;
        font-weight: 600;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-act {
        width: 20px;
        height: 20px;
        padding: 0;
        line-height: 1;
        font-size: 0.65rem;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-stat {
        flex: 1 1 auto;
        min-width: 5.5rem;
        border-radius: 4px;
        padding: 0.25rem 0.4rem;
        line-height: 1.15;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-stat span {
        display: block;
        font-size: 0.62rem;
        opacity: 0.85;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-stat b {
        font-size: 0.75rem;
        font-weight: 700;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-note {
        margin: 0.3rem 0 0;
        font-size: 0.68rem;
        color: #6b7280;
        line-height: 1.3;
    }
    #showPaymentModal{{ $tour->tour_id }} .spd-empty {
        padding: 0.35rem 0.5rem;
        text-align: center;
        color: #6b7280;
        font-size: 0.72rem;
        background: #f9fafb;
        border-radius: 4px;
        margin-bottom: 0.35rem;
    }
</style>
<div class="modal fade" id="showPaymentModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="showPaymentModalLabel{{ $tour->tour_id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content shadow-sm border-0">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title d-flex align-items-center mb-0" id="showPaymentModalLabel{{ $tour->tour_id }}">
                    <i class="fas fa-history me-2" style="color: #38ef7d;"></i>
                    Payment Details — Tour #{{ $tour->tour_id }}
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($spdHasAnyRows || $spdShowOnlinePanel || $spdShowOfflinePanel)
                    <div class="spd-stack">

                        {{-- ONLINE --}}
                        @if($spdShowOnlinePanel)
                            <section class="spd-panel spd-panel-online">
                                <div class="spd-panel-head">
                                    <span><i class="fas fa-globe me-1"></i>Online</span>
                                    <span class="badge bg-warning text-dark">Paid {{ $spdFmt($spdOnlinePaid, $spdCurrency) }}</span>
                                </div>
                                <div class="spd-panel-body">
                                    @if(!empty($spdOnlineRows))
                                        <div class="payment-table-wrap">
                                            <table class="table table-bordered table-hover table-sm mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="text-center">Status</th>
                                                        @if($canManagePayments)<th class="text-center">Act</th>@endif
                                                        <th class="text-center">Date</th>
                                                        <th class="text-center">Amount</th>
                                                        <th class="text-center">Debit</th>
                                                        <th class="text-center">Rate</th>
                                                        <th class="text-center">Mode</th>
                                                        <th class="text-center">Trans ID</th>
                                                        <th class="text-center">Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($spdOnlineRows as $index => $payment)
                                                        @php
                                                            $walletAmt = isset($payment['wallet_amount']) ? (float) $payment['wallet_amount'] : null;
                                                            $walletCur = strtoupper(trim((string) ($payment['wallet_currency'] ?? '')));
                                                            $debitAmt = $walletAmt;
                                                            $debitCur = $walletCur;
                                                            if ($debitAmt === null) {
                                                                $debitAmt = isset($payment['original_amount'])
                                                                    ? (float) $payment['original_amount']
                                                                    : (isset($payment['amount']) ? (float) $payment['amount'] : null);
                                                                $debitCur = strtoupper(trim((string) ($payment['currency'] ?? $spdCurrency)));
                                                            }
                                                        @endphp
                                                        <tr class="is-online-payment">
                                                            <td class="text-center status-cell">
                                                                @if(isset($payment['status']) && (int) $payment['status'] === 1)
                                                                    <span class="badge bg-success text-white spd-badge">Verified</span>
                                                                @elseif(isset($payment['status']) && (int) $payment['status'] === 2)
                                                                    <span class="badge bg-danger text-white spd-badge">Declined</span>
                                                                @elseif(isset($payment['status']))
                                                                    <span class="badge bg-warning text-dark spd-badge">Pending</span>
                                                                @else
                                                                    <span class="badge bg-secondary text-white spd-badge">—</span>
                                                                @endif
                                                            </td>
                                                            @if($canManagePayments)
                                                                <td class="text-center actions-cell">
                                                                    <div class="d-flex justify-content-center gap-1 flex-nowrap">
                                                                        @if(!isset($payment['status']) || (int) $payment['status'] === 0)
                                                                            <button type="button" class="btn btn-xs btn-success spd-act" onclick="verifyPayment({{ $tour->tour_id }}, {{ $index }})" title="Approve"><i class="fas fa-check"></i></button>
                                                                            <button type="button" class="btn btn-xs btn-danger spd-act" onclick="declinePayment({{ $tour->tour_id }}, {{ $index }})" title="Reject"><i class="fas fa-times"></i></button>
                                                                        @endif
                                                                        @if(!isset($payment['status']) || (int) $payment['status'] !== 2)
                                                                            <button type="button" class="btn btn-xs btn-primary spd-act" onclick="openEditPaymentModal({{ $tour->tour_id }}, {{ $index }})" title="Edit"><i class="fas fa-edit"></i></button>
                                                                        @endif
                                                                        <button type="button" class="btn btn-xs btn-outline-danger spd-act" onclick="deletePayment({{ $tour->tour_id }}, {{ $index }})" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            <td class="text-center">{{ isset($payment['payment_date']) ? \Carbon\Carbon::parse($payment['payment_date'])->format('d M Y') : '—' }}</td>
                                                            <td class="text-center fw-bold text-warning">{{ isset($payment['amount']) ? number_format((float) $payment['amount'], 2) : '0.00' }} {{ $spdCurrency }}</td>
                                                            <td class="text-center">
                                                                @if($debitAmt !== null && $debitCur !== '')
                                                                    {{ number_format($debitAmt, $debitCur === 'IDR' ? 0 : 2) }} {{ $debitCur }}
                                                                @else
                                                                    —
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ isset($payment['exchange_rate']) ? number_format((float) $payment['exchange_rate'], 4) : '1.0000' }}</td>
                                                            <td class="text-center">{{ ucfirst($payment['payment_type'] ?? '—') }}</td>
                                                            <td class="text-center" title="{{ $payment['transaction_id'] ?? '' }}">{{ \Illuminate\Support\Str::limit($payment['transaction_id'] ?? '—', 16, '…') }}</td>
                                                            <td class="text-center" title="{{ $payment['remarks'] ?? '' }}">{{ \Illuminate\Support\Str::limit($payment['remarks'] ?? '—', 20, '…') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="spd-empty">No online payments.</div>
                                    @endif

                                    <div class="spd-stats">
                                        {{-- Same chips as Offline; Base/Total = online portion (paid), Tax shown. --}}
                                        <div class="spd-stat bg-secondary text-white">
                                            <span>Base</span>
                                            <b>{{ $spdFmt($spdOnlineBase, $spdCurrency) }}</b>
                                        </div>
                                        <div class="spd-stat bg-info text-white">
                                            <span>Tax</span>
                                            <b>
                                                @if($spdTaxIncluded || $spdOnlineTaxIncluded)
                                                    Included
                                                @else
                                                    {{ $spdFmt($spdOnlineTax, $spdCurrency) }}
                                                @endif
                                            </b>
                                        </div>
                                        <div class="spd-stat bg-primary text-white">
                                            <span>Total</span>
                                            <b>{{ $spdFmt($spdOnlineTotal, $spdCurrency) }}</b>
                                        </div>
                                        <div class="spd-stat bg-success text-white">
                                            <span>Paid</span>
                                            <b>{{ $spdFmt($spdOnlinePaid, $spdCurrency) }}</b>
                                        </div>
                                        <div class="spd-stat bg-warning text-dark">
                                            <span>Remaining</span>
                                            <b>{{ $spdFmt($spdOnlineRemaining, $spdCurrency) }}</b>
                                        </div>
                                    </div>
                                    @if($spdExcludeOnline)
                                        <p class="spd-note">Online is settled separately — not counted in offline remaining.</p>
                                    @endif
                                </div>
                            </section>
                        @endif

                        {{-- OFFLINE --}}
                        @if($spdShowOfflinePanel)
                            <section class="spd-panel spd-panel-offline">
                                <div class="spd-panel-head">
                                    <span><i class="fas fa-handshake me-1"></i>Offline / negotiated</span>
                                    <span class="badge {{ $spdOfflineRemaining > 0.009 ? 'bg-danger' : 'bg-success' }} text-white">
                                        Due {{ $spdFmt($spdOfflineRemaining, $spdCurrency) }}
                                    </span>
                                </div>
                                <div class="spd-panel-body">
                                    @if(!empty($spdOfflineRows))
                                        <div class="payment-table-wrap">
                                            <table class="table table-bordered table-hover table-sm mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="text-center">Status</th>
                                                        @if($canManagePayments)<th class="text-center">Act</th>@endif
                                                        <th class="text-center">Date</th>
                                                        <th class="text-center">Amount</th>
                                                        <th class="text-center">Debit</th>
                                                        <th class="text-center">Rate</th>
                                                        <th class="text-center">Mode</th>
                                                        <th class="text-center">Trans ID</th>
                                                        <th class="text-center">Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($spdOfflineRows as $index => $payment)
                                                        @php
                                                            $walletAmt = isset($payment['wallet_amount']) ? (float) $payment['wallet_amount'] : null;
                                                            $walletCur = strtoupper(trim((string) ($payment['wallet_currency'] ?? '')));
                                                            $debitAmt = $walletAmt;
                                                            $debitCur = $walletCur;
                                                            if ($debitAmt === null) {
                                                                $debitAmt = isset($payment['original_amount'])
                                                                    ? (float) $payment['original_amount']
                                                                    : (isset($payment['amount']) ? (float) $payment['amount'] : null);
                                                                $debitCur = strtoupper(trim((string) ($payment['currency'] ?? $spdCurrency)));
                                                            }
                                                        @endphp
                                                        <tr>
                                                            <td class="text-center status-cell">
                                                                @if(isset($payment['status']) && (int) $payment['status'] === 1)
                                                                    <span class="badge bg-success text-white spd-badge">Verified</span>
                                                                @elseif(isset($payment['status']) && (int) $payment['status'] === 2)
                                                                    <span class="badge bg-danger text-white spd-badge">Declined</span>
                                                                @elseif(isset($payment['status']))
                                                                    <span class="badge bg-warning text-dark spd-badge">Pending</span>
                                                                @else
                                                                    <span class="badge bg-secondary text-white spd-badge">—</span>
                                                                @endif
                                                            </td>
                                                            @if($canManagePayments)
                                                                <td class="text-center actions-cell">
                                                                    <div class="d-flex justify-content-center gap-1 flex-nowrap">
                                                                        @if(!isset($payment['status']) || (int) $payment['status'] === 0)
                                                                            <button type="button" class="btn btn-xs btn-success spd-act" onclick="verifyPayment({{ $tour->tour_id }}, {{ $index }})" title="Approve"><i class="fas fa-check"></i></button>
                                                                            <button type="button" class="btn btn-xs btn-danger spd-act" onclick="declinePayment({{ $tour->tour_id }}, {{ $index }})" title="Reject"><i class="fas fa-times"></i></button>
                                                                        @endif
                                                                        @if(!isset($payment['status']) || (int) $payment['status'] !== 2)
                                                                            <button type="button" class="btn btn-xs btn-primary spd-act" onclick="openEditPaymentModal({{ $tour->tour_id }}, {{ $index }})" title="Edit"><i class="fas fa-edit"></i></button>
                                                                        @endif
                                                                        <button type="button" class="btn btn-xs btn-outline-danger spd-act" onclick="deletePayment({{ $tour->tour_id }}, {{ $index }})" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            <td class="text-center">{{ isset($payment['payment_date']) ? \Carbon\Carbon::parse($payment['payment_date'])->format('d M Y') : '—' }}</td>
                                                            <td class="text-center fw-bold text-success">{{ isset($payment['amount']) ? number_format((float) $payment['amount'], 2) : '0.00' }} {{ $spdCurrency }}</td>
                                                            <td class="text-center">
                                                                @if($debitAmt !== null && $debitCur !== '')
                                                                    {{ number_format($debitAmt, $debitCur === 'IDR' ? 0 : 2) }} {{ $debitCur }}
                                                                @else
                                                                    —
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ isset($payment['exchange_rate']) ? number_format((float) $payment['exchange_rate'], 4) : '1.0000' }}</td>
                                                            <td class="text-center">{{ ucfirst($payment['payment_type'] ?? '—') }}</td>
                                                            <td class="text-center" title="{{ $payment['transaction_id'] ?? '' }}">{{ \Illuminate\Support\Str::limit($payment['transaction_id'] ?? '—', 16, '…') }}</td>
                                                            <td class="text-center" title="{{ $payment['remarks'] ?? '' }}">{{ \Illuminate\Support\Str::limit($payment['remarks'] ?? '—', 20, '…') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="spd-empty">
                                            @if($spdOfflineRemaining <= 0.009)
                                                No offline payment needed (due 0).
                                            @else
                                                No offline payments yet — use Add Payment for due amount.
                                            @endif
                                        </div>
                                    @endif

                                    <div class="spd-stats">
                                        <div class="spd-stat bg-secondary text-white">
                                            <span>Base</span>
                                            <b>{{ $spdFmt($spdOfflineBase, $spdCurrency) }}</b>
                                        </div>
                                        <div class="spd-stat bg-info text-white">
                                            <span>
                                                @if($spdTaxIncluded && ! ($spdExcludeOnline && $spdOnlinePaid > 0.009))
                                                    Tax
                                                @elseif($spdOnlineTaxIncluded)
                                                    Tax (off)
                                                @else
                                                    Tax
                                                @endif
                                            </span>
                                            <b>
                                                @if($spdTaxIncluded && ! ($spdExcludeOnline && $spdOnlinePaid > 0.009))
                                                    Included
                                                @else
                                                    {{ $spdFmt($spdOfflineTax, $spdCurrency) }}
                                                @endif
                                            </b>
                                        </div>
                                        <div class="spd-stat bg-primary text-white">
                                            <span>Total</span>
                                            <b>{{ $spdFmt($spdOfflineTotal, $spdCurrency) }}</b>
                                        </div>
                                        <div class="spd-stat bg-success text-white">
                                            <span>Paid</span>
                                            <b>{{ $spdFmt($spdOfflinePaid, $spdCurrency) }}</b>
                                        </div>
                                        <div class="spd-stat {{ $spdOfflineRemaining > 0.009 ? 'bg-danger text-white' : 'bg-warning text-dark' }}">
                                            <span>Remaining</span>
                                            <b>{{ $spdFmt($spdOfflineRemaining, $spdCurrency) }}</b>
                                        </div>
                                    </div>
                                    @if($spdExcludeOnline && $spdOnlinePaid > 0.009)
                                        <p class="spd-note">Online {{ $spdFmt($spdOnlinePaid, $spdCurrency) }} already deducted from offline total.</p>
                                    @endif
                                </div>
                            </section>
                        @endif

                    </div>
                @else
                    <div class="text-center py-3">
                        <p class="text-muted mb-0 small">No payments recorded for this tour yet.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
