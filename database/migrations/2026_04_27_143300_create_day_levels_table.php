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
        Schema::create('day_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('country')->nullable();
            $table->integer('days')->default(1);

            $table->json('hotels')->nullable();
            $table->string('airport_transfer_type')->nullable();
            $table->decimal('airport_transfer_cost', 12, 2)->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('vehicle_service_type')->nullable();
            $table->integer('vehicle_passengers')->nullable();

            $table->json('activities')->nullable();
            $table->unsignedBigInteger('guide_id')->nullable();
            $table->decimal('guide_cost', 12, 2)->nullable();
            $table->json('inter_city')->nullable();

            $table->unsignedBigInteger('dmc_id')->nullable();
            $table->unsignedBigInteger('master_dmc_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_levels');
    }
};
