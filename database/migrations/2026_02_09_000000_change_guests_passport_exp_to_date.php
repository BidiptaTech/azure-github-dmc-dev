<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: passport_exp was created as TIME but stores expiry date (Y-m-d).
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            // Column may be TIME; cannot cast time to date, so use NULL and change type to date
            DB::statement('ALTER TABLE guests ALTER COLUMN passport_exp TYPE date USING NULL');
        } else {
            try {
                Schema::table('guests', function (Blueprint $table) {
                    $table->date('passport_exp')->nullable()->change();
                });
            } catch (\Throwable $e) {
                Schema::table('guests', function (Blueprint $table) {
                    $table->dropColumn('passport_exp');
                });
                Schema::table('guests', function (Blueprint $table) {
                    $table->date('passport_exp')->nullable()->after('passport');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE guests ALTER COLUMN passport_exp TYPE time USING NULL');
        } else {
            Schema::table('guests', function (Blueprint $table) {
                $table->time('passport_exp')->nullable()->change();
            });
        }
    }
};
