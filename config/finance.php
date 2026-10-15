<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Finance Microservice (Azure Functions)
    |--------------------------------------------------------------------------
    |
    | Laravel delegates all financial operations to the Python finance
    | microservice. This keeps payment/settlement logic isolated and
    | independently scalable per microservice architecture.
    |
    */

    'base_url' => env('FINANCE_SERVICE_BASE_URL', 'http://localhost:7071/api'),

    'bearer_token' => env('FINANCE_API_BEARER_TOKEN', ''),

    'webhook_secret' => env('FINANCE_WEBHOOK_SECRET', ''),

    'timeout_seconds' => (int) env('FINANCE_HTTP_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Web booking confirmation defaults
    |--------------------------------------------------------------------------
    |
    | When a tour is confirmed via the web UI (BookingsController), finance is
    | triggered automatically using this payment mode. Agent tours debit the
    | agent wallet; DMC-only tours debit the DMC wallet.
    |
    */
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
        'online_settlement' => '/settlement/online',
        'offline_settlement' => '/settlement/offline',
        'payment_callback' => '/payment/callback',
        'refund' => '/refund',
        'commission_calculate' => '/commission/calculate',
        'health' => '/health',
        'webhook_verify' => '/webhook/verify',
    ],

    'payment_gateways' => [
        'default' => env('FINANCE_DEFAULT_GATEWAY', 'razorpay'),
        'razorpay' => [
            'key_id' => env('RAZORPAY_KEY_ID', ''),
            'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
        ],
    ],

];
