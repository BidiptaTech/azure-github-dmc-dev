<?php

namespace App\Services\AttractionSuppliers;

use App\Models\Tour;
use App\Services\SupplierEnvService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Online Attractions API order create/pay. Must be called outside a DB transaction.
 */
class OnlineAttractionOrderService
{
    public const PLACEHOLDER_ORDER_REF = '1111111';

    private const DEFAULT_SUPPLIER_CODE = 'sg_attractions';

    public function __construct(
        private AttractionSupplierResolver $resolver,
        private AttractionSupplierFactory $factory,
        private SupplierEnvService $supplierEnv,
    ) {}

    public static function isPlaceholderRef(?string $ref): bool
    {
        $ref = trim((string) $ref);

        return $ref === '' || $ref === self::PLACEHOLDER_ORDER_REF;
    }

    /**
     * @param  array<string, mixed>  $attraction
     */
    public static function isOnlineAttraction(array $attraction): bool
    {
        if (! empty($attraction['isOnlineAttraction'])) {
            return true;
        }

        if (strtolower((string) ($attraction['attractionSourceType'] ?? '')) === 'online') {
            return true;
        }

        return strtolower((string) ($attraction['supplier_code'] ?? '')) === self::DEFAULT_SUPPLIER_CODE
            && ! empty($attraction['sku_id']);
    }

    /**
     * @param  array<string, mixed>  $attraction
     */
    public static function extractSavedRef(object $order, array $attraction = []): ?string
    {
        $candidates = [
            $order->order_ref_no ?? null,
            $attraction['external_order_ref_id'] ?? null,
            $attraction['attraction_order_ref_id'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }
            $candidate = trim((string) $candidate);
            if (! self::isPlaceholderRef($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Create (or reuse) an external Attractions order. Does not touch the database.
     * Lite save and approve both send use_credits like the working Reseller create-order form.
     *
     * @param  array<string, mixed>  $attraction
     * @return array{success: bool, order_ref_id: ?string, external_status: ?string, message: ?string, credits_insufficient: bool}
     */
    public function createOrder(array $attraction, ?int $tourId = null, bool $chargeCredits = false): array
    {
        $existing = self::extractSavedRef((object) ['order_ref_no' => $attraction['order_ref_no'] ?? null], $attraction);
        if ($existing !== null) {
            Log::info('Attraction external create-order skipped (payload already has order_ref_id)', [
                'order_ref_id' => $existing,
            ]);

            return [
                'success' => true,
                'order_ref_id' => $existing,
                'external_status' => (string) ($attraction['external_order_status'] ?? 'pending'),
                'message' => 'Reused existing order_ref_id',
                'credits_insufficient' => false,
            ];
        }

        $cacheKey = $this->pendingRefCacheKey($attraction, $tourId);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && ! self::isPlaceholderRef($cached)) {
            Log::info('Attraction external create-order skipped (cached order_ref_id from prior attempt)', [
                'order_ref_id' => $cached,
            ]);

            return [
                'success' => true,
                'order_ref_id' => $cached,
                'external_status' => 'pending',
                'message' => 'Reused cached order_ref_id',
                'credits_insufficient' => false,
            ];
        }

        $payload = $this->buildCreateOrderPayload($attraction, $tourId, $chargeCredits);

        Log::info('Attraction external create-order starting', [
            'sku_id' => $payload['item_sku_id_1'] ?? null,
            'booking_date' => $payload['item_booking_date_1'] ?? null,
            'quantity' => $payload['item_quantity_1'] ?? null,
            'total_amount' => $payload['total_amount'] ?? null,
            'currency' => $payload['currency'] ?? null,
            'use_credits' => $payload['use_credits'] ?? null,
            'charge_credits' => $chargeCredits,
            'has_email' => ! empty($payload['customer_email']),
            'has_mobile' => ! empty($payload['customer_mobile']),
            'has_name' => ! empty($payload['customer_name']),
            'payload_keys' => array_keys($payload),
        ]);

        if (empty($payload['item_sku_id_1'])) {
            return [
                'success' => false,
                'order_ref_id' => null,
                'external_status' => null,
                'message' => 'Online attraction is missing sku_id for external create-order.',
                'credits_insufficient' => false,
            ];
        }

        try {
            [$supplierCode, $credentials] = $this->resolveSupplier($attraction);
            $adapter = $this->factory->make($supplierCode);
            $result = $adapter->createOrder($payload, $credentials);
            $ref = $result['order_ref_id'] ?? null;
            $message = $result['message'] ?? null;
            $creditsInsufficient = $this->isCreditsInsufficient($message, $result);

            if (is_string($ref) && $ref !== '' && ! self::isPlaceholderRef($ref)) {
                Cache::put($cacheKey, $ref, now()->addDay());
            }

            if (! empty($result['success']) && is_string($ref) && $ref !== '') {

                Log::info('Attraction external create-order succeeded', [
                    'order_ref_id' => $ref,
                    'external_status' => $result['external_status'] ?? null,
                    'supplier_code' => $supplierCode,
                    'charge_credits' => $chargeCredits,
                ]);
            } else {
                if ($creditsInsufficient) {
                    $message = 'Credits balance is not enough';
                } elseif (is_string($message) && stripos($message, 'Item not found') !== false) {
                    $sku = (string) ($payload['item_sku_id_1'] ?? '');
                    $catalogSku = trim((string) ($attraction['sku_id'] ?? ''));
                    $message = 'Item not found'
                        . ($sku !== '' ? ' (SKU ' . $sku . ')' : '')
                        . '. Create-order needs an orderable ticket/package SKU (for example REGDAY), not a catalog attraction SKU'
                        . ($catalogSku !== '' && strcasecmp($sku, $catalogSku) === 0 ? ' such as ' . $catalogSku : '')
                        . '.';
                }

                Log::warning('Attraction external create-order failed', [
                    'message' => $result['message'] ?? null,
                    'sku_id' => $payload['item_sku_id_1'] ?? null,
                    'supplier_code' => $supplierCode,
                    'credits_insufficient' => $creditsInsufficient,
                    'charge_credits' => $chargeCredits,
                ]);
            }

            return [
                'success' => (bool) ($result['success'] ?? false),
                'order_ref_id' => is_string($ref) && $ref !== '' ? $ref : null,
                'external_status' => $result['external_status'] ?? null,
                'message' => $message ?? ($result['message'] ?? null),
                'credits_insufficient' => $creditsInsufficient,
            ];
        } catch (Throwable $e) {
            Log::error('Attraction external create-order exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'order_ref_id' => null,
                'external_status' => null,
                'message' => $e->getMessage(),
                'credits_insufficient' => false,
            ];
        }
    }

    /**
     * POST /order/update with action=pay. Does not touch the database.
     *
     * @return array{success: bool, credits_insufficient: bool, message: ?string, provider_status: ?int}
     */
    public function payOrder(string $orderRefId, array $attraction = []): array
    {
        $orderRefId = trim($orderRefId);
        if (self::isPlaceholderRef($orderRefId)) {
            return [
                'success' => false,
                'credits_insufficient' => false,
                'message' => 'Online attraction order reference is missing.',
                'provider_status' => null,
            ];
        }

        Log::info('Attraction external order pay starting', [
            'order_ref_id' => $orderRefId,
        ]);

        try {
            [$supplierCode, $credentials] = $this->resolveSupplier($attraction);
            $adapter = $this->factory->make($supplierCode);
            $result = $adapter->updateOrder($orderRefId, 'pay', $credentials);
            $providerStatus = $result['provider_status'] ?? null;
            $message = (string) ($result['message'] ?? '');
            $creditsInsufficient = $this->isCreditsInsufficient($message, $result);

            if ($creditsInsufficient) {
                Log::warning('Attraction external pay credits insufficient', [
                    'order_ref_id' => $orderRefId,
                    'provider_status' => $providerStatus,
                ]);
            } elseif (! empty($result['success'])) {
                Log::info('Attraction external pay succeeded', [
                    'order_ref_id' => $orderRefId,
                    'provider_status' => $providerStatus,
                ]);
            } else {
                Log::warning('Attraction external pay failed', [
                    'order_ref_id' => $orderRefId,
                    'provider_status' => $providerStatus,
                    'message' => $message !== '' ? $message : null,
                ]);
            }

            return [
                'success' => ! empty($result['success']) && ! $creditsInsufficient,
                'credits_insufficient' => $creditsInsufficient,
                'message' => $message !== '' ? $message : ($creditsInsufficient ? 'Credits balance is not enough' : 'External attraction payment failed.'),
                'provider_status' => $providerStatus,
            ];
        } catch (Throwable $e) {
            Log::error('Attraction external pay exception', [
                'order_ref_id' => $orderRefId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'credits_insufficient' => false,
                'message' => $e->getMessage(),
                'provider_status' => null,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $attraction
     */
    public function applyRefToAttraction(array $attraction, ?string $ref, ?string $status = null): array
    {
        if ($ref === null || $ref === '') {
            return $attraction;
        }

        $attraction['external_order_ref_id'] = $ref;
        $attraction['attraction_order_ref_id'] = $ref;
        if ($status !== null && $status !== '') {
            $attraction['external_order_status'] = $status;
        }

        return $attraction;
    }

    /**
     * Account credit balance from GET /credits.
     *
     * @param  array<string, mixed>  $attraction
     * @return array{success: bool, credits_balance: ?float, message: ?string}
     */
    public function getCredits(array $attraction = []): array
    {
        try {
            [$supplierCode, $credentials] = $this->resolveSupplier($attraction);
            $adapter = $this->factory->make($supplierCode);
            if (! method_exists($adapter, 'fetchCredits')) {
                return [
                    'success' => false,
                    'credits_balance' => null,
                    'message' => 'Credits lookup is not available for this supplier.',
                ];
            }

            return $adapter->fetchCredits($credentials);
        } catch (Throwable $e) {
            Log::warning('Attraction credits lookup failed', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'credits_balance' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param  array<string, mixed>|int|null  $result
     */
    private function isCreditsInsufficient(?string $message, mixed $result = null): bool
    {
        if (is_string($message) && stripos($message, 'Credits balance is not enough') !== false) {
            return true;
        }

        $status = null;
        if (is_int($result) || is_numeric($result)) {
            $status = (int) $result;
        } elseif (is_array($result)) {
            $status = $result['provider_status']
                ?? $result['provider']['status']
                ?? null;
        }

        return (int) $status === 7000;
    }

    /**
     * @param  array<string, mixed>  $attraction
     * @return array{0: string, 1: array<string, string|null>}
     */
    private function resolveSupplier(array $attraction): array
    {
        $supplierCode = (string) ($attraction['supplier_code'] ?? self::DEFAULT_SUPPLIER_CODE);
        if ($supplierCode === '') {
            $supplierCode = self::DEFAULT_SUPPLIER_CODE;
        }

        $cityName = trim((string) ($attraction['city'] ?? ''));
        if ($cityName !== '') {
            try {
                $resolved = $this->resolver->resolveForCityName($cityName);

                return [
                    $resolved['supplier']->code ?? $supplierCode,
                    $resolved['credentials'] ?? [],
                ];
            } catch (Throwable $e) {
                Log::warning('Attraction supplier resolve by city failed; using default credentials', [
                    'supplier_code' => $supplierCode,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return [$supplierCode, $this->supplierEnv->valuesFor($supplierCode)];
    }

    /**
     * @param  array<string, mixed>  $attraction
     * @return array<string, mixed>
     */
    private function buildCreateOrderPayload(array $attraction, ?int $tourId = null, bool $chargeCredits = false): array
    {
        $attraction = $this->enrichGuestFromTour($attraction, $tourId);

        $skuId = trim((string) ($attraction['sku_id'] ?? ''));
        if ($skuId === '') {
            $attractionId = $attraction['AttractionId'] ?? $attraction['attractionId'] ?? '';
            if (is_string($attractionId) && $attractionId !== '' && ! ctype_digit($attractionId)) {
                $skuId = $attractionId;
            }
        }

        $adults = max(0, (int) ($attraction['adultCount'] ?? 0));
        $children = max(0, (int) ($attraction['childCount'] ?? 0));
        $quantity = $adults + $children;
        if ($quantity < 1) {
            $quantity = 1;
        }

        $visitDate = $this->normalizeVisitDate($attraction['bookingDate'] ?? '');
        $ticket = $this->resolveProviderTicket($attraction, $skuId, $tourId);
        $orderSku = $ticket['sku_id'] !== '' ? $ticket['sku_id'] : $skuId;

        $currency = strtoupper(trim((string) ($attraction['currency'] ?? 'SGD')));
        if ($currency === '') {
            $currency = 'SGD';
        }

        $totalAmount = $this->providerTotalAmount($attraction, $adults, $children, $quantity, $ticket);
        // Working Reseller /order/create uses use_credits=150 (not 0). Sending 0 returns 7000
        // with no order_ref_id. Allow at least 150 credits like the working form request.
        $useCredits = (int) max(150, (int) ceil((float) $totalAmount));

        $payload = [
            'customer_name' => $this->guestName($attraction),
            'customer_email' => $this->guestEmail($attraction),
            'customer_mobile' => $this->normalizeMobile($attraction),
            'item_sku_id_1' => $orderSku,
            'item_quantity_1' => (string) $quantity,
            'item_booking_date_1' => $visitDate,
            'total_amount' => $totalAmount,
            'currency' => $currency,
            'use_credits' => (string) $useCredits,
        ];

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $attraction
     * @return array<string, mixed>
     */
    private function enrichGuestFromTour(array $attraction, ?int $tourId): array
    {
        $guest = [];
        if ($tourId) {
            $tour = Tour::query()->where('tour_id', $tourId)->first();
            if ($tour && is_array($tour->mainguest)) {
                $guest = $tour->mainguest;
            }
        }

        if (trim((string) ($attraction['fullName'] ?? '')) === '') {
            $name = trim((string) ($guest['full_name'] ?? $guest['fullName'] ?? ''));
            if ($name !== '') {
                $attraction['fullName'] = $name;
            } elseif (Auth::user() && trim((string) Auth::user()->name) !== '') {
                $attraction['fullName'] = Auth::user()->name;
            }
        }

        if (trim((string) ($attraction['email'] ?? '')) === '') {
            $email = trim((string) ($guest['email'] ?? ''));
            if ($email === '' && Auth::user()) {
                $email = trim((string) (Auth::user()->email ?? ''));
            }
            if ($email !== '') {
                $attraction['email'] = $email;
            }
        }

        if (trim((string) ($attraction['phone'] ?? $attraction['mobile'] ?? '')) === '') {
            $phone = trim((string) ($guest['phone'] ?? $guest['mobile'] ?? ''));
            $country = trim((string) ($guest['country_code'] ?? $guest['countryCode'] ?? ''));
            if ($phone === '' && Auth::user()) {
                $phone = trim((string) (Auth::user()->phone ?? Auth::user()->mobile ?? ''));
            }
            if ($phone !== '') {
                $attraction['phone'] = $phone;
            }
            if (trim((string) ($attraction['countryCode'] ?? '')) === '' && $country !== '') {
                $attraction['countryCode'] = $country;
            }
        }

        if (trim((string) ($attraction['passport'] ?? $attraction['id_num'] ?? '')) === '') {
            $passport = trim((string) ($guest['passport'] ?? $guest['passport_no'] ?? $guest['id_num'] ?? ''));
            if ($passport !== '') {
                $attraction['passport'] = $passport;
            }
        }

        return $attraction;
    }

    /**
     * @param  array<string, mixed>  $attraction
     * @return array{sku_id: string, ticket_id: string, adult_price: float}
     */
    private function resolveProviderTicket(array $attraction, string $skuId, ?int $tourId): array
    {
        $empty = ['sku_id' => '', 'ticket_id' => '', 'adult_price' => 0.0];

        $fromPayload = $this->ticketFromAttractionPayload($attraction, $skuId);
        $tickets = [];

        if ($skuId !== '') {
            try {
                [$supplierCode, $credentials] = $this->resolveSupplier($attraction);
                $adapter = $this->factory->make($supplierCode);
                if (method_exists($adapter, 'fetchTickets')) {
                    $tickets = $adapter->fetchTickets($skuId, $this->normalizeVisitDate($attraction['bookingDate'] ?? ''), $credentials);
                }
            } catch (Throwable $e) {
                Log::warning('Attraction provider ticket lookup failed', [
                    'sku_id' => $skuId,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $picked = $this->pickTicket($tickets, $attraction, $skuId);

        if ($fromPayload['sku_id'] !== '') {
            foreach ($tickets as $row) {
                $candidate = $this->ticketFields($row, $skuId);
                if ($candidate['sku_id'] !== '' && strcasecmp($candidate['sku_id'], $fromPayload['sku_id']) === 0) {
                    Log::info('Attraction provider ticket resolved', [
                        'catalog_sku' => $skuId,
                        'ticket_sku' => $candidate['sku_id'],
                        'source' => 'selected',
                        'ticket_count' => count($tickets),
                    ]);

                    return $candidate;
                }
            }

            return $fromPayload;
        }

        if ($picked['sku_id'] !== '') {
            Log::info('Attraction provider ticket resolved', [
                'catalog_sku' => $skuId,
                'ticket_sku' => $picked['sku_id'],
                'source' => 'lookup',
                'ticket_count' => count($tickets),
            ]);

            return $picked;
        }

        return $empty;
    }

    /**
     * @param  array<string, mixed>  $attraction
     * @return array{sku_id: string, ticket_id: string, adult_price: float}
     */
    private function ticketFromAttractionPayload(array $attraction, string $skuId): array
    {
        $ticketSku = trim((string) (
            $attraction['ticket_sku_id']
            ?? $attraction['item_sku_id']
            ?? $attraction['provider_sku_id']
            ?? ''
        ));
        if ($this->isSyntheticTicketId($ticketSku, $skuId)) {
            $ticketSku = '';
        }

        $ticketId = trim((string) ($attraction['provider_ticket_id'] ?? ''));
        if ($ticketId === '') {
            $rawTicketId = trim((string) ($attraction['ticketId'] ?? $attraction['ticket_id'] ?? ''));
            if ($rawTicketId !== '' && ! $this->isSyntheticTicketId($rawTicketId, $skuId)) {
                $ticketId = $rawTicketId;
            }
        }

        if ($ticketSku === '' && $ticketId !== '' && strcasecmp($ticketId, $skuId) !== 0) {
            $ticketSku = $ticketId;
        }

        return [
            'sku_id' => $ticketSku,
            'ticket_id' => $ticketId,
            'adult_price' => (float) ($attraction['ticket_details']['adult_price'] ?? 0),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $tickets
     * @param  array<string, mixed>  $attraction
     * @return array{sku_id: string, ticket_id: string, adult_price: float}
     */
    private function pickTicket(array $tickets, array $attraction, string $catalogSku): array
    {
        $empty = ['sku_id' => '', 'ticket_id' => '', 'adult_price' => 0.0];
        if ($tickets === []) {
            return $empty;
        }

        $wantName = strtolower(trim((string) ($attraction['ticketName'] ?? '')));
        if ($wantName !== '') {
            foreach ($tickets as $ticket) {
                $label = strtolower(trim((string) ($ticket['ticketName'] ?? $ticket['type'] ?? $ticket['title'] ?? '')));
                if ($label === '' || (! str_contains($label, $wantName) && ! str_contains($wantName, $label))) {
                    continue;
                }
                $candidate = $this->ticketFields($ticket, $catalogSku);
                if ($candidate['sku_id'] !== '') {
                    return $candidate;
                }
            }
        }

        $wantPremium = stripos((string) ($attraction['ticketName'] ?? $attraction['ticketId'] ?? ''), 'premium') !== false;
        $best = $empty;
        $bestPrice = $wantPremium ? -1.0 : PHP_FLOAT_MAX;
        $found = false;

        foreach ($tickets as $ticket) {
            $candidate = $this->ticketFields($ticket, $catalogSku);
            if ($candidate['sku_id'] === '' && $candidate['ticket_id'] === '') {
                continue;
            }
            $price = $candidate['adult_price'];
            $better = $wantPremium ? $price >= $bestPrice : $price <= $bestPrice;
            if (! $found || $better) {
                $bestPrice = $price;
                $best = $candidate;
                $found = true;
            }
        }

        return $found ? $best : $this->ticketFields($tickets[0], $catalogSku);
    }

    /**
     * @param  array<string, mixed>  $ticket
     * @return array{sku_id: string, ticket_id: string, adult_price: float}
     */
    private function ticketFields(array $ticket, string $catalogSku): array
    {
        $sku = trim((string) (
            $ticket['ticket_sku_id']
            ?? $ticket['item_sku_id']
            ?? $ticket['ticket_sku']
            ?? $ticket['sku_id']
            ?? $ticket['sku']
            ?? ''
        ));
        if ($this->isSyntheticTicketId($sku, $catalogSku)) {
            $sku = '';
        }

        $ticketId = trim((string) ($ticket['ticket_id'] ?? $ticket['ticketId'] ?? $ticket['id'] ?? ''));
        if ($this->isSyntheticTicketId($ticketId, $catalogSku)) {
            $ticketId = '';
        }

        $price = $ticket['price'] ?? null;
        if (is_numeric($price)) {
            $adult = (float) $price;
        } elseif (is_array($price)) {
            $adult = (float) ($price['adult'] ?? $price['price'] ?? 0);
        } else {
            $adult = (float) (
                $ticket['adult_price']
                ?? $ticket['adult']
                ?? $ticket['original_price']
                ?? $ticket['lowest_ticket_price']
                ?? 0
            );
        }

        return [
            'sku_id' => $sku !== '' ? $sku : $ticketId,
            'ticket_id' => $ticketId,
            'adult_price' => $adult,
        ];
    }

    private function isSyntheticTicketId(string $ticketId, string $skuId): bool
    {
        if ($ticketId === '') {
            return true;
        }

        return (bool) preg_match('/-(standard|premium|default)$/', $ticketId)
            || ($skuId !== '' && str_starts_with($ticketId, $skuId . '-'));
    }

    /**
     * @param  array<string, mixed>  $attraction
     */
    private function guestEmail(array $attraction): string
    {
        return trim((string) ($attraction['email'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $attraction
     */
    private function guestName(array $attraction): string
    {
        $name = trim((string) ($attraction['fullName'] ?? $attraction['name'] ?? ''));

        return $name !== '' ? $name : 'Guest';
    }

    /**
     * @param  array<string, mixed>  $attraction
     */
    private function normalizeMobile(array $attraction): string
    {
        $phone = preg_replace('/\D+/', '', (string) ($attraction['phone'] ?? $attraction['mobile'] ?? '')) ?? '';
        $countryCode = preg_replace('/\D+/', '', (string) ($attraction['countryCode'] ?? '')) ?? '';

        if ($phone === '') {
            return '';
        }

        if ($countryCode !== '' && ! str_starts_with($phone, $countryCode)) {
            return $countryCode . $phone;
        }

        return $phone;
    }

    /**
     * @param  array<string, mixed>  $attraction
     */
    private function guestIdNum(array $attraction): string
    {
        return trim((string) (
            $attraction['passport']
            ?? $attraction['passport_no']
            ?? $attraction['id_num']
            ?? $attraction['idNum']
            ?? ''
        ));
    }

    private function normalizeVisitDate(mixed $raw): string
    {
        if (is_array($raw)) {
            $raw = $raw[0] ?? '';
        }

        $raw = trim((string) $raw);
        if ($raw === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $raw)) {
            return substr($raw, 0, 10);
        }

        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $raw, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (Throwable) {
            return $raw;
        }
    }

    /**
     * Provider net total (not DMC markup). /order/create validates this against catalog price.
     *
     * @param  array<string, mixed>  $attraction
     * @param  array{sku_id?: string, ticket_id?: string, adult_price?: float}  $ticket
     */
    private function providerTotalAmount(array $attraction, int $adults, int $children, int $quantity, array $ticket = []): string
    {
        $raw = [];
        if (is_array($attraction['onlineAttractionRaw'] ?? null)) {
            $raw = $attraction['onlineAttractionRaw'];
        } elseif (is_array($attraction['raw'] ?? null)) {
            $raw = $attraction['raw'];
        }

        $low = (float) ($raw['lowest_ticket_price'] ?? $attraction['lowest_ticket_price'] ?? 0);
        $high = (float) ($raw['highest_ticket_price'] ?? $attraction['highest_ticket_price'] ?? $low);
        $wantPremium = stripos((string) ($attraction['ticketName'] ?? $attraction['ticketId'] ?? ''), 'premium') !== false;
        $unit = ($wantPremium && $high > 0) ? $high : $low;

        $ticketUnit = (float) ($ticket['adult_price'] ?? 0);
        if ($ticketUnit > 0) {
            $unit = $ticketUnit;
        }

        if ($unit <= 0) {
            $unit = (float) ($attraction['ticket_details']['adult_price'] ?? $attraction['adultPrice'] ?? 0);
        }

        $childUnit = (float) ($attraction['ticket_details']['child_price'] ?? $unit);
        $total = ($adults * $unit) + ($children * $childUnit);
        if ($total <= 0) {
            $total = $unit * $quantity;
        }

        return number_format($total, 2, '.', '');
    }

    /**
     * @param  array<string, mixed>  $attraction
     */
    private function pendingRefCacheKey(array $attraction, ?int $tourId): string
    {
        $sku = (string) ($attraction['sku_id'] ?? $attraction['AttractionId'] ?? '');
        $date = (string) ($attraction['bookingDate'] ?? '');
        $adults = (string) ($attraction['adultCount'] ?? 0);
        $children = (string) ($attraction['childCount'] ?? 0);

        return 'sg_attractions_pending_order_ref:' . md5(implode('|', [
            (string) ($tourId ?? 0),
            $sku,
            $date,
            $adults,
            $children,
        ]));
    }
}
