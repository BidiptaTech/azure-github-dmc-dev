<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'cost_price')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('cost_price')->nullable()->after('data');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'cost_price')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('cost_price');
            });
        }
    }
};
