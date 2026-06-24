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
        return config('suppliers', []);
    }

    /**
     * @return array<string, string|null>
     */
    public function valuesFor(string $code): array
    {
        $definition = config("suppliers.{$code}");

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

        return filled($values['base_url'] ?? null) && filled($values['api_key'] ?? null);
    }

    /**
     * @return array<string, string>
     */
    public function validationRules(string $code): array
    {
        $definition = config("suppliers.{$code}");
        $rules = [];

        foreach ($definition['fields'] ?? [] as $fieldKey => $field) {
            $rules[$fieldKey] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function updateFromRequest(string $code, Request $request): void
    {
        $definition = config("suppliers.{$code}");

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
