<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'travclicks_refunded')) {
                $table->boolean('travclicks_refunded')->default(false)->after('refunded');
            }
            if (! Schema::hasColumn('orders', 'travclicks_refund_amount')) {
                $table->decimal('travclicks_refund_amount', 18, 2)->nullable()->after('travclicks_refunded');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'travclicks_refund_amount')) {
                $table->dropColumn('travclicks_refund_amount');
            }
            if (Schema::hasColumn('orders', 'travclicks_refunded')) {
                $table->dropColumn('travclicks_refunded');
            }
        });
    }
};
