<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store current_rate with at most 2 decimal places (was numeric(18,8)).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('countries', 'current_rate')) {
            return;
        }

        // Match sync rounding: <100 → 2dp, >=100 → whole number; then shrink column scale.
        DB::statement("
            UPDATE countries
            SET current_rate = CASE
                WHEN current_rate >= 100 THEN ROUND(current_rate::numeric, 0)
                ELSE ROUND(current_rate::numeric, 2)
            END
            WHERE current_rate IS NOT NULL
        ");
        DB::statement('ALTER TABLE countries ALTER COLUMN current_rate TYPE numeric(18,2) USING current_rate::numeric(18,2)');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('countries', 'current_rate')) {
            return;
        }

        DB::statement('ALTER TABLE countries ALTER COLUMN current_rate TYPE numeric(18,8) USING current_rate::numeric(18,8)');
    }
};
