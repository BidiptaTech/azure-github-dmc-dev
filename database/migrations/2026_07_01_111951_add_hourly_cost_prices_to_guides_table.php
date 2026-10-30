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
        Schema::table('guides', function (Blueprint $table) {
            $table->decimal('hourly_cost_price', 10, 2)->nullable()->after('hourly_price');
            $table->decimal('two_hour_cost_price', 10, 2)->nullable()->after('two_hour_price');
            $table->decimal('four_hour_cost_price', 10, 2)->nullable()->after('four_hour_price');
            $table->decimal('six_hour_cost_price', 10, 2)->nullable()->after('six_hour_price');
            $table->decimal('eight_hour_cost_price', 10, 2)->nullable()->after('eight_hour_price');
            $table->decimal('ten_hour_cost_price', 10, 2)->nullable()->after('ten_hour_price');
            $table->decimal('twelve_hour_cost_price', 10, 2)->nullable()->after('twelve_hour_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->dropColumn([
                'hourly_cost_price',
                'two_hour_cost_price',
                'four_hour_cost_price',
                'six_hour_cost_price',
                'eight_hour_cost_price',
                'ten_hour_cost_price',
                'twelve_hour_cost_price',
            ]);
        });
    }
};
