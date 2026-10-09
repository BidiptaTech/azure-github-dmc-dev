<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            // keep it nullable + indexed for SoftDeletes
            $table->softDeletes();
        });

        // Allow re-adding a language after soft-delete, while still preventing duplicates for active rows.
        Schema::table('languages', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['name', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropUnique(['name', 'deleted_at']);
            $table->unique(['name']);
            $table->dropSoftDeletes();
        });
    }
};

