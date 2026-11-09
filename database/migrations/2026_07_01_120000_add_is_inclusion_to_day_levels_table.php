<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('day_levels', function (Blueprint $table) {
            if (! Schema::hasColumn('day_levels', 'is_inclusion')) {
                $table->boolean('is_inclusion')->default(false)->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('day_levels', function (Blueprint $table) {
            if (Schema::hasColumn('day_levels', 'is_inclusion')) {
                $table->dropColumn('is_inclusion');
            }
        });
    }
};
