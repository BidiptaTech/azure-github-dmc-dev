<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('miscellaneous_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('miscellaneous_prices', 'country')) {
                $table->string('country', 191)->default('')->after('dmc_id');
            }
            if (!Schema::hasColumn('miscellaneous_prices', 'city')) {
                $table->string('city', 191)->default('')->after('country');
            }
        });

        DB::table('miscellaneous_prices')->whereNull('country')->update(['country' => '']);
        DB::table('miscellaneous_prices')->whereNull('city')->update(['city' => '']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // Drop legacy unique(mis_id, dmc_id) if present
            DB::statement('ALTER TABLE miscellaneous_prices DROP CONSTRAINT IF EXISTS unique_mis_dmc');
            DB::statement('DROP INDEX IF EXISTS unique_mis_dmc');

            DB::statement('ALTER TABLE miscellaneous_prices DROP CONSTRAINT IF EXISTS unique_mis_dmc_country_city');
            DB::statement('DROP INDEX IF EXISTS unique_mis_dmc_country_city');

            // Soft-deleted rows must not block the same country/city
            DB::statement('CREATE UNIQUE INDEX unique_mis_dmc_country_city ON miscellaneous_prices (mis_id, dmc_id, country, city) WHERE deleted_at IS NULL');
        } else {
            Schema::table('miscellaneous_prices', function (Blueprint $table) {
                try {
                    $table->dropUnique('unique_mis_dmc');
                } catch (\Throwable $e) {
                    // ignore
                }
                try {
                    $table->unique(['mis_id', 'dmc_id', 'country', 'city'], 'unique_mis_dmc_country_city');
                } catch (\Throwable $e) {
                    // ignore
                }
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS unique_mis_dmc_country_city');
            DB::statement('ALTER TABLE miscellaneous_prices DROP CONSTRAINT IF EXISTS unique_mis_dmc_country_city');
        } else {
            Schema::table('miscellaneous_prices', function (Blueprint $table) {
                try {
                    $table->dropUnique('unique_mis_dmc_country_city');
                } catch (\Throwable $e) {
                    // ignore
                }
            });
        }

        Schema::table('miscellaneous_prices', function (Blueprint $table) {
            if (Schema::hasColumn('miscellaneous_prices', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('miscellaneous_prices', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
