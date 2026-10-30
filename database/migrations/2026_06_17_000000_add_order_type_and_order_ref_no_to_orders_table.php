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
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type')->nullable();
            }
            if (! Schema::hasColumn('orders', 'order_ref_no')) {
                $table->string('order_ref_no')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'order_type')) {
                $table->dropColumn('order_type');
            }
            if (Schema::hasColumn('orders', 'order_ref_no')) {
                $table->dropColumn('order_ref_no');
            }
        });
    }
};
