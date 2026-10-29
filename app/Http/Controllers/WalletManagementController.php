<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\CurrencyHelper;
use App\Services\Finance\WalletQueryService;
use App\Services\Finance\WalletService;
use App\Models\Agent;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

class WalletManagementController extends Controller
{
    public function __construct(
        protected WalletQueryService $walletQuery,
        protected WalletService $walletService,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $ownerType = $request->input('owner_type');
        $ownerId = $request->input('owner_id') ? (int) $request->input('owner_id') : null;

        $context = $this->walletQuery->resolveContext($user, $ownerType, $ownerId);
        $wallets = $this->walletQuery->enrichWalletsWithOwnerNames(
            $this->walletQuery->listWalletsForUser($user, $ownerType)
        );

        $roleGroup = $this->resolveRoleGroup($user);
        $allocatorCurrency = $this->resolveAllocatorCurrency($user, $roleGroup);
        $isTravclicks = $roleGroup === 'travclicks_owner';

        return view('finance.wallet.index', array_merge($context, [
            'wallets' => $wallets,
            'pageTitle' => 'Wallet Management',
            'roleId' => (int) ($user->role_id ?? 0),
            'roleGroup' => $roleGroup,
            'canAllocate' => $this->canAllocate($roleGroup),
            'dmcIdForRole' => $this->resolveDmcId($user),
            'allocatorCurrency' => $allocatorCurrency,
            'dmcDrill' => $isTravclicks ? $this->initialDrillData() : ($roleGroup === 'master_dmc' ? $this->initialDrillData((int) $user->userId) : null),
        ]));
    }

    /**
     * Travclicks drill-down (AJAX): Master DMCs -> DMCs -> DMC ledger.
     */
    public function dmcLedger(Request $request)
    {
        $roleGroup = $this->resolveRoleGroup(Auth::user());
        if (! in_array($roleGroup, ['travclicks_owner', 'master_dmc'], true)) {
            abort(403, 'You cannot view the DMC ledger.');
        }

        // A Master DMC is pinned to its own record: it starts at its DMC list, never sees other masters.
        $scoped = $roleGroup === 'master_dmc';
        $view = (string) $request->input('view', 'masters');
        $masterId = $scoped ? (int) Auth::user()->userId : (int) $request->input('master_id', 0);
        $dmcId = (int) $request->input('dmc_id', 0);
        if ($scoped && $view !== 'ledger') {
            $view = 'dmcs';
        }

        if ($view === 'ledger' && $dmcId > 0) {
            $data = $this->walletQuery->buildTravclicksDmcLedgerViewData(
                Auth::user(),
                $dmcId,
                $request->input('dmc_currency'),
                $request->input('dmc_entry_type'),
                $request->input('dmc_date_from'),
                $request->input('dmc_date_to'),
                (int) $request->input('dmc_per_page', WalletQueryService::LEDGER_PER_PAGE_DEFAULT),
                (int) $request->input('dmc_ledger_page', 1),
            );

            $results = [
                'dmcLedger' => $data['ledger'],
                'dmcLedgerQueryParams' => $data['queryParams'] + ['view' => 'ledger', 'master_id' => $masterId],
                'dmcLedgerTotals' => $data['totalsByCurrency'],
            ];

            if ($request->boolean('results_only')) {
                $html = view('finance.partials.dmc-ledger-results', $results)->render();
            } else {
                $html = view('finance.partials.dmc-drill', $results + [
                    'scoped' => $scoped,
                    'view' => 'ledger',
                    'masterId' => $masterId,
                    'dmcId' => $dmcId,
                    'masterName' => $masterId ? $this->walletQuery->resolveOwnerName('master_dmc', $masterId) : 'Direct DMCs',
                    'dmcName' => $this->walletQuery->resolveOwnerName('dmc', $dmcId),
                    'dmcLedgerFilters' => $data['filters'],
                    'currencyOptions' => $this->walletQuery->listDmcWalletSummaries($dmcId)->pluck('wallet_currency_code')->unique()->values()->all(),
                ])->render();
            }
        } else {
            $isDmcs = $view === 'dmcs';
            $q = mb_strtolower(trim((string) $request->input('q', '')));
            $all = ($isDmcs ? $this->walletQuery->listDmcCardsForMaster($masterId) : $this->walletQuery->listMasterDmcCards())
                ->filter(fn ($row) => $q === '' || str_contains(mb_strtolower($row->name), $q))
                ->values();

            $perPage = 10;
            $page = max(1, (int) $request->input('dmc_ledger_page', 1));
            $items = new LengthAwarePaginator($all->forPage($page, $perPage)->values(), $all->count(), $perPage, $page, [
                'path' => route('finance.wallet.dmc-ledger', [], false),
                'pageName' => 'dmc_ledger_page',
            ]);
            $items->appends(array_filter(['view' => $view, 'master_id' => $isDmcs ? $masterId : null, 'q' => $q]));

            $listData = ['view' => $isDmcs ? 'dmcs' : 'masters', 'masterId' => $masterId, 'items' => $items];

            $html = $request->boolean('list_only')
                ? view('finance.partials.dmc-drill-list', $listData)->render()
                : view('finance.partials.dmc-drill', $listData + [
                    'scoped' => $scoped,
                    'masterName' => $masterId ? $this->walletQuery->resolveOwnerName('master_dmc', $masterId) : 'Direct DMCs',
                ])->render();
        }

        return response()->json(['html' => $html]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function initialDrillData(?int $masterId = null): array
    {
        $all = $masterId
            ? $this->walletQuery->listDmcCardsForMaster($masterId)
            : $this->walletQuery->listMasterDmcCards();

        return [
            'view' => $masterId ? 'dmcs' : 'masters',
            'scoped' => (bool) $masterId,
            'masterName' => $masterId ? $this->walletQuery->resolveOwnerName('master_dmc', $masterId) : '',
            'masterId' => (int) $masterId,
            'items' => new LengthAwarePaginator($all->take(10)->values(), $all->count(), 10, 1, [
                'path' => route('finance.wallet.dmc-ledger', [], false),
                'pageName' => 'dmc_ledger_page',
            ]),
        ];
    }

    /**
     * Two-step funding: Travclicks → DMC. DMC → Agency is behind a config flag.
     */
    protected function canAllocate(string $roleGroup): bool
    {
        return match ($roleGroup) {
            'travclicks_owner' => true,
            'dmc' => $this->walletQuery->dmcAgencyAllocationEnabled(),
            default => false,
        };
    }

    /**
     * Allocator debit currency = existing wallet currency (same as Available Balance),
     * not users.currency / country default.
     */
    protected function resolveAllocatorCurrency($user, ?string $roleGroup = null): string
    {
        $roleGroup = $roleGroup ?: $this->resolveRoleGroup($user);

        if ($roleGroup === 'master_dmc') {
            return $this->walletQuery->resolveCurrencyForOwner('master_dmc', (int) $user->userId);
        }

        if ($roleGroup === 'dmc') {
            $dmcId = $this->resolveDmcId($user);
            if ($dmcId) {
                return $this->walletQuery->resolveCurrencyForOwner('dmc', $dmcId);
            }
        }

        if ($roleGroup === 'travclicks_owner') {
            return $this->walletQuery->resolveCurrencyForOwner('travclicks', 1);
        }

        return 'SGD';
    }

    /**
     * Live FX preview using existing CurrencyHelper / CurrencyService conversion.
     */
    public function convertPreview(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        $from = strtoupper($validated['from']);
        $to = strtoupper($validated['to']);
        $amount = (float) $validated['amount'];

        if ($from === $to) {
            return response()->json([
                'success' => true,
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'converted_amount' => round($amount, 4),
                'exchange_rate' => 1,
                'same_currency' => true,
            ]);
        }

        // Same conversion stack as /get-exchange-rate (CurrencyService via CurrencyHelper).
        $converted = CurrencyHelper::convertAmount($amount, $from, $to);
        $rate = CurrencyHelper::getExchangeRate($from, $to);

        if ($converted === null || $rate === null) {
            return response()->json([
                'success' => false,
                'message' => "Exchange rate unavailable for {$from} → {$to}.",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'converted_amount' => round((float) $converted, 4),
            'exchange_rate' => (float) $rate,
            'same_currency' => false,
        ]);
    }

    /**
     * SGD to currency preview for the Send to DMC form, using the stored Countries rate.
     */
    public function sgdPreview(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'to' => 'required|string|size:3',
        ]);

        $to = strtoupper($validated['to']);
        $rate = Country::sgdRateFor($to);

        if ($rate === null) {
            return response()->json([
                'success' => false,
                'message' => "No stored rate for {$to}. Run Sync Currency Rates in Profile.",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'converted_amount' => round((float) $validated['amount'] * $rate, 4),
            'exchange_rate' => round($rate, 2),
        ]);
    }

    public function ledger(Request $request)
    {
        $user = Auth::user();
        $ownerType = (string) $request->input('owner_type', '');
        $ownerId = (int) $request->input('owner_id', 0);

        if ($ownerType === '' || $ownerId <= 0) {
            $context = $this->walletQuery->resolveContext($user, null, null);
            $ownerType = (string) $context['ownerType'];
            $ownerId = (int) $context['ownerId'];
        }

        $ledgerData = $this->walletQuery->buildLedgerViewData(
            $user,
            $ownerType,
            $ownerId,
            $request->input('currency'),
            $request->input('ledger_date_from'),
            $request->input('ledger_date_to'),
            (int) $request->input('ledger_per_page', WalletQueryService::LEDGER_PER_PAGE_DEFAULT),
            (int) $request->input('ledger_page', 1),
        );

        return $this->ledgerJsonResponse(
            $ledgerData['ledger'],
            $ledgerData['ledgerFilters'],
            $ledgerData['ledgerQueryParams'],
            (bool) $request->boolean('show_reference', true),
            $ledgerData['ledgerSummary'] ?? null,
            $ledgerData['ledgerFilteredSummary'] ?? null,
        );
    }

    public function transactionsGlance(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $this->walletQuery->isAdminUser($user);
        $scope = (string) $request->input('scope', $isAdmin ? 'master_dmc_flow' : 'wallet');
        $ownerType = (string) $request->input('owner_type', '');
        $ownerId = (int) $request->input('owner_id', 0);

        if ($isAdmin && in_array($scope, ['all', 'master_dmc_flow'], true)) {
            $masterDmcId = ($ownerType === 'master_dmc' && $ownerId > 0) ? $ownerId : null;

            $glanceData = $this->walletQuery->buildAdminTransactionGlanceViewData(
                $user,
                $masterDmcId ? 'master_dmc' : null,
                $masterDmcId,
                $request->input('currency'),
                $request->input('glance_date_from'),
                $request->input('glance_date_to'),
                (int) $request->input('glance_per_page', WalletQueryService::GLANCE_PER_PAGE_DEFAULT),
                (int) $request->input('glance_page', 1),
            );

            $masterDmcOptions = $this->walletQuery->listMasterDmcsForAdminFilter();

            if ($request->ajax()) {
                return $this->glanceJsonResponse(
                    $glanceData['transactions'],
                    $glanceData['glanceFilters'],
                    $glanceData['glanceQueryParams'],
                    $glanceData['currency'],
                    $glanceData['flowSummary'] ?? null,
                );
            }

            $ownerDisplayName = $masterDmcId
                ? $this->walletQuery->resolveOwnerName('master_dmc', $masterDmcId)
                : 'All Master DMCs';

            return view('finance.wallet.transactions', [
                'pageTitle' => 'Master DMC Fund Flow',
                'transactions' => $glanceData['transactions'],
                'glanceFilters' => $glanceData['glanceFilters'],
                'currency' => $glanceData['currency'],
                'flowSummary' => $glanceData['flowSummary'],
                'ownerDisplayName' => $ownerDisplayName,
                'glanceScope' => 'master_dmc_flow',
                'masterDmcOptions' => $masterDmcOptions,
                'selectedMasterDmcId' => $masterDmcId,
                'glanceAjaxUrl' => route('finance.wallet.transactions'),
                'glancePersistParams' => array_filter([
                    'scope' => 'master_dmc_flow',
                    'owner_type' => $masterDmcId ? 'master_dmc' : null,
                    'owner_id' => $masterDmcId,
                    'currency' => $request->input('currency'),
                ]),
                'backUrl' => route('finance.wallet.index'),
                'ledgerUrl' => null,
            ]);
        }

        if ($ownerType === '' || $ownerId <= 0) {
            $context = $this->walletQuery->resolveContext($user, null, null);
            $ownerType = (string) $context['ownerType'];
            $ownerId = (int) $context['ownerId'];
        }

        $glanceData = $this->walletQuery->buildTransactionGlanceViewData(
            $user,
            $ownerType,
            $ownerId,
            $request->input('currency'),
            $request->input('glance_date_from'),
            $request->input('glance_date_to'),
            (int) $request->input('glance_per_page', WalletQueryService::GLANCE_PER_PAGE_DEFAULT),
            (int) $request->input('glance_page', 1),
        );

        if ($request->ajax()) {
            return $this->glanceJsonResponse(
                $glanceData['transactions'],
                $glanceData['glanceFilters'],
                $glanceData['glanceQueryParams'],
                $glanceData['currency'],
            );
        }

        $backParams = array_filter([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'currency' => $glanceData['currency'],
        ]);

        return view('finance.wallet.transactions', [
            'pageTitle' => 'Wallet Transactions',
            'transactions' => $glanceData['transactions'],
            'glanceFilters' => $glanceData['glanceFilters'],
            'currency' => $glanceData['currency'],
            'ownerDisplayName' => $this->walletQuery->resolveOwnerName($ownerType, $ownerId),
            'glanceAjaxUrl' => route('finance.wallet.transactions'),
            'glancePersistParams' => [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'currency' => $glanceData['currency'],
            ],
            'backUrl' => route('finance.wallet.index', $backParams),
            'ledgerUrl' => route('finance.wallet.index', $backParams).'#wallet-ledger-card',
        ]);
    }

    protected function glanceJsonResponse(
        $transactions,
        array $glanceFilters,
        array $glanceQueryParams,
        string $currency,
        ?array $flowSummary = null,
    ) {
        return response()->json([
            'html' => view('finance.partials.wallet-transactions-glance-results', [
                'transactions' => $transactions,
                'glanceFilters' => $glanceFilters,
                'glanceQueryParams' => $glanceQueryParams,
                'currency' => $currency,
                'flowSummary' => $flowSummary,
            ])->render(),
            'meta' => [
                'total' => $transactions->total(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
            ],
        ]);
    }

    protected function ledgerJsonResponse(
        $ledger,
        array $ledgerFilters,
        array $ledgerQueryParams,
        bool $showReferenceColumn,
        ?array $ledgerSummary = null,
        ?array $ledgerFilteredSummary = null,
    ) {
        return response()->json([
            'html' => view('finance.partials.wallet-ledger-results', [
                'ledger' => $ledger,
                'ledgerFilters' => $ledgerFilters,
                'ledgerQueryParams' => $ledgerQueryParams,
                'showReferenceColumn' => $showReferenceColumn,
                'ledgerSummary' => $ledgerSummary,
                'ledgerFilteredSummary' => $ledgerFilteredSummary,
            ])->render(),
            'meta' => [
                'total' => $ledger->total(),
                'current_page' => $ledger->currentPage(),
                'last_page' => $ledger->lastPage(),
                'per_page' => $ledger->perPage(),
            ],
        ]);
    }

    protected function resolveRoleGroup($user): string
    {
        $roleId = (int) ($user->role_id ?? 0);

        if (in_array($roleId, [1, 2, 3, 4], true)) {
            return 'travclicks_owner';
        }

        if ($roleId === 10) {
            return 'master_dmc';
        }

        $dmcRoles = [11];
        if (in_array($roleId, $dmcRoles, true)) {
            return 'dmc';
        }

        if ($roleId === 20) {
            return 'agent';
        }

        return 'unknown';
    }

    protected function resolveDmcId($user): ?int
    {
        $roleGroup = $this->resolveRoleGroup($user);
        if ($roleGroup !== 'dmc') {
            return null;
        }

        if ((int) ($user->role_id ?? 0) !== 11) {
            return null;
        }

        return (int) $user->userId;
    }

    public function dropdownMasterDmcs(Request $request)
    {
        $user = Auth::user();
        if ((int) ($user->role_id ?? 0) !== 10) {
            abort(403, 'Only Master DMC can access this endpoint.');
        }

        $dmcs = User::where('role_id', 11)
            ->where('master_dmc_id', (int) $user->userId)
            ->whereNull('deleted_at')
            ->orderBy('company_name')
            ->get(['userId', 'company_name', 'name']);

        return response()->json([
            'items' => $dmcs->map(fn ($d) => [
                'id' => (int) $d->userId,
                'label' => $d->company_name ?: $d->name,
            ]),
        ]);
    }

    public function dropdownMasterAgents(Request $request)
    {
        $user = Auth::user();
        if ((int) ($user->role_id ?? 0) !== 10) {
            abort(403, 'Only Master DMC can access this endpoint.');
        }

        $dmcId = (int) $request->query('dmc_id');
        if ($dmcId <= 0) {
            return response()->json(['items' => []]);
        }

        if (! $this->walletQuery->dmcBelongsToMaster($dmcId, (int) $user->userId)) {
            return response()->json(['items' => []], 403);
        }

        return response()->json([
            'items' => $this->walletQuery->formatAgentsForDropdown(
                $this->walletQuery->getAgentsForDmc($dmcId)
            ),
        ]);
    }

    public function dropdownDmcAgents(Request $request)
    {
        $user = Auth::user();
        if ($this->resolveRoleGroup($user) !== 'dmc') {
            abort(403, 'Only DMC can access this endpoint.');
        }

        if (! $this->walletQuery->dmcAgencyAllocationEnabled()) {
            abort(403, 'DMC to Agency wallet funding is currently disabled.');
        }

        $dmcId = $this->resolveDmcId($user);
        if (! $dmcId) {
            abort(403, 'DMC not resolved for this user.');
        }

        return response()->json([
            'items' => $this->walletQuery->formatSelectedAgenciesForDropdown($dmcId),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Currencies for the selected Master DMC / DMC / Agent (via country mapping).
     */
    public function dropdownCurrencies(Request $request)
    {
        $user = Auth::user();
        $roleGroup = $this->resolveRoleGroup($user);

        $ownerType = (string) $request->query('owner_type', '');
        $ownerId = (int) $request->query('owner_id', 0);

        if (! in_array($ownerType, ['travclicks', 'master_dmc', 'dmc', 'agent'], true) || $ownerId <= 0) {
            return response()->json([
                'currencies' => [],
                'default' => null,
                'countries' => [],
            ]);
        }

        if ($roleGroup === 'master_dmc') {
            if ($ownerType !== 'dmc' || ! $this->walletQuery->dmcBelongsToMaster($ownerId, (int) $user->userId)) {
                return response()->json(['currencies' => [], 'default' => null, 'countries' => []], 403);
            }
        } elseif ($roleGroup === 'dmc') {
            $dmcId = $this->resolveDmcId($user);
            if ($ownerType === 'dmc') {
                if (! $dmcId || $ownerId !== $dmcId) {
                    return response()->json(['currencies' => [], 'default' => null, 'countries' => []], 403);
                }
            } elseif ($ownerType === 'agent') {
                $agent = Agent::where('agent_id', $ownerId)->where('status', 1)->first();
                if (! $dmcId || ! $agent || ! $this->walletQuery->agentBelongsToDmc($agent, $dmcId)) {
                    return response()->json(['currencies' => [], 'default' => null, 'countries' => []], 403);
                }
            } else {
                return response()->json(['currencies' => [], 'default' => null, 'countries' => []], 403);
            }
        } elseif ($roleGroup === 'travclicks_owner') {
            // Travclicks may query any Master DMC / DMC currency config.
        } elseif ($roleGroup === 'agent') {
            if ($ownerType !== 'agent' || (int) ($user->agent_id ?? 0) !== $ownerId) {
                return response()->json(['currencies' => [], 'default' => null, 'countries' => []], 403);
            }
        } else {
            abort(403, 'Unauthorized.');
        }

        $payload = $this->walletQuery->getConfiguredCurrenciesForOwner($ownerType, $ownerId);

        return response()->json($payload);
    }

    // ---------------------------------------------------------------------
    // Travclicks Owner (role_id 1..4) dependent dropdowns:
    // Master DMC -> DMC -> Agent
    // ---------------------------------------------------------------------

    public function dropdownTravclicksMasterDmcs(Request $request)
    {
        $user = Auth::user();
        if ($this->resolveRoleGroup($user) !== 'travclicks_owner') {
            abort(403, 'Only Travclicks Owner can access this endpoint.');
        }

        $masterDmcs = User::where('role_id', 10)
            ->whereNull('deleted_at')
            ->orderBy('company_name')
            ->get(['userId', 'company_name', 'name']);

        return response()->json([
            'items' => $masterDmcs->map(fn ($m) => [
                'id' => (int) $m->userId,
                'label' => $m->company_name ?: $m->name,
            ]),
        ]);
    }

    public function dropdownTravclicksDmcs(Request $request)
    {
        $user = Auth::user();
        if ($this->resolveRoleGroup($user) !== 'travclicks_owner') {
            abort(403, 'Only Travclicks Owner can access this endpoint.');
        }

        // Travclicks funds DMCs only — list all DMCs (optional filter by master).
        $query = User::where('role_id', 11)->whereNull('deleted_at')->orderBy('company_name');
        $masterDmcId = (int) $request->query('master_dmc_id');
        if ($masterDmcId > 0) {
            $query->where('master_dmc_id', $masterDmcId);
        }

        $dmcs = $query->get(['userId', 'company_name', 'name']);

        return response()->json([
            'items' => $dmcs->map(fn ($d) => [
                'id' => (int) $d->userId,
                'label' => $d->company_name ?: $d->name,
            ]),
        ]);
    }

    public function dropdownTravclicksAgents(Request $request)
    {
        $user = Auth::user();
        if ($this->resolveRoleGroup($user) !== 'travclicks_owner') {
            abort(403, 'Only Travclicks Owner can access this endpoint.');
        }

        $dmcId = (int) $request->query('dmc_id');
        if ($dmcId <= 0) {
            return response()->json(['items' => []]);
        }

        return response()->json([
            'items' => $this->walletQuery->formatAgentsForDropdown(
                $this->walletQuery->getAgentsForDmc($dmcId)
            ),
        ]);
    }

    public function topup(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'wallet_owner_type' => 'required|in:travclicks,master_dmc,dmc,agent,supplier',
            'wallet_owner_id' => 'required|integer|min:1',
            'topup_amount' => 'nullable|numeric|min:0.01',
            'sgd_amount' => 'nullable|numeric|min:0.01',
            'wallet_currency_code' => 'nullable|string|size:3',
            'ledger_description' => 'nullable|string|max:255',
            'reference_id' => 'required|string|max:100',
        ]);

        try {
            $roleGroup = $this->resolveRoleGroup($user);
            $recipientType = $validated['wallet_owner_type'];
            $recipientId = (int) $validated['wallet_owner_id'];
            $sourceAmount = (float) ($validated['topup_amount'] ?? 0);
            $sourceCurrency = $this->resolveAllocatorCurrency($user, $roleGroup);

            if ($roleGroup === 'agent') {
                abort(403, 'Agents cannot allocate wallet amounts.');
            }

            if (! $this->canAllocate($roleGroup)) {
                if ($roleGroup !== 'dmc') {
                    abort(403, 'You cannot allocate wallet amounts.');
                }

                return redirect()
                    ->route('finance.wallet.index')
                    ->with('error', 'DMC to Agency wallet funding is currently disabled. Wallet funds are sent by Travclicks to DMCs only.');
            }

            // DMC allocations always credit the agency in the DMC country currency.
            if ($roleGroup === 'dmc') {
                $targetCurrency = $sourceCurrency;
            } else {
                $targetCurrency = strtoupper((string) ($validated['wallet_currency_code'] ?? ''));
                $configured = $this->walletQuery->getConfiguredCurrenciesForOwner($recipientType, $recipientId);
                $allowedCurrencies = $configured['currencies'];

                if ($allowedCurrencies !== []) {
                    if ($targetCurrency === '' || ! in_array($targetCurrency, $allowedCurrencies, true)) {
                        $targetCurrency = (string) ($configured['default'] ?: $allowedCurrencies[0]);
                    }
                } elseif ($targetCurrency === '') {
                    $targetCurrency = 'SGD';
                }
            }

            // Travclicks always sends SGD; the DMC is credited the converted amount in its own currency.
            if ($roleGroup === 'travclicks_owner') {
                if ($recipientType !== 'dmc') {
                    abort(403, 'Travclicks can send wallet funds only to DMC wallets.');
                }
                $sourceCurrency = 'SGD';
                $sourceAmount = (float) ($validated['sgd_amount'] ?? 0);
            }

            if ($sourceAmount <= 0) {
                throw new RuntimeException('Enter the amount to send.');
            }

            $exchangeRate = 1.0;
            $creditAmount = $sourceAmount;

            if ($sourceCurrency !== $targetCurrency) {
                // Travclicks to DMC uses the stored Countries rate; other flows keep the live rate.
                $rate = $roleGroup === 'travclicks_owner'
                    ? Country::sgdRateFor($targetCurrency)
                    : CurrencyHelper::getExchangeRate($sourceCurrency, $targetCurrency);
                if ($rate === null) {
                    throw new RuntimeException($roleGroup === 'travclicks_owner'
                        ? "No stored rate for {$targetCurrency}. Run Sync Currency Rates in Profile."
                        : "Unable to convert {$sourceCurrency} to {$targetCurrency}. Exchange rate unavailable.");
                }
                $exchangeRate = (float) $rate;
                $creditAmount = round($sourceAmount * $exchangeRate, 4);
            }

            $validated['wallet_currency_code'] = $targetCurrency;

            if ($roleGroup === 'master_dmc') {
                if ($recipientType !== 'dmc') {
                    abort(403, 'Master DMC can allocate only to DMC wallets.');
                }

                $masterDmcId = (int) $user->userId;
                $allowedDmcIds = $this->walletQuery->getDmcUserIdsUnderMaster($masterDmcId);

                if (! in_array($recipientId, $allowedDmcIds, true)) {
                    abort(403, 'Recipient DMC does not belong to this Master DMC.');
                }
            }

            if ($roleGroup === 'dmc') {
                if ($recipientType !== 'agent') {
                    abort(403, 'DMC can allocate only to its Agencies.');
                }

                $dmcId = $this->resolveDmcId($user);
                if (! $dmcId) {
                    abort(403, 'DMC not resolved for this user.');
                }

                $agent = Agent::where('agent_id', $recipientId)->where('status', 1)->first();
                $allowed = $agent && (
                    $this->walletQuery->agentBelongsToDmc($agent, $dmcId)
                    || $this->walletQuery->agencyIsSelectedByDmc($agent, $dmcId)
                );
                if (! $allowed) {
                    abort(403, 'Recipient Agency does not belong to this DMC.');
                }
            }

            $allocatorWalletOwnerType = match ($roleGroup) {
                'travclicks_owner' => 'travclicks',
                'master_dmc' => 'master_dmc',
                'dmc' => 'dmc',
                default => null,
            };

            $allocatorWalletOwnerId = match ($roleGroup) {
                'travclicks_owner' => 1,
                'master_dmc' => (int) $user->userId,
                'dmc' => (int) $this->resolveDmcId($user),
                default => null,
            };

            if (! $allocatorWalletOwnerType || ! $allocatorWalletOwnerId) {
                abort(403, 'Unable to resolve allocator wallet.');
            }

            $ledgerContext = $this->walletQuery->buildAllocateLedgerContext(
                $allocatorWalletOwnerType,
                $allocatorWalletOwnerId,
                $recipientType,
                $recipientId,
                $validated['ledger_description'] ?? null,
                $user->name ?? $user->email,
            );

            $fxSuffix = $sourceCurrency === $targetCurrency
                ? ''
                : " ({$sourceAmount} {$sourceCurrency} → {$creditAmount} {$targetCurrency} @ {$exchangeRate})";

            $this->walletService->allocate([
                'idempotency_key' => (string) Str::uuid(),
                'allocator_wallet_owner_type' => $allocatorWalletOwnerType,
                'allocator_wallet_owner_id' => $allocatorWalletOwnerId,
                'recipient_wallet_owner_type' => $recipientType,
                'recipient_wallet_owner_id' => $recipientId,
                'allocation_amount' => number_format($creditAmount, 4, '.', ''),
                'wallet_currency_code' => $targetCurrency,
                'allocator_currency_code' => $sourceCurrency,
                'recipient_currency_code' => $targetCurrency,
                'allocator_amount' => number_format($sourceAmount, 4, '.', ''),
                'recipient_amount' => number_format($creditAmount, 4, '.', ''),
                'exchange_rate' => number_format($exchangeRate, 8, '.', ''),
                'platform_spend' => $roleGroup === 'travclicks_owner',
                'supplier_api_id' => null,
                'ledger_description' => $ledgerContext['ledger_description'].$fxSuffix,
                'debit_ledger_description' => $ledgerContext['debit_ledger_description'].$fxSuffix,
                'credit_ledger_description' => $ledgerContext['credit_ledger_description'].$fxSuffix,
                'metadata' => array_merge($ledgerContext['metadata'], [
                    'reference_id' => trim($validated['reference_id']),
                    'fx' => [
                        'source_currency' => $sourceCurrency,
                        'source_amount' => number_format($sourceAmount, 4, '.', ''),
                        'target_currency' => $targetCurrency,
                        'target_amount' => number_format($creditAmount, 4, '.', ''),
                        'exchange_rate' => $exchangeRate,
                    ],
                ]),
            ]);

            $successMsg = $roleGroup === 'travclicks_owner'
                ? "Sent {$sourceAmount} SGD to DMC wallet (credited {$creditAmount} {$targetCurrency})."
                : ($sourceCurrency === $targetCurrency
                    ? 'Wallet allocation successfully.'
                    : "Wallet allocation successfully ({$sourceAmount} {$sourceCurrency} → {$creditAmount} {$targetCurrency}).");

            return redirect()
                ->route('finance.wallet.index', [
                    'owner_type' => $allocatorWalletOwnerType,
                    'owner_id' => $allocatorWalletOwnerId,
                    'currency' => $targetCurrency,
                ])
                ->with('success', $successMsg);
        } catch (RuntimeException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
