<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure restaurants.email has a unique index.
     */
    public function up(): void
    {
        if (!Schema::hasTable('restaurants') || !Schema::hasColumn('restaurants', 'email')) {
            return;
        }

        if ($this->indexExists('restaurants', 'restaurants_email_unique')) {
            return;
        }

        Schema::table('restaurants', function (Blueprint $table) {
            $table->unique('email', 'restaurants_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Index likely pre-existed from 2026_02_04 migration; do not drop on rollback.
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection()->getDriverName();

        if ($connection === 'pgsql') {
            $result = DB::selectOne(
                'SELECT 1 AS exists FROM pg_indexes WHERE tablename = ? AND indexname = ? LIMIT 1',
                [$table, $indexName]
            );

            return (bool) $result;
        }

        if ($connection === 'mysql') {
            $database = Schema::getConnection()->getDatabaseName();
            $result = DB::selectOne(
                'SELECT 1 AS `exists` FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$database, $table, $indexName]
            );

            return (bool) $result;
        }

        return false;
    }
};
