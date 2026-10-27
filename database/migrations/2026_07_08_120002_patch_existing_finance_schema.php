<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = file_get_contents(
            base_path('database/sql/finance/003_patch_existing_finance_schema.sql')
        );

        if ($sql) {
            // Guard against Windows-1252 smart dashes that break PostgreSQL UTF8.
            if (! mb_check_encoding($sql, 'UTF-8')) {
                $sql = mb_convert_encoding($sql, 'UTF-8', 'Windows-1252');
            }
            $sql = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '-', $sql) ?? $sql;
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        // Compatibility migration is intentionally non-reversible to preserve data.
    }
};
