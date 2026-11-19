<?php

namespace App\Services\OnlinePricing;

use App\Helpers\CommonHelper;
use App\Services\Finance\WalletQueryService;
use App\Services\Payment\FxConversionCharge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Data for showing an online service price (vendor/TravClicks currency, e.g. SGD)
 * alongside the DMC's own wallet currency in the booking modals.
 *
 * Uses the same source as the wallet credit (countries.current_rate, 1 SGD = X CUR)
 * so the preview matches what the DMC wallet will be charged/credited.
 */
class DmcCurrencyPreview
{
    /**
     * @return array{dmc_id: ?int, currency: string, sgd_rates: array<string, float>, rates_updated_at: ?string}
     */
    public static function forCurrentUser(): array
    {
        $empty = ['dmc_id' => null, 'currency' => 'SGD', 'sgd_rates' => [], 'dmc_effective_sgd_rate' => null, 'rates_updated_at' => null, 'fx_charge_percent' => null];

        try {
            $user = auth()->user();
            if (! $user) {
                return $empty;
            }

            $dmcId = null;
            try {
                $dmcId = CommonHelper::getDmcId($user) ?: null;
            } catch (Throwable) {
                $dmcId = null;
            }
            $dmcId = $dmcId ?: CommonHelper::resolveMasterDmcId($user);
            if (! $dmcId) {
                return $empty;
            }

            $currency = strtoupper(app(WalletQueryService::class)->resolveDmcWalletCurrency((int) $dmcId));

            $rows = DB::select(
                'SELECT DISTINCT ON (UPPER(currency)) UPPER(currency) AS cur, current_rate, rate_updated_at
                 FROM countries
                 WHERE current_rate > 0 AND currency IS NOT NULL
                 ORDER BY UPPER(currency), rate_updated_at DESC NULLS LAST'
            );

            $rates = ['SGD' => 1.0];
            $updated = null;
            foreach ($rows as $row) {
                $cur = (string) $row->cur;
                if (preg_match('/^[A-Z]{3}$/', $cur) && $cur !== 'SGD') {
                    $rates[$cur] = (float) $row->current_rate;
                }
                if ($row->rate_updated_at && ($updated === null || $row->rate_updated_at > $updated)) {
                    $updated = (string) $row->rate_updated_at;
                }
            }

            $currency = $currency !== '' ? $currency : 'SGD';
            $percent = app(FxConversionCharge::class)->percent();
            $isTravclicks = (int) ($user->role_id ?? 0) === 1;

            // Effective rate = base × (1 + conversion commission %), same as the wallet charge.
            // The commission is folded into the DMC-currency pivot rate, so the page never
            // needs the % itself; only TravClicks admins receive it (to show "incl. X%").
            $dmcBase = $currency === 'SGD' ? 1.0 : (float) ($rates[$currency] ?? 0);

            return [
                'dmc_id' => (int) $dmcId,
                'currency' => $currency,
                'sgd_rates' => $rates,
                'dmc_effective_sgd_rate' => $dmcBase > 0 ? $dmcBase * (1 + $percent / 100) : null,
                'rates_updated_at' => $updated,
                'fx_charge_percent' => $isTravclicks ? $percent : null,
            ];
        } catch (Throwable $e) {
            Log::warning('dmc_currency_preview_failed', ['error' => $e->getMessage()]);

            return $empty;
        }
    }
}
