<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('smartApp_Ntf')) {
            return;
        }

        DB::statement('ALTER TABLE "smartApp_Ntf" ADD COLUMN IF NOT EXISTS dmc_id BIGINT NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('smartApp_Ntf')) {
            return;
        }

        DB::statement('ALTER TABLE "smartApp_Ntf" DROP COLUMN IF EXISTS dmc_id');
    }
};
