<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Models\Tour;
use App\Services\Finance\WalletLedgerService;
use App\Services\Finance\WalletQueryService;
use App\Services\Payment\PaymentEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Per-order commission statement.
 * Travclicks sees API cost and its own online commission (statement only).
 * DMC / multi-role staff see DMC commission + wallet credit amount for online services.
 */
class CommissionReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $viewer = $this->viewer($user);
        $operatingDmcId = $this->operatingDmcId($user, $viewer);
        $tourId = $request->integer('tour_id') ?: null;

        $tours = $this->settledTours($user, $viewer, $operatingDmcId, $tourId);
        $lines = collect();
        $totals = $this->emptyTotals();
        $opened = $tourId ? $tours->firstWhere('tour_id', (int) $tourId) : null;
        $displayCurrency = strtoupper((string) ($opened->currency_code ?? 'SGD'));

        if (Schema::hasTable('order_commission_lines')) {
            if ($tourId) {
                $lineQuery = DB::table('order_commission_lines')->where('tour_id', $tourId)->orderBy('id');
                $this->scopeLines($lineQuery, $operatingDmcId, $viewer);
                $lines = $lineQuery->get()->map(fn ($line) => $this->enrichLine($line, $displayCurrency, $viewer, $operatingDmcId));
                $totals = $this->totals($lines, $opened);
            }
        }

        $selectedTour = $tourId ? Tour::query()->with(['agent', 'dmc'])->where('tour_id', $tourId)->first() : null;
        // Scoping: only show payments for tours already visible to this viewer in the list.
        $payments = $selectedTour && $opened
            ? (json_decode((string) $selectedTour->payment_details, true) ?: [])
            : [];

        return view('finance.commission-report', [
            'payments' => is_array($payments) ? array_values(array_filter($payments, 'is_array')) : [],
            'viewer' => $viewer,
            'tours' => $tours,
            'lines' => $lines,
            'totals' => $totals,
            'tourId' => $tourId,
            'selectedTour' => $selectedTour,
            'operatingDmcId' => $operatingDmcId,
            'displayCurrency' => $displayCurrency,
        ]);
    }

    /**
     * @param  Collection<int, object>  $lines
     * @return array<string, float>
     */
    protected function totals($lines, ?object $opened = null): array
    {
        if ($opened) {
            return [
                'charged_amount' => (float) ($opened->charged_amount ?? 0),
                'agent_price' => (float) ($opened->charged_amount ?? 0),
                'dmc_commission' => (float) ($opened->dmc_commission ?? 0),
                'travclicks_commission' => (float) ($opened->travclicks_commission ?? 0),
                'api_cost' => (float) ($opened->api_cost ?? 0),
                'conversion_charge' => (float) ($opened->conversion_charge ?? 0),
                'wallet_credited_amount' => (float) ($opened->wallet_credited_amount ?? 0),
                'charged_currency' => (string) ($opened->currency_code ?? 'SGD'),
                'currency_code' => (string) ($opened->currency_code ?? 'SGD'),
                'conversion_currency' => (string) ($opened->currency_code ?? 'SGD'),
            ];
        }

        // Detail totals must not double-count wallet settlement + online_approve for the same tour.
        $agg = $this->aggregateTourCommissionLines($lines->all());
        $wallet = 0.0;
        foreach ($lines as $line) {
            $wallet += (float) ($line->wallet_credited_amount ?? 0);
        }

        return [
            'charged_amount' => $agg['charged_amount'],
            'agent_price' => $agg['charged_amount'],
            'dmc_commission' => $agg['dmc_commission'],
            'travclicks_commission' => $agg['travclicks_commission'],
            'api_cost' => $agg['api_cost'],
            'conversion_charge' => $agg['conversion_charge'],
            'wallet_credited_amount' => round($wallet, 4),
            'charged_currency' => $agg['charged_currency'],
            'currency_code' => $agg['currency_code'],
            'conversion_currency' => $agg['conversion_currency'],
        ];
    }

    /**
     * @return array<string, float>
     */
    protected function emptyTotals(): array
    {
        return [
            'charged_amount' => 0.0,
            'agent_price' => 0.0,
            'dmc_commission' => 0.0,
            'travclicks_commission' => 0.0,
            'api_cost' => 0.0,
            'conversion_charge' => 0.0,
            'wallet_credited_amount' => 0.0,
        ];
    }

    protected function settledTours($user, string $viewer, ?int $operatingDmcId, ?int $tourId): Collection
    {
        $eligibility = app(PaymentEligibilityService::class);
        $byTourId = [];
        $sortRank = []; // higher = show first (recent online commissions)

        // 1) Commission lines first — online approve + payment settlement (Travclicks + DMC)
        if (Schema::hasTable('order_commission_lines') && in_array($viewer, ['travclicks', 'dmc'], true)) {
            $lineQuery = DB::table('order_commission_lines')->orderByDesc('id');

            if ($tourId) {
                $lineQuery->where('tour_id', $tourId);
            } else {
                // Pull enough recent lines to cover ~200 tours after grouping.
                $lineQuery->limit(2000);
            }

            if ($viewer === 'dmc') {
                $this->scopeLines($lineQuery, $operatingDmcId, $viewer);
            }

            $grouped = [];
            foreach ($lineQuery->get() as $line) {
                $tid = (int) ($line->tour_id ?? 0);
                if ($tid <= 0) {
                    continue;
                }
                $grouped[$tid][] = $line;
            }

            $count = 0;
            foreach ($grouped as $tid => $tourLines) {
                if (! $tourId && $count >= 200) {
                    break;
                }
                $count++;

                $agg = $this->aggregateTourCommissionLines($tourLines);
                $walletAgg = $this->walletCreditsForTour((int) $tid);
                $row = (object) [
                    'tour_id' => (int) $tid,
                    'currency_code' => $agg['currency_code'],
                    'charged_amount' => $agg['charged_amount'],
                    'charged_currency' => $agg['charged_currency'],
                    'dmc_commission' => $agg['dmc_commission'],
                    'markup_label' => $agg['has_online'] ? 'Online' : 'Paid',
                    'base_amount' => $agg['charged_amount'],
                    'travclicks_commission' => $agg['travclicks_commission'],
                    'api_cost' => $agg['api_cost'],
                    'conversion_charge' => $agg['conversion_charge'],
                    'conversion_currency' => $agg['conversion_currency'],
                    'wallet_credited_amount' => $walletAgg['amount'],
                    'wallet_credited_currency' => $walletAgg['currency'],
                    'has_online_commission' => $agg['has_online'],
                    'latest_line_id' => $agg['latest_id'],
                    'order_currency' => $agg['currency_code'],
                ];
                $byTourId[$tid] = $this->unifyTourRowCurrency($row, $viewer, $operatingDmcId);
                $sortRank[$tid] = 1_000_000_000 + $agg['latest_id'];
            }
        }

        // 2) Fully paid tours (legacy DMC markup) — fill gaps / enrich
        $query = Tour::query()
            ->whereNotNull('payment_details')
            ->orderByDesc('updated_at');

        if ($tourId) {
            $query->where('tour_id', $tourId);
        } else {
            $query->limit(150);
        }

        if ($viewer !== 'travclicks') {
            if ($operatingDmcId) {
                $query->where(function ($q) use ($operatingDmcId) {
                    $q->where('dmc_id', $operatingDmcId)
                        ->orWhere('master_dmc_id', $operatingDmcId)
                        ->orWhere('agent_id', $operatingDmcId);
                });
            } else {
                $ownerId = (int) ($user->userId ?? 0);
                $query->where(function ($q) use ($ownerId) {
                    $q->where('dmc_id', $ownerId)
                        ->orWhere('master_dmc_id', $ownerId)
                        ->orWhere('agent_id', $ownerId);
                });
            }
        }

        foreach ($query->get() as $tour) {
            $row = $eligibility->fullyPaidMarkup($tour);
            if (! $row) {
                continue;
            }
            $tid = (int) $row['tour_id'];
            if (isset($byTourId[$tid])) {
                if ((float) ($byTourId[$tid]->dmc_commission ?? 0) <= 0) {
                    $byTourId[$tid]->dmc_commission = (float) $row['dmc_commission'];
                }
                if ((float) ($byTourId[$tid]->charged_amount ?? 0) <= 0) {
                    $byTourId[$tid]->charged_amount = (float) $row['charged_amount'];
                }
                continue;
            }

            $legacy = (object) array_merge($row, [
                'wallet_credited_amount' => 0.0,
                'wallet_credited_currency' => null,
                'charged_currency' => (string) ($row['currency_code'] ?? 'SGD'),
                'conversion_currency' => null,
                'has_online_commission' => false,
                'latest_line_id' => 0,
                'travclicks_commission' => (float) ($row['travclicks_commission'] ?? 0),
                'api_cost' => (float) ($row['api_cost'] ?? 0),
                'conversion_charge' => (float) ($row['conversion_charge'] ?? 0),
                'order_currency' => (string) ($row['currency_code'] ?? 'SGD'),
            ]);
            $byTourId[$tid] = $this->unifyTourRowCurrency($legacy, $viewer, $operatingDmcId);
            $sortRank[$tid] = $tid; // older fully-paid rows below recent commission lines
        }

        return collect(array_values($byTourId))
            ->sortByDesc(fn ($row) => $sortRank[(int) $row->tour_id] ?? (int) $row->tour_id)
            ->values();
    }

    /**
     * One currency per row so the statement is readable.
     * DMC view → DMC home wallet currency (IDR / SGD).
     * Travclicks view → order currency.
     */
    protected function unifyTourRowCurrency(object $row, string $viewer, ?int $operatingDmcId): object
    {
        $tourId = (int) ($row->tour_id ?? 0);
        $orderCurrency = strtoupper((string) ($row->order_currency ?? $row->currency_code ?? 'SGD'));
        $chargedCurrency = strtoupper((string) ($row->charged_currency ?? $orderCurrency));
        $conversionCurrency = strtoupper((string) ($row->conversion_currency ?? $chargedCurrency));
        $walletCurrency = strtoupper((string) ($row->wallet_credited_currency ?? ''));

        $dmcId = $tourId > 0 ? (int) (Tour::query()->where('tour_id', $tourId)->value('dmc_id') ?? 0) : 0;
        if ($dmcId <= 0 && $operatingDmcId) {
            $dmcId = (int) $operatingDmcId;
        }

        $displayCurrency = $orderCurrency;
        if ($viewer === 'dmc' && $dmcId > 0) {
            $displayCurrency = strtoupper(app(WalletQueryService::class)->resolveDmcWalletCurrency($dmcId));
        }

        $dmcCommissionOrder = (float) ($row->dmc_commission ?? 0);
        $expectedWallet = $this->convertAmount($dmcCommissionOrder, $orderCurrency, $displayCurrency);
        $walletAmount = (float) ($row->wallet_credited_amount ?? 0);

        // Older wrong posts: credited SGD figure into IDR wallet (29 → 29). Prefer converted amount for display.
        if (
            $viewer === 'dmc'
            && $expectedWallet > 0
            && $walletAmount > 0
            && $orderCurrency !== $displayCurrency
            && abs($walletAmount - $dmcCommissionOrder) < 0.05
        ) {
            $walletAmount = $expectedWallet;
            $walletCurrency = $displayCurrency;
        } elseif ($walletAmount > 0 && $walletCurrency !== '' && $walletCurrency !== $displayCurrency) {
            $walletAmount = $this->convertAmount($walletAmount, $walletCurrency, $displayCurrency);
            $walletCurrency = $displayCurrency;
        } elseif ($walletAmount <= 0 && $expectedWallet > 0 && ! empty($row->has_online_commission)) {
            $walletAmount = $expectedWallet;
            $walletCurrency = $displayCurrency;
        } else {
            $walletCurrency = $displayCurrency;
        }

        $row->currency_code = $displayCurrency;
        $row->charged_amount = $this->convertAmount((float) ($row->charged_amount ?? 0), $chargedCurrency, $displayCurrency);
        $row->charged_currency = $displayCurrency;
        $row->dmc_commission = $this->convertAmount($dmcCommissionOrder, $orderCurrency, $displayCurrency);
        $row->travclicks_commission = $this->convertAmount((float) ($row->travclicks_commission ?? 0), $orderCurrency, $displayCurrency);
        $row->api_cost = $this->convertAmount((float) ($row->api_cost ?? 0), $orderCurrency, $displayCurrency);
        $row->conversion_charge = $this->convertAmount((float) ($row->conversion_charge ?? 0), $conversionCurrency, $displayCurrency);
        $row->conversion_currency = $displayCurrency;
        $row->wallet_credited_amount = round($walletAmount, 4);
        $row->wallet_credited_currency = $displayCurrency;
        $row->base_amount = $row->charged_amount;
        $row->display_currency = $displayCurrency;
        $row->order_currency = $orderCurrency;

        return $row;
    }

    protected function convertAmount(float $amount, string $from, string $to): float
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));
        if ($amount == 0.0) {
            return 0.0;
        }
        if ($from === '' || $to === '' || $from === $to) {
            return round($amount, 4);
        }
        $converted = Country::convertAmount($amount, $from, $to);

        return round((float) ($converted ?? $amount), 4);
    }

    /**
     * Prefer online_approve for commission math (avoids double-count with wallet settlement).
     * Paid/total amount uses actual wallet debit currency when FX payment happened.
     *
     * @param  array<int, object>  $lines
     * @return array{
     *   currency_code: string,
     *   charged_amount: float,
     *   charged_currency: string,
     *   dmc_commission: float,
     *   travclicks_commission: float,
     *   api_cost: float,
     *   conversion_charge: float,
     *   conversion_currency: ?string,
     *   has_online: bool,
     *   latest_id: int
     * }
     */
    protected function aggregateTourCommissionLines(array $lines): array
    {
        $latestId = 0;
        $approve = [];
        $walletService = [];
        $fx = [];
        $other = [];

        foreach ($lines as $line) {
            $latestId = max($latestId, (int) ($line->id ?? 0));
            $method = (string) ($line->payment_method ?? '');
            $channel = (string) ($line->channel ?? '');
            if ($method === 'online_approve') {
                $approve[] = $line;
            } elseif ($channel === 'fx' || (float) ($line->conversion_charge ?? 0) > 0) {
                $fx[] = $line;
            } elseif ($method === 'wallet') {
                $walletService[] = $line;
            } else {
                $other[] = $line;
            }
        }

        $commissionSource = $approve !== [] ? $approve : ($walletService !== [] ? $walletService : $other);

        $dmc = 0.0;
        $tc = 0.0;
        $api = 0.0;
        $orderCurrency = null;
        foreach ($commissionSource as $line) {
            $dmc += (float) ($line->dmc_commission ?? 0);
            $tc += (float) ($line->travclicks_commission ?? 0);
            $api += (float) ($line->api_cost ?? 0);
            $meta = $this->decodeMeta($line->metadata ?? null);
            $orderCurrency = $orderCurrency
                ?? ($meta['order_currency'] ?? null)
                ?? ($line->currency_code ?? null);
        }

        $conversion = 0.0;
        $conversionCurrency = null;
        foreach ($fx as $line) {
            $conversion += (float) ($line->conversion_charge ?? 0);
            $conversionCurrency = $conversionCurrency ?? ($line->currency_code ?? null);
            $meta = $this->decodeMeta($line->metadata ?? null);
            $orderCurrency = $orderCurrency ?? ($meta['order_currency'] ?? null);
        }

        $paidAmount = null;
        $paidCurrency = null;
        foreach (array_merge($walletService, $fx, $approve, $other) as $line) {
            $meta = $this->decodeMeta($line->metadata ?? null);
            if (isset($meta['wallet_charged_amount']) && is_numeric($meta['wallet_charged_amount']) && (float) $meta['wallet_charged_amount'] > 0) {
                $paidAmount = (float) $meta['wallet_charged_amount'];
                $paidCurrency = strtoupper((string) ($meta['wallet_currency'] ?? $meta['paid_currency'] ?? $line->currency_code ?? ''));
                break;
            }
            if (isset($meta['paid_amount']) && is_numeric($meta['paid_amount']) && (float) $meta['paid_amount'] > 0) {
                $paidAmount = (float) $meta['paid_amount'];
                $paidCurrency = strtoupper((string) ($meta['paid_currency'] ?? $meta['wallet_currency'] ?? $line->currency_code ?? ''));
                break;
            }
        }

        // Fallback for older wallet FX rows: destination_amount from snapshot
        if (($paidAmount === null || $paidAmount <= 0) && $fx !== [] && Schema::hasTable('currency_conversion_snapshots')) {
            foreach ($fx as $line) {
                $meta = $this->decodeMeta($line->metadata ?? null);
                $snapId = (int) ($meta['fx_snapshot_id'] ?? 0);
                if ($snapId <= 0) {
                    continue;
                }
                $snap = DB::table('currency_conversion_snapshots')->where('id', $snapId)->first();
                if ($snap && (float) ($snap->destination_amount ?? 0) > 0) {
                    $paidAmount = (float) $snap->destination_amount;
                    $paidCurrency = strtoupper((string) ($snap->destination_currency ?? $line->currency_code ?? ''));
                    break;
                }
            }
        }

        if ($paidAmount === null || $paidAmount <= 0) {
            // Same-currency wallet/stripe: sum charged from commission source only (not fx + approve duplicate)
            $paidAmount = 0.0;
            foreach ($commissionSource as $line) {
                $paidAmount += (float) ($line->charged_amount ?? 0);
            }
            $paidCurrency = strtoupper((string) ($orderCurrency ?: ($commissionSource[0]->currency_code ?? 'SGD')));
        }

        $orderCurrency = strtoupper((string) ($orderCurrency ?: 'SGD'));
        $paidCurrency = strtoupper((string) ($paidCurrency ?: $orderCurrency));
        $hasOnline = $approve !== [] || $tc > 0;

        return [
            'currency_code' => $orderCurrency,
            'charged_amount' => round($paidAmount, 4),
            'charged_currency' => $paidCurrency,
            'dmc_commission' => round($dmc, 4),
            'travclicks_commission' => round($tc, 4),
            'api_cost' => round($api, 4),
            'conversion_charge' => round($conversion, 4),
            'conversion_currency' => $conversionCurrency ? strtoupper((string) $conversionCurrency) : null,
            'has_online' => $hasOnline,
            'latest_id' => $latestId,
        ];
    }

    /**
     * Wallet credit in the DMC home currency (never raw order-currency commission).
     *
     * @return array{amount: float, currency: ?string}
     */
    protected function walletCreditsForTour(int $tourId): array
    {
        if ($tourId <= 0) {
            return ['amount' => 0.0, 'currency' => null];
        }

        // 1) Ledger is source of truth (already converted into DMC wallet currency)
        $dmcId = (int) (Tour::query()->where('tour_id', $tourId)->value('dmc_id') ?? 0);
        if ($dmcId > 0) {
            $fromLedger = app(WalletLedgerService::class)->tourCommissionWalletCredit($tourId, 'dmc', $dmcId);
            if ($fromLedger['amount'] > 0) {
                return [
                    'amount' => $fromLedger['amount'],
                    'currency' => $fromLedger['currency'] ?: app(WalletQueryService::class)->resolveDmcWalletCurrency($dmcId),
                ];
            }
        }

        if (! Schema::hasTable('order_commission_lines')) {
            return ['amount' => 0.0, 'currency' => null];
        }

        $rows = DB::table('order_commission_lines')
            ->where('tour_id', $tourId)
            ->get(['metadata', 'currency_code', 'dmc_commission', 'payment_method']);

        $sum = 0.0;
        $currency = null;
        foreach ($rows as $row) {
            $meta = $this->decodeMeta($row->metadata ?? null);
            if (isset($meta['wallet_credited_amount']) && is_numeric($meta['wallet_credited_amount']) && (float) $meta['wallet_credited_amount'] > 0) {
                $sum += (float) $meta['wallet_credited_amount'];
                $currency = $meta['wallet_credited_currency'] ?? $currency ?? null;
            }
        }
        if ($sum > 0) {
            return [
                'amount' => round($sum, 4),
                'currency' => $currency
                    ? strtoupper((string) $currency)
                    : ($dmcId > 0 ? app(WalletQueryService::class)->resolveDmcWalletCurrency($dmcId) : null),
            ];
        }

        // 2) Convert statement DMC commission (order currency) → DMC home wallet currency
        $dmcCommission = 0.0;
        $orderCurrency = null;
        foreach ($rows as $row) {
            $method = (string) ($row->payment_method ?? '');
            $meta = $this->decodeMeta($row->metadata ?? null);
            if ($method !== 'online_approve' && ($meta['dmc_destination'] ?? '') !== 'wallet') {
                continue;
            }
            if (is_numeric($row->dmc_commission ?? null) && (float) $row->dmc_commission > 0) {
                $dmcCommission += (float) $row->dmc_commission;
                $orderCurrency = $orderCurrency
                    ?? ($meta['order_currency'] ?? null)
                    ?? ($meta['wallet_source_currency'] ?? null)
                    ?? $row->currency_code;
            }
        }
        if ($dmcCommission <= 0 || $dmcId <= 0) {
            return ['amount' => 0.0, 'currency' => null];
        }

        $home = app(WalletQueryService::class)->resolveDmcWalletCurrency($dmcId);
        $from = strtoupper((string) ($orderCurrency ?: 'SGD'));
        if ($from === $home) {
            return ['amount' => round($dmcCommission, 4), 'currency' => $home];
        }
        $converted = Country::convertAmount($dmcCommission, $from, $home);

        return [
            'amount' => round((float) ($converted ?? $dmcCommission), 4),
            'currency' => $converted !== null ? $home : $from,
        ];
    }

    protected function enrichLine(
        object $line,
        string $displayCurrency = '',
        string $viewer = 'dmc',
        ?int $operatingDmcId = null
    ): object {
        $meta = $this->decodeMeta($line->metadata ?? null);
        $fromCurrency = strtoupper((string) (
            $meta['order_currency']
            ?? $line->currency_code
            ?? 'SGD'
        ));
        $tourId = (int) ($line->tour_id ?? 0);
        $dmcId = $tourId > 0 ? (int) (Tour::query()->where('tour_id', $tourId)->value('dmc_id') ?? 0) : 0;
        if ($dmcId <= 0 && $operatingDmcId) {
            $dmcId = (int) $operatingDmcId;
        }

        if ($displayCurrency === '') {
            $displayCurrency = ($viewer === 'dmc' && $dmcId > 0)
                ? app(WalletQueryService::class)->resolveDmcWalletCurrency($dmcId)
                : $fromCurrency;
        }
        $displayCurrency = strtoupper($displayCurrency);

        $channel = (string) ($line->channel ?? '');
        $lineFrom = ($channel === 'fx' || (float) ($line->conversion_charge ?? 0) > 0)
            ? strtoupper((string) ($meta['wallet_currency'] ?? $meta['paid_currency'] ?? $line->currency_code ?? $fromCurrency))
            : $fromCurrency;

        $originalDmcCommission = (float) ($line->dmc_commission ?? 0);

        foreach (['charged_amount', 'dmc_commission', 'travclicks_commission', 'api_cost', 'agent_price', 'dmc_visible_price', 'conversion_charge'] as $field) {
            if (! isset($line->{$field}) || $line->{$field} === null) {
                continue;
            }
            $line->{$field} = $this->convertAmount((float) $line->{$field}, $lineFrom, $displayCurrency);
        }

        if (isset($meta['wallet_credited_amount']) && is_numeric($meta['wallet_credited_amount']) && (float) $meta['wallet_credited_amount'] > 0) {
            $creditedFrom = strtoupper((string) ($meta['wallet_credited_currency'] ?? $displayCurrency));
            $creditedAmt = (float) $meta['wallet_credited_amount'];
            $sourceAmt = (float) ($meta['wallet_source_amount'] ?? $meta['source_amount'] ?? 0);
            if ($sourceAmt <= 0) {
                $sourceAmt = $originalDmcCommission;
            }
            if (
                $fromCurrency !== $displayCurrency
                && $sourceAmt > 0
                && abs($creditedAmt - $sourceAmt) < 0.05
            ) {
                // Old wrong post: SGD figure stored into IDR wallet — show converted value
                $line->wallet_credited_amount = $this->convertAmount($sourceAmt, $fromCurrency, $displayCurrency);
            } else {
                $line->wallet_credited_amount = $this->convertAmount($creditedAmt, $creditedFrom, $displayCurrency);
            }
        } elseif (($meta['dmc_destination'] ?? '') === 'wallet' && $originalDmcCommission > 0) {
            $line->wallet_credited_amount = $this->convertAmount($originalDmcCommission, $fromCurrency, $displayCurrency);
        } else {
            $line->wallet_credited_amount = 0.0;
        }

        $line->currency_code = $displayCurrency;
        $line->wallet_credited_currency = $displayCurrency;
        $line->wallet_source_amount = isset($meta['wallet_source_amount'])
            ? (float) $meta['wallet_source_amount']
            : null;
        $line->wallet_source_currency = isset($meta['wallet_source_currency'])
            ? strtoupper((string) $meta['wallet_source_currency'])
            : null;
        $line->meta = $meta;

        return $line;
    }

    protected function decodeMeta(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }

        return [];
    }

    protected function viewer($user): string
    {
        $roleId = (int) ($user->role_id ?? 0);
        if (in_array($roleId, [1, 2, 3, 4], true)) {
            return 'travclicks';
        }

        if ($roleId === 20) {
            return 'agent';
        }

        // DMC owner + multi-role / DMC staff (view parent DMC commission)
        return 'dmc';
    }

    protected function operatingDmcId($user, string $viewer): ?int
    {
        if ($viewer === 'travclicks' || $viewer === 'agent') {
            return null;
        }

        return CommonHelper::resolveDmcUserIdForStaff($user)
            ?: ((int) ($user->userId ?? 0) ?: null);
    }

    protected function scopeLines($query, ?int $operatingDmcId, string $viewer): void
    {
        if ($viewer === 'travclicks') {
            return;
        }

        $dmcId = (int) ($operatingDmcId ?? 0);
        if ($dmcId <= 0) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('tour_id', function ($sub) use ($dmcId) {
            $sub->select('tour_id')->from('tours')->where(function ($q) use ($dmcId) {
                $q->where('dmc_id', $dmcId)
                    ->orWhere('master_dmc_id', $dmcId)
                    ->orWhere('agent_id', $dmcId);
            });
        });
    }
}
