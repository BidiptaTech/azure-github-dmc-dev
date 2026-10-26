<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform payment method defaults (overridden by payment_platform_settings)
    |--------------------------------------------------------------------------
    */
    'offline_always_enabled' => true,

    'wallet_enabled_default' => (bool) env('PAYMENT_WALLET_ENABLED', true),

    'online_enabled_default' => (bool) env('PAYMENT_ONLINE_ENABLED', false),

    /*
    | When true, agent wallets with source_dmc_id NULL may still be used for
    | DMC orders (legacy compatibility). Prefer false after re-allocation.
    */
    'wallet_legacy_unscoped_agent' => (bool) env('PAYMENT_WALLET_LEGACY_UNSCOPED_AGENT', true),

    /*
    | Percent added on top of a converted amount when payment currency differs
    | from the order currency. 0 uses the destination country gateway_percentage.
    */
    'fx_conversion_charge_percent' => env('PAYMENT_FX_CONVERSION_CHARGE_PERCENT'),

    'stripe' => [
        'mode' => env('STRIPE_MODE', 'test'), // test|live
        'publishable_key' => env('STRIPE_PUBLIC_KEY', ''),
        'secret_key' => env('STRIPE_SECRET_KEY', ''),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        'connect_webhook_secret' => env('STRIPE_CONNECT_WEBHOOK_SECRET', env('STRIPE_WEBHOOK_SECRET', '')),
        'connect_refresh_url' => env('STRIPE_CONNECT_REFRESH_URL', env('APP_URL').'/finance/stripe/connect/refresh'),
        'connect_return_url' => env('STRIPE_CONNECT_RETURN_URL', env('APP_URL').'/finance/stripe/connect/return'),
        // Optional default only when DMC does not pick a country (UI requires selection).
        'connect_default_country' => strtolower((string) env('STRIPE_CONNECT_DEFAULT_COUNTRY', '')),
        // Travclicks branding on connected merchant accounts / Checkout.
        'brand_logo_path' => env('STRIPE_BRAND_LOGO_PATH', public_path('assets/images/logo-dark.png')),
        'brand_primary_color' => env('STRIPE_BRAND_PRIMARY_COLOR') ?: '#0B5FFF',
        // Destination charges for DMC; platform charges for Travclicks supplier_initial.
        'charge_model' => 'destination',
        // Used when travclicks_markup is not supplied on a destination charge.
        'application_fee_percent' => (float) env('STRIPE_APPLICATION_FEE_PERCENT', 0),
        'currency_fallback' => env('STRIPE_CURRENCY_FALLBACK', 'usd'),
    ],

    'stages' => [
        'none' => 'none',
        'supplier_initial' => 'supplier_initial',
        'dmc_collection' => 'dmc_collection',
    ],

    'recipients' => [
        'travclicks' => 'travclicks',
        'dmc' => 'dmc',
    ],

];
