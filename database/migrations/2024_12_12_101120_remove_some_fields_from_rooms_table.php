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
            //
            if (Schema::hasColumn('rooms', 'max_capacity')) {
                $table->dropColumn('max_capacity');
            }
            if (Schema::hasColumn('rooms', 'adult_count')) {
                $table->dropColumn('adult_count');
            }
            if (Schema::hasColumn('rooms', 'child_count')) {
                $table->dropColumn('child_count');
            }
            if (Schema::hasColumn('rooms', 'extra_bed')) {
                $table->dropColumn('extra_bed');
            }
            if (Schema::hasColumn('rooms', 'bed_type')) {
                $table->dropColumn('bed_type');
            }
            if (Schema::hasColumn('rooms', 'extra_bed_price')) {
                $table->dropColumn('extra_bed_price');
            }
            if (Schema::hasColumn('rooms', 'child_cot')) {
                $table->dropColumn('child_cot');
            }
            if (Schema::hasColumn('rooms', 'child_cot_price')) {
                $table->dropColumn('child_cot_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            //
        });
    }
};
