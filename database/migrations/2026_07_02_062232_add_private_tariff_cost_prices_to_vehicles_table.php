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
            $table->decimal('base_cost_price', 10, 2)->nullable()->after('base_price');
            $table->decimal('per_km_below_10_cost_price', 10, 2)->nullable()->after('cost_per_km_below_10');
            $table->decimal('per_km_10_to_25_cost_price', 10, 2)->nullable()->after('cost_per_km_10_to_25');
            $table->decimal('per_km_above_25_cost_price', 10, 2)->nullable()->after('cost_per_km_above_25');
            $table->decimal('per_hour_cost_price', 10, 2)->nullable()->after('cost_per_hour');
            $table->decimal('cancel_cost_price', 10, 2)->nullable()->after('cancel_cost');

            $table->decimal('night_base_cost_price', 10, 2)->nullable()->after('night_base_price');
            $table->decimal('night_per_km_below_10_cost_price', 10, 2)->nullable()->after('night_cost_per_km_below_10');
            $table->decimal('night_per_km_10_to_25_cost_price', 10, 2)->nullable()->after('night_cost_per_km_10_to_25');
            $table->decimal('night_per_km_above_25_cost_price', 10, 2)->nullable()->after('night_cost_per_km_above_25');
            $table->decimal('night_per_hour_cost_price', 10, 2)->nullable()->after('night_cost_per_hour');
            $table->decimal('night_cancel_cost_price', 10, 2)->nullable()->after('night_cancel_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'base_cost_price',
                'per_km_below_10_cost_price',
                'per_km_10_to_25_cost_price',
                'per_km_above_25_cost_price',
                'per_hour_cost_price',
                'cancel_cost_price',
                'night_base_cost_price',
                'night_per_km_below_10_cost_price',
                'night_per_km_10_to_25_cost_price',
                'night_per_km_above_25_cost_price',
                'night_per_hour_cost_price',
                'night_cancel_cost_price',
            ]);
        });
    }
};
