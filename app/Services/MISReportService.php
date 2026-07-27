<?php

namespace App\Services;

use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class MISReportService
{
    /**
     * Get tour MIS report data using Query Builder.
     * Restricted to logged-in user's dmc_id. Paginated when $perPage is set.
     *
     * @param array $filters ['from_date', 'to_date', 'agent', 'dmc', 'booking_status']
     * @param int|string|null $dmcId DMC ID to restrict data (tours.dmc_id, invoices.dmc_id)
     * @param int|null $perPage 10 for pagination, null for all (e.g. export)
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getTourMISReport(array $filters = [], $dmcId = null, $perPage = 10)
    {
        $ordersTable = 'orders';
        $toursTable = 'tours';
        $agentsTable = 'agents';
        $agenciesTable = 'agencies';
        $usersTable = 'users';
        $invoicesTable = 'invoices';

        if ($dmcId === null) {
            $currentUser = Auth::user();
            $dmcId = CommonHelper::getDmcId($currentUser);
            if ($dmcId === null && $currentUser && in_array($currentUser->role_id ?? 0, [11])) {
                $dmcId = $currentUser->userId;
            }
        }

        // Subquery: one row per tour_id (latest final invoice)
        $latestInvoiceIds = DB::table($invoicesTable)
            ->select(DB::raw('tour_id, MAX(id) as id'))
            ->where('invoice_type', 'final')
            ->whereNull('deleted_at')
            ->groupBy('tour_id');

        $invoiceSub = DB::table($invoicesTable . ' as i')
            ->joinSub($latestInvoiceIds, 'latest', function ($j) {
                $j->on('i.tour_id', '=', 'latest.tour_id')->on('i.id', '=', 'latest.id');
            })
            ->select('i.tour_id', 'i.invoice_number', 'i.status', 'i.total_amount');

        // Base query: orders -> tours, agent -> agency, dmc user, invoice. Access: tours.dmc_id = user DMC OR agency.dmc_id JSON contains user DMC.
        $query = DB::table($ordersTable)
            ->leftJoin($toursTable, "{$ordersTable}.tour_id", '=', "{$toursTable}.tour_id")
            ->leftJoin($agentsTable, "{$ordersTable}.agent_id", '=', "{$agentsTable}.agent_id")
            ->leftJoin($agenciesTable, "{$agentsTable}.agency_id", '=', "{$agenciesTable}.agency_id")
            ->leftJoin($usersTable . ' as dmc_user', "{$toursTable}.dmc_id", '=', 'dmc_user.userId')
            ->leftJoinSub(
                $invoiceSub,
                'inv',
                function ($join) use ($ordersTable) {
                    $join->on('inv.tour_id', '=', "{$ordersTable}.tour_id");
                }
            )
            ->select(
                "{$ordersTable}.id as booking_id",
                "{$ordersTable}.created_at as booking_date",
                "{$ordersTable}.type as order_type",
                DB::raw("COALESCE({$toursTable}.display_id, '—') as tour_name"),
                "{$toursTable}.destination",
                "{$agentsTable}.name as agent_name",
                'dmc_user.name as dmc_name',
                "{$ordersTable}.data",
                "{$ordersTable}.status as booking_status",
                "{$ordersTable}.tour_id",
                "{$ordersTable}.agent_id",
                "{$toursTable}.dmc_id",
                DB::raw("COALESCE(inv.invoice_number, '') as transaction_reference_number"),
                DB::raw("COALESCE(inv.status, '') as payment_status"),
                DB::raw("COALESCE(inv.total_amount, 0) as invoice_total")
            )
            ->whereNull("{$ordersTable}.deleted_at")
            ->whereNull("{$toursTable}.deleted_at");

        // Restrict to data the current user's DMC has access to: tour belongs to DMC OR agent's agency has this DMC in dmc_id (JSON)
        if ($dmcId !== null && $dmcId !== '') {
            $query->where(function ($q) use ($dmcId, $toursTable, $agenciesTable) {
                $q->where("{$toursTable}.dmc_id", $dmcId)
                    ->orWhere($this->agencyDmcIdContainsRaw($agenciesTable, $dmcId));
            });
        }

        // Filters
        if (!empty($filters['from_date'])) {
            $query->whereDate("{$ordersTable}.created_at", '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate("{$ordersTable}.created_at", '<=', $filters['to_date']);
        }
        if (!empty($filters['agent'])) {
            $query->where("{$ordersTable}.agent_id", $filters['agent']);
        }
        if (!empty($filters['dmc'])) {
            $query->where("{$toursTable}.dmc_id", $filters['dmc']);
        }
        if (isset($filters['booking_status']) && $filters['booking_status'] !== '' && $filters['booking_status'] !== null) {
            $query->where("{$ordersTable}.status", $filters['booking_status']);
        }

        $query->orderBy("{$ordersTable}.created_at", 'desc');

        $mapRow = function ($row) {
            $sellingPrice = 0;
            $pax = 0;
            $agentCommission = 0;
            $dmcCommission = 0;

            $data = is_string($row->data) ? json_decode($row->data, true) : $row->data;
            if (is_array($data)) {
                if (isset($data[0]) && is_array($data[0])) {
                    foreach ($data as $item) {
                        $sellingPrice += (float) (isset($item['totalPrice']) ? $item['totalPrice'] : (isset($item['total_price']) ? $item['total_price'] : 0));
                        $pax += (int) (isset($item['adult']) ? $item['adult'] : 0) + (int) (isset($item['child']) ? $item['child'] : 0);
                    }
                } else {
                    $sellingPrice = (float) (isset($data['totalPrice']) ? $data['totalPrice'] : (isset($data['total_price']) ? $data['total_price'] : 0));
                    $pax = (int) (isset($data['adult']) ? $data['adult'] : 0) + (int) (isset($data['child']) ? $data['child'] : 0);
                }
            }

            // Use invoice total as selling price if available and no price in data
            if ($sellingPrice == 0 && !empty($row->invoice_total)) {
                $sellingPrice = (float) $row->invoice_total;
            }

            $netProfit = $sellingPrice - $agentCommission - $dmcCommission;

            $bookingStatusLabel = $this->bookingStatusLabel($row->booking_status);
            $paymentStatusLabel = $row->payment_status ?: '—';

            return (object) [
                'booking_id' => $row->booking_id,
                'booking_date' => $row->booking_date,
                'type' => $this->orderTypeLabel($row->order_type ?? null),
                'tour_name' => $row->tour_name ?? '—',
                'destination' => $row->destination ?: '—',
                'agent_name' => $row->agent_name ?: '—',
                'dmc_name' => $row->dmc_name ?: '—',
                'pax' => $pax,
                'selling_price' => round($sellingPrice, 2),
                'agent_commission' => round($agentCommission, 2),
                'dmc_commission' => round($dmcCommission, 2),
                'net_profit' => round($netProfit, 2),
                'transaction_reference_number' => $row->transaction_reference_number ?: '—',
                'payment_status' => $paymentStatusLabel,
                'booking_status' => $bookingStatusLabel,
            ];
        };

        if ($perPage !== null) {
            $paginator = $query->paginate($perPage)->withQueryString();
            $paginator->getCollection()->transform($mapRow);
            return $paginator;
        }

        $rows = $query->get();
        return $rows->map($mapRow);
    }

    /**
     * Get distinct agents for filter dropdown. When $dmcId is set, only agents the DMC has access to (tours.dmc_id or agency.dmc_id JSON).
     */
    public function getAgentsForFilter($dmcId = null): Collection
    {
        $q = DB::table('orders')
            ->join('tours', 'orders.tour_id', '=', 'tours.tour_id')
            ->join('agents', 'orders.agent_id', '=', 'agents.agent_id')
            ->leftJoin('agencies', 'agents.agency_id', '=', 'agencies.agency_id')
            ->whereNull('orders.deleted_at')
            ->whereNull('tours.deleted_at');
        if ($dmcId !== null && $dmcId !== '') {
            $q->where(function ($sub) use ($dmcId) {
                $sub->where('tours.dmc_id', $dmcId)
                    ->orWhere($this->agencyDmcIdContainsRaw('agencies', $dmcId));
            });
        }
        return $q->select('agents.agent_id as id', 'agents.name')
            ->distinct()
            ->orderBy('agents.name')
            ->get();
    }

    /**
     * Get distinct DMCs for filter dropdown. When $dmcId is set, returns only that DMC (single option).
     */
    public function getDmcsForFilter($dmcId = null): Collection
    {
        if ($dmcId !== null && $dmcId !== '') {
            return DB::table('users')
                ->where('users.userId', $dmcId)
                ->select('users.userId as id', 'users.name')
                ->get();
        }
        return DB::table('orders')
            ->join('tours', 'orders.tour_id', '=', 'tours.tour_id')
            ->join('users', 'tours.dmc_id', '=', 'users.userId')
            ->whereNull('orders.deleted_at')
            ->whereNull('tours.deleted_at')
            ->select('users.userId as id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get();
    }

    /**
     * Returns a closure for "agency.dmc_id JSON array contains dmcId" (PostgreSQL and MySQL).
     */
    protected function agencyDmcIdContainsRaw(string $agenciesTable, $dmcId): \Closure
    {
        return function ($q) use ($agenciesTable, $dmcId) {
            $driver = DB::connection()->getDriverName();
            $dmcInt = (int) $dmcId;
            if ($driver === 'pgsql') {
                $q->whereRaw($agenciesTable . '.dmc_id::jsonb @> ?::jsonb', [json_encode([$dmcInt])]);
            } else {
                $q->whereRaw('JSON_CONTAINS(' . $agenciesTable . '.dmc_id, CAST(? AS JSON), ?)', [$dmcId, '$']);
            }
        };
    }

    protected function bookingStatusLabel($status): string
    {
        $labels = [
            0 => 'Not Started',
            1 => 'Confirmed',
            2 => 'In Progress',
            3 => 'Completed',
            4 => 'Cancelled',
        ];
        return $labels[(int) $status] ?? (string) $status;
    }

    protected function orderTypeLabel($type): string
    {
        $type = is_string($type) ? trim(strtolower($type)) : '';
        if ($type === '') {
            return '—';
        }

        $labels = [
            'entry_port' => 'Arrival',
            'exit_port' => 'Departure',
            'hotel' => 'Hotel',
            'attraction' => 'Attraction',
            'restaurant' => 'Restaurant',
            'guide' => 'Guide',
            'local_transport' => 'Local Tranfer',
            'travel_hourly' => 'Hourly',
            'travel_point' => 'Point to Point',
        ];

        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}
