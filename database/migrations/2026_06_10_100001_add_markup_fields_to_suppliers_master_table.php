<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers_master', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers_master', 'service_type')) {
                $table->string('service_type')->default('hotels')->after('code');
            }
            if (! Schema::hasColumn('suppliers_master', 'markup_type')) {
                $table->string('markup_type')->default('percentage')->after('service_type');
            }
            if (! Schema::hasColumn('suppliers_master', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('markup_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers_master', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers_master', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('suppliers_master', 'markup_type')) {
                $table->dropColumn('markup_type');
            }
            if (Schema::hasColumn('suppliers_master', 'service_type')) {
                $table->dropColumn('service_type');
            }
        });
    }
};
