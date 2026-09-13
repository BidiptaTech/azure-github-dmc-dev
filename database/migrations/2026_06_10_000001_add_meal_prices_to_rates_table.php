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
        Schema::table('rates', function (Blueprint $table) {
            $table->decimal('breakfast_price', 8, 2)->default(0.00);
            $table->decimal('lunch_price', 8, 2)->default(0.00);
            $table->decimal('dinner_price', 8, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->dropColumn(['breakfast_price', 'lunch_price', 'dinner_price']);
        });
    }
};
