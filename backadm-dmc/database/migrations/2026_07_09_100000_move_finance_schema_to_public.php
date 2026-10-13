<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = file_get_contents(
            base_path('finance-microservice/sql/004_move_finance_schema_to_public.sql')
        );

        if ($sql) {
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        // Data-preserving schema move is intentionally non-reversible.
    }
};
