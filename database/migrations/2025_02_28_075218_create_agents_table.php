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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('agent_id')->unique(); // Big Integer, Unique, Not Auto-Increment
            $table->string('salutation', 10)->nullable(); // Mr, Mrs, Dr, etc.
            $table->string('name'); // Full Name
            $table->string('email')->unique(); // Unique Email
            $table->string('phone')->nullable(); // Contact Number
            $table->string('agent_image')->nullable(); // Profile Image
            $table->string('image')->nullable(); // Additional Image
            $table->string('sales_manager_dmc')->nullable(); // DMC Sales Manager
            $table->string('country')->nullable(); // Country
            $table->string('password'); // Password (Hashed)
            $table->softDeletes(); // Soft Delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
