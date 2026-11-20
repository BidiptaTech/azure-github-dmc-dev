<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('miscellaneous_items', function (Blueprint $table) {
            if (!Schema::hasColumn('miscellaneous_items', 'country')) {
                $table->string('country', 191)->nullable()->after('description');
            }
            if (!Schema::hasColumn('miscellaneous_items', 'city')) {
                $table->string('city', 191)->nullable()->after('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('miscellaneous_items', function (Blueprint $table) {
            if (Schema::hasColumn('miscellaneous_items', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('miscellaneous_items', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
