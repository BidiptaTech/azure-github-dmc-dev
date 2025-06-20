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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->string('room_number');
            $table->integer('is_available');
            $table->integer('max_capacity');
            $table->integer('cancellation_type');
            $table->integer('cancellation_charge');
            $table->json('images')->nullable(); 
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade'); 
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
        Schema::dropIfExists('rooms');
    }
};
