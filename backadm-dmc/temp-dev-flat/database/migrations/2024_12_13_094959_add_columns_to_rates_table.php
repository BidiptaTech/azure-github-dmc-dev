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
            //
            $table->decimal('weekday_price', 8, 2)->default(0.00);
            $table->decimal('weekend_price', 8, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            //
            $table->dropColumn(['weekday_price']);
            $table->dropColumn(['weekend_price']);
        });
    }
};
