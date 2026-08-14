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
            'agency_code' => ['env' => 'MG_BEDBANK_AGENCY_CODE', 'label' => 'Agency Code', 'type' => 'text'],
            'username' => ['env' => 'MG_BEDBANK_USERNAME', 'label' => 'Username', 'type' => 'text'],
            'password' => ['env' => 'MG_BEDBANK_PASSWORD', 'label' => 'Password', 'type' => 'password'],
            'nationality' => ['env' => 'MG_BEDBANK_NATIONALITY', 'label' => 'Guest Nationality Code', 'type' => 'text', 'default' => 'SG'],
            'country_code' => ['env' => 'MG_BEDBANK_COUNTRY_CODE', 'label' => 'Destination Country Code', 'type' => 'text'],
            'city_code' => ['env' => 'MG_BEDBANK_CITY_CODE', 'label' => 'Default Destination City Code', 'type' => 'text'],
            'destination_map' => ['env' => 'MG_BEDBANK_DESTINATION_MAP', 'label' => 'City Map JSON (e.g. {"Singapore":"SG-SIN"})', 'type' => 'text'],
            'hotel_codes' => ['env' => 'MG_BEDBANK_HOTEL_CODES', 'label' => 'Hotel Codes (optional, restricts the search)', 'type' => 'text'],
            'hotel_list_ttl' => ['env' => 'MG_BEDBANK_HOTEL_LIST_TTL', 'label' => 'Hotel List Cache (minutes, 0 disables)', 'type' => 'number', 'default' => '1440'],
            'max_hotel_codes' => ['env' => 'MG_BEDBANK_MAX_HOTEL_CODES', 'label' => 'Max Hotel Codes Per Search', 'type' => 'number', 'default' => '200'],
            'currency' => ['env' => 'MG_BEDBANK_CURRENCY', 'label' => 'Requested Currency', 'type' => 'text', 'default' => 'SGD'],
            'language' => ['env' => 'MG_BEDBANK_LANGUAGE', 'label' => 'Language', 'type' => 'text', 'default' => 'En'],
            'detail_level' => ['env' => 'MG_BEDBANK_DETAIL_LEVEL', 'label' => 'Detail Level', 'type' => 'text', 'default' => 'Basic'],
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
