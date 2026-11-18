<?php

namespace App\Console\Commands;

use App\Services\Payment\PaymentOrchestrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Settle online payment holds:
 *  - apply services booked/removed from any screen (approve, edit, cancel) and settle when done;
 *  - force-settle holds before they expire (Stripe card authorisations last ~7 days):
 *    booked services are charged, the rest is released.
 */
class SettleOnlinePaymentHolds extends Command
{
    protected $signature = 'payments:settle-online-holds {--tour= : Only this tour id} {--dry-run : List open holds without changing anything}';

    protected $description = 'Apply booked services to held online payments; capture/release when done or near expiry';

    public function handle(PaymentOrchestrator $orchestrator): int
    {
        $query = DB::table('payment_attempts')
            ->where('status', 'authorized')
            ->where('payment_stage', 'supplier_initial')
            ->whereIn('payment_method', ['wallet', 'stripe'])
            ->whereRaw("(metadata::jsonb ->> 'hold_opened_at') IS NOT NULL");
        if ($this->option('tour')) {
            $query->where('tour_id', (int) $this->option('tour'));
        }

        $rows = $query->orderBy('id')->get(['id', 'tour_id', 'payment_method', 'requested_amount', 'currency_code', 'metadata']);
        if ($rows->isEmpty()) {
            $this->info('No open online payment holds.');

            return self::SUCCESS;
        }

        $stripeDays = max(1, (int) config('payment.online_hold.stripe_max_days', 6));
        $walletDays = max(1, (int) config('payment.online_hold.wallet_max_days', 30));

        foreach ($rows->groupBy('tour_id') as $tourId => $attempts) {
            $force = false;
            foreach ($attempts as $a) {
                $meta = json_decode((string) $a->metadata, true) ?: [];
                $opened = isset($meta['hold_opened_at']) ? \Carbon\Carbon::parse($meta['hold_opened_at']) : now();
                $limit = $a->payment_method === 'stripe' ? $stripeDays : $walletDays;
                if ($opened->lte(now()->subDays($limit))) {
                    $force = true;
                }
            }

            if ($this->option('dry-run')) {
                foreach ($attempts as $a) {
                    $this->line(sprintf('Tour #%d · %s · %s %s held%s', $tourId, $a->payment_method, $a->currency_code, number_format((float) $a->requested_amount, 2), $force ? ' · WILL FORCE-SETTLE' : ''));
                }

                continue;
            }

            $results = $orchestrator->syncOnlineHold((int) $tourId, $force, $force ? 'hold_expiry' : 'all_services_resolved');
            foreach ($results as $r) {
                $this->line(sprintf('Tour #%d · %s · %s', $tourId, $r['attempt_uuid'] ?? '-', $r['state'] ?? '-'));
            }
        }

        return self::SUCCESS;
    }
}
