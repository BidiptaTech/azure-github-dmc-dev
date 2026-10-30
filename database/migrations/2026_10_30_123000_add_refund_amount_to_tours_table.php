<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tours', 'refund_amount')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->decimal('refund_amount', 18, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tours', 'refund_amount')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->dropColumn('refund_amount');
            });
        }
    }
};
