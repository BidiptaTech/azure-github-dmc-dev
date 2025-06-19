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
            // Meal inclusion flags
            $table->integer('includes_breakfast')->nullable()->default(0)->comment("0 = No, 1 = Yes");
            $table->integer('breakfast_type')->nullable()->comment("0 = buffet, 1 = set buffet");
            $table->decimal('breakfast_price', 8, 2)->nullable()->comment("Price for breakfast");
        
            $table->integer('includes_lunch')->nullable()->default(0)->comment("0 = No, 1 = Yes");
            $table->decimal('lunch_price', 8, 2)->nullable()->comment("Price for lunch");
        
            $table->integer('includes_dinner')->nullable()->default(0)->comment("0 = No, 1 = Yes");
            $table->decimal('dinner_price', 8, 2)->nullable()->comment("Price for dinner");
        
            // Age limits for discounts or policies
            $table->integer('infant_age_limit')->nullable()->comment("Age limit for infants");
            $table->integer('child_age_limit')->nullable()->comment("Age limit for children");
        
            $table->json('weekend_days')
                ->default(json_encode(['Saturday', 'Sunday']));
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            //
        });
    }
};
