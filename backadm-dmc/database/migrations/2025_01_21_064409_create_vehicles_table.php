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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vehicle_id')->unsigned()->unique();
            $table->string('vehicle_name');
            $table->string('vehicle_type');
            $table->string('vehicle_model');
            $table->year('model_year');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('sitting_capacity'); // Dropdown 1 to 50
            $table->string('vehicle_icon')->nullable();
            $table->integer('is_available')->default(true); // Checkbox
            $table->softDeletes(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
