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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'auto_cancel_status')) {
                $table->tinyInteger('auto_cancel_status')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'auto_cancel_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('auto_cancel_status');
            });
        }
    }
};
