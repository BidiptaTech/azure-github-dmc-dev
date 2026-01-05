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
        Schema::create('guide_languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guide_id');
            $table->string('language')->nullable();
            $table->string('proficiency')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Enables soft delete functionality

            // Adding foreign key constraint
            $table->foreign('guide_id')->references('id')->on('guides')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_languages');
    }
};
