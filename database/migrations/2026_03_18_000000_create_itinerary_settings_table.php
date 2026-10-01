<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('itinerary_setting_id')->unique();
            $table->string('country')->index();
            $table->string('city')->index();
            $table->json('emergency_contacts')->nullable();
            $table->json('sic_timings')->nullable();
            $table->json('meeting_points')->nullable();
            $table->unsignedBigInteger('dmc_id')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['dmc_id', 'country', 'city'], 'itinerary_settings_dmc_country_city_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_settings');
    }
};

