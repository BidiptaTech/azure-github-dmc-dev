<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_keywords', function (Blueprint $table) {
            $table->text('keyword')->change();
        });

        DB::table('ai_keywords')
            ->where('category', 'vehicle')
            ->update(['category' => 'transport']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ai_keywords')
            ->where('category', 'transport')
            ->update(['category' => 'vehicle']);

        Schema::table('ai_keywords', function (Blueprint $table) {
            $table->string('keyword')->change();
        });
    }
};
