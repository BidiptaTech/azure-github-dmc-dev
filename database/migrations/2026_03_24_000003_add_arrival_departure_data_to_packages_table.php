<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('arrival_data')->nullable()->after('selected_restaurants');
            $table->json('departure_data')->nullable()->after('arrival_data');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['arrival_data', 'departure_data']);
        });
    }
};

