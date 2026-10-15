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
        Schema::table('packaged_attractions', function (Blueprint $table) {
            $table->string('country')->nullable()->after('name');
            $table->string('city')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packaged_attractions', function (Blueprint $table) {
            $table->dropColumn(['country', 'city']);
        });
    }
};
