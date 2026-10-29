<?php

namespace App\Services\Finance;

use App\Helpers\CommonHelper;
use App\Helpers\CurrencyHelper;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Country;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only queries against wallet and wallet_ledger (public schema).
 */
class WalletQueryService
{
    public function getWallet(string $ownerType, int $ownerId, string $currency = 'USD', ?string $supplierApiId = null): ?object
    {
        return DB::table('wallet')
            ->where('wallet_owner_type', $ownerType)
            ->where('wallet_owner_id', $ownerId)
            ->where('wallet_currency_code', $currency)
            ->whereNull('deleted_at')
            ->when($supplierApiId, fn ($q) => $q->where('supplier_api_id', $supplierApiId))
            ->when(! $supplierApiId, fn ($q) => $q->whereNull('supplier_api_id'))
            ->first();
    }

    public function getOrEmptyWallet(string $ownerType, int $ownerId, string $currency = 'USD'): object
    {
        return $this->getWallet($ownerType, $ownerId, $currency) ?? (object) [
            'wallet_uuid' => null,
            'available_balance' => 0,
            'reserved_balance' => 0,
            'total_credited' => 0,
            'total_debited' => 0,
            'wallet_currency_code' => $currency,
            'wallet_status' => 'active',
        ];
    }

    public const LEDGER_PER_PAGE_DEFAULT = 25;

    public const LEDGER_PER_PAGE_MAX = 100;

    public const GLANCE_PER_PAGE_DEFAULT = 10;

    public const GLANCE_PER_PAGE_MAX = 50;

    /**
     * @return Collection<int, object>
     */
    public function getLedgerEntries(?int $walletId, int $limit = 50): Collection
    {
        if (! $walletId) {
            return collect();
        }

        return $this->ledgerQueryForWallet($walletId)
            ->limit($limit)
            ->get();
    }

    /**
     * Paginated wallet ledger with optional date range — indexed on (wallet_id, created_at DESC, id DESC).
     */
    public function paginateLedgerEntries(
        ?int $walletId,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = self::LEDGER_PER_PAGE_DEFAULT,
        int $page = 1,
    ): LengthAwarePaginator {
        $perPage = max(1, min($perPage, self::LEDGER_PER_PAGE_MAX));
        $page = max(1, $page);

        if (! $walletId) {
            return new Paginator([], 0, $perPage, $page, [
                'path' => request()->url(),
                'pageName' => 'ledger_page',
            ]);
        }

        $paginator = $this->ledgerQueryForWallet($walletId, $dateFrom, $dateTo)
            ->paginate($perPage, ['*'], 'ledger_page', $page);

        $paginator->setCollection(
            $this->enrichLedgerEntries($paginator->getCollection())
        );

        return $paginator;
    }

    /**
     * @return array{date_from: ?string, date_to: ?string, per_page: int}
     */
    public function resolveLedgerFilters(?string $dateFrom = null, ?string $dateTo = null, ?int $perPage = null): array
    {
        $perPage = max(1, min((int) ($perPage ?: self::LEDGER_PER_PAGE_DEFAULT), self::LEDGER_PER_PAGE_MAX));

        $from = $this->normalizeLedgerDate($dateFrom);
        $to = $this->normalizeLedgerDate($dateTo);

        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'date_from' => $from,
            'date_to' => $to,
            'per_page' => $perPage,
        ];
    }

    protected function ledgerQueryForWallet(int $walletId, ?string $dateFrom = null, ?string $dateTo = null): Builder
    {
        $query = DB::table('wallet_ledger')->where('wallet_id', $walletId);

        $from = $this->normalizeLedgerDate($dateFrom);
        $to = $this->normalizeLedgerDate($dateTo);

        if ($from) {
            $query->whereRaw(
                'created_at >= ?::timestamptz',
                [$this->ledgerRangeStart($from)]
            );
        }

        if ($to) {
            $query->whereRaw(
                'created_at < ?::timestamptz',
                [$this->ledgerRangeEndExclusive($to)]
            );
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    protected function normalizeLedgerDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value, config('app.timezone'))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Inclusive start of a filter date, with an offset so PostgreSQL does not
     * reinterpret a naive timestamp in the database session timezone.
     */
    protected function ledgerRangeStart(string $date): string
    {
        return Carbon::parse($date, config('app.timezone'))->startOfDay()->toIso8601String();
    }

    /**
     * Exclusive end: start of the day after the filter date.
     */
    protected function ledgerRangeEndExclusive(string $date): string
    {
        return Carbon::parse($date, config('app.timezone'))->addDay()->startOfDay()->toIso8601String();
    }

    /**
     * Credit and debit totals for the same wallet and date range as the ledger table.
     *
     * @return array{credit: float, debit: float}
     */
    public function summarizeLedger(?int $walletId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        if (! $walletId) {
            return ['credit' => 0.0, 'debit' => 0.0];
        }

        $row = $this->ledgerQueryForWallet($walletId, $dateFrom, $dateTo)
            ->reorder()
            ->selectRaw('COALESCE(SUM(credit_amount), 0) as credit_total, COALESCE(SUM(debit_amount), 0) as debit_total')
            ->first();

        return [
            'credit' => (float) ($row->credit_total ?? 0),
            'debit' => (float) ($row->debit_total ?? 0),
        ];
    }

    /**
     * Entry types offered in the Travclicks DMC ledger filter.
     */
    public const DMC_LEDGER_ENTRY_TYPES = [
        'allocation_credit' => 'Funds received',
        'allocation_debit' => 'Funds sent',
        'topup' => 'Top-up',
        'reserve' => 'Reserved',
        'release' => 'Released',
        'commit' => 'Committed',
        'booking_debit' => 'Booking payment',
        'booking_credit' => 'Booking proceeds',
        'refund_credit' => 'Refund received',
        'refund_debit' => 'Refund issued',
        'adjustment' => 'Adjustment',
    ];

    public function ledgerEntryTypeLabel(string $type): string
    {
        return self::DMC_LEDGER_ENTRY_TYPES[$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    /**
     * One row per DMC wallet (DMC × currency) with funding / usage totals from the ledger.
     *
     * @return Collection<int, object>
     */
    public function listDmcWalletSummaries(?int $dmcId = null, ?string $currency = null): Collection
    {
        if (! Schema::hasTable('wallet') || ! Schema::hasTable('wallet_ledger')) {
            return collect();
        }

        $rows = DB::table('wallet as w')
            ->leftJoin('wallet_ledger as wl', 'wl.wallet_id', '=', 'w.id')
            ->where('w.wallet_owner_type', 'dmc')
            ->whereNull('w.deleted_at')
            ->when($dmcId, fn ($q) => $q->where('w.wallet_owner_id', $dmcId))
            ->when($currency, fn ($q) => $q->where('w.wallet_currency_code', strtoupper($currency)))
            ->groupBy('w.id', 'w.wallet_owner_id', 'w.wallet_currency_code', 'w.available_balance', 'w.reserved_balance')
            ->orderBy('w.wallet_owner_id')
            ->orderBy('w.wallet_currency_code')
            ->selectRaw("
                w.id,
                w.wallet_owner_id,
                w.wallet_currency_code,
                w.available_balance,
                w.reserved_balance,
                COALESCE(SUM(CASE
                    WHEN wl.ledger_entry_type = 'allocation_credit'
                     AND COALESCE(wl.ledger_metadata->'transfer'->>'from_type', '') = 'travclicks'
                    THEN wl.credit_amount ELSE 0 END), 0) AS funded_by_travclicks,
                COALESCE(SUM(wl.credit_amount), 0) AS total_credit,
                COALESCE(SUM(wl.debit_amount), 0) AS total_debit,
                COUNT(wl.id) AS entry_count,
                MAX(wl.created_at) AS last_activity_at
            ")
            ->get();

        $users = $rows->isNotEmpty()
            ? User::whereIn('userId', $rows->pluck('wallet_owner_id')->unique()->values())
                ->get(['userId', 'company_name', 'name'])
                ->keyBy('userId')
            : collect();

        return $rows
            ->map(function ($row) use ($users) {
                $row->owner_name = $this->resolveOwnerName('dmc', (int) $row->wallet_owner_id, $users);
                $row->wallet_currency_code = strtoupper((string) $row->wallet_currency_code);

                return $row;
            })
            ->sortBy(fn ($row) => strtolower($row->owner_name).'|'.$row->wallet_currency_code)
            ->values();
    }

    /**
     * Every DMC (role 11) with its wallets, for the Travclicks drill-down.
     *
     * @return Collection<int, object>
     */
    protected function dmcCards(): Collection
    {
        $wallets = $this->listDmcWalletSummaries()->groupBy('wallet_owner_id');

        return User::where('role_id', 11)->whereNull('deleted_at')
            ->get(['userId', 'company_name', 'name', 'master_dmc_id'])
            ->map(fn ($u) => (object) [
                'id' => (int) $u->userId,
                'name' => $u->company_name ?: $u->name ?: "DMC #{$u->userId}",
                'master_id' => (int) ($u->master_dmc_id ?? 0),
                'wallets' => $wallets->get($u->userId, collect()),
            ])
            ->sortBy(fn ($d) => strtolower($d->name))
            ->values();
    }

    /**
     * @return array<int, array{currency: string, received: float, available: float}>
     */
    protected function sumWalletsByCurrency(Collection $wallets): array
    {
        return $wallets->groupBy('wallet_currency_code')->map(fn ($rows, $code) => [
            'currency' => $code,
            'received' => (float) $rows->sum('funded_by_travclicks'),
            'available' => (float) $rows->sum('available_balance'),
        ])->sortKeys()->values()->all();
    }

    /**
     * Master DMC cards (id 0 = DMCs with no Master DMC) with their DMCs' totals per currency.
     *
     * @return Collection<int, object>
     */
    public function listMasterDmcCards(): Collection
    {
        $dmcs = $this->dmcCards()->groupBy('master_id');

        $masters = User::where('role_id', 10)->whereNull('deleted_at')
            ->get(['userId', 'company_name', 'name'])
            ->map(fn ($m) => (object) ['id' => (int) $m->userId, 'name' => $m->company_name ?: $m->name ?: "Master DMC #{$m->userId}"]);

        if ($dmcs->has(0)) {
            $masters->push((object) ['id' => 0, 'name' => 'Direct DMCs (no Master DMC)']);
        }

        return $masters->map(function ($m) use ($dmcs) {
            $list = $dmcs->get($m->id, collect());
            $m->dmc_count = $list->count();
            $m->totals = $this->sumWalletsByCurrency($list->flatMap(fn ($d) => $d->wallets));

            return $m;
        })->sortBy(fn ($m) => strtolower($m->name))->values();
    }

    /**
     * @return Collection<int, object>
     */
    public function listDmcCardsForMaster(int $masterId): Collection
    {
        return $this->dmcCards()->where('master_id', $masterId)->values();
    }

    /**
     * Ledger entries for one DMC's wallets (all currencies) for Travclicks.
     *
     * @return array{
     *     ledger: LengthAwarePaginator,
     *     filters: array{dmc_id: ?int, currency: ?string, entry_type: ?string, date_from: ?string, date_to: ?string, per_page: int},
     *     queryParams: array<string, mixed>,
     *     totalsByCurrency: array<int, array{currency: string, credit: float, debit: float, count: int}>
     * }
     */
    public function buildTravclicksDmcLedgerViewData(
        Authenticatable $user,
        ?int $dmcId = null,
        ?string $currency = null,
        ?string $entryType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $perPage = null,
        int $page = 1,
    ): array {
        $ownDmc = (int) ($user->role_id ?? 0) === 10 && $dmcId && $this->dmcBelongsToMaster($dmcId, (int) $user->userId);
        if (! $ownDmc && ! $this->isAdminUser($user)) {
            abort(403, 'You do not have access to this view.');
        }

        $dateFilters = $this->resolveLedgerFilters($dateFrom, $dateTo, $perPage);
        $currency = $currency ? strtoupper($currency) : null;
        $entryType = ($entryType && array_key_exists($entryType, self::DMC_LEDGER_ENTRY_TYPES)) ? $entryType : null;
        $dmcId = $dmcId && $dmcId > 0 ? $dmcId : null;

        $filters = [
            'dmc_id' => $dmcId,
            'currency' => $currency,
            'entry_type' => $entryType,
            'date_from' => $dateFilters['date_from'],
            'date_to' => $dateFilters['date_to'],
            'per_page' => $dateFilters['per_page'],
        ];

        $paginator = $this->dmcLedgerQuery($dmcId, $currency, $entryType, $filters['date_from'], $filters['date_to'])
            ->paginate(
                $filters['per_page'],
                ['wl.*', 'w.wallet_owner_type', 'w.wallet_owner_id', 'w.wallet_currency_code'],
                'dmc_ledger_page',
                max($page, 1),
            );

        $dmcUsers = $paginator->getCollection()->isNotEmpty()
            ? User::whereIn('userId', $paginator->getCollection()->pluck('wallet_owner_id')->unique()->values())
                ->get(['userId', 'company_name', 'name'])
                ->keyBy('userId')
            : collect();

        $paginator->setCollection(
            $this->enrichLedgerEntries($paginator->getCollection())
                ->map(function ($entry) use ($dmcUsers) {
                    $entry->dmc_name = $this->resolveOwnerName('dmc', (int) $entry->wallet_owner_id, $dmcUsers);
                    $entry->entry_type_label = $this->ledgerEntryTypeLabel((string) $entry->ledger_entry_type);

                    return $entry;
                })
        );

        $totalsByCurrency = $this->dmcLedgerQuery($dmcId, $currency, $entryType, $filters['date_from'], $filters['date_to'])
            ->reorder()
            ->groupBy('w.wallet_currency_code')
            ->orderBy('w.wallet_currency_code')
            ->selectRaw('w.wallet_currency_code as currency, COALESCE(SUM(wl.credit_amount), 0) as credit, COALESCE(SUM(wl.debit_amount), 0) as debit, COUNT(*) as entry_count')
            ->get()
            ->map(fn ($r) => [
                'currency' => strtoupper((string) $r->currency),
                'credit' => (float) $r->credit,
                'debit' => (float) $r->debit,
                'count' => (int) $r->entry_count,
            ])
            ->values()
            ->all();

        $queryParams = array_filter([
            'dmc_id' => $filters['dmc_id'],
            'dmc_currency' => $filters['currency'],
            'dmc_entry_type' => $filters['entry_type'],
            'dmc_date_from' => $filters['date_from'],
            'dmc_date_to' => $filters['date_to'],
            'dmc_per_page' => $filters['per_page'],
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'ledger' => $paginator,
            'filters' => $filters,
            'queryParams' => $queryParams,
            'totalsByCurrency' => $totalsByCurrency,
        ];
    }

    protected function dmcLedgerQuery(
        ?int $dmcId = null,
        ?string $currency = null,
        ?string $entryType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): Builder {
        $query = DB::table('wallet_ledger as wl')
            ->join('wallet as w', 'w.id', '=', 'wl.wallet_id')
            ->where('w.wallet_owner_type', 'dmc')
            ->whereNull('w.deleted_at');

        if ($dmcId) {
            $query->where('w.wallet_owner_id', $dmcId);
        }

        if ($currency) {
            $query->where('w.wallet_currency_code', strtoupper($currency));
        }

        if ($entryType) {
            $query->where('wl.ledger_entry_type', $entryType);
        }

        $from = $this->normalizeLedgerDate($dateFrom);
        $to = $this->normalizeLedgerDate($dateTo);

        if ($from) {
            $query->whereRaw('wl.created_at >= ?::timestamptz', [$this->ledgerRangeStart($from)]);
        }

        if ($to) {
            $query->whereRaw('wl.created_at < ?::timestamptz', [$this->ledgerRangeEndExclusive($to)]);
        }

        return $query
            ->orderByDesc('wl.created_at')
            ->orderByDesc('wl.id');
    }

    /**
     * @return array{
     *     ledger: LengthAwarePaginator,
     *     ledgerFilters: array{date_from: ?string, date_to: ?string, per_page: int},
     *     ledgerQueryParams: array<string, mixed>,
     *     currency: string
     * }
     */
    public function buildLedgerViewData(
        Authenticatable $user,
        string $ownerType,
        int $ownerId,
        ?string $currency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $perPage = null,
        int $page = 1,
    ): array {
        if (! $this->canAccessWallet($user, $ownerType, $ownerId)) {
            abort(403, 'You do not have access to this wallet.');
        }

        $currency = $this->resolveWalletCurrency($ownerType, $ownerId, $currency);
        $walletRow = $this->getOrEmptyWallet($ownerType, $ownerId, $currency);
        $ledgerFilters = $this->resolveLedgerFilters($dateFrom, $dateTo, $perPage);
        $ledger = $this->paginateLedgerEntries(
            $walletRow->id ?? null,
            $ledgerFilters['date_from'],
            $ledgerFilters['date_to'],
            $ledgerFilters['per_page'],
            max($page, 1),
        );

        $ledgerQueryParams = array_filter([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'currency' => $currency,
            'ledger_date_from' => $ledgerFilters['date_from'],
            'ledger_date_to' => $ledgerFilters['date_to'],
            'ledger_per_page' => $ledgerFilters['per_page'],
        ], fn ($value) => $value !== null && $value !== '');

        $walletId = isset($walletRow->id) ? (int) $walletRow->id : null;
        $ledgerSummary = $this->summarizeLedger($walletId);
        $ledgerFilteredSummary = ($ledgerFilters['date_from'] || $ledgerFilters['date_to'])
            ? $this->summarizeLedger($walletId, $ledgerFilters['date_from'], $ledgerFilters['date_to'])
            : $ledgerSummary;

        return [
            'ledger' => $ledger,
            'ledgerFilters' => $ledgerFilters,
            'ledgerQueryParams' => $ledgerQueryParams,
            'ledgerSummary' => $ledgerSummary,
            'ledgerFilteredSummary' => $ledgerFilteredSummary,
            'currency' => $currency,
        ];
    }

    /**
     * @return array{
     *     transactions: LengthAwarePaginator,
     *     glanceFilters: array{date_from: ?string, date_to: ?string, per_page: int},
     *     glanceQueryParams: array<string, mixed>,
     *     currency: string
     * }
     */
    public function buildTransactionGlanceViewData(
        Authenticatable $user,
        string $ownerType,
        int $ownerId,
        ?string $currency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $perPage = null,
        int $page = 1,
    ): array {
        if (! $this->canAccessWallet($user, $ownerType, $ownerId)) {
            abort(403, 'You do not have access to this wallet.');
        }

        $currency = $this->resolveWalletCurrency($ownerType, $ownerId, $currency);
        $walletRow = $this->getOrEmptyWallet($ownerType, $ownerId, $currency);
        $glanceFilters = $this->resolveGlanceFilters($dateFrom, $dateTo, $perPage);
        $transactions = $this->paginateGlanceTransactions(
            $walletRow->id ?? null,
            $currency,
            $glanceFilters['date_from'],
            $glanceFilters['date_to'],
            $glanceFilters['per_page'],
            max($page, 1),
        );

        $glanceQueryParams = array_filter([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'currency' => $currency,
            'glance_date_from' => $glanceFilters['date_from'],
            'glance_date_to' => $glanceFilters['date_to'],
            'glance_per_page' => $glanceFilters['per_page'],
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'transactions' => $transactions,
            'glanceFilters' => $glanceFilters,
            'glanceQueryParams' => $glanceQueryParams,
            'currency' => $currency,
            'scope' => 'wallet',
        ];
    }

    /**
     * Admin view — funds given to Master DMC and booking returns to Travclicks.
     *
     * @return array{
     *     transactions: LengthAwarePaginator,
     *     glanceFilters: array{date_from: ?string, date_to: ?string, per_page: int},
     *     glanceQueryParams: array<string, mixed>,
     *     currency: string,
     *     scope: string,
     *     flowSummary: array<string, mixed>
     * }
     */
    public function buildAdminTransactionGlanceViewData(
        Authenticatable $user,
        ?string $filterOwnerType = null,
        ?int $filterOwnerId = null,
        ?string $filterCurrency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $perPage = null,
        int $page = 1,
    ): array {
        if (! $this->isAdminUser($user)) {
            abort(403, 'You do not have access to this view.');
        }

        $masterDmcId = ($filterOwnerType === 'master_dmc' && $filterOwnerId) ? $filterOwnerId : null;
        $glanceFilters = $this->resolveGlanceFilters($dateFrom, $dateTo, $perPage);
        $transactions = $this->paginateAdminMasterDmcFlowTransactions(
            $masterDmcId,
            $filterCurrency ? strtoupper($filterCurrency) : null,
            $glanceFilters['date_from'],
            $glanceFilters['date_to'],
            $glanceFilters['per_page'],
            max($page, 1),
        );

        $flowSummary = $this->summarizeAdminMasterDmcFlow(
            $masterDmcId,
            $filterCurrency ? strtoupper($filterCurrency) : null,
            $glanceFilters['date_from'],
            $glanceFilters['date_to'],
        );

        $glanceQueryParams = array_filter([
            'scope' => 'master_dmc_flow',
            'owner_type' => $masterDmcId ? 'master_dmc' : null,
            'owner_id' => $masterDmcId,
            'currency' => $filterCurrency ? strtoupper($filterCurrency) : null,
            'glance_date_from' => $glanceFilters['date_from'],
            'glance_date_to' => $glanceFilters['date_to'],
            'glance_per_page' => $glanceFilters['per_page'],
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'transactions' => $transactions,
            'glanceFilters' => $glanceFilters,
            'glanceQueryParams' => $glanceQueryParams,
            'currency' => $filterCurrency ? strtoupper($filterCurrency) : 'ALL',
            'scope' => 'master_dmc_flow',
            'flowSummary' => $flowSummary,
        ];
    }

    public function dmcAgencyAllocationEnabled(): bool
    {
        return (bool) config('finance.wallet.dmc_agency_allocation_enabled', false);
    }

    public function isAdminUser(Authenticatable $user): bool
    {
        return in_array((int) ($user->role_id ?? 0), [1, 2, 3, 4], true);
    }

    /**
     * @return array{date_from: ?string, date_to: ?string, per_page: int}
     */
    public function resolveGlanceFilters(?string $dateFrom = null, ?string $dateTo = null, ?int $perPage = null): array
    {
        $perPage = max(1, min((int) ($perPage ?: self::GLANCE_PER_PAGE_DEFAULT), self::GLANCE_PER_PAGE_MAX));

        $from = $this->normalizeLedgerDate($dateFrom);
        $to = $this->normalizeLedgerDate($dateTo);

        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'date_from' => $from,
            'date_to' => $to,
            'per_page' => $perPage,
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateGlanceTransactions(
        ?int $walletId,
        string $currency,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = self::GLANCE_PER_PAGE_DEFAULT,
        int $page = 1,
    ): LengthAwarePaginator {
        $perPage = max(1, min($perPage, self::GLANCE_PER_PAGE_MAX));
        $page = max(1, $page);

        if (! $walletId) {
            return new Paginator([], 0, $perPage, $page, [
                'path' => request()->url(),
                'pageName' => 'glance_page',
            ]);
        }

        $paginator = $this->ledgerQueryForWallet($walletId, $dateFrom, $dateTo)
            ->paginate($perPage, ['*'], 'glance_page', $page);

        $mapped = $this->mapLedgerEntriesToGlanceTransactions(
            $this->enrichLedgerEntries($paginator->getCollection()),
            $currency,
        );

        $paginator->setCollection($mapped);

        return $paginator;
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateAdminMasterDmcFlowTransactions(
        ?int $masterDmcId = null,
        ?string $currency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = self::GLANCE_PER_PAGE_DEFAULT,
        int $page = 1,
    ): LengthAwarePaginator {
        $perPage = max(1, min($perPage, self::GLANCE_PER_PAGE_MAX));
        $page = max(1, $page);

        $paginator = $this->adminMasterDmcFlowQuery($masterDmcId, $currency, $dateFrom, $dateTo)
            ->paginate($perPage, ['wl.*', 'w.wallet_owner_type', 'w.wallet_owner_id', 'w.wallet_currency_code'], 'glance_page', $page);

        $mapped = $this->mapAdminFlowGlanceTransactions(
            $this->enrichAdminGlanceEntries($paginator->getCollection()),
        );

        $paginator->setCollection($mapped);

        return $paginator;
    }

    /**
     * @return array{funded: array<string, float>, returned: array<string, float>}
     */
    public function summarizeAdminMasterDmcFlow(
        ?int $masterDmcId = null,
        ?string $currency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        $rows = $this->adminMasterDmcFlowQuery($masterDmcId, $currency, $dateFrom, $dateTo)
            ->get(['wl.ledger_entry_type', 'wl.credit_amount', 'w.wallet_owner_type', 'w.wallet_currency_code']);

        $funded = [];
        $returned = [];

        foreach ($rows as $row) {
            $code = strtoupper((string) $row->wallet_currency_code);
            $amount = (float) $row->credit_amount;

            if ($row->wallet_owner_type === 'master_dmc' && $row->ledger_entry_type === 'topup') {
                $funded[$code] = ($funded[$code] ?? 0) + $amount;
            }

            if ($row->wallet_owner_type === 'travclicks' && $row->ledger_entry_type === 'booking_credit') {
                $returned[$code] = ($returned[$code] ?? 0) + $amount;
            }
        }

        return [
            'funded' => $funded,
            'returned' => $returned,
        ];
    }

    protected function adminMasterDmcFlowQuery(
        ?int $masterDmcId = null,
        ?string $currency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): Builder {
        $query = DB::table('wallet_ledger as wl')
            ->join('wallet as w', 'w.id', '=', 'wl.wallet_id')
            ->whereNull('w.deleted_at')
            ->where(function (Builder $outer) use ($masterDmcId) {
                $outer->where(function (Builder $fund) use ($masterDmcId) {
                    $fund->where('w.wallet_owner_type', 'master_dmc')
                        ->where('wl.ledger_entry_type', 'topup')
                        ->where(function (Builder $source) {
                            $source->whereRaw("COALESCE(wl.ledger_metadata->'transfer'->>'from_type', '') = 'travclicks'")
                                ->orWhere('wl.ledger_description', 'ilike', 'Travclicks credited%');
                        });

                    if ($masterDmcId) {
                        $fund->where('w.wallet_owner_id', $masterDmcId);
                    }
                })->orWhere(function (Builder $ret) {
                    $ret->where('w.wallet_owner_type', 'travclicks')
                        ->where('w.wallet_owner_id', 1)
                        ->where('wl.ledger_entry_type', 'booking_credit');
                });
            });

        if ($currency) {
            $query->where('w.wallet_currency_code', strtoupper($currency));
        }

        $from = $this->normalizeLedgerDate($dateFrom);
        $to = $this->normalizeLedgerDate($dateTo);

        if ($from) {
            $query->whereRaw('wl.created_at >= ?::timestamptz', [$this->ledgerRangeStart($from)]);
        }

        if ($to) {
            $query->whereRaw('wl.created_at < ?::timestamptz', [$this->ledgerRangeEndExclusive($to)]);
        }

        return $query
            ->orderByDesc('wl.created_at')
            ->orderByDesc('wl.id');
    }

    /**
     * @param  Collection<int, object>  $ledger
     * @return Collection<int, array<string, mixed>>
     */
    public function mapAdminFlowGlanceTransactions(Collection $ledger): Collection
    {
        return $ledger->map(fn (object $entry) => $this->mapAdminFlowGlanceTransaction($entry));
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapAdminFlowGlanceTransaction(object $entry): array
    {
        $currency = strtoupper((string) ($entry->wallet_currency_code ?? 'USD'));
        $amount = (float) ($entry->credit_amount ?? 0);
        $isFundToMaster = ($entry->wallet_owner_type ?? '') === 'master_dmc'
            && ($entry->ledger_entry_type ?? '') === 'topup';
        $isBookingReturn = ($entry->wallet_owner_type ?? '') === 'travclicks'
            && ($entry->ledger_entry_type ?? '') === 'booking_credit';

        if ($isFundToMaster) {
            $masterName = $this->resolveOwnerName('master_dmc', (int) ($entry->wallet_owner_id ?? 0));

            return [
                'finance_reference' => (string) ($entry->finance_reference ?? ''),
                'created_at' => $entry->created_at,
                'direction' => 'out',
                'amount' => $amount,
                'currency' => $currency,
                'type' => 'fund_to_master_dmc',
                'type_label' => 'Admin → Master DMC',
                'title' => 'Funded to '.$masterName,
                'subtitle' => (string) ($entry->transfer_summary ?? $entry->ledger_description ?? ''),
                'balance_after' => (float) ($entry->balance_after ?? 0),
                'tour_id' => null,
                'icon' => 'ri-arrow-right-up-line',
                'accent' => 'danger',
                'wallet_label' => $masterName.' · '.$currency,
                'flow_type' => 'fund_out',
            ];
        }

        return [
            'finance_reference' => (string) ($entry->finance_reference ?? ''),
            'created_at' => $entry->created_at,
            'direction' => 'in',
            'amount' => $amount,
            'currency' => $currency,
            'type' => 'booking_return',
            'type_label' => 'Booking return',
            'title' => ! empty($entry->tour_id) ? 'Markup return · Tour #'.$entry->tour_id : 'Booking markup return',
            'subtitle' => (string) ($entry->transfer_summary ?? $entry->ledger_description ?? ''),
            'balance_after' => (float) ($entry->balance_after ?? 0),
            'tour_id' => $entry->tour_id ?? null,
            'icon' => 'ri-arrow-left-down-line',
            'accent' => 'success',
            'wallet_label' => 'Travclicks Platform · '.$currency,
            'flow_type' => 'booking_return',
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function listMasterDmcsForAdminFilter(): Collection
    {
        return DB::table('wallet as w')
            ->where('w.wallet_owner_type', 'master_dmc')
            ->whereNull('w.deleted_at')
            ->orderByDesc('w.updated_at')
            ->get(['w.wallet_owner_id', 'w.wallet_currency_code'])
            ->unique(fn ($row) => (int) $row->wallet_owner_id)
            ->values()
            ->map(function ($row) {
                $row->owner_name = $this->resolveOwnerName('master_dmc', (int) $row->wallet_owner_id);

                return $row;
            });
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateAllGlanceTransactions(
        ?string $ownerType = null,
        ?int $ownerId = null,
        ?string $currency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = self::GLANCE_PER_PAGE_DEFAULT,
        int $page = 1,
    ): LengthAwarePaginator {
        $perPage = max(1, min($perPage, self::GLANCE_PER_PAGE_MAX));
        $page = max(1, $page);

        $paginator = $this->adminGlanceQuery($ownerType, $ownerId, $currency, $dateFrom, $dateTo)
            ->paginate($perPage, ['wl.*', 'w.wallet_owner_type', 'w.wallet_owner_id', 'w.wallet_currency_code'], 'glance_page', $page);

        $mapped = $this->mapLedgerEntriesToGlanceTransactions(
            $this->enrichAdminGlanceEntries($paginator->getCollection()),
            $currency ?: 'USD',
        );

        $paginator->setCollection($mapped);

        return $paginator;
    }

    protected function adminGlanceQuery(
        ?string $ownerType = null,
        ?int $ownerId = null,
        ?string $currency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): Builder {
        $query = DB::table('wallet_ledger as wl')
            ->join('wallet as w', 'w.id', '=', 'wl.wallet_id')
            ->whereNull('w.deleted_at');

        if ($ownerType) {
            $query->where('w.wallet_owner_type', $ownerType);
        }

        if ($ownerId) {
            $query->where('w.wallet_owner_id', $ownerId);
        }

        if ($currency) {
            $query->where('w.wallet_currency_code', strtoupper($currency));
        }

        $from = $this->normalizeLedgerDate($dateFrom);
        $to = $this->normalizeLedgerDate($dateTo);

        if ($from) {
            $query->whereRaw('wl.created_at >= ?::timestamptz', [$this->ledgerRangeStart($from)]);
        }

        if ($to) {
            $query->whereRaw('wl.created_at < ?::timestamptz', [$this->ledgerRangeEndExclusive($to)]);
        }

        return $query
            ->orderByDesc('wl.created_at')
            ->orderByDesc('wl.id');
    }

    /**
     * @param  Collection<int, object>  $ledger
     * @return Collection<int, object>
     */
    public function enrichAdminGlanceEntries(Collection $ledger): Collection
    {
        if ($ledger->isEmpty()) {
            return $ledger;
        }

        $ledger = $this->enrichLedgerEntries($ledger);

        $userOwnerTypes = ['travclicks', 'master_dmc', 'dmc'];
        $userIds = $ledger
            ->filter(fn ($entry) => in_array($entry->wallet_owner_type ?? '', $userOwnerTypes, true))
            ->pluck('wallet_owner_id')
            ->unique()
            ->values();

        $agentIds = $ledger
            ->where('wallet_owner_type', 'agent')
            ->pluck('wallet_owner_id')
            ->unique()
            ->values();

        $users = $userIds->isNotEmpty()
            ? User::whereIn('userId', $userIds)->get(['userId', 'company_name', 'name'])->keyBy('userId')
            : collect();

        $agents = $agentIds->isNotEmpty()
            ? Agent::with('agency:agency_id,agency_name')
                ->whereIn('agent_id', $agentIds)
                ->get(['agent_id', 'name', 'agency_id'])
                ->keyBy('agent_id')
            : collect();

        return $ledger->map(function ($entry) use ($users, $agents) {
            $ownerType = (string) ($entry->wallet_owner_type ?? '');
            $ownerId = (int) ($entry->wallet_owner_id ?? 0);
            $ownerName = $this->resolveOwnerName($ownerType, $ownerId, $users, $agents);
            $entry->wallet_owner_label = $ownerName.' · '.strtoupper((string) ($entry->wallet_currency_code ?? ''));

            return $entry;
        });
    }

    /**
     * @param  Collection<int, object>  $ledger
     * @return Collection<int, array<string, mixed>>
     */
    public function mapLedgerEntriesToGlanceTransactions(Collection $ledger, string $currency): Collection
    {
        return $ledger->map(fn (object $entry) => $this->mapLedgerEntryToGlanceTransaction($entry, $currency));
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapLedgerEntryToGlanceTransaction(object $entry, string $currency): array
    {
        $isCredit = (float) ($entry->credit_amount ?? 0) > 0;
        $amount = $isCredit ? (float) $entry->credit_amount : (float) ($entry->debit_amount ?? 0);
        $type = (string) ($entry->ledger_entry_type ?? 'adjustment');

        return [
            'finance_reference' => (string) ($entry->finance_reference ?? ''),
            'created_at' => $entry->created_at,
            'direction' => $isCredit ? 'in' : 'out',
            'amount' => $amount,
            'currency' => strtoupper((string) ($entry->wallet_currency_code ?? $currency)),
            'type' => $type,
            'type_label' => $this->glanceTypeLabel($type, $isCredit),
            'title' => $this->glanceTitle($entry, $type, $isCredit),
            'subtitle' => (string) ($entry->transfer_summary ?? $entry->ledger_description ?? ''),
            'balance_after' => (float) ($entry->balance_after ?? $entry->running_balance ?? 0),
            'tour_id' => $entry->tour_id ?? null,
            'icon' => $this->glanceIcon($type, $isCredit),
            'accent' => $isCredit ? 'success' : 'danger',
            'wallet_label' => $entry->wallet_owner_label ?? null,
        ];
    }

    protected function glanceTypeLabel(string $type, bool $isCredit): string
    {
        return match ($type) {
            'allocation_credit', 'allocation_debit' => $isCredit ? 'Funds received' : 'Funds sent',
            'topup' => 'Top-up',
            'booking_debit' => 'Booking payment',
            'booking_credit' => 'Booking proceeds',
            'refund_credit' => 'Refund received',
            'refund_debit' => 'Refund issued',
            'reserve' => 'Reserved',
            'release' => 'Released',
            'adjustment' => 'Adjustment',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }

    protected function glanceTitle(object $entry, string $type, bool $isCredit): string
    {
        if (! empty($entry->tour_id)) {
            return 'Tour #'.$entry->tour_id;
        }

        return match ($type) {
            'allocation_credit' => 'Wallet allocation received',
            'allocation_debit' => 'Wallet allocation sent',
            'topup' => 'Wallet credited',
            'booking_debit' => 'Booking debited',
            'booking_credit' => 'Booking credited',
            'refund_credit' => 'Refund to wallet',
            'refund_debit' => 'Refund from wallet',
            'reserve' => 'Funds reserved',
            'release' => 'Funds released',
            default => $isCredit ? 'Money in' : 'Money out',
        };
    }

    protected function glanceIcon(string $type, bool $isCredit): string
    {
        return match ($type) {
            'allocation_credit', 'allocation_debit', 'topup' => 'ri-exchange-dollar-line',
            'booking_debit', 'booking_credit' => 'ri-flight-takeoff-line',
            'refund_credit', 'refund_debit' => 'ri-refund-2-line',
            'reserve', 'release' => 'ri-lock-line',
            default => $isCredit ? 'ri-arrow-down-circle-line' : 'ri-arrow-up-circle-line',
        };
    }

    /**
     * @return Collection<int, object>
     */
    public function listWalletsForUser(Authenticatable $user, ?string $ownerType = null): Collection
    {
        $query = DB::table('wallet')->whereNull('deleted_at');

        if (in_array($user->role_id, [1, 2, 3, 4])) {
            if ($ownerType) {
                $query->where('wallet_owner_type', $ownerType);
            }

            return $query->orderByDesc('updated_at')->limit(100)->get();
        }

        if ($user->role_id == 10) {
            $dmcIds = $this->getDmcUserIdsUnderMaster((int) $user->userId);

            return $query->where(function ($q) use ($user, $dmcIds) {
                $q->where(fn ($q2) => $q2->where('wallet_owner_type', 'master_dmc')->where('wallet_owner_id', $user->userId))
                    ->orWhere(fn ($q2) => $q2->where('wallet_owner_type', 'dmc')->whereIn('wallet_owner_id', $dmcIds));
            })->get();
        }

        if ((int) $user->role_id === 11) {
            $dmcUserId = (int) $user->userId;
            $agentIds = $this->getAgentsForDmc($dmcUserId)->pluck('agent_id');

            return $query->where(function ($q) use ($dmcUserId, $agentIds) {
                $q->where(fn ($q2) => $q2->where('wallet_owner_type', 'dmc')->where('wallet_owner_id', $dmcUserId))
                    ->orWhere(fn ($q2) => $q2->where('wallet_owner_type', 'agent')->whereIn('wallet_owner_id', $agentIds));
            })->get();
        }

        if ($user->agent_id) {
            return $query->where('wallet_owner_type', 'agent')
                ->where('wallet_owner_id', $user->agent_id)
                ->get();
        }

        return collect();
    }

    /**
     * @param  Collection<int, object>  $wallets
     * @return Collection<int, object>
     */
    public function enrichWalletsWithOwnerNames(Collection $wallets): Collection
    {
        if ($wallets->isEmpty()) {
            return $wallets;
        }

        $userOwnerTypes = ['travclicks', 'master_dmc', 'dmc'];
        $userIds = $wallets
            ->filter(fn ($w) => in_array($w->wallet_owner_type, $userOwnerTypes, true))
            ->pluck('wallet_owner_id')
            ->unique()
            ->values();

        $agentIds = $wallets
            ->where('wallet_owner_type', 'agent')
            ->pluck('wallet_owner_id')
            ->unique()
            ->values();

        $users = $userIds->isNotEmpty()
            ? User::whereIn('userId', $userIds)->get(['userId', 'company_name', 'name'])->keyBy('userId')
            : collect();

        $agents = $agentIds->isNotEmpty()
            ? Agent::with('agency:agency_id,agency_name')
                ->whereIn('agent_id', $agentIds)
                ->get(['agent_id', 'name', 'agency_id'])
                ->keyBy('agent_id')
            : collect();

        return $wallets->map(function ($wallet) use ($users, $agents) {
            $wallet->owner_name = $this->resolveOwnerName(
                (string) $wallet->wallet_owner_type,
                (int) $wallet->wallet_owner_id,
                $users,
                $agents,
            );

            return $wallet;
        });
    }

    public function resolveOwnerName(
        string $ownerType,
        int $ownerId,
        ?Collection $users = null,
        ?Collection $agents = null,
    ): string {
        if ($ownerType === 'travclicks' && $ownerId === 1) {
            return 'Travclicks Platform';
        }

        if (in_array($ownerType, ['travclicks', 'master_dmc', 'dmc'], true)) {
            $user = $users?->get($ownerId) ?? User::where('userId', $ownerId)->first(['userId', 'company_name', 'name']);

            return $user?->company_name ?: $user?->name ?: "User #{$ownerId}";
        }

        if ($ownerType === 'agent') {
            $agent = $agents?->get($ownerId)
                ?? Agent::with('agency:agency_id,agency_name')->where('agent_id', $ownerId)->first(['agent_id', 'name', 'agency_id']);

            if ($agent && ! $agent->relationLoaded('agency') && ! empty($agent->agency_id)) {
                $agent->load('agency:agency_id,agency_name');
            }

            return $agent?->agency?->agency_name
                ?: ($agent?->name ?: "Agency #{$ownerId}");
        }

        if ($ownerType === 'supplier') {
            return "Supplier #{$ownerId}";
        }

        return ucfirst(str_replace('_', ' ', $ownerType))." #{$ownerId}";
    }

    public function formatOwnerLabel(string $ownerType, int $ownerId): string
    {
        return $this->resolveOwnerName($ownerType, $ownerId).' ('.$this->ownerTypeLabel($ownerType).')';
    }

    public function ownerTypeLabel(string $ownerType): string
    {
        return match ($ownerType) {
            'master_dmc' => 'Master DMC',
            'dmc' => 'DMC',
            'agent' => 'Agency',
            'travclicks' => 'Travclicks',
            'supplier' => 'Supplier',
            default => ucfirst(str_replace('_', ' ', $ownerType)),
        };
    }

    /**
     * DMC user IDs (role 11) that belong to a Master DMC — matches /users hierarchy.
     *
     * @return array<int, int>
     */
    public function getDmcUserIdsUnderMaster(int $masterDmcId): array
    {
        return User::where('role_id', 11)
            ->where('master_dmc_id', $masterDmcId)
            ->whereNull('deleted_at')
            ->pluck('userId')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function dmcBelongsToMaster(int $dmcUserId, int $masterDmcId): bool
    {
        return User::where('userId', $dmcUserId)
            ->where('role_id', 11)
            ->where('master_dmc_id', $masterDmcId)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Agency IDs linked to a DMC — identical to AgentController@index.
     *
     * @return Collection<int, int|string>
     */
    public function getAgencyIdsForDmc(int $dmcUserId): Collection
    {
        return Agency::where('status', 1)
            ->whereRaw('dmc_id::jsonb @> ?', [json_encode([$dmcUserId])])
            ->pluck('agency_id');
    }

    /**
     * Agents for a DMC — agency scope + created under this DMC hierarchy only.
     *
     * @return Collection<int, Agent>
     */
    public function getAgentsForDmc(int $dmcUserId): Collection
    {
        $agencyIds = $this->getAgencyIdsForDmc($dmcUserId);

        if ($agencyIds->isEmpty()) {
            return collect();
        }

        $hierarchyIds = $this->getDmcHierarchyIds($dmcUserId);

        return Agent::with('agency:agency_id,agency_name')
            ->whereIn('agency_id', $agencyIds)
            ->whereIn('sales_manager_dmc', $hierarchyIds)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function agentBelongsToDmc(Agent $agent, int $dmcUserId): bool
    {
        if (! $agent->sales_manager_dmc) {
            return false;
        }

        return in_array(
            (int) $agent->sales_manager_dmc,
            array_map('intval', $this->getDmcHierarchyIds($dmcUserId)),
            true
        );
    }

    /**
     * One row per agency this DMC selected, including agencies whose agents
     * were created under another DMC. Matches the Selected Agencies list.
     *
     * @return array<int, array{id: int|null, label: string, country: string, selectable: bool}>
     */
    public function formatSelectedAgenciesForDropdown(int $dmcUserId): array
    {
        $agencies = Agency::query()
            ->where('status', 1)
            ->where(function ($query) use ($dmcUserId) {
                $query->whereRaw('dmc_id::jsonb @> ?', [json_encode([(int) $dmcUserId])])
                    ->orWhereRaw('dmc_id::jsonb @> ?', [json_encode([(string) $dmcUserId])]);
            })
            ->orderBy('country')
            ->orderBy('agency_name')
            ->get(['agency_id', 'agency_name', 'country']);

        $hierarchy = array_map('intval', $this->getDmcHierarchyIds($dmcUserId));
        $fundedOwnerIds = collect();
        if (Schema::hasTable('wallet') && Schema::hasColumn('wallet', 'source_dmc_id')) {
            $fundedOwnerIds = DB::table('wallet')
                ->where('wallet_owner_type', 'agent')
                ->where('source_dmc_id', $dmcUserId)
                ->whereNull('deleted_at')
                ->pluck('wallet_owner_id')
                ->map(fn ($id) => (int) $id);
        }

        return $agencies->map(function (Agency $agency) use ($hierarchy, $fundedOwnerIds) {
            $agents = Agent::query()
                ->where('agency_id', $agency->agency_id)
                ->where('status', 1)
                ->orderBy('agent_id')
                ->get(['agent_id', 'sales_manager_dmc']);

            $agent = $agents->first(fn (Agent $row) => $fundedOwnerIds->contains((int) $row->agent_id))
                ?? $agents->first(fn (Agent $row) => in_array((int) $row->sales_manager_dmc, $hierarchy, true))
                ?? $agents->first();

            return [
                'id' => $agent ? (int) $agent->agent_id : null,
                'label' => (string) $agency->agency_name,
                'country' => (string) ($agency->country ?: 'Other'),
                'selectable' => $agent !== null,
            ];
        })->values()->all();
    }

    public function agencyIsSelectedByDmc(Agent $agent, int $dmcUserId): bool
    {
        $agent->loadMissing('agency:agency_id,agency_name,dmc_id');
        $agency = $agent->agency;
        if (! $agency) {
            return false;
        }

        return $agency->hasSelectedByDmc($dmcUserId);
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    public function formatAgentsForDropdown(Collection $agents): array
    {
        $agents->loadMissing('agency:agency_id,agency_name');

        return $agents
            ->groupBy(fn (Agent $agent) => (int) ($agent->agency_id ?: $agent->agent_id))
            ->map(function (Collection $group) {
                /** @var Agent $agent */
                $agent = $group->first();
                $agencyName = $agent->agency?->agency_name;

                return [
                    'id' => (int) $agent->agent_id,
                    'label' => $agencyName ?: $agent->name,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{ledger_description: string, metadata: array<string, mixed>}
     */
    public function buildTopupLedgerContext(
        string $recipientType,
        int $recipientId,
        ?string $userNote = null,
        ?string $initiatedBy = null,
    ): array {
        $recipientLabel = $this->formatOwnerLabel($recipientType, $recipientId);

        return [
            'ledger_description' => $userNote
                ? "Travclicks credited {$recipientLabel}: {$userNote}"
                : "Travclicks credited {$recipientLabel}",
            'metadata' => [
                'transfer' => [
                    'from_type' => 'travclicks',
                    'from_id' => 1,
                    'from_name' => 'Travclicks Platform',
                    'to_type' => $recipientType,
                    'to_id' => $recipientId,
                    'to_name' => $this->resolveOwnerName($recipientType, $recipientId),
                    'initiated_by' => $initiatedBy,
                ],
            ],
        ];
    }

    /**
     * @return array{
     *     ledger_description: string,
     *     debit_ledger_description: string,
     *     credit_ledger_description: string,
     *     metadata: array<string, mixed>
     * }
     */
    public function buildAllocateLedgerContext(
        string $allocatorType,
        int $allocatorId,
        string $recipientType,
        int $recipientId,
        ?string $userNote = null,
        ?string $initiatedBy = null,
    ): array {
        $allocatorLabel = $this->formatOwnerLabel($allocatorType, $allocatorId);
        $recipientLabel = $this->formatOwnerLabel($recipientType, $recipientId);

        return [
            'ledger_description' => $userNote ?: "Allocation {$allocatorLabel} → {$recipientLabel}",
            'debit_ledger_description' => $userNote
                ? "Transferred to {$recipientLabel}: {$userNote}"
                : "Transferred to {$recipientLabel}",
            'credit_ledger_description' => $userNote
                ? "Received from {$allocatorLabel}: {$userNote}"
                : "Received from {$allocatorLabel}",
            'metadata' => [
                'transfer' => [
                    'from_type' => $allocatorType,
                    'from_id' => $allocatorId,
                    'from_name' => $this->resolveOwnerName($allocatorType, $allocatorId),
                    'to_type' => $recipientType,
                    'to_id' => $recipientId,
                    'to_name' => $this->resolveOwnerName($recipientType, $recipientId),
                    'initiated_by' => $initiatedBy,
                ],
            ],
        ];
    }

    /**
     * @param  Collection<int, object>  $ledger
     * @return Collection<int, object>
     */
    public function enrichLedgerEntries(Collection $ledger): Collection
    {
        if ($ledger->isEmpty()) {
            return $ledger;
        }

        $userIds = collect();
        $agentIds = collect();

        foreach ($ledger as $entry) {
            $transfer = $this->extractTransferMeta($entry);
            if (! $transfer) {
                continue;
            }

            if (in_array($transfer['from_type'] ?? '', ['travclicks', 'master_dmc', 'dmc'], true)) {
                $userIds->push((int) ($transfer['from_id'] ?? 0));
            }
            if (in_array($transfer['to_type'] ?? '', ['travclicks', 'master_dmc', 'dmc'], true)) {
                $userIds->push((int) ($transfer['to_id'] ?? 0));
            }
            if (($transfer['from_type'] ?? '') === 'agent') {
                $agentIds->push((int) ($transfer['from_id'] ?? 0));
            }
            if (($transfer['to_type'] ?? '') === 'agent') {
                $agentIds->push((int) ($transfer['to_id'] ?? 0));
            }
        }

        foreach ($ledger as $entry) {
            $parsed = $this->parseLegacyTransferParties($entry);
            if ($parsed['from_type'] ?? null) {
                if (in_array($parsed['from_type'], ['travclicks', 'master_dmc', 'dmc'], true)) {
                    $userIds->push((int) $parsed['from_id']);
                } elseif ($parsed['from_type'] === 'agent') {
                    $agentIds->push((int) $parsed['from_id']);
                }
            }
            if ($parsed['to_type'] ?? null) {
                if (in_array($parsed['to_type'], ['travclicks', 'master_dmc', 'dmc'], true)) {
                    $userIds->push((int) $parsed['to_id']);
                } elseif ($parsed['to_type'] === 'agent') {
                    $agentIds->push((int) $parsed['to_id']);
                }
            }
        }

        $users = $userIds->filter()->unique()->isNotEmpty()
            ? User::whereIn('userId', $userIds->filter()->unique()->values())->get(['userId', 'company_name', 'name'])->keyBy('userId')
            : collect();

        $agents = $agentIds->filter()->unique()->isNotEmpty()
            ? Agent::with('agency:agency_id,agency_name')
                ->whereIn('agent_id', $agentIds->filter()->unique()->values())
                ->get(['agent_id', 'name', 'agency_id'])
                ->keyBy('agent_id')
            : collect();

        return $ledger->map(function ($entry) use ($users, $agents) {
            $entry->transfer_summary = $this->resolveLedgerTransferSummary($entry, $users, $agents);
            $entry->fx_summary = $this->resolveFxSummary($entry);
            $entry->reference_id = json_decode((string) ($entry->ledger_metadata ?? ''), true)['reference_id'] ?? null;

            return $entry;
        });
    }

    /**
     * SGD equivalent of a Travclicks funding credit. Rows sent before SGD was recorded
     * get an estimate at today's rate (estimated = true).
     */
    protected function resolveFxSummary(object $entry): ?array
    {
        if (($entry->ledger_entry_type ?? '') !== 'allocation_credit') {
            return null;
        }

        $meta = json_decode((string) ($entry->ledger_metadata ?? ''), true) ?: [];
        if (($meta['transfer']['from_type'] ?? '') !== 'travclicks') {
            return null;
        }

        $fx = $meta['fx'] ?? [];
        $from = strtoupper((string) ($fx['allocator_currency'] ?? ''));
        $to = strtoupper((string) ($fx['recipient_currency'] ?? $from));

        if ($from === 'SGD') {
            return [
                'sgd' => (float) ($fx['allocator_amount'] ?? $entry->credit_amount),
                'estimated' => false,
                'rate' => ($to !== 'SGD' && ! empty($fx['exchange_rate'])) ? (float) $fx['exchange_rate'] : null,
                'to' => $to,
            ];
        }

        static $rates = [];
        $rates[$to] ??= CurrencyHelper::getExchangeRate($to, 'SGD');

        return $rates[$to]
            ? ['sgd' => (float) $entry->credit_amount * (float) $rates[$to], 'estimated' => true, 'rate' => null, 'to' => $to]
            : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function extractTransferMeta(object $entry): ?array
    {
        if (empty($entry->ledger_metadata)) {
            return null;
        }

        $decoded = json_decode((string) $entry->ledger_metadata, true);

        return is_array($decoded['transfer'] ?? null) ? $decoded['transfer'] : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseLegacyTransferParties(object $entry): array
    {
        $desc = (string) ($entry->ledger_description ?: $entry->description ?: '');

        if (preg_match('/debit to ([a-z_]+):(\d+)/i', $desc, $matches)) {
            return ['to_type' => $matches[1], 'to_id' => (int) $matches[2]];
        }

        if (preg_match('/credit from ([a-z_]+):(\d+)/i', $desc, $matches)) {
            return ['from_type' => $matches[1], 'from_id' => (int) $matches[2]];
        }

        return [];
    }

    protected function resolveLedgerTransferSummary(object $entry, Collection $users, Collection $agents): string
    {
        $transfer = $this->extractTransferMeta($entry) ?? $this->parseLegacyTransferParties($entry);

        if (! empty($transfer['from_type']) || ! empty($transfer['to_type'])) {
            $fromType = (string) ($transfer['from_type'] ?? '');
            $toType = (string) ($transfer['to_type'] ?? '');
            $fromId = (int) ($transfer['from_id'] ?? 0);
            $toId = (int) ($transfer['to_id'] ?? 0);

            $fromName = ($fromType && $fromId)
                ? $this->resolveOwnerName($fromType, $fromId, $users, $agents)
                : ($transfer['from_name'] ?? null);
            $toName = ($toType && $toId)
                ? $this->resolveOwnerName($toType, $toId, $users, $agents)
                : ($transfer['to_name'] ?? null);

            $fromLabel = $fromName ? "{$fromName} ({$this->ownerTypeLabel($fromType)})" : null;
            $toLabel = $toName ? "{$toName} ({$this->ownerTypeLabel($toType)})" : null;

            if (($entry->ledger_entry_type ?? '') === 'allocation_debit' && $toLabel) {
                return "To: {$toLabel}";
            }

            if (($entry->ledger_entry_type ?? '') === 'allocation_credit' && $fromLabel) {
                return "From: {$fromLabel}";
            }

            if ($fromLabel && $toLabel) {
                return "{$fromLabel} → {$toLabel}";
            }
        }

        return (string) ($entry->ledger_description ?: $entry->description ?: '—');
    }

    /**
     * Resolve which wallet the current user is viewing and what they can do.
     *
     * @return array<string, mixed>
     */
    public function resolveContext(Authenticatable $user, ?string $ownerType = null, ?int $ownerId = null): array
    {
        $isAdmin = in_array($user->role_id, [1, 2, 3, 4]);
        $isMasterDmc = (int) $user->role_id === 10;
        $isDmcOwner = (int) $user->role_id === 11;
        $isAgent = (int) $user->role_id === 20 || ! empty($user->agent_id);

        $canTopUp = $isAdmin || $isMasterDmc
            || ($isDmcOwner && $this->dmcAgencyAllocationEnabled());
        $canSwitchWallet = $isAdmin || $isMasterDmc || $isDmcOwner;

        if (! $ownerType || ! $ownerId) {
            if ($isAdmin) {
                $ownerType = $ownerType ?? 'travclicks';
                $ownerId = $ownerId ?? 1;
            } elseif ($isAgent && $user->agent_id) {
                $ownerType = 'agent';
                $ownerId = (int) $user->agent_id;
                $canTopUp = false;
                $canSwitchWallet = false;
            } elseif ($isDmcOwner) {
                $ownerType = 'dmc';
                $ownerId = (int) $user->userId;
            } elseif ($isMasterDmc) {
                $ownerType = 'master_dmc';
                $ownerId = (int) $user->userId;
            }
        }

        if (! $ownerType || ! $ownerId) {
            abort(403, 'Wallet management is available only to DMC owners, Master DMC, agents, and administrators.');
        }

        if (! $this->canAccessWallet($user, $ownerType, $ownerId)) {
            abort(403, 'You do not have access to this wallet.');
        }

        $currency = $this->resolveWalletCurrency($ownerType, $ownerId, request('currency'));
        $walletRow = ($ownerType && $ownerId)
            ? $this->getOrEmptyWallet($ownerType, $ownerId, $currency)
            : (object) ['available_balance' => 0, 'reserved_balance' => 0, 'total_credited' => 0, 'total_debited' => 0, 'wallet_currency_code' => $currency, 'wallet_status' => 'active'];
        $wallet = $walletRow;
        $ledgerData = $this->buildLedgerViewData(
            $user,
            $ownerType,
            $ownerId,
            request('currency'),
            request('ledger_date_from'),
            request('ledger_date_to'),
            (int) request('ledger_per_page', self::LEDGER_PER_PAGE_DEFAULT),
            (int) request('ledger_page', 1),
        );

        $ledger = $ledgerData['ledger'];
        $ledgerFilters = $ledgerData['ledgerFilters'];
        $ledgerSummary = $ledgerData['ledgerSummary'];
        $ledgerFilteredSummary = $ledgerData['ledgerFilteredSummary'];

        $ownerDisplayName = ($ownerType && $ownerId)
            ? $this->resolveOwnerName($ownerType, $ownerId)
            : null;

        $topUpTargets = $this->getTopUpTargets($user);

        return compact(
            'ownerType', 'ownerId', 'wallet', 'ledger', 'ledgerFilters', 'currency',
            'ledgerSummary', 'ledgerFilteredSummary',
            'canTopUp', 'canSwitchWallet', 'topUpTargets',
            'isAdmin', 'isAgent', 'ownerDisplayName'
        ) + ['isDmc' => $isDmcOwner, 'isFinance' => false];
    }

    protected function resolveWalletCurrency(?string $ownerType, ?int $ownerId, ?string $requestedCurrency = null): string
    {
        if ($ownerType && $ownerId) {
            $configured = $this->getConfiguredCurrenciesForOwner($ownerType, $ownerId);
            $allowed = $configured['currencies'];
            $requested = $requestedCurrency ? strtoupper($requestedCurrency) : null;

            if ($requested) {
                $walletExists = DB::table('wallet')
                    ->where('wallet_owner_type', $ownerType)
                    ->where('wallet_owner_id', $ownerId)
                    ->where('wallet_currency_code', $requested)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($walletExists || $allowed === [] || in_array($requested, $allowed, true)) {
                    return $requested;
                }
            }

            $currency = DB::table('wallet')
                ->where('wallet_owner_type', $ownerType)
                ->where('wallet_owner_id', $ownerId)
                ->whereNull('deleted_at')
                ->when($allowed !== [], fn ($q) => $q->whereIn('wallet_currency_code', $allowed))
                ->orderByDesc('updated_at')
                ->value('wallet_currency_code');

            if ($currency) {
                return strtoupper((string) $currency);
            }

            if (! empty($configured['default'])) {
                return (string) $configured['default'];
            }

            if ($allowed !== []) {
                return $allowed[0];
            }
        }

        return 'SGD';
    }

    public function resolveCurrencyForOwner(string $ownerType, int $ownerId, ?string $requestedCurrency = null): string
    {
        return $this->resolveWalletCurrency($ownerType, $ownerId, $requestedCurrency);
    }

    /**
     * Currencies configured for a hierarchy owner via country mapping (and optional user.currency).
     *
     * @return array{currencies: array<int, string>, default: string|null, countries: array<int, string>}
     */
    public function getConfiguredCurrenciesForOwner(string $ownerType, int $ownerId): array
    {
        $userId = $this->resolveUserIdForOwner($ownerType, $ownerId);
        if (! $userId) {
            return [
                'currencies' => [],
                'default' => null,
                'countries' => [],
            ];
        }

        return $this->getConfiguredCurrenciesForUser($userId);
    }

    /**
     * @return array{currencies: array<int, string>, default: string|null, countries: array<int, string>}
     */
    public function getConfiguredCurrenciesForUser(int $userId): array
    {
        $user = User::where('userId', $userId)->first(['userId', 'country', 'currency']);
        if (! $user) {
            return [
                'currencies' => [],
                'default' => null,
                'countries' => [],
            ];
        }

        $countries = CommonHelper::resolveSupportedCountriesForDmc($user);
        $currencies = [];

        if ($countries !== []) {
            $countryRows = Country::query()
                ->where(function ($q) use ($countries) {
                    foreach ($countries as $country) {
                        $q->orWhereRaw('LOWER(name) = ?', [strtolower($country)]);
                    }
                })
                ->get(['name', 'currency']);

            foreach ($countryRows as $row) {
                $code = CurrencyHelper::toIsoCode($row->currency ?? null, '');
                if ($code !== '') {
                    $currencies[] = $code;
                }
            }
        }

        $userCurrency = CurrencyHelper::toIsoCode($user->currency ?? null, '');
        if ($userCurrency !== '') {
            array_unshift($currencies, $userCurrency);
        }

        $currencies = array_values(array_unique(array_filter($currencies)));

        $default = null;
        if ($userCurrency !== '' && in_array($userCurrency, $currencies, true)) {
            $default = $userCurrency;
        } elseif (count($currencies) === 1) {
            $default = $currencies[0];
        } elseif ($currencies !== []) {
            $default = $currencies[0];
        }

        return [
            'currencies' => $currencies,
            'default' => $default,
            'countries' => $countries,
        ];
    }

    /**
     * Resolve the users.userId that owns country/currency config for a wallet owner.
     */
    protected function resolveUserIdForOwner(string $ownerType, int $ownerId): ?int
    {
        if (in_array($ownerType, ['travclicks', 'master_dmc', 'dmc'], true)) {
            return $ownerId > 0 ? $ownerId : null;
        }

        if ($ownerType === 'agent') {
            $agent = Agent::where('agent_id', $ownerId)->where('status', 1)->first(['agent_id', 'sales_manager_dmc']);
            if (! $agent || ! $agent->sales_manager_dmc) {
                return null;
            }

            $currentId = (int) $agent->sales_manager_dmc;
            $guard = 0;

            while ($currentId > 0 && $guard < 6) {
                $user = User::where('userId', $currentId)->first(['userId', 'role_id', 'created_by']);
                if (! $user) {
                    break;
                }

                if ((int) $user->role_id === 11) {
                    return (int) $user->userId;
                }

                $currentId = (int) ($user->created_by ?? 0);
                $guard++;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function getCurrenciesForOwner(string $ownerType, int $ownerId): array
    {
        $configured = $this->getConfiguredCurrenciesForOwner($ownerType, $ownerId)['currencies'];

        $walletCurrencies = DB::table('wallet')
            ->where('wallet_owner_type', $ownerType)
            ->where('wallet_owner_id', $ownerId)
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->pluck('wallet_currency_code')
            ->map(fn ($c) => strtoupper((string) $c))
            ->unique()
            ->values()
            ->all();

        if ($configured !== []) {
            return $configured;
        }

        return $walletCurrencies;
    }

    public function canAccessWallet(Authenticatable $user, string $ownerType, int $ownerId): bool
    {
        if (in_array($user->role_id, [1, 2, 3, 4])) {
            return true;
        }

        if ($ownerType === 'agent' && $user->agent_id == $ownerId) {
            return true;
        }

        if ($ownerType === 'dmc') {
            if ((int) $user->role_id === 11 && (int) $user->userId === $ownerId) {
                return true;
            }

            if ((int) $user->role_id === 10) {
                return $this->dmcBelongsToMaster($ownerId, (int) $user->userId);
            }

            return false;
        }

        if ($ownerType === 'master_dmc' && (int) $user->userId === $ownerId && (int) $user->role_id === 10) {
            return true;
        }

        if ($ownerType === 'agent') {
            $dmcUserId = (int) $user->role_id === 11
                ? (int) $user->userId
                : \App\Helpers\CommonHelper::resolveDmcUserIdForStaff($user);

            if ($dmcUserId) {
                $agent = Agent::where('agent_id', $ownerId)->where('status', 1)->first();

                return $agent && $this->agentBelongsToDmc($agent, $dmcUserId);
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopUpTargets(Authenticatable $user): array
    {
        $targets = [];

        if (in_array($user->role_id, [1, 2, 3, 4])) {
            $targets[] = ['type' => 'travclicks', 'id' => 1, 'label' => 'Travclicks Platform'];
            $masterDmcs = User::where('role_id', 10)->whereNull('deleted_at')->orderBy('company_name')->limit(50)->get(['userId', 'company_name', 'name']);
            foreach ($masterDmcs as $master) {
                $targets[] = ['type' => 'master_dmc', 'id' => (int) $master->userId, 'label' => $master->company_name ?: $master->name];
            }

            $dmcs = User::where('role_id', 11)->whereNull('deleted_at')->orderBy('company_name')->limit(100)->get(['userId', 'company_name', 'name']);
            foreach ($dmcs as $dmc) {
                $targets[] = ['type' => 'dmc', 'id' => $dmc->userId, 'label' => $dmc->company_name ?: $dmc->name];
            }

            $agents = Agent::where('status', 1)->orderBy('name')->limit(500)->get(['agent_id', 'name']);
            foreach ($agents as $agent) {
                $targets[] = ['type' => 'agent', 'id' => (int) $agent->agent_id, 'label' => $agent->name];
            }
        } elseif ((int) ($user->role_id ?? 0) === 10) {
            $master = User::where('userId', $user->userId)->first(['userId', 'company_name', 'name']);
            if ($master) {
                $targets[] = [
                    'type' => 'master_dmc',
                    'id' => (int) $master->userId,
                    'label' => ($master->company_name ?: $master->name).' (Master DMC)',
                ];
            }

            $dmcs = User::where('role_id', 11)
                ->where('master_dmc_id', $user->userId)
                ->whereNull('deleted_at')
                ->orderBy('company_name')
                ->get(['userId', 'company_name', 'name']);

            foreach ($dmcs as $dmc) {
                $targets[] = [
                    'type' => 'dmc',
                    'id' => (int) $dmc->userId,
                    'label' => $dmc->company_name ?: $dmc->name,
                ];
            }
        } elseif ((int) ($user->role_id ?? 0) === 11) {
            $dmcUserId = (int) $user->userId;
            $dmc = User::where('userId', $dmcUserId)->first();
            if ($dmc) {
                $targets[] = ['type' => 'dmc', 'id' => $dmcUserId, 'label' => ($dmc->company_name ?: $dmc->name).' (DMC Wallet)'];
            }
            $agents = $this->getAgentsForDmc($dmcUserId);
            foreach ($agents->groupBy(fn (Agent $agent) => (int) ($agent->agency_id ?: $agent->agent_id)) as $group) {
                /** @var Agent $agent */
                $agent = $group->first();
                $agencyName = $agent->agency?->agency_name ?: $agent->name;
                $targets[] = ['type' => 'agent', 'id' => $agent->agent_id, 'label' => $agencyName.' (Agency)'];
            }
        }

        return $targets;
    }

    /**
     * @return array<int, int|string>
     */
    private function getDmcHierarchyIds(int $dmcId): array
    {
        $salesHeadRoles = [33, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138];

        $ids = collect([$dmcId]);
        $salesHeads = User::where('created_by', $dmcId)->whereIn('role_id', $salesHeadRoles)->pluck('userId');
        $ids = $ids->merge($salesHeads);
        $salesManagers = User::whereIn('created_by', $salesHeads)->whereIn('role_id', [12, 37])->pluck('userId');
        $ids = $ids->merge($salesManagers);
        $assistants = User::whereIn('created_by', $salesManagers)->where('role_id', 38)->pluck('userId');
        $ids = $ids->merge($assistants);

        return $ids->unique()->values()->all();
    }
}
