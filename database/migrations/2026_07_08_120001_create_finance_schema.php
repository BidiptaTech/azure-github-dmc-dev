<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = file_get_contents(
            base_path('database/sql/finance/001_create_finance_schema.sql')
        );

        if ($sql) {
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        DB::unprepared('
            DROP TABLE IF EXISTS callback_processing_log CASCADE;
            DROP TABLE IF EXISTS wallet_ledger CASCADE;
            DROP TABLE IF EXISTS settlements CASCADE;
            DROP TABLE IF EXISTS payment_transactions CASCADE;
            DROP TABLE IF EXISTS wallet CASCADE;
            DROP TABLE IF EXISTS idempotency_store CASCADE;
            DROP SEQUENCE IF EXISTS finance_reference_seq CASCADE;
            DROP FUNCTION IF EXISTS set_finance_updated_at() CASCADE;
        ');
    }
};
