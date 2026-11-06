<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ai_response')) {
                $table->string('ai_response', 3)->nullable()->after('guide_pax');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'ai_response')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('ai_response');
            });
        }
    }
};
