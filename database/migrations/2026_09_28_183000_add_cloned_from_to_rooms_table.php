<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track which admin/base room a DMC room copy was cloned from (stores rooms.room_id).
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('rooms', 'cloned_from')) {
                $table->unsignedBigInteger('cloned_from')->nullable()->after('dmc_base_room')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'cloned_from')) {
                $table->dropColumn('cloned_from');
            }
        });
    }
};
