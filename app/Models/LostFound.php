<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostFound extends Model
{
    protected $table = 'lost_found';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'resolved' => 'boolean',
        'comments' => 'array',
        'images' => 'array',
        'guest_images' => 'array',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }

    /**
     * Append a staff comment entry to the existing JSON array in comments column.
     *
     * @param  array{comments: string, user: ?string, time_date: string}  $entry
     */
    public function appendCommentEntry(array $entry): void
    {
        if ($this->exists) {
            $this->refresh();
        }

        $raw = $this->getRawOriginal('comments');
        if ($raw === null && array_key_exists('comments', $this->attributes)) {
            $raw = $this->attributes['comments'];
        }

        $existing = self::decodeCommentsList($raw);

        $existing[] = [
            'comments' => $entry['comments'],
            'user' => $entry['user'] ?? null,
            'time_date' => $entry['time_date'],
        ];

        $this->comments = array_values($existing);
    }

    /**
     * @return list<array{comments: string, user: ?string, time_date: ?string}>
     */
    public static function decodeCommentsList($raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if ($raw instanceof \JsonSerializable) {
            $raw = $raw->jsonSerialize();
        }

        if (is_array($raw)) {
            return self::normalizeCommentsList($raw);
        }

        if (!is_string($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $plain = trim($raw);

            return $plain === '' ? [] : self::normalizeCommentsList([[
                'comments' => $plain,
                'user' => null,
                'time_date' => null,
            ]]);
        }

        if (isset($decoded['comments']) || isset($decoded['comment']) || isset($decoded['user']) || isset($decoded['time_date'])) {
            return self::normalizeCommentsList([$decoded]);
        }

        return self::normalizeCommentsList($decoded);
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array{comments: string, user: ?string, time_date: ?string}>
     */
    private static function normalizeCommentsList(array $items): array
    {
        $normalized = [];

        foreach (array_values($items) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $text = trim((string) ($item['comments'] ?? $item['comment'] ?? ''));
            if ($text === '') {
                continue;
            }

            $normalized[] = [
                'comments' => $text,
                'user' => isset($item['user']) && $item['user'] !== ''
                    ? (string) $item['user']
                    : null,
                'time_date' => isset($item['time_date']) && $item['time_date'] !== ''
                    ? (string) $item['time_date']
                    : null,
            ];
        }

        return $normalized;
    }
}
