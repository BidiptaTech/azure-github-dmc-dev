<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fair / Blackout cost is stored on rates.price_cost (sell stays on rates.price).
     */
    public function up(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            if (!Schema::hasColumn('rates', 'price_cost')) {
                $table->decimal('price_cost', 50, 2)->nullable()->after('price');
            }
        });

        if (Schema::hasColumn('rates', 'price_cost') && Schema::hasColumn('rates', 'weekday_cost_price')) {
            DB::table('rates')
                ->whereIn('event_type', ['Fair Date', 'Blackout Date'])
                ->whereNull('price_cost')
                ->whereNotNull('weekday_cost_price')
                ->update([
                    'price_cost' => DB::raw('weekday_cost_price'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            if (Schema::hasColumn('rates', 'price_cost')) {
                $table->dropColumn('price_cost');
            }
        });
    }
};
