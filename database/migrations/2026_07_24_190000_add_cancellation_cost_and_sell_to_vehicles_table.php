<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'cancellation_cost')) {
                $table->decimal('cancellation_cost', 10, 2)->nullable()->after('cancel_cost_price');
            }
            if (!Schema::hasColumn('vehicles', 'cancellation_sell')) {
                $table->decimal('cancellation_sell', 10, 2)->nullable()->after('cancellation_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'cancellation_sell')) {
                $table->dropColumn('cancellation_sell');
            }
            if (Schema::hasColumn('vehicles', 'cancellation_cost')) {
                $table->dropColumn('cancellation_cost');
            }
        });
    }
};
