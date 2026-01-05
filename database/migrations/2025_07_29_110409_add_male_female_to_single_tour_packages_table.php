<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('single_tour_packages', function (Blueprint $table) {
            $table->integer('male')->default(0)->after('adults');
            $table->integer('female')->default(0)->after('male');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('single_tour_packages', function (Blueprint $table) {
            $table->dropColumn(['male', 'female']);
        });
    }
};
