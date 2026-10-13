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
        if (! Schema::hasColumn('orders', 'booking_details')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('booking_details')->nullable()->after('cost_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'booking_details')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('booking_details');
            });
        }
    }
};
