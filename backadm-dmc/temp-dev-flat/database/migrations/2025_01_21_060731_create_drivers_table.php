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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('driver_id')->unsigned()->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->text('address');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('mobile_number');
            $table->string('email')->unique();
            $table->dateTime('activation_in');
            $table->string('password');
            $table->string('vehicle_plate_no');
            $table->string('vehicle_model');
            $table->year('model_year');
            $table->unsignedBigInteger('vehicle_id');
            $table->integer('sharable')->default(false);
            $table->time('night_time'); // Set in main settings
            $table->unsignedBigInteger('operational_country_id');
            $table->integer('is_active');
            // Bank details
            $table->string('bank_account_holder_name');
            $table->string('account_number');
            $table->string('bank_name')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('swift_code')->nullable();
            $table->softDeletes(); // For soft deletion
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicles')->onDelete('cascade');
            $table->foreign('operational_country_id')->references('operational_country_id')->on('operational_countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
