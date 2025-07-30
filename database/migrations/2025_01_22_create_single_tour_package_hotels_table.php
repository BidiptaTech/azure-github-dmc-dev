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
        Schema::create('single_tour_package_hotels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('hotel_id');
            $table->string('room_type')->nullable();
            $table->string('bed_type')->nullable();
            $table->integer('number_of_rooms')->default(1);
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('nights')->default(1);
            $table->decimal('room_rate', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->text('special_requests')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('package_id')->references('id')->on('single_tour_packages')->onDelete('cascade');
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('single_tour_package_hotels');
    }
}; 