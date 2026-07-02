<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'currency')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country')) {
                $table->string('currency', 3)->nullable()->after('country');
            } else {
                $table->string('currency', 3)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'currency')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
