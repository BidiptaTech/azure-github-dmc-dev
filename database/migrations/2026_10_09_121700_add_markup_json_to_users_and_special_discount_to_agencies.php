<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'markup_json')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('markup_json')->nullable()->after('markup_price_flight');
            });
        }

        if (! Schema::hasColumn('agencies', 'special_discount')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->decimal('special_discount', 12, 2)->default(0)->after('sales_dmc');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'markup_json')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('markup_json');
            });
        }

        if (Schema::hasColumn('agencies', 'special_discount')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->dropColumn('special_discount');
            });
        }
    }
};
