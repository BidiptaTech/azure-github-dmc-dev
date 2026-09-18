<?php

return [

    'tinivia' => [
        'label' => 'Tinivia',
        'fields' => [
            'base_url' => ['env' => 'TINIVA_API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url'],
            'api_key' => ['env' => 'TINIVA_API_KEY', 'label' => 'API Key', 'type' => 'text'],
            'jwt' => ['env' => 'TINIVA_JWT', 'label' => 'JWT', 'type' => 'password'],
            'entity_id' => ['env' => 'TINIVA_ENTITY_ID', 'label' => 'Entity ID', 'type' => 'text'],
            'timeout' => ['env' => 'TINIVA_API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '30'],
        ],
    ],

    'mybeds' => [
        'label' => 'MyBeds',
        'fields' => [
            'base_url' => ['env' => 'MYBEDS_API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url'],
            'api_key' => ['env' => 'MYBEDS_API_KEY', 'label' => 'API Key', 'type' => 'text'],
            'api_secret' => ['env' => 'MYBEDS_API_SECRET', 'label' => 'API Secret', 'type' => 'password'],
            'timeout' => ['env' => 'MYBEDS_API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '30'],
        ],
    ],

    'mg_bedbank' => [
        'label' => 'MG Bedbank',
        'fields' => [
            'base_url' => ['env' => 'MG_BEDBANK_API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url'],
            'api_key' => ['env' => 'MG_BEDBANK_API_KEY', 'label' => 'API Key', 'type' => 'text'],
            'api_secret' => ['env' => 'MG_BEDBANK_API_SECRET', 'label' => 'API Secret', 'type' => 'password'],
            'timeout' => ['env' => 'MG_BEDBANK_API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '30'],
        ],
    ],

    'hotelbeds' => [
        'label' => 'Hotelbeds',
        'fields' => [
            'base_url' => ['env' => 'HOTELBEDS_API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url', 'default' => 'https://api.test.hotelbeds.com'],
            'api_key' => ['env' => 'HOTELBEDS_API_KEY', 'label' => 'API Key', 'type' => 'text'],
            'api_secret' => ['env' => 'HOTELBEDS_API_SECRET', 'label' => 'API Secret', 'type' => 'password'],
            'timeout' => ['env' => 'HOTELBEDS_API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '30'],
        ],
    ],

    'sg_attractions' => [
        'label' => 'SG Attractions',
        'fields' => [
            'base_url' => ['env' => 'SG_ATTRACTIONS_API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url'],
            'api_key' => ['env' => 'SG_ATTRACTIONS_API_KEY', 'label' => 'API Key', 'type' => 'text'],
            'secret_key' => ['env' => 'SG_ATTRACTIONS_SECRET_KEY', 'label' => 'Secret Key', 'type' => 'password'],
            'bearer_token' => ['env' => 'SG_ATTRACTIONS_BEARER_TOKEN', 'label' => 'Bearer Token (optional)', 'type' => 'password'],
            'timeout' => ['env' => 'SG_ATTRACTIONS_API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '60'],
        ],
    ],

];
