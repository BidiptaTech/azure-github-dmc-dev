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
        Schema::create('rates', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('rate_id')->unique(); // Unique rate ID
            $table->bigInteger('hotel_id'); // Foreign key for hotel ID
            $table->string('event'); // Event name
            $table->string('event_type'); // Event type (e.g., Fair Date, Blackout Date)
            $table->double('price'); // Room price
            $table->date('start_date'); // Start date for the event or rate
            $table->date('end_date'); // End date for the event or rate
            $table->timestamps(); // Created at and updated at columns
            $table->softDeletes(); // Soft delete column for deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
