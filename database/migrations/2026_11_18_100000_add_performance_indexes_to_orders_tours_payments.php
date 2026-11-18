<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the hottest lookups on orders / tours / payment + wallet tables.
 *
 * orders had no index on tour_id although almost every booking screen filters by it
 * (Order::where('tour_id', …) appears 100+ times). Each statement is IF NOT EXISTS and
 * guarded by column checks so the migration is safe to re-run on any environment.
 *
 * Partitioning is intentionally NOT applied here: orders/tours are referenced by
 * tour_id / booking_id unique keys that PostgreSQL cannot enforce across partitions
 * unless the partition column is part of the key. See docs/ONLINE_SUPPLIER_CREDIT_AND_FX.md.
 */
return new class extends Migration
{
    /**
     * @return array<int, array{0: string, 1: string, 2: array<int, string>, 3?: string}>
     */
    private function indexes(): array
    {
        return [
            // [table, index name, columns, optional WHERE]
            ['orders', 'idx_orders_tour_id', ['tour_id']],
            ['orders', 'idx_orders_tour_type_active', ['tour_id', 'type'], 'deleted_at IS NULL'],
            ['orders', 'idx_orders_agent_id', ['agent_id']],
            ['orders', 'idx_orders_online', ['tour_id'], "order_type = 'online'"],
            ['orders', 'idx_orders_created_at', ['created_at']],
            ['tours', 'idx_tours_dmc_id', ['dmc_id']],
            ['tours', 'idx_tours_tour_status', ['tour_status']],
            ['tours', 'idx_tours_created_at', ['created_at']],
            ['tours', 'idx_tours_multi_enq_id', ['multi_enq_id'], 'multi_enq_id IS NOT NULL'],
            ['tours', 'idx_tours_display_id', ['display_id'], 'display_id IS NOT NULL'],
            ['wallet_ledger', 'idx_wallet_ledger_tour_id', ['tour_id'], 'tour_id IS NOT NULL'],
            ['order_commission_lines', 'idx_order_commission_lines_order_id', ['order_id']],
            ['order_commission_lines', 'idx_order_commission_lines_created_at', ['created_at']],
            ['currency_conversion_snapshots', 'idx_ccs_dmc_id', ['dmc_id'], 'dmc_id IS NOT NULL'],
            ['payment_attempts', 'idx_payment_attempts_dmc_created', ['dmc_id', 'created_at']],
            ['payment_transactions', 'idx_payment_transactions_created_at', ['created_at']],
        ];
    }

    public function up(): void
    {
        foreach ($this->indexes() as $def) {
            [$table, $name, $columns] = $def;
            $where = $def[3] ?? null;

            if (! Schema::hasTable($table) || ! Schema::hasColumns($table, $columns)) {
                continue;
            }

            $cols = implode(', ', array_map(fn ($c) => '"'.$c.'"', $columns));
            DB::statement(sprintf(
                'CREATE INDEX IF NOT EXISTS %s ON %s (%s)%s',
                $name,
                $table,
                $cols,
                $where ? ' WHERE '.$where : ''
            ));
        }

        foreach (['orders', 'tours', 'wallet_ledger', 'order_commission_lines', 'payment_attempts'] as $table) {
            if (Schema::hasTable($table)) {
                DB::statement('ANALYZE '.$table);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes() as $def) {
            DB::statement('DROP INDEX IF EXISTS '.$def[1]);
        }
    }
};
