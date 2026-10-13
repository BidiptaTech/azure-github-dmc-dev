<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy/default mismatch: column default was 'draft' but chk_tours_finance_status
        // only allows pending, partially_settled, settled, failed, refunded.
        DB::statement("UPDATE tours SET finance_status = 'pending' WHERE finance_status = 'draft' OR finance_status IS NULL");
        DB::statement("ALTER TABLE tours ALTER COLUMN finance_status SET DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tours ALTER COLUMN finance_status SET DEFAULT 'draft'");
    }
};
