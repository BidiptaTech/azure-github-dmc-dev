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
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('room_type_id')->nullable(); // Ensure it's an unsigned big integer
            $table->foreign('room_type_id')->references('id')->on('room_type')->onDelete('cascade'); // Foreign key definition
            
            $table->integer('is_complete')->nullable(); // Add nullable column for completion status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            //
        });
    }
};
