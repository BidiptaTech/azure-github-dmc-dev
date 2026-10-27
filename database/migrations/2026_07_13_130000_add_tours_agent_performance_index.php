<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(file_get_contents(
            base_path('database/sql/finance/007_tours_agent_performance_index.sql')
        ) ?: '');
    }

    public function down(): void
    {
        DB::unprepared('DROP INDEX IF EXISTS idx_tours_agent_created_at;');
    }
};
