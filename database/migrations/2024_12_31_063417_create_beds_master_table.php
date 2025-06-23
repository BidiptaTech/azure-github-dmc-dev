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
        Schema::create('beds_master', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('no_of_king_bed');
            $table->integer('no_of_queen_bed');
            $table->integer('no_of_twin_bed');
            $table->integer('no_of_bunk_bed');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds_master');
    }
};
