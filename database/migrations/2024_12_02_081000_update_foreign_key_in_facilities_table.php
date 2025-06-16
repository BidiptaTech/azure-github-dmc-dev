<?php

use Brick\Math\BigInteger;
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
        Schema::table('facilities', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['category_id']);
        });

        Schema::table('facilities', function (Blueprint $table) {

                $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            //
            if (Schema::hasColumn('categories', 'category_id')) {
                $table->dropUnique(['category_id']); // Drop unique constraint
                $table->dropColumn('category_id'); // Drop the column
            }
        });
    }
};
