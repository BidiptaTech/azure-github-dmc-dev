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
        Schema::create('roomrates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id'); // Add the hotel_id column
            $table->integer('category');
            $table->enum('rate_type', ['1', '2'])->comment('1=>weekday, 2=>weekend');
            $table->enum('room_type', ['1', '2','3'])->comment('1=>single, 2=>double, 3=>triple');
            $table->decimal('single_rate', 8, 2)->nullable(); 
            $table->decimal('double_rate', 8, 2)->nullable(); 
            $table->decimal('triple_rate', 8, 2)->nullable(); 
            $table->decimal('kids_below_6', 8, 2)->nullable(); 
            $table->decimal('kids_above_6', 8, 2)->nullable(); 
            $table->decimal('breakfast', 8, 2)->nullable(); 
            $table->decimal('lunch', 8, 2)->nullable(); 
            $table->decimal('dinner', 8, 2)->nullable(); 
            $table->decimal('breakfast_kids_below_6', 8, 2)->nullable(); 
            $table->decimal('lunch_kids_below_6', 8, 2)->nullable(); 
            $table->decimal('dinner_kids_below_6', 8, 2)->nullable(); 
            $table->decimal('extra_bed', 8, 2)->nullable();
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade'); 
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roomrates');
    }
};
