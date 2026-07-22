<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SupplierEnvService
{
    public function __construct(
        private EnvWriter $envWriter
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allDefinitions(): array
    {
        return $this->definitions();
    }

    /**
     * Authoritative supplier registry — always read from config/suppliers.php
     * so new entries (e.g. sg_attractions) appear even when config is cached.
     *
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        if (is_file(config_path('suppliers.php'))) {
            return require config_path('suppliers.php');
        }

        return config('suppliers', []);
    }

    /**
     * @return array<int, string>
     */
    public function allowedCodes(): array
    {
        return array_keys($this->definitions());
    }

    /**
     * @return array<string, string|null>
     */
    public function valuesFor(string $code): array
    {
        $definition = $this->definitions()[$code] ?? null;

        if (! is_array($definition)) {
            return [];
        }

        $values = [];

        foreach ($definition['fields'] ?? [] as $fieldKey => $field) {
            $envKey = $field['env'] ?? null;

            if (! $envKey) {
                continue;
            }

            $values[$fieldKey] = env($envKey, $field['default'] ?? null);
        }

        return $values;
    }

    public function isConfigured(string $code): bool
    {
        $values = $this->valuesFor($code);

        if ($code === 'mg_bedbank') {
            if (filled($values['base_url'] ?? null)
                && filled($values['agency_code'] ?? null)
                && filled($values['username'] ?? null)
                && filled($values['password'] ?? null)) {
                return true;
            }
        } elseif (in_array($code, ['hotelbeds', 'mybeds'], true)) {
            if (filled($values['base_url'] ?? null)
                && filled($values['api_key'] ?? null)
                && filled($values['api_secret'] ?? null)) {
                return true;
            }
        } elseif (filled($values['base_url'] ?? null) && filled($values['api_key'] ?? null)) {
            return true;
        }

        // Fallback when .env values are cached under config/services.php (e.g. tinivia → tiniva).
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
            return $this->isSgAttractionsConfigured($values);
        }

        return false;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    private function isSgAttractionsConfigured(array $values): bool
    {
        $baseUrl = $values['base_url'] ?? config('services.sg_attractions.base_url');
        $apiKey = $values['api_key'] ?? config('services.sg_attractions.api_key');
        $secretKey = $values['secret_key'] ?? config('services.sg_attractions.secret_key');
        $bearerToken = $values['bearer_token'] ?? config('services.sg_attractions.bearer_token');

        if (! filled($baseUrl)) {
            return false;
        }

        if (filled($bearerToken)) {
            return true;
        }

        return filled($apiKey) && filled($secretKey);
    }

    /**
     * @return array<string, string>
     */
    public function validationRules(string $code): array
    {
        $definition = $this->definitions()[$code] ?? null;
        $rules = [];

        if (! is_array($definition)) {
            return $rules;
        }

        foreach ($definition['fields'] ?? [] as $fieldKey => $field) {
            $rules[$fieldKey] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function updateFromRequest(string $code, Request $request): void
    {
        $definition = $this->definitions()[$code] ?? null;

        if (! is_array($definition)) {
            throw new \InvalidArgumentException("Unknown supplier code [{$code}].");
        }

        $envUpdates = [];

        foreach ($definition['fields'] ?? [] as $fieldKey => $field) {
            $envKey = $field['env'] ?? null;

            if (! $envKey) {
                continue;
            }

            $type = $field['type'] ?? 'text';

            if ($type === 'password' && ! $request->filled($fieldKey)) {
                continue;
            }

            if (! $request->has($fieldKey)) {
                continue;
            }

            $envUpdates[$envKey] = (string) $request->input($fieldKey, '');
        }

        if ($envUpdates === []) {
            return;
        }

        $this->envWriter->setMany($envUpdates);
        Artisan::call('config:clear');
    }
}
