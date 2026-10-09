<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('agencies', 'special_discount_type')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->string('special_discount_type', 20)
                    ->default('percentage')
                    ->comment('percentage|flat')
                    ->after('special_discount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('agencies', 'special_discount_type')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->dropColumn('special_discount_type');
            });
        }
    }
};
