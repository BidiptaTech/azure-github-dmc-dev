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

        // When config is cached, env() is blank at runtime. Fill DEMO gaps from
        // config/services.php so isConfigured() and valuesFor() stay aligned.
        if ($environment === ApiEnvironmentResolver::DEMO
            && ! $this->credentialsLookComplete($code, $values)) {
            foreach ($this->legacyServicesValues($code) as $key => $legacyValue) {
                if (! filled($values[$key] ?? null) && filled($legacyValue)) {
                    $values[$key] = (string) $legacyValue;
                }
            }
        }

        $values['api_environment'] = $environment;

        return $values;
    }

    public function isConfigured(string $code, string $environment = ApiEnvironmentResolver::DEMO): bool
    {
        $environment = $this->environment($environment);
        $code = $this->normalizeCode($code);

        return $this->credentialsLookComplete($code, $this->valuesFor($code, $environment));
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
        // Prefer cached config so baked field values from config:cache are used.
        $definitions = config('suppliers', []);

        if (is_array($definitions) && $definitions !== []) {
            return $definitions;
        }

        if (is_file(config_path('suppliers.php'))) {
            $definitions = require config_path('suppliers.php');

            return is_array($definitions) ? $definitions : [];
        }

        return [];
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
        if (array_key_exists('value', $field) && $field['value'] !== null && trim((string) $field['value']) !== '') {
            return (string) $field['value'];
        }

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
            $value = $this->readEnvValue($envKey);

            if ($value !== null && trim($value) !== '') {
                return $value;
            }
        }

        if (array_key_exists('default', $field) && $field['default'] !== null && $field['default'] !== '') {
            return (string) $field['default'];
        }

        return null;
    }

    /**
     * env() returns null when config is cached; $_ENV / $_SERVER still hold .env values.
     */
    private function readEnvValue(string $key): ?string
    {
        $value = env($key);

        if ($value !== null && trim((string) $value) !== '') {
            return (string) $value;
        }

        foreach ([$_ENV[$key] ?? null, $_SERVER[$key] ?? null, getenv($key) ?: null] as $fallback) {
            if ($fallback !== null && trim((string) $fallback) !== '') {
                return (string) $fallback;
            }
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

    /**
     * @return array<string, string|null>
     */
    private function legacyServicesValues(string $code): array
    {
        if ($code === 'tinivia') {
            return [
                'base_url' => config('services.tiniva.base_url'),
                'api_key' => config('services.tiniva.api_key'),
                'jwt' => config('services.tiniva.jwt'),
                'entity_id' => config('services.tiniva.entity_id'),
                'timeout' => config('services.tiniva.timeout'),
            ];
        }

        if ($code === 'hotelbeds') {
            return [
                'base_url' => config('services.hotelbeds.base_url'),
                'api_key' => config('services.hotelbeds.api_key'),
                'api_secret' => config('services.hotelbeds.api_secret'),
                'timeout' => config('services.hotelbeds.timeout'),
            ];
        }

        if ($code === 'mg_bedbank') {
            return [
                'base_url' => config('services.mg_bedbank.base_url'),
                'agency_code' => config('services.mg_bedbank.agency_code'),
                'username' => config('services.mg_bedbank.username'),
                'password' => config('services.mg_bedbank.password'),
                'timeout' => config('services.mg_bedbank.timeout'),
            ];
        }

        if ($code === 'sg_attractions') {
            return [
                'base_url' => config('services.sg_attractions.base_url'),
                'api_key' => config('services.sg_attractions.api_key'),
                'secret_key' => config('services.sg_attractions.secret_key'),
                'bearer_token' => config('services.sg_attractions.bearer_token'),
                'api_version' => config('services.sg_attractions.api_version'),
                'timeout' => config('services.sg_attractions.timeout'),
            ];
        }

        return [];
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
