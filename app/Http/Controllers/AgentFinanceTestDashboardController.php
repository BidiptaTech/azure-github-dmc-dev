<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Tour;
use App\Services\Finance\AgentTourQueryService;
use App\Services\Finance\BookingFinanceOrchestrator;
use App\Services\Finance\WalletQueryService;
use App\Services\Finance\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

class AgentFinanceTestDashboardController extends Controller
{
    public function __construct(
        protected WalletQueryService $walletQuery,
        protected WalletService $walletService,
        protected AgentTourQueryService $agentTourQuery,
        protected BookingFinanceOrchestrator $bookingFinance,
    ) {}

    public function show(Request $request, int $agent)
    {
        $user = Auth::user();

        if (! CommonHelper::canCreateTourOnBehalfOfAgent($user)) {
            abort(403, 'You do not have permission to access agent wallet and tour management.');
        }

        $agentModel = Agent::where('agent_id', $agent)->where('status', 1)->firstOrFail();
        $dmcUserId = CommonHelper::resolveDmcUserIdForStaff($user);

        if (! $dmcUserId || ! $this->walletQuery->agentBelongsToDmc($agentModel, $dmcUserId)) {
            abort(403, 'This agent does not belong to your DMC.');
        }

        if (! $this->walletQuery->canAccessWallet($user, 'agent', $agent)) {
            abort(403, 'You do not have access to this agent wallet.');
        }

        $ledgerData = $this->walletQuery->buildLedgerViewData(
            $user,
            'agent',
            $agent,
            $request->input('currency'),
            $request->input('ledger_date_from'),
            $request->input('ledger_date_to'),
            (int) $request->input('ledger_per_page', WalletQueryService::LEDGER_PER_PAGE_DEFAULT),
            (int) $request->input('ledger_page', 1),
        );

        $walletRow = $this->walletQuery->getOrEmptyWallet('agent', $agent, $ledgerData['currency']);
        $ledger = $ledgerData['ledger'];
        $ledgerFilters = $ledgerData['ledgerFilters'];

        $toursData = $this->agentTourQuery->buildToursViewData(
            $agent,
            $request->input('tours_date_from'),
            $request->input('tours_date_to'),
            (int) $request->input('tours_per_page', AgentTourQueryService::PER_PAGE_DEFAULT),
            (int) $request->input('tours_page', 1),
        );

        $agency = $agentModel->agency_id ? Agency::find($agentModel->agency_id) : null;
        $roleId = (int) $user->role_id;
        $tourCreateRoleIds = [33, 34, 37, 38, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138];
        $configuredCurrencies = $this->walletQuery->getConfiguredCurrenciesForOwner('agent', $agent);

        return view('finance.agent-test-dashboard', [
            'agent' => $agentModel,
            'agency' => $agency,
            'wallet' => $walletRow,
            'ledger' => $ledger,
            'ledgerFilters' => $ledgerFilters,
            'currency' => $ledgerData['currency'],
            'availableCurrencies' => $configuredCurrencies['currencies'] ?: [$ledgerData['currency']],
            'defaultCurrency' => $configuredCurrencies['default'] ?? $ledgerData['currency'],
            'tours' => $toursData['tours'],
            'toursFilters' => $toursData['toursFilters'],
            'walletExists' => ! empty($walletRow->id),
            'canAllocate' => $roleId === 11,
            'canCreateTour' => in_array($roleId, $tourCreateRoleIds, true),
            'canProForm' => CommonHelper::dmcCanAccessProForm($user),
            'canLiteForm' => CommonHelper::dmcCanAccessLiteForm($user),
            'dmcUserId' => $dmcUserId,
        ]);
    }

    public function ledger(Request $request, int $agent)
    {
        $user = Auth::user();

        if (! CommonHelper::canCreateTourOnBehalfOfAgent($user)) {
            abort(403, 'You do not have permission to access agent wallet and tour management.');
        }

        $agentModel = Agent::where('agent_id', $agent)->where('status', 1)->firstOrFail();
        $dmcUserId = CommonHelper::resolveDmcUserIdForStaff($user);

        if (! $dmcUserId || ! $this->walletQuery->agentBelongsToDmc($agentModel, $dmcUserId)) {
            abort(403, 'This agent does not belong to your DMC.');
        }

        $ledgerData = $this->walletQuery->buildLedgerViewData(
            $user,
            'agent',
            $agent,
            $request->input('currency'),
            $request->input('ledger_date_from'),
            $request->input('ledger_date_to'),
            (int) $request->input('ledger_per_page', WalletQueryService::LEDGER_PER_PAGE_DEFAULT),
            (int) $request->input('ledger_page', 1),
        );

        $ledgerQueryParams = array_merge($ledgerData['ledgerQueryParams'], ['agent' => $agent]);

        return response()->json([
            'html' => view('finance.partials.wallet-ledger-results', [
                'ledger' => $ledgerData['ledger'],
                'ledgerFilters' => $ledgerData['ledgerFilters'],
                'ledgerQueryParams' => $ledgerQueryParams,
                'showReferenceColumn' => false,
            ])->render(),
            'meta' => [
                'total' => $ledgerData['ledger']->total(),
                'current_page' => $ledgerData['ledger']->currentPage(),
                'last_page' => $ledgerData['ledger']->lastPage(),
                'per_page' => $ledgerData['ledger']->perPage(),
            ],
        ]);
    }

    public function transactionsGlance(Request $request, int $agent)
    {
        $user = Auth::user();

        if (! CommonHelper::canCreateTourOnBehalfOfAgent($user)) {
            abort(403, 'You do not have permission to access agent wallet and tour management.');
        }

        $agentModel = Agent::where('agent_id', $agent)->where('status', 1)->firstOrFail();
        $dmcUserId = CommonHelper::resolveDmcUserIdForStaff($user);

        if (! $dmcUserId || ! $this->walletQuery->agentBelongsToDmc($agentModel, $dmcUserId)) {
            abort(403, 'This agent does not belong to your DMC.');
        }

        $glanceData = $this->walletQuery->buildTransactionGlanceViewData(
            $user,
            'agent',
            $agent,
            $request->input('currency'),
            $request->input('glance_date_from'),
            $request->input('glance_date_to'),
            (int) $request->input('glance_per_page', WalletQueryService::GLANCE_PER_PAGE_DEFAULT),
            (int) $request->input('glance_page', 1),
        );

        $glanceQueryParams = array_merge($glanceData['glanceQueryParams'], ['agent' => $agent]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('finance.partials.wallet-transactions-glance-results', [
                    'transactions' => $glanceData['transactions'],
                    'glanceFilters' => $glanceData['glanceFilters'],
                    'glanceQueryParams' => $glanceQueryParams,
                    'currency' => $glanceData['currency'],
                ])->render(),
                'meta' => [
                    'total' => $glanceData['transactions']->total(),
                    'current_page' => $glanceData['transactions']->currentPage(),
                    'last_page' => $glanceData['transactions']->lastPage(),
                    'per_page' => $glanceData['transactions']->perPage(),
                ],
            ]);
        }

        $backUrl = route('finance.agent.dashboard', array_filter([
            'agent' => $agent,
            'currency' => $glanceData['currency'],
        ]));

        return view('finance.wallet.transactions', [
            'pageTitle' => 'Wallet Transactions — '.$agentModel->name,
            'transactions' => $glanceData['transactions'],
            'glanceFilters' => $glanceData['glanceFilters'],
            'currency' => $glanceData['currency'],
            'ownerDisplayName' => $agentModel->name,
            'glanceAjaxUrl' => route('finance.agent.transactions', $agent),
            'glancePersistParams' => [
                'currency' => $glanceData['currency'],
            ],
            'backUrl' => $backUrl,
            'backLabel' => 'Agent Wallet & Tour',
            'backButtonLabel' => 'Back to Dashboard',
            'ledgerUrl' => $backUrl.'#wallet-ledger-card',
        ]);
    }

    public function tours(Request $request, int $agent)
    {
        $user = Auth::user();

        if (! CommonHelper::canCreateTourOnBehalfOfAgent($user)) {
            abort(403, 'You do not have permission to access agent wallet and tour management.');
        }

        $agentModel = Agent::where('agent_id', $agent)->where('status', 1)->firstOrFail();
        $dmcUserId = CommonHelper::resolveDmcUserIdForStaff($user);

        if (! $dmcUserId || ! $this->walletQuery->agentBelongsToDmc($agentModel, $dmcUserId)) {
            abort(403, 'This agent does not belong to your DMC.');
        }

        $toursData = $this->agentTourQuery->buildToursViewData(
            $agent,
            $request->input('tours_date_from'),
            $request->input('tours_date_to'),
            (int) $request->input('tours_per_page', AgentTourQueryService::PER_PAGE_DEFAULT),
            (int) $request->input('tours_page', 1),
        );

        return response()->json([
            'html' => view('finance.partials.agent-tours-results', [
                'tours' => $toursData['tours'],
                'toursQueryParams' => $toursData['toursQueryParams'],
                'agentId' => $agent,
            ])->render(),
            'meta' => [
                'total' => $toursData['tours']->total(),
                'current_page' => $toursData['tours']->currentPage(),
                'last_page' => $toursData['tours']->lastPage(),
                'per_page' => $toursData['tours']->perPage(),
            ],
        ]);
    }

    public function allocateToAgent(Request $request, int $agent)
    {
        $user = Auth::user();

        if ((int) $user->role_id !== 11) {
            abort(403, 'Only DMC can allocate funds to agent wallets.');
        }

        $agentModel = Agent::where('agent_id', $agent)->where('status', 1)->firstOrFail();
        $dmcId = (int) $user->userId;

        if (! $this->walletQuery->agentBelongsToDmc($agentModel, $dmcId)) {
            abort(403, 'This agent does not belong to your DMC.');
        }

        $validated = $request->validate([
            'topup_amount' => 'required|numeric|min:0.01',
            'wallet_currency_code' => 'nullable|string|size:3',
            'ledger_description' => 'nullable|string|max:255',
        ]);

        try {
            $configured = $this->walletQuery->getConfiguredCurrenciesForOwner('agent', $agent);
            $allowedCurrencies = $configured['currencies'];
            $requestedCurrency = strtoupper((string) ($validated['wallet_currency_code'] ?? ''));

            if ($allowedCurrencies !== []) {
                if ($requestedCurrency === '' || ! in_array($requestedCurrency, $allowedCurrencies, true)) {
                    $requestedCurrency = (string) ($configured['default'] ?: $allowedCurrencies[0]);
                }
            } elseif ($requestedCurrency === '') {
                $requestedCurrency = 'SGD';
            }

            $ledgerContext = $this->walletQuery->buildAllocateLedgerContext(
                'dmc',
                $dmcId,
                'agent',
                $agent,
                $validated['ledger_description'] ?? null,
                $user->name ?? $user->email,
            );

            $this->walletService->allocate([
                'idempotency_key' => (string) Str::uuid(),
                'allocator_wallet_owner_type' => 'dmc',
                'allocator_wallet_owner_id' => $dmcId,
                'recipient_wallet_owner_type' => 'agent',
                'recipient_wallet_owner_id' => $agent,
                'allocation_amount' => (string) $validated['topup_amount'],
                'wallet_currency_code' => $requestedCurrency,
                'supplier_api_id' => null,
                'ledger_description' => $ledgerContext['ledger_description'],
                'debit_ledger_description' => $ledgerContext['debit_ledger_description'],
                'credit_ledger_description' => $ledgerContext['credit_ledger_description'],
                'metadata' => $ledgerContext['metadata'],
            ]);

            return redirect()
                ->route('finance.agent.dashboard', [
                    'agent' => $agent,
                    'currency' => $requestedCurrency,
                ])
                ->with('success', 'Wallet funds allocated to agent successfully via finance service.');
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function initializeTour(Request $request, int $agent)
    {
        $user = Auth::user();

        if (! CommonHelper::canCreateTourOnBehalfOfAgent($user)) {
            abort(403, 'You do not have permission to create tours on behalf of agents.');
        }

        $agentModel = Agent::where('agent_id', $agent)->where('status', 1)->firstOrFail();
        $dmcUserId = CommonHelper::resolveDmcUserIdForStaff($user);

        if (! $dmcUserId || ! $this->walletQuery->agentBelongsToDmc($agentModel, $dmcUserId)) {
            abort(403, 'This agent does not belong to your DMC.');
        }

        $agency = $agentModel->agency_id ? Agency::find($agentModel->agency_id) : null;
        $startDate = now()->addDays(7)->format('Y-m-d');
        $endDate = now()->addDays(10)->format('Y-m-d');
        $destination = $user->country ?? 'Singapore';

        $request->session()->put('tour_pro_initial_data', [
            'tour_type' => 'FIT',
            'tour_start_date' => $startDate,
            'tour_end_date' => $endDate,
            'adult_count' => 2,
            'child_count' => 0,
            'infant_count' => 0,
            'agency_id' => $agentModel->agency_id,
            'agent_id' => $agentModel->agent_id,
            'agency_name' => $agency->agency_name ?? '',
            'agent_name' => $agentModel->name ?? '',
            'salutation' => 'Mr',
            'customer_name' => 'Guest',
            'destination_display' => $destination,
            'destinations_array' => [$destination],
            'multiple_destination' => false,
        ]);

        return redirect()->route('enquiry-form-pro.create');
    }

    public function settleTourFinance(Request $request, int $agent, int $tourId)
    {
        $user = Auth::user();

        if (! CommonHelper::canCreateTourOnBehalfOfAgent($user)) {
            abort(403, 'You do not have permission to settle tour finance.');
        }

        $agentModel = Agent::where('agent_id', $agent)->where('status', 1)->firstOrFail();
        $dmcUserId = CommonHelper::resolveDmcUserIdForStaff($user);

        if (! $dmcUserId || ! $this->walletQuery->agentBelongsToDmc($agentModel, $dmcUserId)) {
            abort(403, 'This agent does not belong to your DMC.');
        }

        $tour = Tour::where('tour_id', $tourId)->where('agent_id', $agent)->firstOrFail();

        if (! in_array($tour->tour_status, ['Confirmed', 'Definite', 'Actual'], true)) {
            return redirect()
                ->route('finance.agent.dashboard', ['agent' => $agent])
                ->with('error', 'Wallet settlement is only available for confirmed tours.');
        }

        if ((string) ($tour->finance_status ?? '') === 'settled') {
            return redirect()
                ->route('finance.agent.dashboard', ['agent' => $agent])
                ->with('info', 'Tour #'.$tourId.' finance is already settled.');
        }

        $sellingPrice = (float) ($tour->selling_price ?? 0);
        if ($sellingPrice <= 0) {
            $sellingPrice = $this->calculateTourSellingPrice((int) $tour->tour_id);
        }

        if ($sellingPrice <= 0) {
            return redirect()
                ->route('finance.agent.dashboard', ['agent' => $agent])
                ->with('error', 'Cannot settle wallet: tour has no selling price. Add services with pricing first.');
        }

        try {
            $this->bookingFinance->processOnTourConfirmation($tour, $sellingPrice);

            return redirect()
                ->route('finance.agent.dashboard', ['agent' => $agent])
                ->with('success', 'Wallet settlement completed for tour #'.$tourId.'.');
        } catch (RuntimeException $e) {
            return redirect()
                ->route('finance.agent.dashboard', ['agent' => $agent])
                ->with('error', 'Wallet settlement failed: '.$e->getMessage());
        }
    }

    protected function calculateTourSellingPrice(int $tourId): float
    {
        $orders = \App\Models\Order::where('tour_id', $tourId)->get();
        $sum = 0.0;

        foreach ($orders as $order) {
            $payload = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (is_array($payload)) {
                foreach ($payload as $item) {
                    if (is_array($item)) {
                        foreach (['totalPrice', 'total_price', 'price', 'amount'] as $key) {
                            if (isset($item[$key]) && is_numeric($item[$key])) {
                                $sum += (float) $item[$key];
                                break;
                            }
                        }
                    }
                }
            }
        }

        return round($sum, 2);
    }
}
