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
        Schema::table('package_bookings', function (Blueprint $table) {
            $table->json('arrival_data')->nullable()->after('selected_restaurants');
            $table->json('departure_data')->nullable()->after('arrival_data');
            $table->json('transfer_data')->nullable()->after('departure_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_bookings', function (Blueprint $table) {
            $table->dropColumn(['arrival_data', 'departure_data', 'transfer_data']);
        });
    }
};

