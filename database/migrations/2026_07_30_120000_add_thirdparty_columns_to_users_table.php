<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'thirdparty')) {
                $table->enum('thirdparty', ['yes', 'no'])->default('no')->after('company_name');
            }

            if (!Schema::hasColumn('users', 'thirdparty_enabled')) {
                $table->enum('thirdparty_enabled', ['yes', 'no'])->default('no')->after('thirdparty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'thirdparty_enabled')) {
                $table->dropColumn('thirdparty_enabled');
            }

            if (Schema::hasColumn('users', 'thirdparty')) {
                $table->dropColumn('thirdparty');
            }
        });
    }
};
