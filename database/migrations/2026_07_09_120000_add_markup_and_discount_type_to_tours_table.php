<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            // Column order: discount, discount_type, discount_amount, markup, markup_type, markup_amount
            if (!Schema::hasColumn('tours', 'discount_type')) {
                $table->string('discount_type', 20)->nullable()->after('discount');
            }
            if (!Schema::hasColumn('tours', 'markup')) {
                $table->boolean('markup')->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('tours', 'markup_type')) {
                $table->string('markup_type', 20)->nullable()->after('markup');
            }
            if (!Schema::hasColumn('tours', 'markup_amount')) {
                $table->decimal('markup_amount', 12, 2)->nullable()->after('markup_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            foreach (['markup_amount', 'markup_type', 'markup', 'discount_type'] as $column) {
                if (Schema::hasColumn('tours', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
