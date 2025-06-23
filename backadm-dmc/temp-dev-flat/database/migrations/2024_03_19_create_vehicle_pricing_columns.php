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
            // Regular Day Pricing
            $table->decimal('base_price', 10, 2)->nullable();
            $table->decimal('cost_per_km_below_10', 10, 2)->nullable();
            $table->decimal('cost_per_km_10_to_25', 10, 2)->nullable();
            $table->decimal('cost_per_km_above_25', 10, 2)->nullable();
            $table->decimal('cost_per_hour', 10, 2)->nullable();
            $table->decimal('cancel_cost', 10, 2)->nullable();

            // Regular Night Pricing
            $table->decimal('night_base_price', 10, 2)->nullable();
            $table->decimal('night_cost_per_km_below_10', 10, 2)->nullable();
            $table->decimal('night_cost_per_km_10_to_25', 10, 2)->nullable();
            $table->decimal('night_cost_per_km_above_25', 10, 2)->nullable();
            $table->decimal('night_cost_per_hour', 10, 2)->nullable();
            $table->decimal('night_cancel_cost', 10, 2)->nullable();

            // Sharable Day Pricing
            $table->decimal('sharable_base_price', 10, 2)->nullable();
            $table->decimal('sharable_cost_per_km_below_10', 10, 2)->nullable();
            $table->decimal('sharable_cost_per_km_10_to_25', 10, 2)->nullable();
            $table->decimal('sharable_cost_per_km_above_25', 10, 2)->nullable();
            $table->decimal('sharable_cost_per_hour', 10, 2)->nullable();
            $table->decimal('sharable_cancel_cost', 10, 2)->nullable();

            // Sharable Night Pricing
            $table->decimal('sharable_night_base_price', 10, 2)->nullable();
            $table->decimal('sharable_night_cost_per_km_below_10', 10, 2)->nullable();
            $table->decimal('sharable_night_cost_per_km_10_to_25', 10, 2)->nullable();
            $table->decimal('sharable_night_cost_per_km_above_25', 10, 2)->nullable();
            $table->decimal('sharable_night_cost_per_hour', 10, 2)->nullable();
            $table->decimal('sharable_night_cancel_cost', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop Regular Day Pricing
            $table->dropColumn('base_price');
            $table->dropColumn('cost_per_km_below_10');
            $table->dropColumn('cost_per_km_10_to_25');
            $table->dropColumn('cost_per_km_above_25');
            $table->dropColumn('cost_per_hour');
            $table->dropColumn('cancel_cost');

            // Drop Regular Night Pricing
            $table->dropColumn('night_base_price');
            $table->dropColumn('night_cost_per_km_below_10');
            $table->dropColumn('night_cost_per_km_10_to_25');
            $table->dropColumn('night_cost_per_km_above_25');
            $table->dropColumn('night_cost_per_hour');
            $table->dropColumn('night_cancel_cost');

            // Drop Sharable Day Pricing
            $table->dropColumn('sharable_base_price');
            $table->dropColumn('sharable_cost_per_km_below_10');
            $table->dropColumn('sharable_cost_per_km_10_to_25');
            $table->dropColumn('sharable_cost_per_km_above_25');
            $table->dropColumn('sharable_cost_per_hour');
            $table->dropColumn('sharable_cancel_cost');

            // Drop Sharable Night Pricing
            $table->dropColumn('sharable_night_base_price');
            $table->dropColumn('sharable_night_cost_per_km_below_10');
            $table->dropColumn('sharable_night_cost_per_km_10_to_25');
            $table->dropColumn('sharable_night_cost_per_km_above_25');
            $table->dropColumn('sharable_night_cost_per_hour');
            $table->dropColumn('sharable_night_cancel_cost');
        });
    }
}; 