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
            if (! Schema::hasColumn('orders', 'country')) {
                $table->string('country')->nullable()->after('type');
            }
            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 10)->nullable()->after('country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('orders', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
