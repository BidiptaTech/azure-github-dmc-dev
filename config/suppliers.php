<?php

if (! function_exists('azure_supplier_env_fields')) {
    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, array<string, mixed>>
     */
    function azure_supplier_env_fields(array $fields, string $prefix, bool $isDemo): array
    {
        $envName = $isDemo ? 'DEMO' : 'LIVE';
        $mapped = [];

        foreach ($fields as $key => $field) {
            $suffix = (string) ($field['env_suffix'] ?? '');
            $legacySuffix = $fields[$key]['legacy_suffix'] ?? null;
            $legacySuffixes = $fields[$key]['legacy_suffixes'] ?? [];
            unset($field['env_suffix'], $field['legacy_suffix'], $field['legacy_suffixes']);

            $field['env'] = $prefix . '_' . $envName . '_' . $suffix;

            $legacy = [];
            if ($isDemo) {
                if (! empty($legacySuffix)) {
                    $legacy[] = $prefix . '_' . $legacySuffix;
                }
                foreach ($legacySuffixes as $legacyItem) {
                    if (is_string($legacyItem) && $legacyItem !== '') {
                        $legacy[] = $prefix . '_' . $legacyItem;
                    }
                }
                if ($legacy !== []) {
                    $field['legacy_env'] = $legacy[0];
                    if (count($legacy) > 1) {
                        $field['legacy_envs'] = $legacy;
                    }
                }
            }

            // Resolve at config-load time so `php artisan config:cache` bakes credentials in.
            // Runtime env() is empty when the config cache is present.
            $resolved = env($field['env']);
            if (($resolved === null || trim((string) $resolved) === '') && $legacy !== []) {
                foreach ($legacy as $legacyKey) {
                    $candidate = env($legacyKey);
                    if ($candidate !== null && trim((string) $candidate) !== '') {
                        $resolved = $candidate;
                        break;
                    }
                }
            }
            if ($resolved !== null && trim((string) $resolved) !== '') {
                $field['value'] = (string) $resolved;
            }

            $mapped[$key] = $field;
        }

        return $mapped;
    }
}

$tiniviaFields = [
    'base_url' => ['env_suffix' => 'API_BASE_URL', 'legacy_suffix' => 'API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url', 'default' => 'https://elevator-staging-api.tiniva.com'],
    'api_key' => ['env_suffix' => 'API_KEY', 'legacy_suffix' => 'API_KEY', 'label' => 'API Key', 'type' => 'text'],
    'jwt' => ['env_suffix' => 'JWT', 'legacy_suffix' => 'JWT', 'label' => 'JWT', 'type' => 'password'],
    'entity_id' => ['env_suffix' => 'ENTITY_ID', 'legacy_suffix' => 'ENTITY_ID', 'label' => 'Entity ID', 'type' => 'text'],
    'timeout' => ['env_suffix' => 'API_TIMEOUT', 'legacy_suffix' => 'API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '30'],
];

$mybedsFields = [
    'base_url' => ['env_suffix' => 'API_BASE_URL', 'legacy_suffix' => 'API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url'],
    'api_key' => ['env_suffix' => 'API_KEY', 'legacy_suffix' => 'API_KEY', 'label' => 'API Key', 'type' => 'text'],
    'api_secret' => ['env_suffix' => 'API_SECRET', 'legacy_suffix' => 'API_SECRET', 'label' => 'API Secret', 'type' => 'password'],
    'timeout' => ['env_suffix' => 'API_TIMEOUT', 'legacy_suffix' => 'API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '30'],
];

$mgBedbankFields = [
    'base_url' => ['env_suffix' => 'API_BASE_URL', 'legacy_suffix' => 'API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url', 'default' => 'https://uat-jarvis1-xmlsell.mgbedbank.com/1.0/Hotel'],
    'agency_code' => ['env_suffix' => 'AGENCY_CODE', 'legacy_suffix' => 'AGENCY_CODE', 'label' => 'Agency Code', 'type' => 'text'],
    'username' => ['env_suffix' => 'USERNAME', 'legacy_suffix' => 'USERNAME', 'label' => 'Username', 'type' => 'text'],
    'password' => ['env_suffix' => 'PASSWORD', 'legacy_suffix' => 'PASSWORD', 'label' => 'Password', 'type' => 'password'],
    'nationality' => ['env_suffix' => 'NATIONALITY', 'legacy_suffix' => 'NATIONALITY', 'label' => 'Guest Nationality Code', 'type' => 'text', 'default' => 'SG'],
    'country_code' => ['env_suffix' => 'COUNTRY_CODE', 'legacy_suffix' => 'COUNTRY_CODE', 'label' => 'Destination Country Code', 'type' => 'text'],
    'city_code' => ['env_suffix' => 'CITY_CODE', 'legacy_suffix' => 'CITY_CODE', 'label' => 'Default Destination City Code', 'type' => 'text'],
    'destination_map' => ['env_suffix' => 'DESTINATION_MAP', 'legacy_suffix' => 'DESTINATION_MAP', 'label' => 'City Map JSON (e.g. {"Singapore":"SG-SIN"})', 'type' => 'text'],
    'hotel_codes' => ['env_suffix' => 'HOTEL_CODES', 'legacy_suffix' => 'HOTEL_CODES', 'label' => 'Hotel Codes (optional, restricts the search)', 'type' => 'text'],
    'hotel_list_ttl' => ['env_suffix' => 'HOTEL_LIST_TTL', 'legacy_suffix' => 'HOTEL_LIST_TTL', 'label' => 'Hotel List Cache (minutes, 0 disables)', 'type' => 'number', 'default' => '1440'],
    'max_hotel_codes' => ['env_suffix' => 'MAX_HOTEL_CODES', 'legacy_suffix' => 'MAX_HOTEL_CODES', 'label' => 'Max Hotel Codes Per Search (0 = full catalogue)', 'type' => 'number', 'default' => '0'],
    'search_hotel_chunk_size' => ['env_suffix' => 'SEARCH_HOTEL_CHUNK_SIZE', 'legacy_suffix' => 'SEARCH_HOTEL_CHUNK_SIZE', 'label' => 'SearchHotel Codes Per Request', 'type' => 'number', 'default' => '100'],
    'currency' => ['env_suffix' => 'CURRENCY', 'legacy_suffix' => 'CURRENCY', 'label' => 'Requested Currency', 'type' => 'text', 'default' => 'INR'],
    'language' => ['env_suffix' => 'LANGUAGE', 'legacy_suffix' => 'LANGUAGE', 'label' => 'Language', 'type' => 'text', 'default' => 'En'],
    'detail_level' => ['env_suffix' => 'DETAIL_LEVEL', 'legacy_suffix' => 'DETAIL_LEVEL', 'label' => 'Detail Level', 'type' => 'text', 'default' => 'Basic'],
    'timeout' => ['env_suffix' => 'API_TIMEOUT', 'legacy_suffix' => 'API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '30'],
];

$hotelbedsFields = [
    'base_url' => ['env_suffix' => 'API_BASE_URL', 'legacy_suffix' => 'API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url', 'default' => 'https://api.test.hotelbeds.com'],
    'api_key' => ['env_suffix' => 'API_KEY', 'legacy_suffix' => 'API_KEY', 'label' => 'API Key', 'type' => 'text'],
    'api_secret' => ['env_suffix' => 'API_SECRET', 'legacy_suffix' => 'API_SECRET', 'label' => 'API Secret', 'type' => 'password'],
    'timeout' => ['env_suffix' => 'API_TIMEOUT', 'legacy_suffix' => 'API_TIMEOUT', 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '30'],
];

$sgAttractionsFields = [
    'base_url' => ['env_suffix' => 'API_BASE_URL', 'legacy_suffix' => 'API_BASE_URL', 'label' => 'API Base URL', 'type' => 'url', 'default' => 'https://tdpapi.attractionsg.com'],
    'api_key' => ['env_suffix' => 'API_KEY', 'legacy_suffix' => 'API_KEY', 'label' => 'API Key', 'type' => 'text'],
    'secret_key' => ['env_suffix' => 'SECRET_KEY', 'legacy_suffix' => 'SECRET_KEY', 'label' => 'Secret Key', 'type' => 'password'],
    'bearer_token' => ['env_suffix' => 'BEARER_TOKEN', 'legacy_suffix' => 'BEARER_TOKEN', 'label' => 'Bearer Token (optional)', 'type' => 'password'],
    'api_version' => ['env_suffix' => 'API_VERSION', 'legacy_suffix' => 'API_VERSION', 'label' => 'API Version', 'type' => 'text', 'default' => 'v1.10'],
    'timeout' => ['env_suffix' => 'TIMEOUT', 'legacy_suffixes' => ['API_TIMEOUT', 'TIMEOUT'], 'label' => 'Timeout (seconds)', 'type' => 'number', 'default' => '60'],
];

$tiniviaLive = $tiniviaFields;
$tiniviaLive['base_url']['default'] = '';

$mybedsLive = $mybedsFields;

$mgBedbankLive = $mgBedbankFields;
$mgBedbankLive['base_url']['default'] = '';

$hotelbedsLive = $hotelbedsFields;
$hotelbedsLive['base_url']['default'] = '';

$sgAttractionsLive = $sgAttractionsFields;
$sgAttractionsLive['base_url']['default'] = '';

return [

    'tinivia' => [
        'label' => 'Tinivia',
        'demo' => ['fields' => azure_supplier_env_fields($tiniviaFields, 'TINIVA', true)],
        'live' => ['fields' => azure_supplier_env_fields($tiniviaLive, 'TINIVA', false)],
    ],

    'mybeds' => [
        'label' => 'MyBeds',
        'demo' => ['fields' => azure_supplier_env_fields($mybedsFields, 'MYBEDS', true)],
        'live' => ['fields' => azure_supplier_env_fields($mybedsLive, 'MYBEDS', false)],
    ],

    'mg_bedbank' => [
        'label' => 'MG Bedbank',
        'demo' => ['fields' => azure_supplier_env_fields($mgBedbankFields, 'MG_BEDBANK', true)],
        'live' => ['fields' => azure_supplier_env_fields($mgBedbankLive, 'MG_BEDBANK', false)],
    ],

    'hotelbeds' => [
        'label' => 'Hotelbeds',
        'demo' => ['fields' => azure_supplier_env_fields($hotelbedsFields, 'HOTELBEDS', true)],
        'live' => ['fields' => azure_supplier_env_fields($hotelbedsLive, 'HOTELBEDS', false)],
    ],

    'sg_attractions' => [
        'label' => 'SG Attractions',
        'demo' => ['fields' => azure_supplier_env_fields($sgAttractionsFields, 'SG_ATTRACTIONS', true)],
        'live' => ['fields' => azure_supplier_env_fields($sgAttractionsLive, 'SG_ATTRACTIONS', false)],
    ],

];
