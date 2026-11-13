<?php

namespace App\Services\Payment;

use App\Models\Tour;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Online pays in full and confirms. Mix pays online services first, then
 * enquiry negotiation is limited to the offline total.
 */
class AcceptanceFlowService
{
    public function __construct(
        protected OrderClassificationService $classificationService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forTour(Tour $tour): array
    {
        $classification = $this->classificationService->classifyTour($tour);
        if ($classification === OrderClassificationService::EMPTY) {
            $classification = (string) ($tour->order_classification ?: OrderClassificationService::EMPTY);
        }

        $breakdown = $this->classificationService->amountBreakdown($tour);
        $onlineAmount = (float) ($breakdown['online_amount'] ?? 0);
        $onlinePaid = $this->onlinePortionSettled($tour);
        $onlinePaidAmount = $onlinePaid
            ? $this->sumVerifiedOnlinePayments($tour, $onlineAmount)
            : 0.0;

        return $this->describe(
            $classification,
            $onlinePaid,
            $onlineAmount,
            (float) ($breakdown['offline_amount'] ?? 0),
            (string) ($breakdown['currency'] ?? $this->classificationService->resolveTourCurrency($tour)),
            $onlinePaidAmount,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function describe(
        string $classification,
        bool $onlinePaid,
        float $onlineAmount,
        float $offlineAmount,
        string $currency = 'SGD',
        float $onlinePaidAmount = 0.0,
    ): array {
        $onlineAmount = round($onlineAmount, 4);
        $offlineAmount = round($offlineAmount, 4);
        $onlinePaidAmount = round(max(0.0, $onlinePaidAmount), 4);
        if ($onlinePaid && $onlinePaidAmount <= 0 && $onlineAmount > 0) {
            $onlinePaidAmount = $onlineAmount;
        }
        $money = static function (float $amount) use ($currency): string {
            return strtoupper(trim($currency)).' '.number_format($amount, 2, '.', '');
        };

        if ($classification === OrderClassificationService::ONLINE) {
            return [
                'classification' => $classification,
                'allow_enquiry' => false,
                'allow_negotiate' => false,
                'exclude_online_from_negotiation' => false,
                'online_paid' => $onlinePaid,
                'online_amount' => $onlineAmount,
                'online_paid_amount' => $onlinePaid ? $onlinePaidAmount : 0.0,
                'next_action' => 'full_payment',
                'negotiable_amount' => 0.0,
                'payable_now' => $onlineAmount > 0 ? $onlineAmount : $offlineAmount,
                'currency' => $currency,
                'button_label' => 'Pay full amount',
                'hint' => $onlinePaid && $onlinePaidAmount > 0
                    ? 'Online payment complete: '.$money($onlinePaidAmount).'. All services are online.'
                    : 'All services are online. Pay the full amount to confirm. Negotiation is not available.',
            ];
        }

        if ($classification === OrderClassificationService::MIX) {
            if (! $onlinePaid) {
                return [
                    'classification' => $classification,
                    'allow_enquiry' => false,
                    'allow_negotiate' => false,
                    'exclude_online_from_negotiation' => true,
                    'online_paid' => false,
                    'online_amount' => $onlineAmount,
                    'online_paid_amount' => 0.0,
                    'next_action' => 'pay_online',
                    'negotiable_amount' => $offlineAmount,
                    'payable_now' => $onlineAmount,
                    'currency' => $currency,
                    'button_label' => 'Pay now',
                    'hint' => $onlineAmount > 0
                        ? 'Pay online services first ('.$money($onlineAmount).'). Negotiation applies only to the offline total.'
                        : 'Pay every online service first. Negotiation applies only to the offline total.',
                ];
            }

            return [
                'classification' => $classification,
                'allow_enquiry' => true,
                'allow_negotiate' => true,
                'exclude_online_from_negotiation' => true,
                'online_paid' => true,
                'online_amount' => $onlineAmount,
                'online_paid_amount' => $onlinePaidAmount,
                'next_action' => 'negotiate_offline',
                'negotiable_amount' => $offlineAmount,
                'payable_now' => 0.0,
                'currency' => $currency,
                'button_label' => 'Negotiate',
                'hint' => $onlinePaidAmount > 0
                    ? 'Online payment complete: '.$money($onlinePaidAmount).'. Negotiate the offline total only.'
                    : 'Online services are paid. Negotiate the offline total only.',
            ];
        }

        return [
            'classification' => $classification,
            'allow_enquiry' => true,
            'allow_negotiate' => true,
            'exclude_online_from_negotiation' => false,
            'online_paid' => false,
            'online_amount' => $onlineAmount,
            'online_paid_amount' => 0.0,
            'next_action' => 'negotiate',
            'negotiable_amount' => $offlineAmount,
            'payable_now' => 0.0,
            'currency' => $currency,
            'button_label' => 'Negotiate',
            'hint' => null,
        ];
    }

    /**
     * Sum verified online ledger payments; fall back to quoted online sell when empty.
     */
    protected function sumVerifiedOnlinePayments(Tour $tour, float $fallbackOnlineAmount = 0.0): float
    {
        $raw = $tour->payment_details ?? null;
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($raw)) {
            return round(max(0.0, $fallbackOnlineAmount), 4);
        }

        $paid = 0.0;
        foreach ($raw as $payment) {
            if (! is_array($payment) || (int) ($payment['status'] ?? 0) !== 1) {
                continue;
            }
            if (strtolower(trim((string) ($payment['payment_for'] ?? ''))) !== 'online') {
                continue;
            }
            $paid += (float) ($payment['amount'] ?? 0);
        }

        $paid = round($paid, 4);

        return $paid > 0 ? $paid : round(max(0.0, $fallbackOnlineAmount), 4);
    }

    public function onlinePortionSettled(Tour $tour): bool
    {
        $stored = (string) ($tour->payment_stage ?? '');
        if ($stored === 'offline_negotiation') {
            return true;
        }

        $tourId = (int) ($tour->tour_id ?? $tour->getKey());
        if ($tourId <= 0 || ! Schema::hasTable('payment_attempts')) {
            return false;
        }

        return DB::table('payment_attempts')
            ->where('tour_id', $tourId)
            ->where('payment_stage', 'supplier_initial')
            ->where('status', 'completed')
            ->exists();
    }
}
