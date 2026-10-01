<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('default_value', function (Blueprint $table) {
            if (!Schema::hasColumn('default_value', 'country')) {
                $table->string('country')->nullable()->after('dmc_id');
            }
            if (!Schema::hasColumn('default_value', 'city')) {
                $table->string('city')->nullable()->after('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('default_value', function (Blueprint $table) {
            if (Schema::hasColumn('default_value', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('default_value', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
