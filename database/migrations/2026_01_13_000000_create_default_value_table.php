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
        Schema::create('default_value', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('default_id')->unique();
            $table->unsignedBigInteger('dmc_id');
            $table->enum('name', ['hotel', 'restaurant', 'attraction', 'car_private', 'car_shared', 'port']);
            $table->string('service_id'); // Stores hotel_unique_id, restaurant_id, attraction_id, vehicle_id
            $table->tinyInteger('status')->default(1)->comment('1=Active, 0=Inactive');
            $table->softDeletes();
            $table->timestamps();

            // Add indexes for better performance
            $table->index('dmc_id');
            $table->index('name');
            $table->index(['dmc_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('default_value');
    }
};

