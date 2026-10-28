<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Services\Payment\OrderClassificationService;
use App\Services\Payment\PaymentEligibilityService;
use App\Services\Payment\PaymentOrchestrator;
use App\Services\Payment\PaymentSettingsService;
use App\Services\Payment\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use RuntimeException;

class PaymentServiceController extends Controller
{
    public function __construct(
        protected PaymentEligibilityService $eligibilityService,
        protected PaymentOrchestrator $orchestrator,
        protected PaymentSettingsService $settingsService,
        protected OrderClassificationService $classificationService,
        protected StripeConnectService $stripeConnectService,
    ) {}

    public function eligibility(Request $request, int $tourId): JsonResponse
    {
        $tour = $this->findAuthorizedTour($tourId);
        $classification = $this->classificationService->persistClassification($tour);
        $tour->refresh();

        $requestedAmount = $request->query('amount');
        $amount = is_numeric($requestedAmount) ? (float) $requestedAmount : null;

        return response()->json([
            'success' => true,
            'data' => $this->eligibilityService->forTour($tour, $amount),
            'order_classification' => $classification,
        ]);
    }

    public function initiate(Request $request, int $tourId): JsonResponse
    {
        $tour = $this->findAuthorizedTour($tourId);
        $validated = $request->validate([
            'payment_method' => 'required|string|max:40',
            'amount' => 'nullable|numeric|min:0.01',
            'idempotency_key' => 'nullable|string|min:8|max:128',
            'remarks' => 'nullable|string|max:2000',
            'transaction_id' => 'nullable|string|max:128',
            'payment_date' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        try {
            $this->classificationService->persistClassification($tour);
            $tour->refresh();
            $result = $this->orchestrator->initiate($tour, $validated);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function completeWallet(Request $request, int $tourId): JsonResponse
    {
        $tour = $this->findAuthorizedTour($tourId);
        $validated = $request->validate([
            'attempt_uuid' => 'required|uuid',
            'supplier_results' => 'nullable|array',
        ]);

        try {
            $result = $this->orchestrator->completeWalletAfterSupplier(
                $tour,
                $validated['attempt_uuid'],
                $validated['supplier_results'] ?? []
            );

            return response()->json(['success' => true, 'data' => $result]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function completeStripe(Request $request, int $tourId): JsonResponse
    {
        $tour = $this->findAuthorizedTour($tourId);
        $validated = $request->validate([
            'attempt_uuid' => 'required|uuid',
            'supplier_results' => 'nullable|array',
        ]);

        try {
            $result = $this->orchestrator->completeStripeAfterSupplier(
                $tour,
                $validated['attempt_uuid'],
                $validated['supplier_results'] ?? []
            );

            return response()->json(['success' => true, 'data' => $result]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Travclicks admin UI — Offline / Wallet / Online switches.
     */
    public function settingsPage(): View
    {
        $this->authorizePlatformAdmin();

        return view('finance.payment-settings', [
            'settings' => $this->settingsService->get(),
        ]);
    }

    public function settingsSave(Request $request): RedirectResponse
    {
        $this->authorizePlatformAdmin();

        $request->validate([
            'wallet_enabled' => 'nullable',
            'online_enabled' => 'nullable',
        ]);

        try {
            $this->settingsService->update([
                'wallet_enabled' => $request->boolean('wallet_enabled'),
                'online_enabled' => $request->boolean('online_enabled'),
                // Offline is always forced on by PaymentSettingsService.
                // Stripe keys + mode come from .env only.
                'offline_enabled' => true,
            ], auth()->user()?->email ?? auth()->user()?->name);

            return redirect()
                ->route('payment-service.settings.page')
                ->with('success', 'Payment settings saved.');
        } catch (RuntimeException $e) {
            return redirect()
                ->route('payment-service.settings.page')
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function settingsShow(): JsonResponse
    {
        $this->authorizePlatformAdmin();

        return response()->json(['success' => true, 'data' => $this->settingsService->get()]);
    }

    public function settingsUpdate(Request $request): JsonResponse
    {
        $this->authorizePlatformAdmin();

        $validated = $request->validate([
            'wallet_enabled' => 'sometimes|boolean',
            'online_enabled' => 'sometimes|boolean',
            'offline_enabled' => 'sometimes|boolean',
        ]);

        try {
            // Stripe keys / STRIPE_MODE are env-only; ignore any stripe_* in payload.
            unset($validated['stripe_mode'], $validated['stripe_public_key'], $validated['stripe_secret_key']);

            $data = $this->settingsService->update(
                $validated,
                auth()->user()?->email ?? auth()->user()?->name
            );

            return response()->json(['success' => true, 'data' => $data]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * DMC portal page to connect / manage Stripe merchant (Connect) account.
     */
    public function stripeConnectPage(): View|RedirectResponse
    {
        $dmcId = $this->currentDmcId();
        if ($dmcId <= 0) {
            abort(403, 'Only DMC accounts can manage Stripe merchant settings.');
        }

        $account = $this->stripeConnectService->getAccountForDmc($dmcId);
        $ready = $this->stripeConnectService->isReadyForCharges($dmcId);

        return view('finance.dmc-stripe-connect', [
            'dmcId' => $dmcId,
            'account' => $account,
            'ready' => $ready,
            'settings' => $this->settingsService->get(),
            'countries' => $this->connectCountryOptions(),
            'travclicksLogo' => asset('assets/images/logo-dark.png'),
        ]);
    }

    /**
     * Start Stripe Connect Express onboarding and redirect to Stripe.
     */
    public function stripeConnectStart(Request $request): RedirectResponse
    {
        $dmcId = $this->currentDmcId();
        if ($dmcId <= 0) {
            return redirect()
                ->route('finance.stripe.connect.page')
                ->with('error', 'Only DMC accounts can manage Stripe merchant settings.');
        }

        $existing = $this->stripeConnectService->getAccountForDmc($dmcId);
        $countryInput = $request->input('country_code')
            ?: ($existing['country_code'] ?? null);

        $request->merge(['country_code' => $countryInput]);

        $validated = $request->validate([
            'country_code' => 'required|string|size:2',
        ]);

        try {
            $user = auth()->user();
            [$country, $currency] = $this->resolveConnectCountryCurrency($validated['country_code']);

            $result = $this->stripeConnectService->createOnboardingLink(
                $dmcId,
                $user?->email,
                $user?->name ?? $user?->company_name ?? 'Travclicks Merchant',
                $country,
                $currency
            );
            $url = $result['onboarding_url'] ?? null;
            if (! $url) {
                return redirect()
                    ->route('finance.stripe.connect.page')
                    ->with('error', 'Stripe did not return an onboarding URL.');
            }

            return redirect()->away($url);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('finance.stripe.connect.page')
                ->with('error', $this->friendlyStripeError($e))
                ->withInput();
        }
    }

    /**
     * Clear incomplete Connect link so DMC can pick a different business country.
     */
    public function stripeConnectResetCountry(Request $request): RedirectResponse
    {
        $dmcId = $this->currentDmcId();
        if ($dmcId <= 0) {
            return redirect()
                ->route('finance.stripe.connect.page')
                ->with('error', 'Only DMC accounts can manage Stripe merchant settings.');
        }

        try {
            $this->stripeConnectService->resetForCountryChange($dmcId);

            return redirect()
                ->route('finance.stripe.connect.page')
                ->with('success', 'Country unlocked. Select your real business country, then connect again.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('finance.stripe.connect.page')
                ->with('error', $this->friendlyStripeError($e));
        }
    }

    public function stripeConnectStatus(): JsonResponse
    {
        $dmcId = $this->currentDmcId();
        if ($dmcId <= 0) {
            return response()->json(['success' => false, 'message' => 'DMC context required.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->stripeConnectService->getAccountForDmc($dmcId),
            'ready' => $this->stripeConnectService->isReadyForCharges($dmcId),
        ]);
    }

    public function stripeConnectOnboard(Request $request): JsonResponse|RedirectResponse
    {
        $dmcId = $this->currentDmcId();
        if ($dmcId <= 0) {
            return response()->json(['success' => false, 'message' => 'DMC context required.'], 403);
        }

        $validated = $request->validate([
            'country_code' => 'required|string|size:2',
        ]);

        try {
            $user = auth()->user();
            [$country, $currency] = $this->resolveConnectCountryCurrency($validated['country_code']);

            $result = $this->stripeConnectService->createOnboardingLink(
                $dmcId,
                $user?->email,
                $user?->name ?? $user?->company_name ?? 'Travclicks Merchant',
                $country,
                $currency
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'data' => $result]);
            }

            $url = $result['onboarding_url'] ?? null;
            if ($url) {
                return redirect()->away($url);
            }

            return redirect()->route('finance.stripe.connect.page')
                ->with('error', 'Stripe did not return an onboarding URL.');
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $this->friendlyStripeError($e)], 422);
            }

            return redirect()->route('finance.stripe.connect.page')
                ->with('error', $this->friendlyStripeError($e))
                ->withInput();
        }
    }

    public function stripeConnectReturn(): View|RedirectResponse|JsonResponse
    {
        $dmcId = $this->currentDmcId();
        if ($dmcId <= 0) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'DMC context required.'], 403);
            }
            abort(403, 'DMC context required.');
        }

        try {
            $account = $this->stripeConnectService->syncFromStripe($dmcId);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'data' => $account]);
            }

            $ready = $this->stripeConnectService->isReadyForCharges($dmcId);

            return redirect()
                ->route('finance.stripe.connect.page')
                ->with(
                    $ready ? 'success' : 'warning',
                    $ready
                        ? 'Stripe merchant account connected and ready to accept card payments.'
                        : 'Stripe onboarding saved. Complete any outstanding requirements before charges are enabled.'
                );
        } catch (\Throwable $e) {
            report($e);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $this->friendlyStripeError($e)], 422);
            }

            return redirect()
                ->route('finance.stripe.connect.page')
                ->with('error', $this->friendlyStripeError($e));
        }
    }

    public function stripeWebhook(Request $request): JsonResponse
    {
        try {
            $result = $this->stripeConnectService->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Invalid webhook'], 400);
        }
    }

    /**
     * Gate used by confirm flow: whether supplier_initial payment is satisfied.
     */
    public function supplierInitialSatisfied(int $tourId): bool
    {
        if (! Schema::hasTable('payment_attempts')) {
            return false;
        }

        return DB::table('payment_attempts')
            ->where('tour_id', $tourId)
            ->where('payment_stage', 'supplier_initial')
            ->whereIn('status', ['completed', 'authorized'])
            ->exists();
    }

    protected function findAuthorizedTour(int $tourId): Tour
    {
        $tour = Tour::where('tour_id', $tourId)->firstOrFail();

        // Lightweight access check: admin / owning DMC / owning agent
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        return $tour;
    }

    protected function authorizePlatformAdmin(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        // Travclicks platform admin is role_id = 1 (same as sidebar $sidebarIsAdmin).
        $roleId = (int) ($user->role_id ?? 0);
        $role = strtolower((string) ($user->role ?? $user->user_type ?? ''));
        $allowed = $roleId === 1
            || str_contains($role, 'admin')
            || str_contains($role, 'travclicks')
            || str_contains($role, 'owner')
            || (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('Super Admin')));

        if (! $allowed) {
            abort(403, 'Unauthorized to modify payment settings.');
        }
    }

    protected function currentDmcId(): int
    {
        $user = auth()->user();
        if (! $user) {
            return 0;
        }

        $roleId = (int) ($user->role_id ?? 0);
        // Platform admin uses Payment Settings; agents cannot own a DMC Connect account.
        if (in_array($roleId, [1, 20], true)) {
            return 0;
        }

        if (class_exists(\App\Helpers\CommonHelper::class)
            && method_exists(\App\Helpers\CommonHelper::class, 'getDmcId')
        ) {
            $resolved = (int) \App\Helpers\CommonHelper::getDmcId($user);
            if ($resolved > 0) {
                return $resolved;
            }
        }

        if ($roleId === 11) {
            return (int) ($user->userId ?? 0);
        }

        return (int) ($user->dmc_id ?? 0);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object{code: string, name: string, currency: string}>
     */
    protected function connectCountryOptions()
    {
        if (! class_exists(\App\Models\Country::class)) {
            return collect();
        }

        return \App\Models\Country::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['name', 'short_code', 'currency'])
            ->map(function ($row) {
                $code = \App\Models\Country::resolveShortCode($row->name, $row->short_code);
                if (! $code || trim((string) $row->name) === '') {
                    return null;
                }

                return (object) [
                    'code' => $code,
                    'name' => (string) $row->name,
                    'currency' => strtoupper((string) ($row->currency ?: '')),
                ];
            })
            ->filter()
            ->unique('code')
            ->values();
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    protected function resolveConnectCountryCurrency(?string $countryCode): array
    {
        $code = strtolower(trim((string) $countryCode));
        if ($code === '' || strlen($code) !== 2) {
            return [null, null];
        }

        $currency = null;
        if (class_exists(\App\Models\Country::class)) {
            $row = \App\Models\Country::query()
                ->whereRaw('LOWER(short_code) = ?', [$code])
                ->first(['currency']);
            $currency = $row?->currency ? strtolower((string) $row->currency) : null;
        }

        return [$code, $currency];
    }

    protected function friendlyStripeError(\Throwable $e): string
    {
        $message = trim($e->getMessage());

        if ($message === '') {
            return 'Unable to connect to Stripe. Check STRIPE_SECRET_KEY in .env and try again.';
        }

        if (str_contains($message, 'Signed request has invalid signature')
            || str_contains($message, 'Invalid API Key')
        ) {
            return 'Stripe API key is invalid. Update STRIPE_SECRET_KEY in .env.';
        }

        if (str_contains($message, 'You can only create new accounts if you\'ve signed up for Connect')
            || str_contains($message, 'signed up for Connect')
            || str_contains($message, 'accounts_v2_access_blocked')
            || str_contains($message, 'platform_registration_required')
            || str_contains($message, 'connect_profile_not_submitted')
        ) {
            return 'Stripe Connect is not fully enabled. In Stripe Dashboard → Connect, complete platform setup / profile, then try again.';
        }

        // Keep message readable but avoid dumping huge Stripe JSON.
        return strlen($message) > 280 ? substr($message, 0, 277).'...' : $message;
    }
}
