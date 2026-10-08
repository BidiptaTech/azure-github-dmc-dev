<?php

namespace App\Services;

use App\Helpers\CommonHelper;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Chooses demo vs live third-party API credentials from the current user's Master DMC.
 *
 * online_api = false → third-party calls are blocked.
 * online_api = true + live_api = false → DEMO.
 * online_api = true + live_api = true → LIVE.
 */
class ApiEnvironmentResolver
{
    public const DEMO = 'demo';

    public const LIVE = 'live';

    /**
     * Environment for a new third-party request (search, availability, create-order).
     *
     * Console / unauthenticated callers stay on DEMO so existing artisan syncs keep working.
     */
    public function resolve(?User $user = null): string
    {
        $user = $user ?: Auth::user();

        if (! $user) {
            return self::DEMO;
        }

        $this->assertOnlineApiEnabled($user);

        $master = $this->masterDmc($user);

        return $this->isLive($master) ? self::LIVE : self::DEMO;
    }

    /**
     * Prefer the environment stored on the booking/order so a demo enquiry is
     * rechecked and confirmed against DEMO even if Live API is turned on later.
     *
     * @param  array<string, mixed>  $record
     */
    public function resolveForRecord(array $record, ?User $user = null): string
    {
        $this->assertOnlineApiEnabled($user ?: Auth::user());

        $stored = $this->storedEnvironment($record);

        return $stored ?? $this->resolve($user);
    }

    public function assertOnlineApiEnabled(?User $user = null): void
    {
        $user = $user ?: Auth::user();

        if (! $user) {
            return;
        }

        if ($this->isOnlineApiEnabled($user)) {
            return;
        }

        throw new RuntimeException(
            'Online hotel and attraction APIs are disabled for your Master DMC. Enable Online API in user settings.'
        );
    }

    public function isOnlineApiEnabled(?User $user = null): bool
    {
        $user = $user ?: Auth::user();
        $master = $this->masterDmc($user);

        return $master ? (bool) $master->online_api : false;
    }

    public function normalize(?string $environment): ?string
    {
        $environment = strtolower(trim((string) $environment));

        return in_array($environment, [self::DEMO, self::LIVE], true) ? $environment : null;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    public function storedEnvironment(array $record): ?string
    {
        $candidates = [
            $record['api_environment'] ?? null,
            is_array($record['onlineHotelBooking'] ?? null)
                ? ($record['onlineHotelBooking']['api_environment'] ?? null)
                : null,
            is_array($record['onlineAttractionRaw'] ?? null)
                ? ($record['onlineAttractionRaw']['api_environment'] ?? null)
                : null,
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalize(is_string($candidate) ? $candidate : null);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    public function masterDmc(?User $user = null): ?User
    {
        $user = $user ?: Auth::user();

        if (! $user) {
            return null;
        }

        $masterId = CommonHelper::resolveMasterDmcId($user);

        if (! $masterId) {
            return null;
        }

        if ((int) $user->userId === (int) $masterId) {
            return $user;
        }

        return User::query()
            ->where('userId', $masterId)
            ->first(['userId', 'role_id', 'online_api', 'live_api', 'demo_api']);
    }

    private function isLive(?User $master): bool
    {
        return $master ? (bool) ($master->live_api ?? false) : false;
    }
}
