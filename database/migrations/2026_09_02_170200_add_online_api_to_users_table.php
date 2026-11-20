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
        if (! Schema::hasColumn('users', 'online_api')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('online_api')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'online_api')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('online_api');
            });
        }
    }
};
