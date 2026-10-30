<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers_master', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers_master', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Allow re-adding a country mapping after soft-delete.
        try {
            Schema::table('suppliers_master', function (Blueprint $table) {
                $table->dropUnique(['country_id']);
            });
        } catch (\Throwable) {
            // Unique index may already be removed.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('suppliers_master', function (Blueprint $table) {
                $table->unique('country_id');
            });
        } catch (\Throwable) {
            // Index may already exist.
        }

        Schema::table('suppliers_master', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers_master', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
