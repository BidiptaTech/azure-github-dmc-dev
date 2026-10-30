<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'refund_amount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('refund_amount', 18, 2)->nullable()->after('refunded');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'refund_amount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('refund_amount');
            });
        }
    }
};
