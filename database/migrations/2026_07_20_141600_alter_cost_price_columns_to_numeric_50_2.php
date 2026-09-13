<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->alterTableColumns('rooms', [
            'child_with_bed',
            'child_with_bed_cost',
            'child_without_bed',
            'child_without_bed_cost',
            'weekday_cost_price',
            'weekend_cost_price',
            'double_weekday_cost_price',
            'double_weekend_cost_price',
            'breakfast_cost_price',
            'lunch_cost_price',
            'dinner_cost_price',
        ]);

        $this->alterTableColumns('beds', [
            'extra_bed_cost_price',
            'baby_cot_cost_price',
        ]);

        $this->alterTableColumns('rates', [
            'weekday_cost_price',
            'weekend_cost_price',
            'double_weekday_cost_price',
            'double_weekend_cost_price',
            'breakfast_cost_price',
            'lunch_cost_price',
            'dinner_cost_price',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->alterTableColumns('rooms', [
            'child_with_bed',
            'child_with_bed_cost',
            'child_without_bed',
            'child_without_bed_cost',
            'weekday_cost_price',
            'weekend_cost_price',
            'double_weekday_cost_price',
            'double_weekend_cost_price',
            'breakfast_cost_price',
            'lunch_cost_price',
            'dinner_cost_price',
        ], 10);

        $this->alterTableColumns('beds', [
            'extra_bed_cost_price',
            'baby_cot_cost_price',
        ], 10);

        $this->alterTableColumns('rates', [
            'weekday_cost_price',
            'weekend_cost_price',
            'double_weekday_cost_price',
            'double_weekend_cost_price',
            'breakfast_cost_price',
            'lunch_cost_price',
            'dinner_cost_price',
        ], 10);
    }

    private function alterTableColumns(string $table, array $columns, int $precision = 50, int $scale = 2): void
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                DB::statement(sprintf(
                    'ALTER TABLE "%s" ALTER COLUMN "%s" TYPE NUMERIC(%d,%d) USING "%s"::NUMERIC(%d,%d)',
                    $table,
                    $column,
                    $precision,
                    $scale,
                    $column,
                    $precision,
                    $scale
                ));
            }
        }
    }
};

