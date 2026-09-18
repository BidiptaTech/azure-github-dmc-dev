<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist room profit type (% / flat) and amount on each room row.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'profit_type')) {
                $table->string('profit_type', 20)->nullable()->default('percentage')->after('varient_price');
            }
            if (!Schema::hasColumn('rooms', 'profit_amount')) {
                $table->decimal('profit_amount', 50, 2)->nullable()->default(0)->after('profit_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'profit_amount')) {
                $table->dropColumn('profit_amount');
            }
            if (Schema::hasColumn('rooms', 'profit_type')) {
                $table->dropColumn('profit_type');
            }
        });
    }
};
