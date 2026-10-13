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
        Schema::table('multi_restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('multi_restaurants', 'vehicle')) {
                $table->boolean('vehicle')->default(false)->after('status');
            }
            if (! Schema::hasColumn('multi_restaurants', 'guide')) {
                $table->boolean('guide')->default(false)->after('vehicle');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multi_restaurants', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('multi_restaurants', 'vehicle') ? 'vehicle' : null,
                Schema::hasColumn('multi_restaurants', 'guide') ? 'guide' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
