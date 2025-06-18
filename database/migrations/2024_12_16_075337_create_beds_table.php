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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('room_id'); // Use appropriate data type
            $table->foreign('room_id')->references('room_id')->on('rooms')->onDelete('cascade');
            $table->integer('no_of_rooms');
            $table->integer('max_occupancy');
            $table->integer('adult_count')->nullable();
            $table->integer('child_count')->nullable();
            $table->boolean('extra_bed')->nullable();
            $table->decimal('extra_bed_price', 8, 2)->nullable();
            $table->boolean('baby_cot')->nullable();
            $table->decimal('baby_cot_price', 8, 2)->nullable();
            $table->integer('is_active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
