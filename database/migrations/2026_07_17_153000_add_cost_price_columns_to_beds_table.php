<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add cost-price counterparts for extra bed and baby cot sell prices.
     */
    public function up(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            if (!Schema::hasColumn('beds', 'extra_bed_cost_price')) {
                $table->numeric('extra_bed_cost_price', 50, 2)->nullable()->after('extra_bed_price');
            }
            if (!Schema::hasColumn('beds', 'baby_cot_cost_price')) {
                $table->numeric('baby_cot_cost_price', 50, 2)->nullable()->after('baby_cot_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            if (Schema::hasColumn('beds', 'extra_bed_cost_price')) {
                $table->dropColumn('extra_bed_cost_price');
            }
            if (Schema::hasColumn('beds', 'baby_cot_cost_price')) {
                $table->dropColumn('baby_cot_cost_price');
            }
        });
    }
};
