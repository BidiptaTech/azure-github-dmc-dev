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
        Schema::create('city_explorations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id')->unique();
            
            // City Overview - JSON format
            $table->json('overview')->nullable()->comment('City name, image, short description, best known for, language, currency, timezone, population');
            
            // Top Attractions - JSON array
            $table->json('attractions')->nullable()->comment('Array of attractions with type, name, image');
            
            // Food and Cuisine - JSON format
            $table->json('food_cuisine')->nullable()->comment('Famous dishes, restaurants, street spots with images');
            
            // Stay and Accommodation - JSON array
            $table->json('accommodation')->nullable()->comment('Popular hotels with images');
            
            // Transportation - JSON format
            $table->json('transportation')->nullable()->comment('Airports, railway stations, local transport options');
            
            // Best Time to Visit - JSON format
            $table->json('best_time_visit')->nullable()->comment('Seasonal highlights and festival periods');
            
            // Shopping - JSON array
            $table->json('shopping')->nullable()->comment('Famous markets and malls');
            
            // Hospitals and Emergency - JSON format
            $table->json('hospitals_emergency')->nullable()->comment('Hospitals, pharmacies, emergency numbers');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key
            $table->foreign('city_id')->references('city_id')->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_explorations');
    }
};

