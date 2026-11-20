<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationTemplate extends Model
{
    public const TYPE_ITINERARY = 'itinerary';
    public const TYPE_DETAILED = 'detailed';

    protected $table = 'quotation_templates';

    protected $fillable = [
        'dmc_id',
        'quotation_type',
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
        return $this->hasMany(QuotationTemplateVersion::class)->orderBy('version');
    }

    public function activeVersion(): BelongsTo
    {
        return $this->belongsTo(QuotationTemplateVersion::class, 'active_version_id');
    }
}
