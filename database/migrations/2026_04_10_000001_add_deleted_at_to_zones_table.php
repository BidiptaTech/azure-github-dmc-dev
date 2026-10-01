<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            if (!Schema::hasColumn('zones', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            if (Schema::hasColumn('zones', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};

