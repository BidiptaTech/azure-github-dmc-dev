<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    public const TYPE_PROFORMA = 'proforma';

    public const TYPE_PROFORMA_PRICE_ONLY = 'proforma_price_only';

    public const TYPE_FINAL = 'final';

    public const TYPE_FINAL_PRICE_ONLY = 'final_price_only';

    public const TYPE_ALTERNATE = 'alternate';

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_PROFORMA,
            self::TYPE_PROFORMA_PRICE_ONLY,
            self::TYPE_FINAL,
            self::TYPE_FINAL_PRICE_ONLY,
            self::TYPE_ALTERNATE,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_PROFORMA => 'Proforma Invoice',
            self::TYPE_PROFORMA_PRICE_ONLY => 'Proforma (Price Only)',
            self::TYPE_FINAL => 'Final Invoice',
            self::TYPE_FINAL_PRICE_ONLY => 'Final (Price Only)',
            self::TYPE_ALTERNATE => 'Alternate Layout',
        ];
    }

    protected $table = 'invoice_templates';

    protected $fillable = [
        'dmc_id',
        'invoice_type',
        'active_version_id',
    ];

    protected $casts = [
        'dmc_id' => 'integer',
        'active_version_id' => 'integer',
    ];

    public function dmc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(InvoiceTemplateVersion::class)->orderBy('version');
    }

    public function activeVersion(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplateVersion::class, 'active_version_id');
    }
}
