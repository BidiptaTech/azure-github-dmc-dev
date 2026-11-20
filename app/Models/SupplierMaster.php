<?php

namespace App\Models;

use App\Services\SupplierEnvService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierMaster extends Model
{
    use SoftDeletes;

    protected $table = 'suppliers_master';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function serviceTypeOptions(): array
    {
        return [
            'hotels' => 'Hotels',
            'attractions' => 'Attractions',
            'flights' => 'Flights',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function markupTypeOptions(): array
    {
        return [
            'percentage' => 'Percentage (%)',
            'flat' => 'Flat',
        ];
    }

    public function serviceTypeLabel(): string
    {
        return self::serviceTypeOptions()[$this->service_type] ?? (string) $this->service_type;
    }

    public function markupTypeLabel(): string
    {
        return self::markupTypeOptions()[$this->markup_type] ?? (string) $this->markup_type;
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

        foreach (app(SupplierEnvService::class)->definitions() as $code => $definition) {
            $options[$code] = $definition['label'] ?? $code;
        }

        return $options;
    }

    public static function forCountry(int $countryId): ?self
    {
        return static::forCountryAndService($countryId, 'hotels');
    }

    public static function forCountryAndService(int $countryId, string $serviceType = 'hotels'): ?self
    {
        return static::query()
            ->where('country_id', $countryId)
            ->where('service_type', $serviceType)
            ->where('status', true)
            ->first();
    }
}
