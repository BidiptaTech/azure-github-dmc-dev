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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cuisine');
            $table->string('hotel_id');
            $table->integer('breakfast_available')->default(0)->comment('0 = Not Available, 1 = Available');
            $table->time('opening_time_bf')->nullable();
            $table->time('closing_time_bf')->nullable();
            $table->string('breakfast_type')->nullable();

            $table->integer('lunch_available')->default(0)->comment('0 = Not Available, 1 = Available');
            $table->time('opening_time_lunch')->nullable();
            $table->time('closing_time_lunch')->nullable();
            $table->string('lunch_type')->nullable();

            $table->integer('dinner_available')->default(0)->comment('0 = Not Available, 1 = Available');
            $table->time('opening_time_dinner')->nullable();
            $table->time('closing_time_dinner')->nullable();
            $table->string('dinner_type')->nullable();

            $table->integer('owned_by')->nullable()->comment('0 = Third party, 1 = Owned by hotel');

            $table->foreign('hotel_id')->references('hotel_unique_id')->on('hotels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
