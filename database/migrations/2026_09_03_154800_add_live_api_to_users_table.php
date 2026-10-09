<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'live_api')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('live_api')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'live_api')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('live_api');
            });
        }
    }
};
