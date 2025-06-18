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
        Schema::table('room_type', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('room_type', 'hotel_id')) {
                $table->bigInteger('hotel_id')->nullable()->after('status'); // Add column
                $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_type', function (Blueprint $table) {
            //
            Schema::table('room_type', function (Blueprint $table) {
                if (Schema::hasColumn('room_type', 'hotel_id')) {
                    $table->dropForeign(['hotel_id']); // Drop foreign key constraint
                    $table->dropColumn('hotel_id'); // Drop column
                }
            });
        });
    }
};
