<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\SupplierCreditAccount;
use App\Models\SupplierCreditTransaction;
use App\Services\SupplierCredit\SupplierCreditService;
use App\Services\SupplierEnvService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * TravClicks admin: credit held with each API vendor (sandbox + live) and its statement.
 */
class SupplierCreditController extends Controller
{
    public function __construct(
        private SupplierCreditService $credits,
        private SupplierEnvService $supplierEnv,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $accounts = SupplierCreditAccount::query()
            ->orderBy('supplier_code')
            ->orderByDesc('environment')
            ->get();

        $filters = $this->filters($request);
        $transactions = $this->transactionQuery($filters)
            ->with('account')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('supplier-credits.index', [
            'accounts' => $accounts,
            'transactions' => $transactions,
            'filters' => $filters,
            'supplierCodes' => $this->supplierCodes(),
            'currencies' => $this->currencies(),
            'typeLabels' => SupplierCreditTransaction::TYPE_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'supplier_code' => ['required', 'string', Rule::in(array_keys($this->supplierCodes()))],
            'environment' => ['required', Rule::in(['demo', 'live'])],
            'currency_code' => ['required', 'string', Rule::in($this->currencies())],
            'opening_credit' => ['nullable', 'numeric', 'min:0', 'max:999999999999', 'decimal:0,2'],
            'low_balance_threshold' => ['nullable', 'numeric', 'min:0', 'max:999999999999', 'decimal:0,2'],
            'enforce_limit' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $code = $this->credits->normalizeCode($data['supplier_code']);
        if ($this->credits->findAccount($code, $data['environment'])) {
            return back()->withInput()->with('error', 'A credit account for this vendor and environment already exists. Edit it instead.');
        }

        $account = new SupplierCreditAccount;
        $account->fill([
            'supplier_code' => $code,
            'environment' => $data['environment'],
            'currency_code' => $data['currency_code'],
            'low_balance_threshold' => $data['low_balance_threshold'] ?? null,
            'enforce_limit' => $request->boolean('enforce_limit'),
            'status' => 'active',
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ])->save();

        $opening = (float) ($data['opening_credit'] ?? 0);
        if ($opening > 0) {
            $this->credits->adminPost($account, 'topup', $opening, 'Opening credit', auth()->id(), $request->ip());
        }

        return redirect()->route('supplier-credits.index')->with('success', 'Vendor credit account created.');
    }

    public function update(Request $request, SupplierCreditAccount $account): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'currency_code' => ['required', 'string', Rule::in($this->currencies())],
            'low_balance_threshold' => ['nullable', 'numeric', 'min:0', 'max:999999999999', 'decimal:0,2'],
            'enforce_limit' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['currency_code'] !== $account->currency_code && $account->transactions()->exists()) {
            return back()->with('error', 'Currency cannot be changed after transactions exist. Create a new account instead.');
        }

        $account->fill([
            'currency_code' => $data['currency_code'],
            'low_balance_threshold' => $data['low_balance_threshold'] ?? null,
            'enforce_limit' => $request->boolean('enforce_limit'),
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'updated_by' => auth()->id(),
        ])->save();

        return back()->with('success', 'Vendor credit account updated.');
    }

    public function post(Request $request, SupplierCreditAccount $account): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'type' => ['required', Rule::in(['topup', 'adjustment_credit', 'adjustment_debit'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999', 'decimal:0,2'],
            'note' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        try {
            $txn = $this->credits->adminPost($account, $data['type'], (float) $data['amount'], $data['note'], auth()->id(), $request->ip());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', sprintf(
            '%s of %s %s posted. New balance: %s %s.',
            $txn->typeLabel(),
            $txn->currency_code,
            number_format((float) $txn->amount, 2),
            $txn->currency_code,
            number_format((float) $txn->balance_after, 2),
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $query = $this->transactionQuery($this->filters($request))->orderBy('id');
        $filename = 'supplier-credit-statement-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Date', 'Vendor', 'Environment', 'Transaction', 'Tour #', 'Order #', 'Service',
                'Vendor amount', 'Vendor currency', 'FX rate', 'Debit', 'Credit', 'Account currency',
                'Balance before', 'Balance after', 'Cancellation charge', 'Vendor reference',
                'Agency reference', 'Reconciliation', 'Description',
            ]);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, array_map([$this, 'csvSafe'], [
                        optional($t->created_at)->format('Y-m-d H:i:s'),
                        $t->supplier_code,
                        $t->environment === 'live' ? 'Live' : 'Sandbox',
                        $t->typeLabel(),
                        $t->tour_id,
                        $t->order_id,
                        $t->service_type,
                        $t->supplier_amount !== null ? number_format((float) $t->supplier_amount, 2, '.', '') : '',
                        $t->supplier_currency,
                        $t->fx_rate !== null ? rtrim(rtrim(number_format((float) $t->fx_rate, 8, '.', ''), '0'), '.') : '',
                        $t->direction === 'debit' ? number_format((float) $t->amount, 2, '.', '') : '',
                        $t->direction === 'credit' ? number_format((float) $t->amount, 2, '.', '') : '',
                        $t->currency_code,
                        number_format((float) $t->balance_before, 2, '.', ''),
                        number_format((float) $t->balance_after, 2, '.', ''),
                        $t->cancellation_charge !== null ? number_format((float) $t->cancellation_charge, 2, '.', '') : '',
                        $t->supplier_booking_reference,
                        $t->agency_reference,
                        $t->reconciliation_status,
                        $t->description,
                    ]));
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Prevent spreadsheet formula injection from vendor-supplied strings.
     */
    public function csvSafe(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric($value))
            ? "'".$value
            : $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $validated = validator($request->query(), [
            'account_id' => ['nullable', 'integer'],
            'txn_type' => ['nullable', Rule::in(array_keys(SupplierCreditTransaction::TYPE_LABELS))],
            'reconciliation' => ['nullable', Rule::in(['unreconciled', 'reconciled', 'needs_review'])],
            'tour_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ])->validate();

        return array_filter($validated, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @param  array<string, mixed>  $f
     */
    private function transactionQuery(array $f): Builder
    {
        return SupplierCreditTransaction::query()
            ->when($f['account_id'] ?? null, fn ($q, $v) => $q->where('account_id', $v))
            ->when($f['txn_type'] ?? null, fn ($q, $v) => $q->where('txn_type', $v))
            ->when($f['reconciliation'] ?? null, fn ($q, $v) => $q->where('reconciliation_status', $v))
            ->when($f['tour_id'] ?? null, fn ($q, $v) => $q->where('tour_id', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->where('created_at', '>=', $v.' 00:00:00'))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->where('created_at', '<=', $v.' 23:59:59'));
    }

    /**
     * @return array<string, string>
     */
    private function supplierCodes(): array
    {
        $codes = [];
        foreach ($this->supplierEnv->definitions() as $code => $def) {
            $codes[$this->credits->normalizeCode($code)] = (string) ($def['label'] ?? $code);
        }

        return $codes;
    }

    /**
     * @return array<int, string>
     */
    private function currencies(): array
    {
        $list = Country::query()
            ->whereNotNull('currency')
            ->pluck('currency')
            ->map(fn ($c) => strtoupper(trim((string) $c)))
            ->filter(fn ($c) => preg_match('/^[A-Z]{3}$/', $c))
            ->push('SGD', 'USD')
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $list;
    }

    private function authorizeAdmin(): void
    {
        $ok = auth()->check()
            && (int) auth()->user()->role_id === 1
            && (hasPermission('settings') || hasPermission('edit settings'));

        abort_unless($ok, 403, 'Only TravClicks administrators can access vendor credit.');
    }
}
