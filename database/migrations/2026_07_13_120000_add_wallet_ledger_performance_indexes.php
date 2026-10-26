<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(file_get_contents(
            base_path('database/sql/finance/006_wallet_ledger_performance_indexes.sql')
        ) ?: '');
    }

    public function down(): void
    {
        DB::unprepared('
            DROP INDEX IF EXISTS idx_wallet_ledger_wallet_created_id;
            DROP INDEX IF EXISTS idx_wallet_ledger_created_at;
        ');
    }
};
