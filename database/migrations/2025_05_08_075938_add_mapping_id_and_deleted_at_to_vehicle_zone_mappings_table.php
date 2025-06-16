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
        Schema::table('vehicle_zone_mappings', function (Blueprint $table) {
            $table->string('mapping_id')->nullable()->after('id'); // Add after id if needed
            $table->softDeletes(); // Adds deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_zone_mappings', function (Blueprint $table) {
            $table->dropColumn('mapping_id');
            $table->dropSoftDeletes();
        });
    }
};
