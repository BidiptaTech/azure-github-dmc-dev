<?php

namespace App\Models;

use App\Services\SupplierEnvService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierMaster extends Model
{
    protected $table = 'suppliers_master';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function credentialsConfigured(): bool
    {
        if (! filled($this->code)) {
            return false;
        }

        return app(SupplierEnvService::class)->isConfigured($this->code);
    }

    /**
     * @return array<string, string>
     */
    public static function codeOptions(): array
    {
        $options = [];

        foreach (config('suppliers', []) as $code => $definition) {
            $options[$code] = $definition['label'] ?? $code;
        }

        return $options;
    }

    public static function forCountry(int $countryId): ?self
    {
        return static::query()
            ->where('country_id', $countryId)
            ->where('status', true)
            ->first();
    }
}
