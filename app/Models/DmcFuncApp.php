<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmcFuncApp extends Model
{
    protected $table = 'dmc_func_apps';

    protected $fillable = [
        'function_name',
        'maximum_limit',
        'dmc_id',
    ];

    protected $casts = [
        'maximum_limit' => 'integer',
        'dmc_id' => 'array',
    ];

    public function getDmcIdsAttribute(): array
    {
        return array_values(array_unique(array_map('intval', (array) ($this->dmc_id ?? []))));
    }

    public function assignedCount(): int
    {
        return count($this->dmc_ids);
    }

    public function remainingSlots(): int
    {
        return max(0, (int) $this->maximum_limit - $this->assignedCount());
    }

    public function hasReachedLimit(): bool
    {
        return $this->remainingSlots() <= 0;
    }

    public static function assignedDmcIds(?int $exceptId = null): array
    {
        $query = static::query();
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $ids = [];
        foreach ($query->get(['dmc_id']) as $row) {
            foreach ((array) $row->dmc_id as $dmcId) {
                $ids[] = (int) $dmcId;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    public static function badgePalette(): array
    {
        return [
            ['bg' => '#DBEAFE', 'color' => '#1D4ED8'],
            ['bg' => '#DCFCE7', 'color' => '#15803D'],
            ['bg' => '#FEF3C7', 'color' => '#B45309'],
            ['bg' => '#FCE7F3', 'color' => '#BE185D'],
            ['bg' => '#EDE9FE', 'color' => '#6D28D9'],
            ['bg' => '#FFEDD5', 'color' => '#C2410C'],
            ['bg' => '#E0E7FF', 'color' => '#3730A3'],
            ['bg' => '#CCFBF1', 'color' => '#0F766E'],
            ['bg' => '#FEE2E2', 'color' => '#B91C1C'],
            ['bg' => '#F3E8FF', 'color' => '#7E22CE'],
            ['bg' => '#CFFAFE', 'color' => '#0E7490'],
            ['bg' => '#FDE68A', 'color' => '#92400E'],
        ];
    }

    public static function badgeStyle($id): array
    {
        $palette = self::badgePalette();

        return $palette[abs((int) $id) % count($palette)];
    }

    public static function functionNameForDmc(int $dmcId): ?string
    {
        $settings = self::settingsForDmc($dmcId);

        return $settings['function_name'] ?? null;
    }

    /**
     * @return array{function_name: string|null, maximum_limit: int|null}
     */
    public static function settingsForDmc(int $dmcId): array
    {
        $map = self::settingsForMany([$dmcId]);

        return $map[$dmcId] ?? [
            'function_name' => null,
            'maximum_limit' => null,
        ];
    }

    /**
     * @param  list<int>  $dmcIds
     * @return array<int, array{function_name: string|null, maximum_limit: int|null}>
     */
    public static function settingsForMany(array $dmcIds): array
    {
        $dmcIds = array_values(array_unique(array_filter(array_map('intval', $dmcIds))));
        $result = [];
        foreach ($dmcIds as $id) {
            $result[$id] = [
                'function_name' => null,
                'maximum_limit' => null,
            ];
        }

        if ($dmcIds === []) {
            return $result;
        }

        foreach (static::query()->get(['function_name', 'maximum_limit', 'dmc_id']) as $row) {
            $assigned = array_map('intval', (array) $row->dmc_id);
            foreach ($assigned as $assignedId) {
                if (! isset($result[$assignedId])) {
                    continue;
                }
                $result[$assignedId] = [
                    'function_name' => $row->function_name !== null ? (string) $row->function_name : null,
                    'maximum_limit' => $row->maximum_limit !== null ? (int) $row->maximum_limit : null,
                ];
            }
        }

        return $result;
    }
}
