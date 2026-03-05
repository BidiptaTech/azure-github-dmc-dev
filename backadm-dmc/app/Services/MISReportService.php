<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class MISReportService
{
    /**
     * Get tour MIS report data using Query Builder.
     *
     * @param array $filters ['from_date', 'to_date', 'agent', 'dmc', 'booking_status']
     * @return \Illuminate\Support\Collection
     */
    public function getTourMISReport(array $filters = []): Collection
    {
        $ordersTable = 'orders';
        $toursTable = 'tours';
        $agentsTable = 'agents';
        $usersTable = 'users';
        $invoicesTable = 'invoices';

        // Subquery: one row per tour_id (latest final invoice)
        $latestInvoiceIds = DB::table($invoicesTable)
            ->select(DB::raw('tour_id, MAX(id) as id'))
            ->where('invoice_type', 'final')
            ->whereNull('deleted_at')
            ->groupBy('tour_id');
        $invoiceSub = DB::table($invoicesTable . ' as i')
            ->joinSub($latestInvoiceIds, 'latest', function ($j) use ($invoicesTable) {
                $j->on('i.tour_id', '=', 'latest.tour_id')->on('i.id', '=', 'latest.id');
            })
            ->select('i.tour_id', 'i.invoice_number', 'i.status', 'i.total_amount');

        // Base query: orders with tour, agent, dmc (user), and optional invoice
        $query = DB::table($ordersTable)
            ->leftJoin($toursTable, "{$ordersTable}.tour_id", '=', "{$toursTable}.tour_id")
            ->leftJoin($agentsTable, "{$ordersTable}.agent_id", '=', "{$agentsTable}.agent_id")
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

        $rows = $query->get();

        // Map to report rows with computed fields (pax, selling_price, commissions from order data / invoice)
        return $rows->map(function ($row) {
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
        });
    }

    /**
     * Get distinct agents for filter dropdown (from users table if agent is user, else from agents table).
     * Returns agents that have at least one order.
     */
    public function getAgentsForFilter(): Collection
    {
        return DB::table('orders')
            ->join('agents', 'orders.agent_id', '=', 'agents.agent_id')
            ->whereNull('orders.deleted_at')
            ->select('agents.agent_id as id', 'agents.name')
            ->distinct()
            ->orderBy('agents.name')
            ->get();
    }

    /**
     * Get distinct DMCs (users) for filter dropdown.
     */
    public function getDmcsForFilter(): Collection
    {
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
}
