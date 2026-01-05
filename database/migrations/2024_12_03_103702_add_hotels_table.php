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
        Schema::table('hotels', function (Blueprint $table) {
            $table->boolean('12_hour_book')->default(false)->comment("0 = not available, 1 = available"); 
            $table->boolean('cancellation_type')->default(false)->comment("0 = free, 1 = chargeable"); 
            $table->json('cancellation_data')->nullable()->comment("The hour before cancellation");
            $table->boolean('conference_room')->default(false)->comment("0 = not available, 1 = available");
            $table->json('conference_data')->nullable()->comment("JSON data for conference room details");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
