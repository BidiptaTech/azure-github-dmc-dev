<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add cost-price counterparts for season/rate sell pricing fields.
     */
    public function up(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            if (!Schema::hasColumn('rates', 'weekday_cost_price')) {
                $table->numeric('weekday_cost_price', 50, 2)->nullable()->after('weekday_price');
            }
            if (!Schema::hasColumn('rates', 'weekend_cost_price')) {
                $table->numeric('weekend_cost_price', 50, 2)->nullable()->after('weekend_price');
            }
            if (!Schema::hasColumn('rates', 'double_weekday_cost_price')) {
                $table->numeric('double_weekday_cost_price', 50, 2)->nullable()->after('double_weekday_price');
            }
            if (!Schema::hasColumn('rates', 'double_weekend_cost_price')) {
                $table->numeric('double_weekend_cost_price', 50, 2)->nullable()->after('double_weekend_price');
            }
            if (!Schema::hasColumn('rates', 'breakfast_cost_price')) {
                $table->numeric('breakfast_cost_price', 50, 2)->nullable()->after('breakfast_price');
            }
            if (!Schema::hasColumn('rates', 'lunch_cost_price')) {
                $table->numeric('lunch_cost_price', 50, 2)->nullable()->after('lunch_price');
            }
            if (!Schema::hasColumn('rates', 'dinner_cost_price')) {
                $table->numeric('dinner_cost_price', 50, 2)->nullable()->after('dinner_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $columns = [
                'weekday_cost_price',
                'weekend_cost_price',
                'double_weekday_cost_price',
                'double_weekend_cost_price',
                'breakfast_cost_price',
                'lunch_cost_price',
                'dinner_cost_price',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('rates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
