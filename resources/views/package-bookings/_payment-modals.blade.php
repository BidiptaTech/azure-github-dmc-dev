@php
    /** @var \Illuminate\Support\Collection $bookings */
    $pkgCurrency = $pkgCurrency ?? 'SGD';
@endphp

@foreach($bookings as $b)
    @php
        $details = is_array($b->booking_details ?? null) ? ($b->booking_details ?? []) : (is_string($b->booking_details ?? null) ? (json_decode($b->booking_details, true) ?: []) : []);
        $approxActual = (float) \App\Helpers\CommonHelper::calculatePackageBookingActualAmount((string) $b->booking_id, $b->package_id ?? null, $b->dmc_id ?? null);
        $bookingComments = ($packageComments ?? collect([]))
            ->where('booking_id', $b->booking_id)
            ->sortByDesc('created_at')
            ->values();
        $agentNegotiationBaseline = \App\Helpers\CommonHelper::packageNegotiatedPriceExclTax($approxActual, $bookingComments);
        $taxResult = \App\Helpers\CommonHelper::calculatePackageBookingTaxBreakdown(
            (float) $agentNegotiationBaseline,
            $b->taxes ?? [],
            $details
        );
        $taxAmount = (float) ($taxResult['total_tax'] ?? 0);
        $taxBreakdown = $taxResult['breakdown'] ?? [];
        $finalTotalInclTax = (float) $agentNegotiationBaseline + $taxAmount;
        $paidAmount = 0.0;
        $hasPendingPayments = false;
        $paymentRows = \App\Helpers\CommonHelper::normalizeJsonArray($b->payment_details);
        foreach ($paymentRows as $p) {
            if ((int) ($p['status'] ?? 0) === 1) {
                $paidAmount += (float) ($p['payment_amount'] ?? 0);
            }
            if ((int) ($p['status'] ?? 0) === 0) {
                $hasPendingPayments = true;
            }
        }
        $dueAmount = max(0, $finalTotalInclTax - $paidAmount);
        $packageActualPrice = round($approxActual, 2);
        $packageBaseAmount = round((float) $agentNegotiationBaseline, 2);
        $packageDiscountAmount = max(0, round($packageActualPrice - $packageBaseAmount, 2));
        $u = auth()->user();
        $canAddPkgPayment = $u && (
            (function_exists('hasPermission') && hasPermission('add payment'))
            || in_array((int) ($u->role_id ?? 0), [11, 33, 36, 37, 38, 126, 127, 128, 129, 131, 132, 133, 134, 135, 137, 138], true)
        );
        $canFinancePkgPayment = $u && in_array((int) ($u->role_id ?? 0), [11, 33, 36, 37, 38, 126, 127, 128, 129, 131, 132, 133, 134, 136, 137, 138], true);
    @endphp

    @if($canAddPkgPayment)
        <div class="modal fade" id="pkgAddPaymentModal{{ $b->id }}" tabindex="-1" aria-labelledby="pkgAddPaymentLabel{{ $b->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content shadow rounded">
                    <div class="modal-header text-white d-flex align-items-center justify-content-start border-0" style="padding: 10px 12px; border-radius: 8px 8px 0 0; background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);">
                        <h6 class="modal-title d-flex align-items-center mb-0 fw-semibold" id="pkgAddPaymentLabel{{ $b->id }}" style="color: white;">
                            <i class="fas fa-money-bill-wave me-2" style="color: #38ef7d; font-size: 1.1rem;"></i>
                            <span>Add Payment for Package Booking #{{ $b->booking_id }}</span>
                        </h6>
                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                    </div>
                    <div class="modal-body p-2">
                        <form id="pkgPaymentForm{{ $b->id }}" method="POST" action="{{ route('package.add-payment', $b->booking_id) }}">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $b->booking_id }}">

                            <div class="mb-2">
                                <label class="form-label fw-bold mb-1" style="font-size: .85rem;">
                                    <i class="fas fa-info-circle text-info me-2"></i>Payment Information
                                </label>
                                <div class="mb-1" style="font-size: .8rem;">
                                    <small class="text-muted">Package currency: <strong>{{ $pkgCurrency }}</strong></small>
                                </div>
                                <div class="alert alert-info mb-0 py-2 px-2" style="font-size: .82rem;">
                                    @if($packageDiscountAmount > 0)
                                        <div class="row text-center mb-2">
                                            <div class="col-6">
                                                <small class="text-muted">Actual Price</small>
                                                <div class="fw-bold text-secondary">{{ number_format($packageActualPrice, 2) }} {{ $pkgCurrency }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Discount</small>
                                                <div class="fw-bold text-success">- {{ number_format($packageDiscountAmount, 2) }} {{ $pkgCurrency }}</div>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                    @endif
                                    <div class="row text-center mb-2">
                                        <div class="col-4">
                                            <small class="text-muted">Base Amount</small>
                                            <div class="fw-bold text-dark">{{ number_format($packageBaseAmount, 2) }} {{ $pkgCurrency }}</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">
                                                Tax @if(!empty($taxBreakdown))({{ count($taxBreakdown) }})@endif
                                            </small>
                                            <div class="fw-bold text-warning">{{ number_format(round($taxAmount, 2), 2) }} {{ $pkgCurrency }}</div>
                                            @if(!empty($taxBreakdown))
                                                <div style="font-size: 0.68rem; margin-top: 2px; line-height: 1.1;">
                                                    @foreach($taxBreakdown as $taxName => $taxVal)
                                                        <div class="text-primary">{{ $taxName }}: {{ number_format(round((float) $taxVal, 2), 2) }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Total Amount</small>
                                            <div class="fw-bold text-primary">{{ number_format(round($finalTotalInclTax, 2), 2) }} {{ $pkgCurrency }}</div>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <small class="text-muted">Paid Amount</small>
                                            <div class="fw-bold text-success">{{ number_format(round($paidAmount, 2), 2) }} {{ $pkgCurrency }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Remaining</small>
                                            <div class="fw-bold text-danger">{{ number_format(round($dueAmount, 2), 2) }} {{ $pkgCurrency }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="pkg_currency{{ $b->id }}" class="form-label fw-bold mb-1" style="font-size: .85rem;">
                                    <i class="fas fa-coins text-warning me-2"></i>Select Currency
                                </label>
                                <select class="form-select form-select-sm" id="pkg_currency{{ $b->id }}" name="currency">
                                    <option value="">Select Currency</option>
                                    @foreach(\App\Models\Setting::getCurrencyCodes() as $cur)
                                        <option value="{{ $cur }}" {{ $cur === $pkgCurrency ? 'selected' : '' }}>{{ $cur }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: .75rem;">Amounts above are in <strong>{{ $pkgCurrency }}</strong>. Enter the payment in the same currency unless your finance team converts externally.</small>
                            </div>

                            <div class="mb-2">
                                <label for="pkg_payment_amount{{ $b->id }}" class="form-label fw-bold mb-1" style="font-size: .85rem;">
                                    <i class="fas fa-money-bill-wave text-success me-2"></i>Payment Amount
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">{{ $pkgCurrency }}</span>
                                    <input type="number"
                                        class="form-control form-control-sm"
                                        id="pkg_payment_amount{{ $b->id }}"
                                        name="payment_amount"
                                        placeholder="Enter payment amount"
                                        required
                                        min="0.01"
                                        max="{{ $dueAmount }}"
                                        step="0.01"
                                        value="{{ $dueAmount > 0 ? number_format($dueAmount, 2, '.', '') : '' }}"
                                        oninput="if(this.value && parseFloat(this.value) > {{ $dueAmount }}) this.value = {{ $dueAmount }};"
                                    >
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="pkg_payment_date{{ $b->id }}" class="form-label fw-bold mb-1" style="font-size: .85rem;">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>Payment Date
                                </label>
                                <input type="date"
                                    class="form-control form-control-sm"
                                    id="pkg_payment_date{{ $b->id }}"
                                    name="payment_date"
                                    value="{{ date('Y-m-d') }}"
                                    required
                                >
                            </div>

                            <div class="mb-2">
                                <label for="pkg_payment_type{{ $b->id }}" class="form-label fw-bold mb-1" style="font-size: .85rem;">
                                    <i class="fas fa-credit-card text-primary me-2"></i>Payment Type
                                </label>
                                <select class="form-select form-select-sm" id="pkg_payment_type{{ $b->id }}" name="payment_type" required>
                                    <option value="">Select Payment Type</option>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Bank Transfer</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label for="pkg_transaction_id{{ $b->id }}" class="form-label fw-bold mb-1" style="font-size: .85rem;">
                                    <i class="fas fa-hashtag text-primary me-2"></i>Transaction ID (Optional)
                                </label>
                                <input type="text" class="form-control form-control-sm" id="pkg_transaction_id{{ $b->id }}" name="transaction_id" placeholder="—">
                            </div>

                            <div class="mb-2">
                                <label for="pkg_remarks{{ $b->id }}" class="form-label fw-bold mb-1" style="font-size: .85rem;">
                                    <i class="fas fa-comment-alt text-warning me-2"></i>Remarks (Optional)
                                </label>
                                <textarea class="form-control form-control-sm" id="pkg_remarks{{ $b->id }}" name="remarks" rows="2" placeholder="Enter payment remarks here..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer bg-light d-flex justify-content-between" style="padding: 10px 12px; border-radius: 0 0 8px 8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-sm btn-success" onclick="document.getElementById('pkgPaymentForm{{ $b->id }}').submit()">
                            <i class="fas fa-save me-2"></i>Verify Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($paymentRows))
        @php
            $approvedByIds = collect($paymentRows)
                ->map(fn ($p) => (int) ($p['approved_by'] ?? 0))
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            $approvedByNameMap = empty($approvedByIds)
                ? []
                : \App\Models\User::query()
                    ->whereIn('userId', $approvedByIds)
                    ->with('role')
                    ->get()
                    ->mapWithKeys(function ($u) {
                        $roleName = data_get($u, 'role.name');
                        $label = trim((string) ($u->name ?? ''));
                        if (!empty($roleName)) {
                            $label .= ' (' . $roleName . ')';
                        }
                        return [data_get($u, 'userId') => $label];
                    })
                    ->all();
        @endphp

        <div class="modal fade" id="pkgPaymentHistoryModal{{ $b->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%); padding: 10px 14px;">
                        <h6 class="modal-title mb-0 fw-semibold">Payment history — {{ $b->booking_id }}</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-2">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <div class="flex-grow-1 border rounded px-3 py-2 bg-white">
                                <div class="text-muted small">Total (incl. tax)</div>
                                <div class="fw-bold">{{ $pkgCurrency }} {{ number_format($finalTotalInclTax, 2) }}</div>
                            </div>
                            <div class="flex-grow-1 border rounded px-3 py-2 bg-white border-success border-opacity-50">
                                <div class="text-muted small">Paid (approved)</div>
                                <div class="fw-bold text-success">{{ $pkgCurrency }} {{ number_format($paidAmount, 2) }}</div>
                            </div>
                            <div class="flex-grow-1 border rounded px-3 py-2 bg-white border-warning border-opacity-50">
                                <div class="text-muted small">Due</div>
                                <div class="fw-bold text-warning">{{ $pkgCurrency }} {{ number_format($dueAmount, 2) }}</div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle" style="font-size: .82rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Txn ID</th>
                                        <th>Approved By</th>
                                        <th style="width:150px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentRows as $index => $payment)
                                        <tr>
                                            <td>{{ isset($payment['payment_date']) ? \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') : '—' }}</td>
                                            <td class="fw-semibold">{{ $pkgCurrency }} {{ number_format((float) ($payment['payment_amount'] ?? 0), 2) }}</td>
                                            <td>{{ $payment['payment_type'] ?? '—' }}</td>
                                            <td>
                                                @if((int) ($payment['status'] ?? 0) === 1)
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif((int) ($payment['status'] ?? 0) === 2)
                                                    <span class="badge bg-danger">Declined</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $payment['transaction_id'] ?? '—' }}</small></td>
                                            <td>
                                                @php
                                                    $approvedById = (int) ($payment['approved_by'] ?? 0);
                                                    $approvedByName = $approvedById > 0 ? ($approvedByNameMap[$approvedById] ?? null) : null;
                                                @endphp
                                                <small class="text-muted">{{ $approvedByName ?: '—' }}</small>
                                            </td>
                                            <td>
                                                @if((int) ($payment['status'] ?? 0) === 0)
                                                    <div class="d-flex flex-column gap-1">
                                                        @if($canFinancePkgPayment)
                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <button type="button" class="btn btn-sm btn-outline-success px-2" title="Approve" onclick="pkgApprovePackagePayment('{{ $b->booking_id }}', {{ (int) $index }})"><i class="ri-check-line"></i></button>
                                                                <button type="button" class="btn btn-sm btn-outline-danger px-2" title="Reject" onclick="pkgDeclinePackagePayment('{{ $b->booking_id }}', {{ (int) $index }})"><i class="ri-close-line"></i></button>
                                                            </div>
                                                        @endif
                                                        @if($canAddPkgPayment)
                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-secondary px-2"
                                                                    title="Edit"
                                                                    data-amt="{{ (float) ($payment['payment_amount'] ?? 0) }}"
                                                                    data-pdate="{{ isset($payment['payment_date']) ? \Carbon\Carbon::parse($payment['payment_date'])->format('Y-m-d') : '' }}"
                                                                    data-ptype="{{ e((string) ($payment['payment_type'] ?? 'cash')) }}"
                                                                    data-txn="{{ e((string) ($payment['transaction_id'] ?? '')) }}"
                                                                    onclick="pkgOpenEditPackagePayment('{{ $b->booking_id }}', {{ $b->id }}, {{ (int) $index }}, this)"
                                                                ><i class="ri-edit-line"></i></button>
                                                                <button type="button" class="btn btn-sm btn-outline-danger px-2" title="Delete" onclick="pkgDeletePackagePayment('{{ $b->booking_id }}', {{ (int) $index }})"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @elseif((int) ($payment['status'] ?? 0) === 2 && !empty($payment['decline_reason']))
                                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit((string) $payment['decline_reason'], 40) }}</small>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="pkgEditPaymentModal{{ $b->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Edit payment</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="pkgEditPaymentForm{{ $b->id }}" onsubmit="return false;">
                        @csrf
                        <input type="hidden" name="payment_index" id="pkgEditPayIdx{{ $b->id }}">
                        <div class="mb-2">
                            <label class="form-label small">Amount</label>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="payment_amount" id="pkgEditPayAmt{{ $b->id }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Date</label>
                            <input type="date" class="form-control form-control-sm" name="payment_date" id="pkgEditPayDate{{ $b->id }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Mode</label>
                            <select class="form-select form-select-sm" name="payment_type" id="pkgEditPayType{{ $b->id }}" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Bank transfer</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Transaction ID</label>
                            <input type="text" class="form-control form-control-sm" name="transaction_id" id="pkgEditPayTxn{{ $b->id }}">
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="pkgSubmitEditPackagePayment('{{ $b->booking_id }}', {{ $b->id }})">Save</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
