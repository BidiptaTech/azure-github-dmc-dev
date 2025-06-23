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
        Schema::create('hotel_policy', function (Blueprint $table) {
            $table->id();
            $table->string('name');    // For the hotel policy name
            $table->text('policy');    // For the policy description
            $table->string('file');    // Store the file path (it should be a string, not file)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_policy');
    }
};
