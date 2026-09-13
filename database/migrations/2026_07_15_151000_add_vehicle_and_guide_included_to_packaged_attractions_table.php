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
        Schema::table('packaged_attractions', function (Blueprint $table) {
            $table->boolean('vehicle_included')->default(false)->after('child_price');
            $table->boolean('guide_included')->default(false)->after('vehicle_included');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packaged_attractions', function (Blueprint $table) {
            $table->dropColumn(['vehicle_included', 'guide_included']);
        });
    }
};
