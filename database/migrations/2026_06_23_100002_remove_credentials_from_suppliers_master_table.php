<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers_master', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers_master', 'api_url')) {
                $table->dropColumn(['api_url', 'api_key', 'api_secret']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers_master', function (Blueprint $table) {
            $table->string('api_url')->nullable();
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
        });
    }
};
