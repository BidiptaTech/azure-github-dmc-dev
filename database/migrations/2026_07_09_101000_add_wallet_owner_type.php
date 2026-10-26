<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = file_get_contents(
            base_path('database/sql/finance/005_add_wallet_owner_type.sql')
        );

        if ($sql) {
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        // Non-reversible data backfill migration.
    }
};
