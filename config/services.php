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

    /*
    | Azure AI Search — day-level JSON indexer.
    | Fill these so delete removes index docs, then reset+run refreshes from current blobs.
    */
    'azure_search' => [
        'endpoint' => rtrim((string) env('AZURE_SEARCH_ENDPOINT', ''), '/'),
        'admin_key' => env('AZURE_SEARCH_ADMIN_KEY', ''),
        'index' => env('AZURE_SEARCH_INDEX_NAME', ''),
        'indexer' => env('AZURE_SEARCH_INDEXER_NAME', ''),
        'key_field' => env('AZURE_SEARCH_KEY_FIELD', 'id'),
        'api_version' => env('AZURE_SEARCH_API_VERSION', '2024-07-01'),
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

    'hotelbeds' => [
        'base_url' => env('HOTELBEDS_API_BASE_URL', 'https://api.test.hotelbeds.com'),
        'api_key' => env('HOTELBEDS_API_KEY', ''),
        'api_secret' => env('HOTELBEDS_API_SECRET', ''),
        'timeout' => (int) env('HOTELBEDS_API_TIMEOUT', 30),
    ],

    'mg_bedbank' => [
        'base_url' => env('MG_BEDBANK_API_BASE_URL', ''),
        'agency_code' => env('MG_BEDBANK_AGENCY_CODE', ''),
        'username' => env('MG_BEDBANK_USERNAME', ''),
        'password' => env('MG_BEDBANK_PASSWORD', ''),
        'nationality' => env('MG_BEDBANK_NATIONALITY', 'SG'),
        'country_code' => env('MG_BEDBANK_COUNTRY_CODE', ''),
        'city_code' => env('MG_BEDBANK_CITY_CODE', ''),
        'destination_map' => env('MG_BEDBANK_DESTINATION_MAP', ''),
        'hotel_codes' => env('MG_BEDBANK_HOTEL_CODES', ''),
        'hotel_list_ttl' => env('MG_BEDBANK_HOTEL_LIST_TTL', '1440'),
        'max_hotel_codes' => env('MG_BEDBANK_MAX_HOTEL_CODES', '200'),
        'currency' => env('MG_BEDBANK_CURRENCY', 'SGD'),
        'language' => env('MG_BEDBANK_LANGUAGE', 'En'),
        'detail_level' => env('MG_BEDBANK_DETAIL_LEVEL', 'Basic'),
        'timeout' => (int) env('MG_BEDBANK_API_TIMEOUT', 30),
    ],

];
