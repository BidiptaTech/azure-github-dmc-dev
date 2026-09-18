<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (!Schema::hasColumn('tours', 'currency_markups')) {
                $table->json('currency_markups')->nullable()->after('markup_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'currency_markups')) {
                $table->dropColumn('currency_markups');
            }
        });
    }
};
