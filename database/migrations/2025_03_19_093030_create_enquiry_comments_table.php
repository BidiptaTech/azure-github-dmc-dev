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
        Schema::create('enquiry_comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('enquiry_id')->unique(); // Big Integer, Unique, Not Auto-Increment
            $table->bigInteger('tour_id');
            $table->bigInteger('booking_id');
            $table->integer('sender_id');
            $table->string('sender_type');
            $table->integer('receiver_id');
            $table->string('receiver_type'); // Removed duplicate
            $table->string('current_position');
            $table->decimal('amount', 10, 2)->nullable(); // Added amount column
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->text('comment')->nullable(); // Added comment column
            $table->integer('status');
            $table->timestamps();

            // // Corrected foreign key constraints
            // $table->foreign('tour_id')->references('tour_id')->on('tours')->onDelete('cascade');
            // $table->foreign('booking_id')->references('booking_id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiry_comments');
    }
};
