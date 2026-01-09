<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add Group Pax and Markup columns to users table for DMC-level settings
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add columns that don't already exist
            // markup_type and markup_price already exist, so we only add:
            if (!Schema::hasColumn('users', 'group_pax')) {
                $table->integer('group_pax')->nullable()->after('role_id');
            }
            if (!Schema::hasColumn('users', 'markup_service')) {
                $table->string('markup_service')->nullable()->after('markup_type'); // all_service, hotels_only, others_only
            }
            // Note: markup_value will use the existing markup_price column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'group_pax')) {
                $table->dropColumn('group_pax');
            }
            if (Schema::hasColumn('users', 'markup_service')) {
                $table->dropColumn('markup_service');
            }
        });
    }
};

