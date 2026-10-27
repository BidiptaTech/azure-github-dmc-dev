<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Finance engine (Laravel-native)
    |--------------------------------------------------------------------------
    |
    | Former Azure Functions Python microservice has been retired.
    | All wallet / settlement / refund operations run in App\Services\Finance.
    |
    */

    'engine' => 'laravel',

    'timeout_seconds' => (int) env('FINANCE_HTTP_TIMEOUT', 30),

    'default_payment_mode_on_confirm' => env('FINANCE_DEFAULT_PAYMENT_MODE_ON_CONFIRM', 'wallet'),

    'retry' => [
        'max_attempts' => (int) env('FINANCE_RETRY_MAX_ATTEMPTS', 3),
        'backoff_seconds' => [10, 60, 300],
    ],

    'endpoints' => [
        'wallet_topup' => '/wallet/topup',
        'wallet_allocate' => '/wallet/allocate',
        'wallet_booking' => '/wallet/booking',
        'wallet_booking_distribute_proceeds' => '/wallet/booking/distribute-proceeds',
        'wallet_reserve' => '/wallet/reserve',
        'wallet_commit' => '/wallet/commit',
        'wallet_release' => '/wallet/release',
        'online_settlement' => '/settlement/online',
        'offline_settlement' => '/settlement/offline',
        'payment_callback' => '/payment/callback',
        'refund' => '/refund',
        'commission_calculate' => '/commission/calculate',
        'health' => '/health',
    ],

    'payment_gateways' => [
        'default' => 'stripe',
        'stripe' => [
            'enabled' => (bool) env('PAYMENT_ONLINE_ENABLED', false),
            'mode' => env('STRIPE_MODE', 'test'),
        ],
    ],

    // Legacy keys kept empty so old env vars do not accidentally re-enable HTTP client.
    'base_url' => '',
    'bearer_token' => '',
    'webhook_secret' => env('FINANCE_WEBHOOK_SECRET', ''),

];
