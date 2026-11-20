<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'demo_api')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('demo_api')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'live_api')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('live_api');
            });
        }
    }
};
