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
        Schema::create('package_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id');
            $table->unsignedBigInteger('package_id');

            $table->json('booking_details');
            $table->json('travel_dates');
            $table->json('selected_hotels');
            $table->json('selected_attractions');
            $table->json('selected_guides');
            $table->json('selected_restaurants');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('booked_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_bookings');
    }
};
