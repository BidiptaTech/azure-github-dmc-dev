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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('hourly_price_1', 10, 2)->nullable()->after('cost_per_hour');
            $table->decimal('hourly_price_2', 10, 2)->nullable()->after('hourly_price_1');
            $table->decimal('hourly_price_3', 10, 2)->nullable()->after('hourly_price_2');
            $table->decimal('hourly_price_4', 10, 2)->nullable()->after('hourly_price_3');
            $table->decimal('hourly_price_5', 10, 2)->nullable()->after('hourly_price_4');
            $table->decimal('hourly_price_6', 10, 2)->nullable()->after('hourly_price_5');
            $table->decimal('hourly_price_7', 10, 2)->nullable()->after('hourly_price_6');
            $table->decimal('hourly_price_8', 10, 2)->nullable()->after('hourly_price_7');
            $table->decimal('hourly_price_9', 10, 2)->nullable()->after('hourly_price_8');
            $table->decimal('hourly_price_10', 10, 2)->nullable()->after('hourly_price_9');
            $table->decimal('hourly_price_11', 10, 2)->nullable()->after('hourly_price_10');
            $table->decimal('hourly_price_12', 10, 2)->nullable()->after('hourly_price_11');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'hourly_price_1',
                'hourly_price_2',
                'hourly_price_3',
                'hourly_price_4',
                'hourly_price_5',
                'hourly_price_6',
                'hourly_price_7',
                'hourly_price_8',
                'hourly_price_9',
                'hourly_price_10',
                'hourly_price_11',
                'hourly_price_12',
            ]);
        });
    }
};
