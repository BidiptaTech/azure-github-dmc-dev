<?php

namespace App\Services\Finance;

use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class AgentTourQueryService
{
    public const PER_PAGE_DEFAULT = 25;

    public const PER_PAGE_MAX = 100;

    /**
     * @return array{
     *     tours: LengthAwarePaginator,
     *     toursFilters: array{date_from: ?string, date_to: ?string, per_page: int},
     *     toursQueryParams: array<string, mixed>
     * }
     */
    public function buildToursViewData(
        int $agentId,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $perPage = null,
        int $page = 1,
    ): array {
        $toursFilters = $this->resolveTourFilters($dateFrom, $dateTo, $perPage);
        $tours = $this->paginateAgentTours(
            $agentId,
            $toursFilters['date_from'],
            $toursFilters['date_to'],
            $toursFilters['per_page'],
            max($page, 1),
        );

        $toursQueryParams = array_filter([
            'tours_date_from' => $toursFilters['date_from'],
            'tours_date_to' => $toursFilters['date_to'],
            'tours_per_page' => $toursFilters['per_page'],
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'tours' => $tours,
            'toursFilters' => $toursFilters,
            'toursQueryParams' => $toursQueryParams,
        ];
    }

    public function paginateAgentTours(
        int $agentId,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = self::PER_PAGE_DEFAULT,
        int $page = 1,
    ): LengthAwarePaginator {
        $perPage = max(1, min($perPage, self::PER_PAGE_MAX));
        $page = max(1, $page);

        return $this->agentToursQuery($agentId, $dateFrom, $dateTo)
            ->paginate($perPage, [
                'tour_id', 'display_id', 'tour_status', 'finance_status',
                'payment_mode', 'payment_status', 'selling_price', 'created_at',
            ], 'tours_page', $page);
    }

    /**
     * @return array{date_from: ?string, date_to: ?string, per_page: int}
     */
    public function resolveTourFilters(?string $dateFrom = null, ?string $dateTo = null, ?int $perPage = null): array
    {
        $perPage = max(1, min((int) ($perPage ?: self::PER_PAGE_DEFAULT), self::PER_PAGE_MAX));

        $from = $this->normalizeTourDate($dateFrom);
        $to = $this->normalizeTourDate($dateTo);

        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'date_from' => $from,
            'date_to' => $to,
            'per_page' => $perPage,
        ];
    }

    protected function agentToursQuery(int $agentId, ?string $dateFrom = null, ?string $dateTo = null): Builder
    {
        $query = Tour::query()->where('agent_id', $agentId);

        $from = $this->normalizeTourDate($dateFrom);
        $to = $this->normalizeTourDate($dateTo);

        if ($from) {
            $query->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->where('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('tour_id');
    }

    protected function normalizeTourDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
