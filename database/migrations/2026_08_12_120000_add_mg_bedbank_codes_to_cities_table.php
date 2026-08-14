<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('mg_country_code', 10)->nullable()->after('country');
            $table->string('mg_city_code', 20)->nullable()->after('mg_country_code');

            $table->index(['mg_country_code', 'mg_city_code'], 'cities_mg_bedbank_codes_index');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex('cities_mg_bedbank_codes_index');
            $table->dropColumn(['mg_country_code', 'mg_city_code']);
        });
    }
};
