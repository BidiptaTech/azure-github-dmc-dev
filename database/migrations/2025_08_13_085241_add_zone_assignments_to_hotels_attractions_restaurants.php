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
        // Add zone_assignments column to hotels table
        if (Schema::hasTable('hotels') && !Schema::hasColumn('hotels', 'zone_assignments')) {
            Schema::table('hotels', function (Blueprint $table) {
                $table->json('zone_assignments')->nullable()->after('dmc_id');
            });
        }
        
        // Add zone_assignments column to attractions table
        if (Schema::hasTable('attractions') && !Schema::hasColumn('attractions', 'zone_assignments')) {
            Schema::table('attractions', function (Blueprint $table) {
                $table->json('zone_assignments')->nullable()->after('dmc_id');
            });
        }
        
        // Add zone_assignments column to restaurants table
        if (Schema::hasTable('restaurants') && !Schema::hasColumn('restaurants', 'zone_assignments')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->json('zone_assignments')->nullable()->after('dmc_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove zone_assignments column from hotels table
        if (Schema::hasTable('hotels') && Schema::hasColumn('hotels', 'zone_assignments')) {
            Schema::table('hotels', function (Blueprint $table) {
                $table->dropColumn('zone_assignments');
            });
        }
        
        // Remove zone_assignments column from attractions table
        if (Schema::hasTable('attractions') && Schema::hasColumn('attractions', 'zone_assignments')) {
            Schema::table('attractions', function (Blueprint $table) {
                $table->dropColumn('zone_assignments');
            });
        }
        
        // Remove zone_assignments column from restaurants table
        if (Schema::hasTable('restaurants') && Schema::hasColumn('restaurants', 'zone_assignments')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->dropColumn('zone_assignments');
            });
        }
    }
};
