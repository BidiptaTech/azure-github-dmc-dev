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
        Schema::table('restaurants', function (Blueprint $table) {
            //
            $table->decimal('bf_price')->nullable();
            $table->decimal('lunch_price')->nullable();
            $table->decimal('dinner_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            //
            $table->dropColumn('bf_price');
            $table->dropColumn('lunch_price');
            $table->dropColumn('dinner_price');
        });
    }
};
