<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_settings', function (Blueprint $table) {
            $table->longText('quotation_information')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_settings', function (Blueprint $table) {
            $table->dropColumn('quotation_information');
        });
    }
};

