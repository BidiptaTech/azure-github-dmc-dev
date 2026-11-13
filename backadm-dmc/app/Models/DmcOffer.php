<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DmcOffer extends Model
{
    protected $table = 'dmc_offer';

    public const CURRENCY = 'SGD';

    /**
     * Only true DMC accounts (role_id = 11).
     * Role 138 is multi-role staff under the same company_name — including it
     * makes the dropdown look like duplicate DMC names.
     */
    public const DMC_ROLE_IDS = [11];

    protected $fillable = [
        'dmc_offer_id',
        'dmc_id',
        'start_date',
        'end_date',
        'adult_price',
        'child_price',
        'infant_price',
        'currency',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'dmc_offer_id' => 'integer',
        'dmc_id' => 'integer',
        'created_by' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->currency = self::CURRENCY;
        });

        static::updating(function (self $model) {
            $model->currency = self::CURRENCY;
        });

        static::created(function (self $model) {
            if ($model->dmc_offer_id === null) {
                $model->forceFill(['dmc_offer_id' => $model->id])->saveQuietly();
            }
        });
    }

    public function dmc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dmc_id', 'userId');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'userId');
    }

    public function scopeForDmc(Builder $query, int $dmcId): Builder
    {
        return $query->where('dmc_id', $dmcId);
    }

    public function scopeActiveOn(Builder $query, $date): Builder
    {
        $day = Carbon::parse($date)->toDateString();

        return $query
            ->whereDate('start_date', '<=', $day)
            ->whereDate('end_date', '>=', $day);
    }

    /**
     * Active offer for a DMC on a date, if any (latest wins if overlap slipped in).
     */
    public static function forDmcOnDate(int $dmcId, $date): ?self
    {
        return static::query()
            ->forDmc($dmcId)
            ->activeOn($date)
            ->latest('id')
            ->first();
    }

    /**
     * True when another offer for the same DMC overlaps [start, end] (inclusive).
     */
    public static function hasOverlap(int $dmcId, string $startDate, string $endDate, ?int $exceptId = null): bool
    {
        $query = static::query()
            ->where('dmc_id', $dmcId)
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}
