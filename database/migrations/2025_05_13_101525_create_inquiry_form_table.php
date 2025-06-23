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
        Schema::create('inquiry_form', function (Blueprint $table) {
            $table->id();

            // Basic details
            $table->unsignedBigInteger('agent_id');
            $table->string('country');
            $table->string('city');
            $table->string('unique_tour_id')->unique();

            // Guest counts
            $table->integer('adult')->default(0);
            $table->integer('child')->default(0);
            $table->integer('infant')->default(0);
            $table->integer('male_count')->default(0);
            $table->integer('female_count')->default(0);

            // Check-in/out
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();

            // Hotel section
            $table->boolean('hotel')->default(0); // 0 or 1 flag
            $table->json('hotel_ids')->nullable(); // multiple hotel IDs
            $table->json('hotel_categories')->nullable(); // multiple categories
            $table->text('hotel_remarks')->nullable();

            // Pickup
            $table->boolean('pickup')->default(0); // 0 or 1
            $table->text('pickup_remarks')->nullable();

            // Local transfer
            $table->boolean('local_transfer')->default(0); // 0 or 1

            // Attraction
            $table->boolean('attraction')->default(0);
            $table->json('attraction_ids')->nullable();
            $table->text('attraction_remarks')->nullable();

            // Restaurant
            $table->boolean('restaurant')->default(0);
            $table->json('restaurant_ids')->nullable();
            $table->text('restaurant_remarks')->nullable();

            // Guide
            $table->boolean('guide')->default(0);
            $table->json('guide_ids')->nullable();
            $table->text('guide_remarks')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_form');
    }
};
