<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itinerary_settings', function (Blueprint $table) {
            $table->longText('itinerary_information')->nullable()->after('meeting_points');
        });
    }

    public function down(): void
    {
        Schema::table('itinerary_settings', function (Blueprint $table) {
            $table->dropColumn('itinerary_information');
        });
    }
};

