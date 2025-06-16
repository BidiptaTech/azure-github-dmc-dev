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
        Schema::create('hotel_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('icon', 191)->nullable();
            $table->boolean('status')->default(true);
            $table->bigInteger('category_id')->unique()->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_categories');
    }
};
