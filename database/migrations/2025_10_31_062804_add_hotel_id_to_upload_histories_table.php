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
        Schema::table('upload_histories', function (Blueprint $table) {
            $table->string('hotel_id')->nullable()->after('upload_type')->comment('Hotel unique ID for room imports');
            $table->index('hotel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_histories', function (Blueprint $table) {
            $table->dropIndex(['hotel_id']);
            $table->dropColumn('hotel_id');
        });
    }
};
