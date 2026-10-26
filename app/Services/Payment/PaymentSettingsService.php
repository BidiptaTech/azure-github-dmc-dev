<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Platform payment method settings. Offline cannot be disabled.
 */
class PaymentSettingsService
{
    public function get(): array
    {
        $row = $this->fetchRow();
        $stripeMode = $this->envStripeMode();

        return [
            'offline_enabled' => true, // always on — never trust stored false
            'wallet_enabled' => (bool) ($row['wallet_enabled'] ?? config('payment.wallet_enabled_default', true)),
            'online_enabled' => (bool) ($row['online_enabled'] ?? config('payment.online_enabled_default', false)),
            // Keys + mode always from .env / config — never editable in UI.
            'stripe_mode' => $stripeMode,
            'stripe_configured' => $this->isStripeConfigured($stripeMode),
            'stripe_publishable_key_masked' => $this->maskSecret((string) config('payment.stripe.publishable_key', '')),
            'stripe_secret_key_masked' => $this->maskSecret((string) config('payment.stripe.secret_key', '')),
            'updated_by' => $row['updated_by'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    /**
     * @param  array{wallet_enabled?: bool, online_enabled?: bool}  $input
     */
    public function update(array $input, ?string $updatedBy = null): array
    {
        if (array_key_exists('offline_enabled', $input) && ! $input['offline_enabled']) {
            throw new RuntimeException('Offline payments cannot be disabled.');
        }

        if (! Schema::hasTable('payment_platform_settings')) {
            throw new RuntimeException('payment_platform_settings table is missing. Run migrations.');
        }

        $stripeMode = $this->envStripeMode();

        $current = $this->fetchRow() ?? [
            'wallet_enabled' => config('payment.wallet_enabled_default', true),
            'online_enabled' => config('payment.online_enabled_default', false),
            'stripe_mode' => $stripeMode,
            'audit_log' => [],
        ];

        $walletEnabled = array_key_exists('wallet_enabled', $input)
            ? (bool) $input['wallet_enabled']
            : (bool) $current['wallet_enabled'];

        $onlineEnabled = array_key_exists('online_enabled', $input)
            ? (bool) $input['online_enabled']
            : (bool) $current['online_enabled'];

        // Ignore any stripe_mode / keys from request — credentials live in .env only.
        if ($onlineEnabled && ! $this->isStripeConfigured($stripeMode)) {
            throw new RuntimeException('Cannot enable Online payments: set STRIPE_PUBLIC_KEY and STRIPE_SECRET_KEY in .env for STRIPE_MODE='.$stripeMode.'.');
        }

        $audit = is_string($current['audit_log'] ?? null)
            ? (json_decode($current['audit_log'], true) ?: [])
            : (array) ($current['audit_log'] ?? []);

        $audit[] = [
            'at' => now()->toIso8601String(),
            'by' => $updatedBy,
            'from' => [
                'wallet_enabled' => (bool) $current['wallet_enabled'],
                'online_enabled' => (bool) $current['online_enabled'],
                'stripe_mode' => $current['stripe_mode'] ?? $stripeMode,
            ],
            'to' => [
                'wallet_enabled' => $walletEnabled,
                'online_enabled' => $onlineEnabled,
                'stripe_mode' => $stripeMode,
            ],
        ];

        if (! empty($current['id'])) {
            DB::table('payment_platform_settings')->where('id', $current['id'])->update([
                'offline_enabled' => true,
                'wallet_enabled' => $walletEnabled,
                'online_enabled' => $onlineEnabled,
                'stripe_mode' => $stripeMode,
                'updated_by' => $updatedBy,
                'audit_log' => json_encode($audit),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('payment_platform_settings')->insert([
                'offline_enabled' => true,
                'wallet_enabled' => $walletEnabled,
                'online_enabled' => $onlineEnabled,
                'stripe_mode' => $stripeMode,
                'updated_by' => $updatedBy,
                'audit_log' => json_encode($audit),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $this->get();
    }

    public function assertMethodAllowed(string $method): void
    {
        $settings = $this->get();
        $method = strtolower($method);

        if (in_array($method, ['cash', 'cheque', 'bank_transfer', 'online', 'card', 'offline_office'], true)) {
            return; // offline always allowed
        }

        if ($method === 'wallet' && ! $settings['wallet_enabled']) {
            throw new RuntimeException('Wallet payments are disabled by platform settings.');
        }

        if (in_array($method, ['stripe', 'online_gateway', 'card_online'], true) && ! $settings['online_enabled']) {
            throw new RuntimeException('Online card payments are disabled by platform settings.');
        }
    }

    public function isStripeConfigured(?string $mode = null): bool
    {
        $mode = $mode ?: $this->envStripeMode();
        $secret = (string) config('payment.stripe.secret_key', '');
        $publishable = (string) config('payment.stripe.publishable_key', '');

        if ($secret === '' || $publishable === '') {
            return false;
        }

        $secretLooksTest = str_starts_with($secret, 'sk_test_');
        $secretLooksLive = str_starts_with($secret, 'sk_live_');
        $pubLooksTest = str_starts_with($publishable, 'pk_test_');
        $pubLooksLive = str_starts_with($publishable, 'pk_live_');

        if ($mode === 'test' && ($secretLooksLive || $pubLooksLive)) {
            return false;
        }
        if ($mode === 'live' && ($secretLooksTest || $pubLooksTest)) {
            return false;
        }

        return true;
    }

    protected function envStripeMode(): string
    {
        $mode = strtolower((string) config('payment.stripe.mode', 'test'));

        return in_array($mode, ['test', 'live'], true) ? $mode : 'test';
    }

    protected function maskSecret(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '(not set)';
        }

        $len = strlen($value);
        if ($len <= 12) {
            return str_repeat('•', $len);
        }

        return substr($value, 0, 7).str_repeat('•', max(4, $len - 11)).substr($value, -4);
    }

    protected function fetchRow(): ?array
    {
        if (! Schema::hasTable('payment_platform_settings')) {
            return null;
        }

        $row = DB::table('payment_platform_settings')->orderBy('id')->first();

        return $row ? (array) $row : null;
    }
}
