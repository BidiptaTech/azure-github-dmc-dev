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
        Schema::create('room_type', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('breakfast', 8, 2)->nullable(); 
            $table->decimal('lunch', 8, 2)->nullable(); 
            $table->decimal('dinner', 8, 2)->nullable(); 
            $table->decimal('extra_bed', 8, 2)->nullable();
            $table->json('facilities')->nullable(); 
            $table->integer('inserted_by_user');
            $table->text('description')->nullable(); 
            $table->integer('status');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_type');
    }
};
