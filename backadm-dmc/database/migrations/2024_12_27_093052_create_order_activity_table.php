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
        Schema::create('order_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id'); 
            $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
            $table->string('type');
            $table->integer('status');
            $table->string('activity');
            $table->integer('agent_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_activity');
    }
};
