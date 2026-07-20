<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add cost-price counterparts for room sell pricing fields.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'child_with_bed')) {
                $table->numeric('child_with_bed', 50, 2)->nullable()->after('children_price');
            }
            if (!Schema::hasColumn('rooms', 'child_with_bed_cost')) {
                $table->numeric('child_with_bed_cost', 50, 2)->nullable()->after('child_with_bed');
            }
            if (!Schema::hasColumn('rooms', 'child_without_bed')) {
                $table->numeric('child_without_bed', 50, 2)->nullable()->after('child_with_bed_cost');
            }
            if (!Schema::hasColumn('rooms', 'child_without_bed_cost')) {
                $table->numeric('child_without_bed_cost', 50, 2)->nullable()->after('child_without_bed');
            }

            if (!Schema::hasColumn('rooms', 'weekday_cost_price')) {
                $table->numeric('weekday_cost_price', 50, 2)->nullable()->after('weekday_price');
            }
            if (!Schema::hasColumn('rooms', 'weekend_cost_price')) {
                $table->numeric('weekend_cost_price', 50, 2)->nullable()->after('weekend_price');
            }
            if (!Schema::hasColumn('rooms', 'double_weekday_cost_price')) {
                $table->numeric('double_weekday_cost_price', 50, 2)->nullable()->after('double_weekday_price');
            }
            if (!Schema::hasColumn('rooms', 'double_weekend_cost_price')) {
                $table->numeric('double_weekend_cost_price', 50, 2)->nullable()->after('double_weekend_price');
            }

            if (!Schema::hasColumn('rooms', 'breakfast_cost_price')) {
                $table->numeric('breakfast_cost_price', 50, 2)->nullable()->after('breakfast_price');
            }
            if (!Schema::hasColumn('rooms', 'lunch_cost_price')) {
                $table->numeric('lunch_cost_price', 50, 2)->nullable()->after('lunch_price');
            }
            if (!Schema::hasColumn('rooms', 'dinner_cost_price')) {
                $table->numeric('dinner_cost_price', 50, 2)->nullable()->after('dinner_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $columns = [
                'weekday_cost_price',
                'weekend_cost_price',
                'double_weekday_cost_price',
                'double_weekend_cost_price',
                'breakfast_cost_price',
                'lunch_cost_price',
                'dinner_cost_price',
                'child_with_bed_cost',
                'child_without_bed_cost',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('rooms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
