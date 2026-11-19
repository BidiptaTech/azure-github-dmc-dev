<?php

namespace App\Console\Commands;

use App\Services\Database\MonthlyPartitionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Keep month/year partitions ready ahead of time (scheduled monthly) and report status.
 */
class EnsureMonthlyPartitions extends Command
{
    protected $signature = 'partitions:ensure
        {--months=3 : Months ahead to create}
        {--status : Show rows per partition}
        {--drop-legacy : Drop the pre-partition backup tables (<table>__prepart) after you have verified the conversion}';

    protected $description = 'Create upcoming monthly partitions for tours/orders/payment tables';

    public function handle(MonthlyPartitionManager $manager): int
    {
        $months = max(1, (int) $this->option('months'));

        foreach (MonthlyPartitionManager::TABLES as $table) {
            if (! $manager->isPartitioned($table)) {
                $this->warn("{$table}: not partitioned (run migrations) — skipped");

                continue;
            }

            $manager->ensureDefault($table);
            $created = $manager->ensureRange($table, null, $months);
            $this->line(sprintf('%-30s %s', $table, $created ? 'created '.implode(', ', $created) : 'up to date'));

            $default = (int) DB::selectOne("SELECT count(*) AS c FROM \"{$table}_pdefault\"")->c;
            if ($default > 0) {
                $this->warn("  {$default} row(s) in {$table}_pdefault (dates outside the monthly range) — run with a wider --months or check created_at values");
            }

            if ($this->option('status')) {
                foreach ($manager->summary($table) as $p) {
                    $this->line(sprintf('    %-40s %d', $p['partition'], $p['rows']));
                }
            }

            if ($this->option('drop-legacy') && $manager->relationExists($table.'__prepart')) {
                DB::statement("DROP TABLE \"{$table}__prepart\"");
                $this->info("  dropped {$table}__prepart");
            }
        }

        return self::SUCCESS;
    }
}
