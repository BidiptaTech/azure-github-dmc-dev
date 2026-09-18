<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tours', 'discount_amount')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->decimal('discount_amount', 12, 2)->nullable()->after('discount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tours', 'discount_amount')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->dropColumn('discount_amount');
            });
        }
    }
};
