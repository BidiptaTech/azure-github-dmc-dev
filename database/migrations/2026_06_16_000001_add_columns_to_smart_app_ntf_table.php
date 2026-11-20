<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('smartApp_Ntf')) {
            DB::statement('CREATE TABLE "smartApp_Ntf" ()');
        }

        DB::statement('ALTER TABLE "smartApp_Ntf" ADD COLUMN IF NOT EXISTS id BIGSERIAL PRIMARY KEY');
        DB::statement('ALTER TABLE "smartApp_Ntf" ADD COLUMN IF NOT EXISTS sender_type VARCHAR(50)');
        DB::statement("ALTER TABLE \"smartApp_Ntf\" ADD COLUMN IF NOT EXISTS receiver JSONB NOT NULL DEFAULT '[]'::jsonb");
        DB::statement('ALTER TABLE "smartApp_Ntf" ADD COLUMN IF NOT EXISTS title VARCHAR(255)');
        DB::statement('ALTER TABLE "smartApp_Ntf" ADD COLUMN IF NOT EXISTS message TEXT');
        DB::statement('ALTER TABLE "smartApp_Ntf" ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL');
        DB::statement('ALTER TABLE "smartApp_Ntf" ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('smartApp_Ntf')) {
            return;
        }

        foreach (['updated_at', 'created_at', 'message', 'title', 'receiver', 'sender_type', 'id'] as $column) {
            DB::statement('ALTER TABLE "smartApp_Ntf" DROP COLUMN IF EXISTS ' . $column);
        }
    }
};
