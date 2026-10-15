<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Change existing tours.discount_amount to hold decimal currency (no new column).
     * Fixes PostgreSQL: invalid input syntax for type integer: "1605.05"
     */
    public function up(): void
    {
        if (! Schema::hasColumn('tours', 'discount_amount')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement(
                'ALTER TABLE tours ALTER COLUMN discount_amount TYPE DECIMAL(12,2) USING (COALESCE(discount_amount::text, \'0\')::numeric)'
            );
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE tours MODIFY discount_amount DECIMAL(12,2) NULL DEFAULT 0');
        } elseif ($driver === 'sqlite') {
            // SQLite: types are dynamic; skip
        } else {
            Schema::table('tours', function ($table) {
                $table->decimal('discount_amount', 12, 2)->default(0)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tours', 'discount_amount')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement(
                'ALTER TABLE tours ALTER COLUMN discount_amount TYPE INTEGER USING (ROUND(COALESCE(discount_amount, 0))::integer)'
            );
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE tours MODIFY discount_amount INT NULL DEFAULT 0');
        }
    }
};
