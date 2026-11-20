<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'is_verify')) {
            Schema::table('orders', function (Blueprint $table) {
                // 0 = not initiated, 2 = refund initiated (hold), 1 = verified/accepted
                $table->smallInteger('is_verify')->default(0)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'is_verify')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('is_verify');
            });
        }
    }
};
