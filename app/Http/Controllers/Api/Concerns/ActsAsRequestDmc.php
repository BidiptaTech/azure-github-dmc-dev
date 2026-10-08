<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\User;
use App\Services\ApiEnvironmentResolver;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Agent SPA sends an operating dmc_id. OnlineHotelAggregator / OnlineAttractionAggregator
 * read Auth::user() for Master DMC online_api/live_api and DMC markups — so we briefly
 * run the aggregator as that DMC without changing the service layer.
 */
trait ActsAsRequestDmc
{
    protected function resolveRequestDmc(int $dmcId): User
    {
        if ($dmcId <= 0) {
            throw new RuntimeException('A valid dmc_id is required for online hotel and attraction APIs.');
        }

        $dmc = User::query()->where('userId', $dmcId)->first();

        if (! $dmc) {
            throw new RuntimeException("DMC [{$dmcId}] was not found.");
        }

        return $dmc;
    }

    /**
     * @template T
     * @param  callable(): T  $callback
     * @return T
     */
    protected function withDmcAuthContext(User $dmc, callable $callback): mixed
    {
        app(ApiEnvironmentResolver::class)->assertOnlineApiEnabled($dmc);

        $guard = Auth::guard();
        $previous = $guard->user();
        $guard->setUser($dmc);

        try {
            return $callback();
        } finally {
            if ($previous) {
                $guard->setUser($previous);
            } else {
                $guard->forgetUser();
            }
        }
    }
}
