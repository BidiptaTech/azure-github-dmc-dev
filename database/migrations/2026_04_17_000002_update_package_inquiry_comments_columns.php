<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add new column package_inquiry_id
        Schema::table('package_inquiry_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('package_inquiry_comments', 'package_inquiry_id')) {
                $table->bigInteger('package_inquiry_id')->nullable()->unique()->after('id');
            }
        });

        // 2) Backfill from enquiry_id if present
        if (Schema::hasColumn('package_inquiry_comments', 'enquiry_id')) {
            DB::statement("UPDATE package_inquiry_comments SET package_inquiry_id = enquiry_id WHERE package_inquiry_id IS NULL");
        }

        // 3) Drop package_id if present (not needed)
        Schema::table('package_inquiry_comments', function (Blueprint $table) {
            if (Schema::hasColumn('package_inquiry_comments', 'package_id')) {
                $table->dropColumn('package_id');
            }
        });

        // 4) Drop old enquiry_id column if present
        Schema::table('package_inquiry_comments', function (Blueprint $table) {
            if (Schema::hasColumn('package_inquiry_comments', 'enquiry_id')) {
                $table->dropUnique(['enquiry_id']);
                $table->dropColumn('enquiry_id');
            }
        });

        // 5) Make package_inquiry_id non-null going forward
        // (Some DBs require raw SQL for altering nullability; keep nullable to avoid doctrine/dbal dependency.)
    }

    public function down(): void
    {
        // Best-effort rollback (does not restore old data perfectly)
        Schema::table('package_inquiry_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('package_inquiry_comments', 'enquiry_id')) {
                $table->bigInteger('enquiry_id')->nullable()->unique()->after('id');
            }
        });

        DB::statement("UPDATE package_inquiry_comments SET enquiry_id = package_inquiry_id WHERE enquiry_id IS NULL");

        Schema::table('package_inquiry_comments', function (Blueprint $table) {
            if (Schema::hasColumn('package_inquiry_comments', 'package_inquiry_id')) {
                $table->dropUnique(['package_inquiry_id']);
                $table->dropColumn('package_inquiry_id');
            }
        });
    }
};

