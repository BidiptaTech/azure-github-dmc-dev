<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Throwable;

/**
 * DMC Stripe Connect onboarding via Accounts v2 + hosted Account Links.
 */
class StripeConnectService
{
    protected ?StripeClient $client = null;

    public function __construct(
        protected PaymentSettingsService $settingsService,
    ) {}

    public function getAccountForDmc(int $dmcId): ?array
    {
        if (! Schema::hasTable('dmc_stripe_accounts')) {
            return null;
        }

        $row = DB::table('dmc_stripe_accounts')
            ->where('dmc_id', $dmcId)
            ->whereNull('deleted_at')
            ->first();

        return $row ? (array) $row : null;
    }

    public function isReadyForCharges(int $dmcId): bool
    {
        $account = $this->getAccountForDmc($dmcId);
        if (! $account || empty($account['stripe_account_id'])) {
            return false;
        }

        $settings = $this->settingsService->get();
        if (($account['stripe_mode'] ?? '') !== ($settings['stripe_mode'] ?? 'test')) {
            return false;
        }

        return (bool) ($account['charges_enabled'] ?? false);
    }

    /**
     * Start or resume Connect hosted onboarding for a DMC.
     *
     * @return array{account: array, onboarding_url: string}
     */
    public function createOnboardingLink(
        int $dmcId,
        ?string $email = null,
        ?string $displayName = null,
        ?string $countryCode = null,
        ?string $currency = null,
    ): array {
        $client = $this->stripe();

        if (! Schema::hasTable('dmc_stripe_accounts')) {
            throw new RuntimeException('Stripe merchant table is missing. Run migrations first.');
        }

        $existing = $this->getAccountForDmc($dmcId);
        $accountId = $existing['stripe_account_id'] ?? null;
        $activeMode = (string) ($this->settingsService->get()['stripe_mode'] ?? 'test');
        $requestedCountry = $this->normalizeCountry($countryCode);
        $existingCountry = $this->normalizeCountry($existing['country_code'] ?? null);
        $chargesEnabled = (bool) ($existing['charges_enabled'] ?? false);

        if ($accountId && ($existing['stripe_mode'] ?? '') !== '' && ($existing['stripe_mode'] ?? '') !== $activeMode) {
            $this->detachLocalStripeAccount($dmcId);
            $existing = $this->getAccountForDmc($dmcId);
            $accountId = null;
        }

        // Country is changeable until charges are live. Stripe cannot move an account
        // between countries, so we detach the old Connect account and create a new one.
        if ($accountId
            && $requestedCountry !== ''
            && $existingCountry !== ''
            && $requestedCountry !== $existingCountry
        ) {
            if ($chargesEnabled) {
                throw new RuntimeException(
                    'Business country cannot be changed after charges are enabled. Contact Travclicks support to reconnect under a new country.'
                );
            }

            $this->detachLocalStripeAccount($dmcId);
            $existing = $this->getAccountForDmc($dmcId);
            $accountId = null;
        }

        if (! $accountId) {
            $country = $requestedCountry !== ''
                ? $requestedCountry
                : $this->normalizeCountry($existing['country_code'] ?? null);
            $currencyCode = $this->normalizeCurrency(
                $currency ?: ($existing['default_currency'] ?? null),
                $country
            );

            if ($country === '') {
                throw new RuntimeException('Select your business country before connecting Stripe.');
            }

            $account = $this->createConnectedAccount($dmcId, $email, $displayName, $country, $currencyCode);
            $accountId = (string) $account->id;
            $this->upsertLocal($dmcId, [
                'stripe_account_id' => $accountId,
                'country_code' => $country,
                'default_currency' => $currencyCode,
                'onboarding_status' => 'pending',
                'charges_enabled' => false,
                'payouts_enabled' => false,
                'details_submitted' => false,
                'stripe_mode' => $activeMode,
            ]);
        }

        $link = $client->v2->core->accountLinks->create([
            'account' => $accountId,
            'use_case' => [
                'type' => 'account_onboarding',
                'account_onboarding' => [
                    'configurations' => ['merchant'],
                    'collection_options' => [
                        'fields' => 'eventually_due',
                    ],
                    'refresh_url' => $this->connectUrl('finance.stripe.connect.refresh'),
                    'return_url' => $this->connectUrl('finance.stripe.connect.return'),
                ],
            ],
        ]);

        $url = (string) ($link->url ?? '');
        if ($url === '') {
            throw new RuntimeException('Stripe did not return an onboarding URL.');
        }

        return [
            'account' => $this->getAccountForDmc($dmcId) ?? [],
            'onboarding_url' => $url,
        ];
    }

    public function syncFromStripe(int $dmcId): array
    {
        $local = $this->getAccountForDmc($dmcId);
        if (! $local || empty($local['stripe_account_id'])) {
            throw new RuntimeException('No Stripe account linked for this merchant.');
        }

        $account = $this->stripe()->v2->core->accounts->retrieve(
            (string) $local['stripe_account_id'],
            [
                'include' => [
                    'configuration.merchant',
                    'identity',
                    'requirements',
                ],
            ]
        );

        $chargesEnabled = $this->capabilityActive($account, 'card_payments');
        $payoutsEnabled = $this->payoutCapabilityActive($account);
        $detailsSubmitted = $this->hasSubmittedDetails($account);

        return $this->upsertLocal($dmcId, [
            'stripe_account_id' => (string) $account->id,
            'charges_enabled' => $chargesEnabled,
            'payouts_enabled' => $payoutsEnabled,
            'details_submitted' => $detailsSubmitted,
            'requirements_json' => json_encode($this->toArray($account->requirements ?? null)),
            'capabilities_json' => json_encode($this->toArray($account->configuration->merchant->capabilities ?? null)),
            'onboarding_status' => $chargesEnabled ? 'complete' : 'pending',
            'last_synced_at' => now(),
            'stripe_mode' => $this->settingsService->get()['stripe_mode'],
        ]);
    }

    /**
     * Handle Connect-related webhook events (account.updated, etc.).
     */
    public function handleWebhook(string $payload, ?string $signature): array
    {
        $secret = (string) config('payment.stripe.connect_webhook_secret', '');
        if ($secret === '') {
            throw new RuntimeException('Stripe Connect webhook secret not configured.');
        }

        $event = Webhook::constructEvent($payload, (string) $signature, $secret);
        $type = $event->type;
        $object = $event->data->object;

        if (in_array($type, ['account.updated', 'v2.core.account[requirements].updated'], true)
            && isset($object->id)
        ) {
            $dmcId = (int) ($object->metadata['dmc_id'] ?? 0);
            if ($dmcId <= 0 && Schema::hasTable('dmc_stripe_accounts')) {
                $row = DB::table('dmc_stripe_accounts')
                    ->where('stripe_account_id', $object->id)
                    ->whereNull('deleted_at')
                    ->first();
                $dmcId = (int) ($row->dmc_id ?? 0);
            }
            if ($dmcId > 0) {
                try {
                    $this->syncFromStripe($dmcId);
                } catch (Throwable $e) {
                    Log::warning('stripe_connect_webhook_sync_failed', [
                        'dmc_id' => $dmcId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('stripe_connect_webhook', ['type' => $type, 'id' => $event->id]);

        return ['handled' => true, 'type' => $type];
    }

    protected function createConnectedAccount(
        int $dmcId,
        ?string $email,
        ?string $displayName,
        string $country,
        string $currency,
    ): object {
        $name = trim((string) ($displayName ?: 'DMC Merchant'));
        $merchantConfig = [
            'capabilities' => [
                'card_payments' => ['requested' => true],
            ],
        ];

        $branding = [];
        $logoFileId = $this->travclicksBrandFileId('business_logo');
        $iconFileId = $this->travclicksBrandFileId('business_icon');
        if ($logoFileId) {
            $branding['logo'] = $logoFileId;
        }
        if ($iconFileId) {
            $branding['icon'] = $iconFileId;
        }
        $primaryColor = $this->normalizeHexColor(
            (string) config('payment.stripe.brand_primary_color', '#0B5FFF')
        );
        if ($primaryColor !== null) {
            $branding['primary_color'] = $primaryColor;
        }
        if ($branding !== []) {
            $merchantConfig['branding'] = $branding;
        }

        $payload = [
            'display_name' => $name !== '' ? $name : 'Travclicks Merchant',
            'dashboard' => 'express',
            'identity' => [
                'country' => $country,
            ],
            'defaults' => [
                'currency' => $currency,
                'responsibilities' => [
                    'fees_collector' => 'application',
                    'losses_collector' => 'application',
                ],
            ],
            'configuration' => [
                'merchant' => $merchantConfig,
            ],
            'include' => [
                'configuration.merchant',
                'identity',
                'defaults',
                'requirements',
            ],
            'metadata' => [
                'dmc_id' => (string) $dmcId,
                'platform' => 'travclicks',
                'country' => $country,
                'currency' => $currency,
            ],
        ];

        if ($email) {
            $payload['contact_email'] = $email;
        }

        return $this->stripe()->v2->core->accounts->create($payload);
    }

    /**
     * Upload Travclicks logo/icon with the Stripe purpose Stripe expects.
     * logo → business_logo, icon → business_icon (cannot reuse one file for both).
     */
    protected function travclicksBrandFileId(string $purpose): ?string
    {
        $purpose = in_array($purpose, ['business_logo', 'business_icon'], true)
            ? $purpose
            : 'business_logo';

        $cacheKey = 'stripe_travclicks_'.$purpose.'_file_id';
        $cached = cache()->get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $path = (string) config('payment.stripe.brand_logo_path', public_path('assets/images/logo-dark.png'));
        if ($path === '' || ! is_file($path)) {
            $fallback = public_path('build/images/logo1.png');
            $path = is_file($fallback) ? $fallback : '';
        }
        if ($path === '') {
            return null;
        }

        try {
            $fp = fopen($path, 'r');
            if ($fp === false) {
                return null;
            }

            $file = $this->stripe()->files->create([
                'purpose' => $purpose,
                'file' => $fp,
            ]);
            if (is_resource($fp)) {
                fclose($fp);
            }

            $id = (string) ($file->id ?? '');
            if ($id !== '') {
                cache()->put($cacheKey, $id, now()->addDays(30));
            }

            return $id !== '' ? $id : null;
        } catch (Throwable $e) {
            Log::warning('stripe_travclicks_brand_upload_failed', [
                'purpose' => $purpose,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Clear the local Stripe account link so a new country/currency can be chosen.
     * Does not force USA — next connect uses the country the DMC selects.
     */
    public function resetForCountryChange(int $dmcId): void
    {
        $existing = $this->getAccountForDmc($dmcId);
        if (! $existing) {
            return;
        }

        if (! empty($existing['charges_enabled'])) {
            throw new RuntimeException(
                'Cannot change country after charges are enabled. Contact Travclicks support.'
            );
        }

        $this->detachLocalStripeAccount($dmcId);
    }

    protected function detachLocalStripeAccount(int $dmcId): void
    {
        $existing = $this->getAccountForDmc($dmcId);
        if (! $existing) {
            return;
        }

        $oldAccountId = $existing['stripe_account_id'] ?? null;
        if ($oldAccountId) {
            try {
                $this->stripe()->v2->core->accounts->close((string) $oldAccountId);
            } catch (Throwable $e) {
                Log::info('stripe_connect_account_close_skipped', [
                    'dmc_id' => $dmcId,
                    'account' => $oldAccountId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        DB::table('dmc_stripe_accounts')->where('id', $existing['id'])->update([
            'stripe_account_id' => null,
            'country_code' => null,
            'default_currency' => null,
            'onboarding_status' => 'not_started',
            'charges_enabled' => false,
            'payouts_enabled' => false,
            'details_submitted' => false,
            'requirements_json' => null,
            'capabilities_json' => null,
            'last_synced_at' => null,
            'updated_at' => now(),
        ]);
    }

    protected function normalizeHexColor(?string $color): ?string
    {
        $color = trim((string) $color);
        if ($color === '') {
            return '#0B5FFF';
        }
        if ($color[0] !== '#') {
            $color = '#'.$color;
        }
        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return '#0B5FFF';
        }

        return strtoupper($color);
    }

    protected function normalizeCountry(?string $country): string
    {
        $country = strtolower(trim((string) $country));
        if ($country === '' || ! preg_match('/^[a-z]{2}$/', $country)) {
            return '';
        }

        return $country;
    }

    protected function normalizeCurrency(?string $currency, string $country): string
    {
        $currency = strtolower(trim((string) $currency));
        if ($currency !== '' && preg_match('/^[a-z]{3}$/', $currency)) {
            return $currency;
        }

        // Common fallbacks by country when currency missing from local catalog.
        $map = [
            'us' => 'usd', 'gb' => 'gbp', 'in' => 'inr', 'ae' => 'aed', 'au' => 'aud',
            'ca' => 'cad', 'sg' => 'sgd', 'eu' => 'eur', 'de' => 'eur', 'fr' => 'eur',
            'it' => 'eur', 'es' => 'eur', 'nl' => 'eur', 'ie' => 'eur', 'jp' => 'jpy',
            'nz' => 'nzd', 'ch' => 'chf', 'hk' => 'hkd', 'th' => 'thb', 'my' => 'myr',
            'id' => 'idr', 'ph' => 'php', 'za' => 'zar', 'sa' => 'sar', 'qa' => 'qar',
            'kw' => 'kwd', 'bh' => 'bhd', 'om' => 'omr', 'lk' => 'lkr', 'np' => 'npr',
        ];

        return $map[$country] ?? strtolower((string) config('payment.stripe.currency_fallback', 'usd'));
    }

    protected function stripe(): StripeClient
    {
        if ($this->client instanceof StripeClient) {
            return $this->client;
        }

        $secret = (string) config('payment.stripe.secret_key', '');
        if ($secret === '') {
            throw new RuntimeException('Stripe secret key not configured. Set STRIPE_SECRET_KEY in .env.');
        }

        $this->client = new StripeClient([
            'api_key' => $secret,
        ]);

        return $this->client;
    }

    protected function connectUrl(string $routeName): string
    {
        $configured = match ($routeName) {
            'finance.stripe.connect.refresh' => (string) config('payment.stripe.connect_refresh_url', ''),
            'finance.stripe.connect.return' => (string) config('payment.stripe.connect_return_url', ''),
            default => '',
        };

        if ($configured !== ''
            && ! str_contains($configured, 'localhost')
            && filter_var($configured, FILTER_VALIDATE_URL)
        ) {
            return $configured;
        }

        return route($routeName);
    }

    protected function capabilityActive(object $account, string $capability): bool
    {
        $caps = $account->configuration->merchant->capabilities ?? null;
        if (! $caps) {
            return false;
        }

        $status = $caps->{$capability}->status ?? null;

        return $status === 'active';
    }

    protected function payoutCapabilityActive(object $account): bool
    {
        $caps = $account->configuration->merchant->capabilities ?? null;
        if (! $caps) {
            return false;
        }

        $status = $caps->stripe_balance->payouts->status
            ?? $caps->stripe_balance->status
            ?? null;

        return $status === 'active';
    }

    protected function hasSubmittedDetails(object $account): bool
    {
        $requirements = $account->requirements ?? null;
        if (! $requirements) {
            return (bool) ($account->configuration->merchant->applied ?? false);
        }

        $entries = $this->toArray($requirements->entries ?? []);

        return $entries === [];
    }

    protected function toArray(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        return (array) $value;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function upsertLocal(int $dmcId, array $data): array
    {
        if (! Schema::hasTable('dmc_stripe_accounts')) {
            throw new RuntimeException('dmc_stripe_accounts table missing. Run migrations.');
        }

        $existing = $this->getAccountForDmc($dmcId);
        $payload = array_merge($data, ['updated_at' => now()]);

        if ($existing) {
            DB::table('dmc_stripe_accounts')->where('id', $existing['id'])->update($payload);
        } else {
            $payload['dmc_id'] = $dmcId;
            $payload['created_at'] = now();
            DB::table('dmc_stripe_accounts')->insert($payload);
        }

        return $this->getAccountForDmc($dmcId) ?? [];
    }
}
