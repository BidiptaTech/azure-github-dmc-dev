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
        $onlinePaid = $this->onlinePortionSettled($tour);

        return $this->describe(
            $classification,
            $onlinePaid,
            (float) ($breakdown['online_amount'] ?? 0),
            (float) ($breakdown['offline_amount'] ?? 0),
            (string) ($breakdown['currency'] ?? $this->classificationService->resolveTourCurrency($tour)),
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
    ): array {
        $onlineAmount = round($onlineAmount, 4);
        $offlineAmount = round($offlineAmount, 4);

        if ($classification === OrderClassificationService::ONLINE) {
            return [
                'classification' => $classification,
                'allow_enquiry' => false,
                'allow_negotiate' => false,
                'exclude_online_from_negotiation' => false,
                'online_paid' => $onlinePaid,
                'next_action' => 'full_payment',
                'negotiable_amount' => 0.0,
                'payable_now' => $onlineAmount > 0 ? $onlineAmount : $offlineAmount,
                'currency' => $currency,
                'button_label' => 'Pay full amount',
                'hint' => 'All services are online. Pay the full amount to confirm. Negotiation is not available.',
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
                    'next_action' => 'pay_online',
                    'negotiable_amount' => $offlineAmount,
                    'payable_now' => $onlineAmount,
                    'currency' => $currency,
                    'button_label' => 'Pay online services',
                    'hint' => 'Pay every online service first. Negotiation applies only to the offline total.',
                ];
            }

            return [
                'classification' => $classification,
                'allow_enquiry' => true,
                'allow_negotiate' => true,
                'exclude_online_from_negotiation' => true,
                'online_paid' => true,
                'next_action' => 'negotiate_offline',
                'negotiable_amount' => $offlineAmount,
                'payable_now' => 0.0,
                'currency' => $currency,
                'button_label' => 'Negotiate',
                'hint' => 'Online services are paid. Negotiate the offline total only.',
            ];
        }

        return [
            'classification' => $classification,
            'allow_enquiry' => true,
            'allow_negotiate' => true,
            'exclude_online_from_negotiation' => false,
            'online_paid' => false,
            'next_action' => 'negotiate',
            'negotiable_amount' => $offlineAmount,
            'payable_now' => 0.0,
            'currency' => $currency,
            'button_label' => 'Negotiate',
            'hint' => null,
        ];
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
