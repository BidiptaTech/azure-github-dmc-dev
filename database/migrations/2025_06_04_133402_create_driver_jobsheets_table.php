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
        Schema::create('jobsheets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('jobsheet_id');
            $table->unsignedBigInteger('dmc_id');
            $table->unsignedBigInteger('tour_id');
            $table->date('date');
            $table->json('data')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('dmc_id')->references('userId')->on('users')->onDelete('cascade');
            // Remove the tour_id foreign key constraint since tour_id is not unique in tours table
            $table->foreign('created_by')->references('userId')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobsheets');
    }
};
