<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce:
 * - only one Travclicks dmc_invoice price row (keep latest)
 * - only one dmc_offer row per dmc_id (keep latest per DMC)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dmc_invoice')) {
            $keepId = DB::table('dmc_invoice')->orderByDesc('id')->value('id');
            if ($keepId) {
                DB::table('dmc_invoice')->where('id', '!=', $keepId)->delete();
            }
        }

        if (! Schema::hasTable('dmc_offer')) {
            return;
        }

        $duplicateDmcIds = DB::table('dmc_offer')
            ->select('dmc_id')
            ->groupBy('dmc_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('dmc_id');

        foreach ($duplicateDmcIds as $dmcId) {
            $keepId = DB::table('dmc_offer')
                ->where('dmc_id', $dmcId)
                ->orderByDesc('id')
                ->value('id');

            if ($keepId) {
                DB::table('dmc_offer')
                    ->where('dmc_id', $dmcId)
                    ->where('id', '!=', $keepId)
                    ->delete();
            }
        }

        if (! $this->hasUniqueDmcIdIndex()) {
            Schema::table('dmc_offer', function (Blueprint $table) {
                $table->unique('dmc_id', 'dmc_offer_dmc_id_unique');
            });
        }
    }

    private function hasUniqueDmcIdIndex(): bool
    {
        try {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'pgsql') {
                return DB::table('pg_indexes')
                    ->where('indexname', 'dmc_offer_dmc_id_unique')
                    ->exists();
            }
            if ($driver === 'mysql') {
                $rows = DB::select('SHOW INDEX FROM dmc_offer WHERE Key_name = ?', ['dmc_offer_dmc_id_unique']);

                return ! empty($rows);
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    public function down(): void
    {
        if (! Schema::hasTable('dmc_offer')) {
            return;
        }

        try {
            Schema::table('dmc_offer', function (Blueprint $table) {
                $table->dropUnique('dmc_offer_dmc_id_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
