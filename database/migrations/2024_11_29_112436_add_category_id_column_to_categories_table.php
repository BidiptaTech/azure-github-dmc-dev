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
        if (DB::getSchemaBuilder()->hasColumn('categories', 'category_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('category_id');
            });
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->bigInteger('category_id')->unique()->after('id');
        });
    }

    public function down(): void
    {
        if (DB::getSchemaBuilder()->hasColumn('categories', 'category_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique(['category_id']); // Drop unique constraint
                $table->dropColumn('category_id'); // Drop the column
            });
        }
    }
};
