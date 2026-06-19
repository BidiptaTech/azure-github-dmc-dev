<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'freecurrencyapi' => [
        'key' => env('FREECURRENCYAPI_KEY'),
    ],

    'azure' => [
    'vault' => env('AZURE_KEYVAULT_NAME'),
    'client_id' => env('AZURE_CLIENT_ID'),
    'client_secret' => env('AZURE_CLIENT_SECRET'),
    'tenant_id' => env('AZURE_TENANT_ID'),
    ],

    'tiniva' => [
        'base_url' => env('TINIVA_API_BASE_URL', ''),
        'api_key' => env('TINIVA_API_KEY', ''),
        'jwt' => env('TINIVA_JWT', ''),
        'entity_id' => env('TINIVA_ENTITY_ID', ''),
        'timeout' => (int) env('TINIVA_API_TIMEOUT', 30),
    ],

    'sg_attractions' => [
        'base_url' => env('SG_ATTRACTIONS_API_BASE_URL', 'https://tdpapi.attractionsg.com'),
        'api_key' => env('SG_ATTRACTIONS_API_KEY', ''),
        'secret_key' => env('SG_ATTRACTIONS_SECRET_KEY', ''),
        'bearer_token' => env('SG_ATTRACTIONS_BEARER_TOKEN', ''),
        'api_version' => env('SG_ATTRACTIONS_API_VERSION', 'v1.10'),
        'timeout' => (int) env('SG_ATTRACTIONS_TIMEOUT', 60),
    ],


];
