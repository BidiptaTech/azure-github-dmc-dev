<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiKeyword extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORIES = [
        'hotel' => 'Hotel',
        'attraction' => 'Attraction',
        'restaurant' => 'Restaurant',
        'transport' => 'Transport',
    ];

    protected $table = 'ai_keywords';

    protected $fillable = [
        'keyword',
        'category',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public static function parseKeywordLine(?string $line): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $line))
            ->map(fn ($keyword) => trim($keyword))
            ->filter()
            ->values()
            ->all();
    }

    public static function keywordsToLine(string $input): string
    {
        $keywords = collect(preg_split('/[\r\n,]+/', $input))
            ->map(fn ($keyword) => trim($keyword))
            ->filter()
            ->unique(fn ($keyword) => strtolower($keyword))
            ->values()
            ->all();

        return implode(', ', $keywords);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'category_label' => self::CATEGORIES[$this->category] ?? $this->category,
            'keywords' => self::parseKeywordLine($this->keyword),
            'keywords_raw' => $this->keyword,
            'status' => $this->status,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
