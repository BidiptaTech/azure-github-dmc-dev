<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = file_get_contents(
            base_path('finance-microservice/sql/003_patch_existing_finance_schema.sql')
        );

        if ($sql) {
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        // Compatibility migration is intentionally non-reversible to preserve data.
    }
};
