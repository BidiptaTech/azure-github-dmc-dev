<?php

namespace App\Support;

/**
 * DMC-facing copy must never expose online API vendor brands.
 * TravClicks is the only provider name DMC users should see.
 */
final class SupplierFacingCopy
{
    public const PUBLIC_PROVIDER = 'TravClicks';

    /**
     * Brand / code tokens that must never appear in DMC UI or API messages.
     *
     * @return list<string>
     */
    public static function brandTokens(): array
    {
        return [
            'MG Bedbank',
            'MG Bed Bank',
            'MGBedbank',
            'mg bedbank',
            'mg_bedbank',
            'mgbedbank',
            'MG Booking',
            'MGBooking',
            'mgBookingID',
            'MGBookingID',
            'Hotelbeds',
            'HotelBeds',
            'Hotel Beds',
            'hotelbeds',
            'Tinivia',
            'Tiniva',
            'tinivia',
            'tiniva',
            'SG Attractions',
            'sg_attractions',
            'Sg Attractions',
            'AttractionsSG',
            'Attractions SG',
            'attractionsSG',
        ];
    }

    public static function publicSupplierName(): string
    {
        return self::PUBLIC_PROVIDER;
    }

    /**
     * Strip / replace vendor brands in any user-facing string.
     */
    public static function mask(?string $message, string $replacement = self::PUBLIC_PROVIDER): string
    {
        $message = trim((string) $message);
        if ($message === '') {
            return $message;
        }

        $masked = $message;
        foreach (self::brandTokens() as $token) {
            $masked = str_ireplace($token, $replacement, $masked);
        }

        // Collapse awkward doubles after replacement.
        $escaped = preg_quote($replacement, '/');
        $masked = preg_replace('/\b'.$escaped.'(?:\s+'.$escaped.')+/i', $replacement, $masked) ?? $masked;

        return trim($masked);
    }

    /**
     * Present supplier meta for DMC clients: keep internal code for logic, hide brand name.
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function presentMeta(array $meta): array
    {
        if (array_key_exists('supplier_name', $meta)) {
            $meta['supplier_name'] = self::publicSupplierName();
        }

        if (isset($meta['message']) && is_string($meta['message'])) {
            $meta['message'] = self::mask($meta['message']);
        }

        return $meta;
    }
}
