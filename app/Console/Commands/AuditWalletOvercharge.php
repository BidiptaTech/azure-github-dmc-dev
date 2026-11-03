<?php

namespace App\Console\Commands;

use App\Models\Tour;
use App\Services\Payment\OrderClassificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Read-only. Lists completed wallet payments that were converted from a wrong "order currency"
 * (tours.payment_received_currency defaulting to USD) and charged an FX conversion fee although
 * the tour is priced in the wallet currency. Never writes: corrections must be approved by finance
 * and posted as a new, referenced ledger credit.
 */
class AuditWalletOvercharge extends Command
{
    protected $signature = 'payments:audit-wallet-overcharge';

    protected $description = 'List wallet payments that were over-deducted by a wrong tour currency conversion (read-only)';

    public function handle(OrderClassificationService $classification): int
    {
        $rows = [];

        foreach (DB::table('payment_attempts')->where('payment_method', 'wallet')->where('status', 'completed')->orderBy('id')->get() as $a) {
            $fx = (json_decode((string) $a->eligibility_snapshot, true) ?: [])['fx'] ?? [];
            $walletCurrency = strtoupper((string) ($fx['wallet_currency'] ?? $a->currency_code));
            $snapshotCurrency = strtoupper((string) ($fx['order_currency'] ?? $walletCurrency));
            $tour = Tour::where('tour_id', $a->tour_id)->first();
            if (! $tour || $snapshotCurrency === $walletCurrency || $classification->resolveTourCurrency($tour) !== $walletCurrency) {
                continue;
            }

            $rate = (float) ($fx['exchange_rate'] ?? 0);
            $pct = (float) ($fx['charge_percent'] ?? 0);
            if ($rate <= 0) {
                continue;
            }
            $expected = round((float) $a->captured_amount / ($rate * (1 + $pct / 100)), 4);
            $rows[] = [
                $a->tour_id, $a->id, $a->wallet_reservation_ref, $walletCurrency,
                number_format((float) $a->captured_amount, 4, '.', ''), number_format($expected, 4, '.', ''),
                number_format((float) $a->captured_amount - $expected, 4, '.', ''),
            ];
        }

        $rows
            ? $this->table(['Tour', 'Attempt', 'Reservation', 'Wallet cur.', 'Deducted', 'Should be', 'Excess'], $rows)
            : $this->info('No over-deducted wallet payments found.');

        return self::SUCCESS;
    }
}
