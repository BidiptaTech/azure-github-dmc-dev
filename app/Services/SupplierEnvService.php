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
        return app(SupplierConfigResolver::class)->definitions();
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
    public function valuesFor(string $code, string $environment = ApiEnvironmentResolver::DEMO): array
    {
        return app(SupplierConfigResolver::class)->valuesFor($code, $environment);
    }

    public function isConfigured(string $code, string $environment = ApiEnvironmentResolver::DEMO): bool
    {
        return app(SupplierConfigResolver::class)->isConfigured($code, $environment);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function fieldsFor(string $code, string $environment = ApiEnvironmentResolver::DEMO): array
    {
        return app(SupplierConfigResolver::class)->fieldsFor($code, $environment);
    }

    /**
     * @return array<string, string>
     */
    public function validationRules(string $code, string $environment = ApiEnvironmentResolver::DEMO): array
    {
        $rules = [
            'environment' => ['nullable', 'in:demo,live'],
        ];

        foreach ($this->fieldsFor($code, $environment) as $fieldKey => $field) {
            $rules[$fieldKey] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function updateFromRequest(string $code, Request $request): void
    {
        $definitions = $this->definitions();

        if (! isset($definitions[$code]) || ! is_array($definitions[$code])) {
            throw new \InvalidArgumentException("Unknown supplier code [{$code}].");
        }

        $environment = strtolower(trim((string) $request->input('environment', ApiEnvironmentResolver::DEMO)));
        if (! in_array($environment, [ApiEnvironmentResolver::DEMO, ApiEnvironmentResolver::LIVE], true)) {
            $environment = ApiEnvironmentResolver::DEMO;
        }

        $envUpdates = [];

        foreach ($this->fieldsFor($code, $environment) as $fieldKey => $field) {
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
