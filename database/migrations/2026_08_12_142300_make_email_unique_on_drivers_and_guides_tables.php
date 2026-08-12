<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drivers: unique among non-deleted rows (soft deletes allowed to share historical emails).
     * Guides: full unique index if missing (existing DB already has guides_email_unique).
     */
    public function up(): void
    {
        if (Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'email')) {
            if (!$this->indexExists('drivers', 'drivers_email_unique')) {
                DB::statement('CREATE UNIQUE INDEX drivers_email_unique ON drivers (email) WHERE deleted_at IS NULL');
            }
        }

        if (Schema::hasTable('guides') && Schema::hasColumn('guides', 'email')) {
            if (!$this->indexExists('guides', 'guides_email_unique')) {
                Schema::table('guides', function (Blueprint $table) {
                    $table->unique('email', 'guides_email_unique');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('drivers') && $this->indexExists('drivers', 'drivers_email_unique')) {
            DB::statement('DROP INDEX IF EXISTS drivers_email_unique');
        }

        // guides_email_unique already existed before this migration; do not drop it on rollback.
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
