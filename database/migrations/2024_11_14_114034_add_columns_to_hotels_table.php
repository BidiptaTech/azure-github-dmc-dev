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
            $table->string('address')->nullable(); 
            $table->string('city')->nullable(); 
            $table->string('state')->nullable(); 
            $table->string('country')->nullable(); 
            $table->string('main_image')->nullable(); 
            $table->string('zipcode', 20)->nullable(); 
            $table->string('latitude', 20)->nullable(); 
            $table->string('longitude', 20)->nullable(); 
            $table->time('check_in_time')->nullable(); 
            $table->time('check_out_time')->nullable(); 
            $table->string('hotel_owner_company_name')->nullable(); 
            $table->string('phone')->nullable(); 
            $table->string('email')->nullable(); 
            $table->json('images')->nullable(); 
            $table->text('description')->nullable(); 
            $table->text('policies')->nullable(); 
            $table->boolean('is_complete')->default(0); 
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
