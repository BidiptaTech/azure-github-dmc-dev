<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * lost_found already exists — add JSON column for Azure image URLs (staff responses).
     */
    public function up(): void
    {
        if (!Schema::hasTable('lost_found')) {
            return;
        }

        if (!Schema::hasColumn('lost_found', 'images')) {
            Schema::table('lost_found', function (Blueprint $table) {
                $table->json('images')->nullable()->after('comments');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lost_found') && Schema::hasColumn('lost_found', 'images')) {
            Schema::table('lost_found', function (Blueprint $table) {
                $table->dropColumn('images');
            });
        }
    }
};
