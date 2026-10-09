<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guest-submitted image URLs for lost & found reports (shown in staff modal).
     */
    public function up(): void
    {
        if (!Schema::hasTable('lost_found')) {
            return;
        }

        if (!Schema::hasColumn('lost_found', 'guest_images')) {
            Schema::table('lost_found', function (Blueprint $table) {
                $table->json('guest_images')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lost_found') && Schema::hasColumn('lost_found', 'guest_images')) {
            Schema::table('lost_found', function (Blueprint $table) {
                $table->dropColumn('guest_images');
            });
        }
    }
};
