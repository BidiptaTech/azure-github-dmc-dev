<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen vehicle zone mapping price columns to NUMERIC(50,2).
     */
    public function up(): void
    {
        $columns = [
            'private_cost_price',
            'shared_cost_price',
            'private_profit_amount',
            'shared_profit_amount',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('vehicle_zone_mappings', $column)) {
                DB::statement("ALTER TABLE vehicle_zone_mappings ALTER COLUMN {$column} TYPE NUMERIC(50,2) USING COALESCE({$column}, 0)::numeric(50,2)");
            }
        }
    }

    public function down(): void
    {
        $columns = [
            'private_cost_price',
            'shared_cost_price',
            'private_profit_amount',
            'shared_profit_amount',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('vehicle_zone_mappings', $column)) {
                DB::statement("ALTER TABLE vehicle_zone_mappings ALTER COLUMN {$column} TYPE NUMERIC(10,2) USING COALESCE({$column}, 0)::numeric(10,2)");
            }
        }
    }
};
