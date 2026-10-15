<?php

namespace App\Services;

use RuntimeException;

/**
 * Returns the demo or live credential set for a supplier. Adapters stay environment-agnostic
 * and only consume the array this class produces.
 */
class SupplierConfigResolver
{
    /**
     * @return array<string, string|null>
     */
    public function valuesFor(string $code, string $environment = ApiEnvironmentResolver::DEMO): array
    {
        $environment = $this->environment($environment);
        $code = $this->normalizeCode($code);
        $values = [];

        foreach ($this->fieldsFor($code, $environment) as $fieldKey => $field) {
            if (! is_array($field)) {
                continue;
            }

            $values[$fieldKey] = $this->readField($field, $environment);
        }

        $values['api_environment'] = $environment;

        return $values;
    }

    public function isConfigured(string $code, string $environment = ApiEnvironmentResolver::DEMO): bool
    {
        $environment = $this->environment($environment);
        $code = $this->normalizeCode($code);
        $values = $this->valuesFor($code, $environment);

        if ($this->credentialsLookComplete($code, $values)) {
            return true;
        }

        // Legacy config/services.php keys are DEMO-only so LIVE never silently
        // reuses staging credentials.
        if ($environment !== ApiEnvironmentResolver::DEMO) {
            return false;
        }

        return $this->legacyServicesConfigured($code);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function fieldsFor(string $code, string $environment = ApiEnvironmentResolver::DEMO): array
    {
        $definition = $this->definition($code);
        $environment = $this->environment($environment);

        if (isset($definition[$environment]['fields']) && is_array($definition[$environment]['fields'])) {
            return $definition[$environment]['fields'];
        }

        if (isset($definition['environments'][$environment]['fields'])
            && is_array($definition['environments'][$environment]['fields'])) {
            return $definition['environments'][$environment]['fields'];
        }

        return is_array($definition['fields'] ?? null) ? $definition['fields'] : [];
    }

    public function label(string $code): string
    {
        $code = $this->normalizeCode($code);
        $definition = $this->definition($code);

        return (string) ($definition['label'] ?? $code);
    }

    public function normalizeCode(string $code): string
    {
        $code = strtolower(trim($code));

        return $code === 'tiniva' ? 'tinivia' : $code;
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(string $code): array
    {
        $code = $this->normalizeCode($code);
        $definitions = $this->definitions();

        return is_array($definitions[$code] ?? null) ? $definitions[$code] : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        if (is_file(config_path('suppliers.php'))) {
            $definitions = require config_path('suppliers.php');

            return is_array($definitions) ? $definitions : [];
        }

        $definitions = config('suppliers', []);

        return is_array($definitions) ? $definitions : [];
    }

    public function environmentLabel(string $environment): string
    {
        return $this->environment($environment) === ApiEnvironmentResolver::LIVE ? 'LIVE' : 'DEMO';
    }

    public function missingCredentialsMessage(string $code, string $environment): string
    {
        $label = $this->label($code);
        $envLabel = $this->environmentLabel($environment);

        return "{$label} {$envLabel} API credentials are not configured. Set them in Supplier Master → API Credentials.";
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function readField(array $field, string $environment): ?string
    {
        $keys = [];

        if (! empty($field['env']) && is_string($field['env'])) {
            $keys[] = $field['env'];
        }

        if ($environment === ApiEnvironmentResolver::DEMO) {
            foreach ($this->legacyEnvKeys($field) as $legacyKey) {
                $keys[] = $legacyKey;
            }
        }

        foreach ($keys as $envKey) {
            $value = env($envKey);

            if ($value !== null && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        if (array_key_exists('default', $field) && $field['default'] !== null && $field['default'] !== '') {
            return (string) $field['default'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<int, string>
     */
    private function legacyEnvKeys(array $field): array
    {
        $keys = [];

        if (! empty($field['legacy_env']) && is_string($field['legacy_env'])) {
            $keys[] = $field['legacy_env'];
        }

        if (isset($field['legacy_envs']) && is_array($field['legacy_envs'])) {
            foreach ($field['legacy_envs'] as $key) {
                if (is_string($key) && $key !== '') {
                    $keys[] = $key;
                }
            }
        }

        return $keys;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    private function credentialsLookComplete(string $code, array $values): bool
    {
        if ($code === 'mg_bedbank') {
            return filled($values['base_url'] ?? null)
                && filled($values['agency_code'] ?? null)
                && filled($values['username'] ?? null)
                && filled($values['password'] ?? null);
        }

        if (in_array($code, ['hotelbeds', 'mybeds'], true)) {
            return filled($values['base_url'] ?? null)
                && filled($values['api_key'] ?? null)
                && filled($values['api_secret'] ?? null);
        }

        if ($code === 'sg_attractions') {
            if (! filled($values['base_url'] ?? null)) {
                return false;
            }

            if (filled($values['bearer_token'] ?? null)) {
                return true;
            }

            return filled($values['api_key'] ?? null) && filled($values['secret_key'] ?? null);
        }

        return filled($values['base_url'] ?? null) && filled($values['api_key'] ?? null);
    }

    private function legacyServicesConfigured(string $code): bool
    {
        if ($code === 'tinivia') {
            return filled(config('services.tiniva.base_url')) && filled(config('services.tiniva.api_key'));
        }

        if ($code === 'hotelbeds') {
            return filled(config('services.hotelbeds.base_url'))
                && filled(config('services.hotelbeds.api_key'))
                && filled(config('services.hotelbeds.api_secret'));
        }

        if ($code === 'mg_bedbank') {
            return filled(config('services.mg_bedbank.base_url'))
                && filled(config('services.mg_bedbank.agency_code'))
                && filled(config('services.mg_bedbank.username'))
                && filled(config('services.mg_bedbank.password'));
        }

        if ($code === 'sg_attractions') {
            $baseUrl = config('services.sg_attractions.base_url');
            $apiKey = config('services.sg_attractions.api_key');
            $secretKey = config('services.sg_attractions.secret_key');
            $bearerToken = config('services.sg_attractions.bearer_token');

            if (! filled($baseUrl)) {
                return false;
            }

            if (filled($bearerToken)) {
                return true;
            }

            return filled($apiKey) && filled($secretKey);
        }

        return false;
    }

    private function environment(string $environment): string
    {
        $normalized = strtolower(trim($environment));

        if (! in_array($normalized, [ApiEnvironmentResolver::DEMO, ApiEnvironmentResolver::LIVE], true)) {
            throw new RuntimeException('Unsupported API environment [' . $environment . '].');
        }

        return $normalized;
    }
}
