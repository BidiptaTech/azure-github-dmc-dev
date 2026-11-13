<?php

namespace App\Services;

use App\Helpers\CommonHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\TaxHelper;
use App\Models\Country;
use App\Models\DmcInvoice;
use App\Models\DmcOffer;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\Tour;
use App\Models\User;
use Carbon\Carbon;

class TravclicksInvoiceService
{
    /**
     * Build monthly Travclicks invoice payload for a DMC.
     *
     * @return array{
     *   month:int, year:int, period_label:string, invoice_number:string,
     *   dmc:User, travclicks:array, base_prices:DmcInvoice,
     *   lines:array<int, array>, totals:array, currency:array
     * }
     */
    public function build(User $authUser, int $month, int $year): array
    {
        $dmcId = CommonHelper::getDmcId($authUser);
        if ($dmcId === null) {
            $dmcId = (int) $authUser->userId;
        }
        $dmcId = (int) $dmcId;

        $dmc = $this->resolveRootDmc($dmcId, $authUser);
        if (!$dmc) {
            throw new \RuntimeException('Unable to resolve DMC account for this user.');
        }

        $basePrices = DmcInvoice::current();
        if (!$basePrices) {
            throw new \RuntimeException('Travclicks invoice prices are not configured yet. Please contact Travclicks admin.');
        }

        $periodStart = Carbon::create($year, $month, 1)->startOfDay();
        $periodEnd = (clone $periodStart)->endOfMonth();

        // TESTING: end-date-before-today filter temporarily removed.
        // Month filter uses tour start (check_in_time) within the selected period.
        $tours = Tour::query()
            ->where('tour_status', 'Actual')
            ->whereNotNull('check_in_time')
            ->whereBetween('check_in_time', [$periodStart, $periodEnd])
            ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmcId, $authUser, 'dmc_id', 'destination'))
            ->orderBy('check_in_time')
            ->orderBy('tour_id')
            ->get([
                'id',
                'tour_id',
                'display_id',
                'dmc_id',
                'adult',
                'child',
                'infant',
                'check_in_time',
                'check_out_time',
                'tour_status',
                'destination',
                'city',
                'mainguest',
            ]);

        $defaultAdult = (float) $basePrices->adult_price;
        $defaultChild = (float) $basePrices->child_price;
        $defaultInfant = (float) $basePrices->infant_price;

        $lines = [];
        $grossTotal = 0.0;
        $discountTotal = 0.0;
        $netTotal = 0.0;

        foreach ($tours as $tour) {
            $adult = (int) ($tour->adult ?? 0);
            $child = (int) ($tour->child ?? 0);
            $infant = (int) ($tour->infant ?? 0);

            $startDate = $tour->check_in_time ? Carbon::parse($tour->check_in_time) : null;
            $offer = $startDate
                ? DmcOffer::forDmcOnDate((int) ($tour->dmc_id ?: $dmcId), $startDate)
                : null;

            // Fallback: offer stored against root DMC id
            if (!$offer && $startDate && (int) $tour->dmc_id !== $dmcId) {
                $offer = DmcOffer::forDmcOnDate($dmcId, $startDate);
            }

            $rateAdult = $defaultAdult;
            $rateChild = $defaultChild;
            $rateInfant = $defaultInfant;
            $hasOffer = false;

            if ($offer) {
                $hasOffer = true;
                $rateAdult = (float) $offer->adult_price;
                $rateChild = (float) $offer->child_price;
                $rateInfant = (float) $offer->infant_price;
            }

            $gross = ($adult * $defaultAdult) + ($child * $defaultChild) + ($infant * $defaultInfant);
            $charged = ($adult * $rateAdult) + ($child * $rateChild) + ($infant * $rateInfant);
            $discount = max(0, round($gross - $charged, 2));
            $lineNet = round($charged, 2);

            $grossTotal += $gross;
            $discountTotal += $discount;
            $netTotal += $lineNet;

            $lines[] = [
                'tour_id' => $tour->tour_id,
                'display_id' => (string) ($tour->display_id ?: $tour->tour_id),
                'guest' => $this->resolveGuestName($tour->mainguest),
                'destination' => $tour->destination,
                'city' => $tour->city,
                'start_date' => $startDate ? $startDate->format('Y-m-d') : '—',
                'end_date' => $tour->check_out_time ? Carbon::parse($tour->check_out_time)->format('Y-m-d') : '—',
                'adult' => $adult,
                'child' => $child,
                'infant' => $infant,
                'default_adult' => $defaultAdult,
                'default_child' => $defaultChild,
                'default_infant' => $defaultInfant,
                'rate_adult' => $rateAdult,
                'rate_child' => $rateChild,
                'rate_infant' => $rateInfant,
                'gross' => round($gross, 2),
                'discount' => $discount,
                'net' => $lineNet,
                'has_offer' => $hasOffer,
                'offer_id' => $offer->dmc_offer_id ?? $offer->id ?? null,
            ];
        }

        $grossTotal = round($grossTotal, 2);
        $discountTotal = round($discountTotal, 2);
        $netTotal = round($netTotal, 2);

        $taxRows = $this->activeTaxesForDmc($dmcId);
        $taxResult = TaxHelper::calculateTourTaxes($netTotal, $taxRows, 1, 1);
        $taxTotal = round((float) ($taxResult['total_tax'] ?? 0), 2);
        $grandTotal = round($netTotal + $taxTotal, 2);

        $dmcCurrency = strtoupper(CommonHelper::resolveDmcCurrencyForInvoice($dmc));
        $exchangeRate = null;
        $exchangeRateDisplay = null;
        $commissionPercent = 0.0;
        $commissionOnRate = null;
        $effectiveRate = null;
        $dmcAmount = null;

        if ($dmcCurrency !== 'SGD') {
            $rawRate = CurrencyHelper::getExchangeRate('SGD', $dmcCurrency);
            $commissionPercent = $this->resolveCountryCommissionPercent($dmc);

            if ($rawRate !== null && (float) $rawRate > 0) {
                // Display & calculate with 2 decimal places; commission % is baked into the rate.
                $exchangeRate = round((float) $rawRate, 2);
                $commissionOnRate = round($exchangeRate * ($commissionPercent / 100), 2);
                $effectiveRate = round($exchangeRate + $commissionOnRate, 2);
                $dmcAmount = round($grandTotal * $effectiveRate, 2);
                $exchangeRateDisplay = $effectiveRate;
            }
        }

        return [
            'month' => $month,
            'year' => $year,
            'period_label' => $periodStart->format('F Y'),
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'generated_at' => Carbon::now()->format('Y-m-d H:i'),
            'invoice_number' => sprintf('TCI-%s-%s-%s', $dmcId, $periodStart->format('Ym'), substr(md5($dmcId . $month . $year), 0, 6)),
            'dmc' => $dmc,
            'travclicks' => $this->travclicksProfile(),
            'base_prices' => $basePrices,
            'lines' => $lines,
            'totals' => [
                'gross' => $grossTotal,
                'discount' => $discountTotal,
                'net' => $netTotal,
                'tax_breakdown' => $taxResult['breakdown'] ?? [],
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'has_tax' => $taxTotal > 0,
                'tour_count' => count($lines),
            ],
            'currency' => [
                'base' => 'SGD',
                'dmc' => $dmcCurrency,
                'show_conversion' => $dmcCurrency !== 'SGD',
                'exchange_rate' => $exchangeRate,
                'commission_percent' => $commissionPercent,
                'commission_on_rate' => $commissionOnRate,
                'effective_rate' => $effectiveRate ?? $exchangeRateDisplay,
                'dmc_amount' => $dmcAmount,
            ],
        ];
    }

    /**
     * Country commission % for the DMC operating country (used on FX rate).
     */
    protected function resolveCountryCommissionPercent(User $dmc): float
    {
        $countryName = CommonHelper::resolveUserOperatingCountry($dmc)
            ?: trim((string) explode(',', (string) ($dmc->country ?? ''))[0]);

        if ($countryName === '') {
            return 0.0;
        }

        $country = Country::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($countryName)])
            ->first();

        return round((float) ($country->commission_percentage ?? 0), 2);
    }

    protected function resolveRootDmc(int $dmcId, User $authUser): ?User
    {
        $user = User::where('userId', $dmcId)->first() ?: $authUser;
        $root = $user;
        $visited = [];
        while ($root && (int) $root->role_id !== 11 && $root->created_by && !in_array($root->created_by, $visited, true)) {
            $visited[] = $root->created_by;
            $parent = User::where('userId', $root->created_by)->first();
            if (!$parent) {
                break;
            }
            $root = $parent;
        }

        return $root ?: $user;
    }

    protected function activeTaxesForDmc(int $dmcId): array
    {
        return Tax::where('dmc_id', $dmcId)
            ->where('is_active', 1)
            ->orderBy('tax_id')
            ->get()
            ->map(function (Tax $tax) {
                return [
                    'tax_name' => $tax->tax_name,
                    'tax_type' => $tax->tax_type,
                    'tax_value' => $tax->tax_value,
                    'calculate_on' => $tax->calculate_on ?? 'total',
                    'if_fixed' => $tax->if_fixed,
                ];
            })
            ->values()
            ->all();
    }

    protected function travclicksProfile(): array
    {
        $get = function (string $name) {
            $row = Setting::where('name', $name)->where('status', 1)->first()
                ?: Setting::where('name', $name)->first();

            return $row->value ?? null;
        };

        return [
            'name' => $get('name') ?: 'Travclicks Technologies Pte Ltd',
            'logo' => $get('logo'),
            'email' => $get('support_email') ?: $get('email'),
            'phone' => $get('support_phone') ?: $get('phone'),
            'address' => $get('address') ?: $get('company_address'),
        ];
    }

    /**
     * mainguest is often JSON/array — return a plain display name string.
     */
    protected function resolveGuestName(mixed $mainguest): ?string
    {
        if ($mainguest === null || $mainguest === '') {
            return null;
        }

        if (is_string($mainguest)) {
            $decoded = json_decode($mainguest, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $mainguest = $decoded;
            } else {
                $name = trim($mainguest);

                return $name !== '' ? $name : null;
            }
        }

        if (!is_array($mainguest)) {
            return null;
        }

        $name = trim((string) (
            $mainguest['full_name']
            ?? $mainguest['fullName']
            ?? $mainguest['guest_name']
            ?? $mainguest['name']
            ?? ''
        ));

        return $name !== '' ? $name : null;
    }

    public static function encodeLogo(?string $logo): ?string
    {
        if (!$logo) {
            return null;
        }

        try {
            if (preg_match('/^data:image\//i', $logo)) {
                return $logo;
            }

            $content = preg_match('/^https?:\/\//i', $logo)
                ? @file_get_contents($logo)
                : @file_get_contents(public_path(ltrim($logo, '/')));

            if (!$content) {
                return null;
            }

            $mime = 'image/png';
            if (preg_match('/\.(jpe?g)$/i', $logo)) {
                $mime = 'image/jpeg';
            } elseif (preg_match('/\.gif$/i', $logo)) {
                $mime = 'image/gif';
            } elseif (preg_match('/\.webp$/i', $logo)) {
                $mime = 'image/webp';
            }

            return 'data:' . $mime . ';base64,' . base64_encode($content);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
