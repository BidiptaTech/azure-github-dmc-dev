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
        Schema::create('operational_countries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('operational_country_id')->unsigned()->unique();
            $table->string('name');
            $table->string('distance_unit');
            $table->string('city_currency');
            $table->string('city_car_traffic');
            $table->float('base_distance');
            $table->decimal('cost_per_km_below_10', 8, 2);
            $table->decimal('cost_per_km_10_to_25', 8, 2);
            $table->decimal('cost_per_km_above_25', 8, 2);
            $table->decimal('cost_per_hour', 8, 2);
            $table->decimal('cancel_cost', 8, 2);
            $table->decimal('night_cost_per_km_below_10', 8, 2);
            $table->decimal('night_cost_per_km_10_to_25', 8, 2);
            $table->decimal('night_cost_per_km_above_25', 8, 2);
            $table->decimal('night_cost_per_hour', 8, 2);
            $table->decimal('night_cancel_cost', 8, 2);
            
            $table->integer('charge type');
            $table->decimal('holiday_charges');
            $table->json('holiday_dates');
            $table->integer('holiday_charge type');
            $table->decimal('entry_port_pickup_charge', 8, 2)->nullable();
            $table->decimal('exit_port_drop_charge', 8, 2)->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_countries');
    }
};
