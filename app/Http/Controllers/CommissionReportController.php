<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Services\Payment\PaymentEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Per-order commission statement.
 * Travclicks sees API cost and its own online commission.
 * DMC sees the same statement without those two figures.
 */
class CommissionReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $viewer = $this->viewer($user);
        $tourId = $request->integer('tour_id') ?: null;

        $tours = $this->settledTours($user, $viewer, $tourId);
        $lines = collect();
        $totals = $this->emptyTotals();

        if (Schema::hasTable('order_commission_lines')) {
            if ($tourId) {
                $lineQuery = DB::table('order_commission_lines')->where('tour_id', $tourId)->orderBy('id');
                $this->scopeLines($lineQuery, $user, $viewer);
                $lines = $lineQuery->get();
                $totals = $this->totals($lines);
            }
        }

        return view('finance.commission-report', [
            'viewer' => $viewer,
            'tours' => $tours,
            'lines' => $lines,
            'totals' => $totals,
            'tourId' => $tourId,
            'selectedTour' => $tourId ? Tour::query()->where('tour_id', $tourId)->first() : null,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $lines
     * @return array<string, float>
     */
    protected function totals($lines): array
    {
        $totals = $this->emptyTotals();
        foreach ($lines as $line) {
            $totals['charged_amount'] += (float) $line->charged_amount;
            $totals['dmc_commission'] += (float) $line->dmc_commission;
            $totals['travclicks_commission'] += (float) $line->travclicks_commission;
            $totals['api_cost'] += (float) ($line->api_cost ?? 0);
            $totals['conversion_charge'] += (float) $line->conversion_charge;
            $totals['agent_price'] += (float) $line->agent_price;
        }

        return $totals;
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
        ];
    }

    protected function settledTours($user, string $viewer, ?int $tourId)
    {
        $query = Tour::query()
            ->whereNotNull('payment_details')
            ->orderByDesc('updated_at');

        if ($tourId) {
            $query->where('tour_id', $tourId);
        } else {
            $query->limit(150);
        }

        if ($viewer !== 'travclicks') {
            $ownerId = (int) ($user->userId ?? 0);
            $query->where(function ($q) use ($ownerId) {
                $q->where('dmc_id', $ownerId)
                    ->orWhere('master_dmc_id', $ownerId)
                    ->orWhere('agent_id', $ownerId);
            });
        }

        $eligibility = app(PaymentEligibilityService::class);

        return $query->get()
            ->map(fn (Tour $tour) => $eligibility->fullyPaidMarkup($tour))
            ->filter()
            ->map(fn (array $row) => (object) $row)
            ->values();
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

        return 'dmc';
    }

    protected function scopeTours($query, $user, string $viewer): void
    {
        if ($viewer === 'travclicks') {
            return;
        }

        $dmcId = (int) ($user->userId ?? 0);
        $query->whereIn('tour_id', function ($sub) use ($dmcId) {
            $sub->select('tour_id')->from('tours')->where(function ($q) use ($dmcId) {
                $q->where('dmc_id', $dmcId)->orWhere('master_dmc_id', $dmcId)->orWhere('agent_id', $dmcId);
            });
        });
    }

    protected function scopeLines($query, $user, string $viewer): void
    {
        if ($viewer === 'travclicks') {
            return;
        }

        $dmcId = (int) ($user->userId ?? 0);
        $query->whereIn('tour_id', function ($sub) use ($dmcId) {
            $sub->select('tour_id')->from('tours')->where(function ($q) use ($dmcId) {
                $q->where('dmc_id', $dmcId)->orWhere('master_dmc_id', $dmcId)->orWhere('agent_id', $dmcId);
            });
        });
    }
}
