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
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('no_of_room')->default(0); 
            $table->integer('weekday_price')->default(0); 
            $table->integer('weekend_price')->default(0); 
            $table->integer('adult_count')->default(0); 
            $table->integer('child_count')->default(0); 
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
