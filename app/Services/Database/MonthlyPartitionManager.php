<?php

namespace App\Services\Database;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Month/year RANGE partitions on created_at (PostgreSQL).
 *
 * Partition names: {table}_pYYYYMM, plus {table}_pdefault for anything outside the range.
 */
class MonthlyPartitionManager
{
    /** Tables converted by 2026_11_18_120000_partition_core_tables_by_month. */
    public const TABLES = [
        'tours',
        'orders',
        'payment_attempts',
        'payment_transactions',
        'settlements',
        'wallet_ledger',
        'order_commission_lines',
        'currency_conversion_snapshots',
        'supplier_booking_outcomes',
        'supplier_credit_transactions',
        'credit_notes',
    ];

    public static function partitionName(string $table, CarbonImmutable $month): string
    {
        return $table.'_p'.$month->format('Ym');
    }

    public function isPartitioned(string $table): bool
    {
        return (bool) DB::selectOne(
            "SELECT 1 AS ok FROM pg_partitioned_table pt JOIN pg_class c ON c.oid = pt.partrelid
             JOIN pg_namespace n ON n.oid = c.relnamespace
             WHERE c.relname = ? AND n.nspname = current_schema()",
            [$table]
        );
    }

    /**
     * Create the month partition for [month start, next month start) if missing.
     * Rows already sitting in the default partition for that month are moved in.
     *
     * @return bool true when created
     */
    public function ensureMonth(string $table, CarbonImmutable $month): bool
    {
        $this->assertIdent($table);
        $month = $month->startOfMonth();
        $name = self::partitionName($table, $month);
        if ($this->relationExists($name)) {
            return false;
        }

        $from = $month->format('Y-m-d');
        $to = $month->addMonth()->format('Y-m-d');
        $default = $table.'_pdefault';

        $stray = $this->relationExists($default)
            ? (int) DB::selectOne("SELECT count(*) AS c FROM \"{$default}\" WHERE created_at >= ? AND created_at < ?", [$from, $to])->c
            : 0;

        if ($stray === 0) {
            DB::statement("CREATE TABLE \"{$name}\" PARTITION OF \"{$table}\" FOR VALUES FROM ('{$from}') TO ('{$to}')");

            return true;
        }

        // Default holds rows for this month: build the partition standalone, move rows, attach.
        DB::transaction(function () use ($table, $name, $default, $from, $to) {
            DB::statement("CREATE TABLE \"{$name}\" (LIKE \"{$table}\" INCLUDING DEFAULTS INCLUDING CONSTRAINTS)");
            DB::statement("ALTER TABLE \"{$table}\" DETACH PARTITION \"{$default}\"");
            DB::statement("INSERT INTO \"{$name}\" SELECT * FROM \"{$default}\" WHERE created_at >= '{$from}' AND created_at < '{$to}'");
            DB::statement("DELETE FROM \"{$default}\" WHERE created_at >= '{$from}' AND created_at < '{$to}'");
            DB::statement("ALTER TABLE \"{$table}\" ATTACH PARTITION \"{$name}\" FOR VALUES FROM ('{$from}') TO ('{$to}')");
            DB::statement("ALTER TABLE \"{$table}\" ATTACH PARTITION \"{$default}\" DEFAULT");
        });

        return true;
    }

    /**
     * Ensure partitions exist from $from month through now + $monthsAhead.
     *
     * @return array<int, string> created partition names
     */
    public function ensureRange(string $table, ?CarbonImmutable $from = null, int $monthsAhead = 3): array
    {
        $created = [];
        $cursor = ($from ?? CarbonImmutable::now())->startOfMonth();
        $end = CarbonImmutable::now()->startOfMonth()->addMonths(max(0, $monthsAhead));
        while ($cursor->lte($end)) {
            if ($this->ensureMonth($table, $cursor)) {
                $created[] = self::partitionName($table, $cursor);
            }
            $cursor = $cursor->addMonth();
        }

        return $created;
    }

    public function ensureDefault(string $table): void
    {
        $this->assertIdent($table);
        $default = $table.'_pdefault';
        if (! $this->relationExists($default)) {
            DB::statement("CREATE TABLE \"{$default}\" PARTITION OF \"{$table}\" DEFAULT");
        }
    }

    /**
     * @return array<int, array{partition: string, rows: int}>
     */
    public function summary(string $table): array
    {
        $this->assertIdent($table);

        return array_map(fn ($r) => ['partition' => $r->partition, 'rows' => (int) $r->rows], DB::select(
            "SELECT c.relname AS partition, COALESCE(s.n_live_tup, 0) AS rows
             FROM pg_inherits i JOIN pg_class c ON c.oid = i.inhrelid JOIN pg_class p ON p.oid = i.inhparent
             LEFT JOIN pg_stat_user_tables s ON s.relid = c.oid
             WHERE p.relname = ? ORDER BY c.relname",
            [$table]
        ));
    }

    public function relationExists(string $name): bool
    {
        return (bool) DB::selectOne(
            'SELECT 1 AS ok FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE c.relname = ? AND n.nspname = current_schema()',
            [$name]
        );
    }

    private function assertIdent(string $name): void
    {
        if (! preg_match('/^[a-z_][a-z0-9_]{0,50}$/', $name)) {
            throw new RuntimeException("Invalid table name: {$name}");
        }
    }
}
